<?php

/*
 *  Who cares?
 */

namespace Chimp;

/**
 * Description of lists
 *
 * @author michael
 */
use WC\DB\Server;
use WC\UserSession;
use WC\Valid;

use Pcan\DB\PageInfo;
use Pcan\DB\Member;
use Pcan\DB\MemberEmail;



use Chimp\DB\ChimpEntry;
use Chimp\DB\ChimpLists;

function gen_array(string &$outs, $prop, &$obj) {
    if ($prop === "_links") {
        return;
    }
    if (!empty($prop)) {
        $outs .= "<li>ARRAY $prop</li>";
    }
    $outs .= "<ul>" . PHP_EOL;
    foreach($obj as $key => $value) {
        if (is_string($value)) {
            $outs .= "<li>" . $key . ": " . $value . "</li>" . PHP_EOL;
        }
        else if (is_object($value)) {
            gen_object($outs, $key, $value);
        }
        else if (is_array($value)) {
            gen_array($outs, $key, $value);
        }
    }
    $outs .= "</ul>" . PHP_EOL;    
}
function gen_object(string &$outs, $prop, $obj) {
    $outs .= "<li><b>$prop</b></li>";
    $outs .= "<ul>" . PHP_EOL;
    foreach($obj as $key => $value) {
        if (is_string($value)) {
            $outs .= "<li>" . $key . ": " . $value . "</li>" . PHP_EOL;
        }
        else if (is_object($value)) {
            gen_object($outs, $key, $value);
        }
        else if (is_array($value)) {
            gen_array($outs, $key, $value);
        }
    }
    $outs .= "</ul>" . PHP_EOL;
}
class Lists extends \WC\Controller {
    //put your code here
    public function getAllowRole() {
        return 'Chimp';
    }
    public function beforeRoute() {
        if (!$this->auth()) {
            return false;
        }
    }
    public function index($f3, $args) {
        

        // get id of first list
        /*$first = $lists->lists[0];
        $id = $first->id;
        $stats = $first->stats;

        $total = $stats->member_count + $stats->unsubscribe_count + $stats->cleaned_count;*/

        $view = $this->view;
        $view->content = 'chimp/lists.phtml';
        $view->data = ChimpLists::allLists();
        $view->assets(['bootstrap']);
        echo $view->render();
    }
    
    private function member_list($f3, $args) {
        $view = $this->view;
        $view->content = 'chimp/members.phtml';
        $view->assets(['bootstrap']);
       
        echo $view->render();
    }
    public function members($f3, $args) {
        $list = ChimpLists::getById($id);
        $db = Server::db();
        $req = &$f3->ref('REQUEST');
        $pageAll = Valid::toStr($req,'page','all');
        if (is_numeric($pageAll)) {
            $page = Valid::toint($req,'page',1);
        }
        else {
            $page = 0;
        }
        
        $orderby = Valid::toStr($req, 'orderby', null);
        $order_field = Member::viewOrderBy($this->view, $orderby);
$sql = <<<EOD
select SQL_CALC_FOUND_ROWS M.*, ME.email_address, CE.status as mcstatus from member M 
 join member_email ME on ME.memberid = M.id
 join chimp_entry CE on CE.emailid = ME.id
        where CE.listid = :id
EOD;
        $id = $args['lid'];
        $params = [":id" => $id];
        $sql .= " order by " . $order_field;
        if ($page > 0) {
            $sql .= " limit :start, :ct";
            $pgsize = 16;
            $params[':ct'] = $pgsize;
            $params[':start'] = ($page - 1) * $pgsize;
        }
        
        $results = $db->exec($sql,$params);
        $maxrows = $db->exec("SELECT FOUND_ROWS() as total");
        $total = $maxrows[0]['total'];
        if ($page === 0) {
            $pgsize = $total;
            $page = 1;
        }
        $paginator = new PageInfo($page, $pgsize, $results, $total);
        $view = $this->view;
        $view->page = $paginator;
        $view->url = "/chimp/mlist/$id";
        $view->pgsize = $pgsize;
        $this->member_list($f3, $args);
    }
    private function errorPDO($e, $blogid) {
        $err = $e->errorInfo;
        $this->flash($err[0] . ": " . $err[1]);
        UserSession::reroute('/admin/blog/edit/' . $blogid);
        return false;
    }
    public function downsync($f3, $args) {
        $all = ChimpLists::sync(); // sync lists
        // sync each list
        try {
            foreach($all as $rec) {
                $rec->syncMembers();
            }
           
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
        $this->index($f3, $args);
    }
    
    public function sync($f3, $args) {
        $id = $args['lid'];
        $list = ChimpLists::getById($id);
        $list->syncMembers();
       
        $this->members($f3,$args);
        
       
    }
    
}
