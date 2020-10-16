<?php

/*
 *  Who cares?
 */

namespace App\Controllers;

/**
 * Description of lists
 *
 * @author michael
 */
use WC\Db\DbQuery;
use WC\UserSession;
use WC\Valid;

use App\Link\PageInfo;
use App\Models\Member;
use App\Models\MemberEmail;
use App\Models\ChimpLists;

use Phalcon\Db\Column;

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

class ChimpListsController extends BaseController {
use \WC\Mixin\Auth;
use \WC\Mixin\ViewPhalcon;
use \App\Chimp\ChimpData;
use \App\Link\MemberData;

    //put your code here
    public function getAllowRole() {
        return 'Chimp';
    }

    public function indexAction() {
        $view = $this->getView();
        $view->m->data = $this->allLists();
        return  $this->render('chimp','lists');
    }
    

    public function membersAction($id) {
        $list = ChimpLists::findFirstById($id);
       
        $req = $_REQUEST;
        $pageAll = Valid::toStr($req,'page','all');
        $pgsize = Valid::toStr($req, 'pgsize', null);
        if (is_numeric($pageAll)) {
            $page = Valid::toint($req,'page',1);
            if (empty($pgsize)) {
                $pgsize = 20;
            }
        }
        else {
            $page = 0;
        }
        $view = $this->getView();
        $m = $view->m;
        $orderby = Valid::toStr($req, 'orderby', null);
        
        $order_field = self::getOrderBy($m, $orderby);
$sql = <<<EOD
select count(*) over() as full_count, M.*, ME.email_address, CE.status as mcstatus from member M 
 join member_email ME on ME.memberid = M.id
 join chimp_entry CE on CE.emailid = ME.id
        where CE.listid = :id
EOD;

        $params = ["id" => $id];
        $sql .= " order by " . $order_field;
        
        $bind = ['id' => Column::BIND_PARAM_INT];
        if ($page > 0) {
            $sql .= " LIMIT  :ct OFFSET :start  ";
            $params['ct'] = $pgsize;
            $params['start'] = ($page - 1) * $pgsize;
            $bind['ct'] = Column::BIND_PARAM_INT;
            $bind['start'] = Column::BIND_PARAM_INT;
        }
         $qry = new DbQuery($this->db);
        $results = $qry->arraySet($sql,$params, $bind);
        $total = (count($results) > 0) ? $results[0]['full_count'] : 0;
        if ($page === 0) {
            $pgsize = $total;
            $page = 1;
        }
        $paginator = new PageInfo($page, $pgsize, $results, $total);

        $m->page = $paginator;
        $m->url = "/admin/chimp/mlist/$id";
        $m->pgsize = $pgsize;
        return $this->render('chimp','members');
    }
    private function errorPDO($e, $blogid) {
        $err = $e->errorInfo;
        $this->flash($err[0] . ": " . $err[1]);
        UserSession::reroute('/admin/blog/edit/' . $blogid);
        return false;
    }
    public function downsyncAction() {
        $all = $this->chimp_sync(); // sync lists
        // sync each list
        try {
            foreach($all as $rec) {
                $this->syncMembers($rec);
            }
           
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
        return $this->indexAction();
    }
    
}
