<?php
declare(strict_types=1);
namespace App\Controllers;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model;
use App\Models\Donation;
use App\Models\Member;
use WC\Valid;
use WC\Db\DbQuery;
use Phalcon\Db\Column;

class DonationController extends BaseController
{
    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
    
    
    private function getPurposeList() : array {
        return [ 'member' => 'Membership', 'donate' => 'Donation'];
    }
    public function getAllowRole() {
        return 'Admin';
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        return $this->render('donation','index');
    }

    /**
     * Searches for donation
     */
    public function searchAction()
    {
        $req = $_GET;
        $fromDate = Valid::toDate($req,"fromdate", null);
        $toDate = Valid::toDate($req,"todate", null);
        $purpose = Valid::toStr($req, "purpose");
        
        
 $sql = <<<EOS
SELECT  D.* , M.fname, M.lname from donation D 
    join member M on M.id = D.memberid
EOS;
        $db = $this->dbq;
        if (!empty($purpose)) {
            $db->whereCondition("D.purpose = ?", $purpose);
        }
        if (!empty($fromDate)) {
            $db->whereCondition("D.member_date >= ?",  $fromDate);
        }
        if (!empty($toDate)) {
             $db->whereCondition("D.member_date < ?",  $toDate);
        }
        $db->order("member_date asc");
        $results = $db->queryAA($sql);
        
        if (empty($results)) {
            $this->flash->notice("The search did not find any donation");

            $this->dispatcher->forward([
                "controller" => "donation",
                "action" => "index"
            ]);

            return;
        }
        $m = $this->getViewModel();
        $m->page = $results;
        $criteria = $db->getCriteria();
        $m->criteria = $criteria;
        
        return $this->render("donation", "search");
    }

    /**
     * Displays the creation form
     */
    public function newAction()
    {
        //
    }

    /**
     * Edits a donation
     *
     * @param string $id
     */
    public function editAction($id)
    {
       
        
        if (!$this->request->isPost()) {
            $m = $this->getViewModel();
            
             $donation = Donation::findFirstById($id);
            if (!$donation) {
                $this->flash->error("donation was not found");

                $this->dispatcher->forward([
                    'controller' => "donation",
                    'action' => 'index'
                ]);

                return;
            }
            // get member
            //$member = $donation->getRelated("App\\Models\\Member");
            
            $m->member = Member::findFirstById($donation->memberid);
            
            $m->donation = $donation;
            $m->purpose =$this-> getPurposeList();
            return $this->render('donation','edit');

            
        }
    }

    /**
     * Creates a new donation
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "donation",
                'action' => 'index'
            ]);

            return;
        }

        $donation = new Donation();
        $donation->setmemberid($this->request->getPost("memberid", "int"));
        $donation->setamount($this->request->getPost("amount", "int"));
        $donation->setpurpose($this->request->getPost("purpose", "int"));
        $donation->setcreatedAt($this->request->getPost("created_at", "int"));
        $donation->setmemberDate($this->request->getPost("member_date", "int"));
        $donation->setDetail($this->request->getPost("detail", "string"));

        if (!$donation->save()) {
            foreach ($donation->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "donation",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("donation was created successfully");

        $this->dispatcher->forward([
            'controller' => "donation",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a donation edited
     *
     */
    public function saveAction()
    {

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "donation",
                'action' => 'index'
            ]);

            return;
        }
        $post = $_POST;
        $id = Valid::toInt($post, "id");
        $donation = Donation::findFirstById($id);

        if (!$donation) {
            $this->flash->error("donation does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "donation",
                'action' => 'index'
            ]);

            return;
        }
        $donation->memberid = Valid::toInt($post, 'memberid');
        $donation->amount = Valid::toMoney($post, 'amount');
        $donation->purpose = Valid::toStr($post, 'purpose');
        $donation->created_at = Valid::toDateTime($post,'created_at');
        $donation->member_date = Valid::toDate($post, 'member_date');
        $donation->detail = Valid::toStr($post, 'detail');

        if (!$donation->update()) {

            foreach ($donation->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "donation",
                'action' => 'edit',
                'params' => [$donation->getId()]
            ]);

            return;
        }

        $this->flash->success("donation was updated successfully");

        $this->dispatcher->forward([
            'controller' => "donation",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a donation
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $donation = Donation::findFirstById($id);
        if (!$donation) {
            $this->flash->error("donation was not found");

            $this->dispatcher->forward([
                'controller' => "donation",
                'action' => 'index'
            ]);

            return;
        }

        if (!$donation->delete()) {

            foreach ($donation->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "donation",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("donation was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "donation",
            'action' => "index"
        ]);
    }
}
