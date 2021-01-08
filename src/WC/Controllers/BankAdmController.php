<?php
namespace WC\Controllers;

use \WC\Valid;
/**
 * Make a transaction report from imported CSV 
 *
 * @author michael
 */
class BankAdmController extends BaseController

{
    use \WC\Mixin\ViewPhalcon;
    
    private function getCategories() : array {
        return $this->dbq->simpleMap('id', 'shortform', 'bank_category');
        /*
        return [
            0 => '_',
            1 => 'Donation/Membership',
            2 => 'Yearly Expense',
            3 => 'Misc Expense',
            4 => 'Collaboration Expense',
            5 => 'Other income',
            6 => 'In-Out transfer'
        ];*/
    }
    
    public function reportAction() {
        $post = $_POST;
        $todate = new \DateTime(Valid::toDate($post,'todate'));
        $dateinfo = getdate($todate->getTimestamp());
        $end_mon = $dateinfo['mon'];
        $end_year = $dateinfo['year'];
        $end_day = $dateinfo['mday'];
        
        if ($end_mon < 7) {
            $start_year = $end_year - 1;
        }
        else {
            $start_year = $end_year;
        }
        $start_day = 1;
        $start_mon = 7;
        
        $start_period = date(Valid::DATE_FMT, mktime(0,0,0,$start_mon, $start_day, $start_year));
        $end_period = date(Valid::DATE_FMT, mktime(0,0,0,$end_mon, $end_day, $end_year));
        
        $sql = 'select * from bank_report' .
                ' where tdate >= :start ' .
                ' and tdate < :end';
        $dbq = $this->dbq;
        
        $dbq->bindParam('start', $start_period);
        $dbq->bindParam('end', $end_period);
        
        $rows = $dbq->queryOA($sql);
        
        $m = $this->getViewModel();
        $m->rows = $rows;
        $m->category = $this->getCategories();
        $m->start_period = $start_period;
        $m->end_period = $end_period;
        
        if(count($rows) > 0) {
            $first = $rows[0];
            $m->startBalance = $first->balance - $first->delta;
            $last = $rows[count($rows)-1];
            $m->endBalance = $last->balance;
            
            $net = [];
            
            foreach($rows as $t) {
                $cat = $t->category;
                if (isset($net[$cat])) {
                    $net[$cat] += $t->delta;
                }
                else {
                    $net[$cat] = $t->delta;
                }
            }
            
            $m->net = $net;
        }
        return $this->render('report', 'report');
        
    }
    public function postAction() {
        $post = $_POST;
        $rows = $this->dbq->arraySet('select id, category from bank_report');
        $catvalues = [];
        foreach($rows as $row) {
            $catvalues[intval($row['id'])] = intval($row['category']);
        }
        foreach($post as $ix => $val) {
        if (substr($ix,0,4)==='scat') {
            $recid = intval(substr($ix,4));
            $newval = intval($val);
            if ($newval !== $catvalues[$recid]) {
                $sql = "UPDATE bank_report set category = $newval"
                        . " where id = $recid";
                $this->db->execute($sql);
            }
        }
        }
        $this->reroute('/admin/bank/index');
    }
    public function indexAction() {
        $m = $this->getViewModel();
        $m->category = $this->getCategories();
        $m->rows = $this->dbq->objectSet('select * from bank_report');
        return $this->render('report', 'index');
    }
}
