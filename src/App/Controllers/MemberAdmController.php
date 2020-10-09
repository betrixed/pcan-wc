<?php

namespace App\Controllers;

use App\Models\Donation;
use App\Models\Member;
use App\Models\MemberEmail;
use App\Models\ChimpEntry;
use App\Models\ChimpLists;

use WC\Db\Server;
use WC\Db\DbQuery;
use WC\UserSession;

use App\Link\PageInfo;
use WC\Valid;

use Phalcon\Mvc\Controller;
use Phalcon\Db\Column;


class MemberAdmController extends Controller {
use \WC\Mixin\Auth;
use \WC\Mixin\ViewPhalcon;
use \App\Link\MemberData;
use \App\Chimp\ChimpData;
    public $url = "/admin/member/";

    public function getAllowRole() : array
    {
        return ['Admin','Finance'];
    }
    
    public function searchAction() {
        $m = $this->getViewModel();
        return $this->render('member','index');
    }
    public function indexAction() {
        $m = $this->getViewModel();
        
        $qry =  $this->dbq;
        
        $req = $_REQUEST;
        $req_query = $_SERVER['QUERY_STRING'];
        
        $pageAll = Valid::toStr($req, 'page', 'all');
        $pgsize = Valid::toInt($req, 'pgsize', 0);
        
        if (is_numeric($pageAll)) {
            $page = Valid::toint($req, 'page', 1);
        } else {
            $page = 0;
        }
        if ($pgsize > 0 && $page == 0) {
            $page = 1;
        }
        $orderby = Valid::toStr($req, 'orderby', null);
        $order_field = self::getOrderBy($m, $orderby);
        $sql = <<<EOD
select M.*, ME.email_address, ME.status as email_status,
  count(*) over() as full_count
  from member M 
  left outer join member_email ME on ME.memberid = M.id
EOD;

        
        $fname = Valid::toStr($req, 'fname');
        if (!empty($fname)) {
            $qry->bindCondition('M.fname = ?', $fname);
        }
        $lname = Valid::toStr($req, 'lname');
        if (!empty($lname)) {
            $qry->bindCondition('M.lname = ?', $lname);
        }
        $city = Valid::toStr($req, 'city');
        if (!empty($city)) {
            $qry->bindCondition('M.city = ?', $city);
        }        
        $postcode = Valid::toStr($req, 'postcode');
        if (!empty($postcode)) {
            $qry->bindCondition('M.postcode = ?', $postcode);
        } 
        $status = Valid::toStr($req, 'status');
        if (!empty($status)) {
            $qry->bindCondition('M.status = ?', $status);
        }         
        $qry->order($order_field);
        
        
        if ($page > 0) {
            if ($pgsize === 0) {
                $pgsize = 16;
            }
            $qry->bindLimit($pgsize, ($page - 1) * $pgsize);
        }
        $results = $qry->queryAA($sql);
        
        $total = !empty($results) ? $results[0]['full_count'] : 0;
        if ($page === 0) {
            $pgsize = $total;
            $page = 1;
        }
        $paginator = new PageInfo($page, $pgsize, $results, $total);

        $m->req_query = $req_query;
        $m->page = $paginator;
        $m->url = "/admin/member/list";
        $m->pgsize = $pgsize;

        return $this->render('member','list');
    }

    private function editForm() {
        $view = $this->getView();
        $m = $view->m;
        $m->url = $this->url;
        $this->assets->add(['bootstrap','member-js']);
        $rec = $m->rec;
        $id = $rec->id;
        if ($id > 0) {
            $m->emails = $this->getMemberEmails($id);
            $m->donations = $this->getMemberDonations($id);
        } else {
            $m->emails = [];
            $m->donations = [];
        }
        return $this->render('member','fields');
    }

    public function newAction() {
        $view = $this->getView();
        $m = $view->m;
        $m->rec = new Member();
        $m->title = "New member";
        return $this->editForm();
    }

    protected function editId($mid) {
        $view = $this->getView();
        $m = $view->m;
        $m->rec = Member::findFirstById($mid);
        $m->title = "Member edit";
        return $this->editForm();
    }

    public function editAction($mid) {
        return $this->editId($mid);
    }

    public function updateStatusAction() {
        $sql = <<<EOS
status <> 'unsubscribed' and status <> 'cleaned' 
or status is null
EOS;                
        $mb_email_set = MemberEmail::find($sql);
        $req_query = $_SERVER['QUERY_STRING'];
        
        try {
        foreach($mb_email_set as $mb_email) {
            $entry = ChimpEntry::findFirstByEmailid($mb_email->id);
            if (empty($entry)) {
                $status = 'no-chimp';
            }
            else {
                $status = $entry->status;
            }
            list($list, $info, $entry) = $this->getEmailStatus($mb_email->email_address);
            if ($info !== false) {
                    if ($status !== $info->status) {
                        $status = $info->status;
                    }    
                    
            }
            if ($mb_email->status !== $status) {
                $mb_email->status = $status;
                $mb_email->update();
            }
            if ( !empty($entry) && ($entry->status !== $status)) {
                $entry->status = $status;
                $entry->update();
            }
        }
        }
        catch(\Exception $ex) {
            $this->flash($ex->getMessage());
        }
        $this->reroute('\admin\member\list?' . $req_query);
    }
    /**
     * Get latest mail chimp status for all emails, and redisplay

     */
    public function updateAction() {
        $post = $_POST;
        $mid = Valid::toInt($post, 'uid', 0);
        try {
            $emails = $this->getMemberEmails($mid);
            // see if chimpentry exists //
            $hasSubscription = false;
            foreach ($emails as $ix => $val) {
                $entry = ChimpEntry::findFirstByEmailid($val['id']);
                // get currently recorded status
                if (empty($entry)) {
                    $status = 'no-chimp';
                } else {
                    $status = $entry->status;
                }
                // Go to mail chimp for current status
                list($list, $info, $entry) = $this->getEmailStatus($val['email_address']);
                
                if ($info !== false) {
                    if ($status !== $info->status) {
                        $status = $info->status;
                    }   
                    if (!$hasSubscription) {
                        $hasSubscription = ($status === 'subscribed');
                    }
                }
                
                if ($val['status'] !== $status) {
                    $this->setEmailStatus($val['id'], $status);
                }
                if ( !empty($entry) && $entry->status !== $status) {
                    $entry->status = $status;
                    $entry->update();
                }
            }
            if (!$hasSubscription) {
                $member = Member::findFirstById($mid);
                if (!empty($member) && ($member->status === 'subscribed')) {
                    $member->status = $status;
                    $member->update();
                }
            }
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
        return $this->reroute($this->url . 'edit/' . $mid);
    }

    /**
     * Email posts done for existing member
     * @param type $f3
     * @param type $args
     */
    public function  post() {
        $post = $_POST;
        $mid = Valid::toInt($post, 'mid', 0);
        try {
            foreach ($post as $ix => $val) {
                if (strpos($ix, 'eml') === 0) {
                    $eid = intval(substr($ix, 3));
                    // Add New record
                    if ($eid === 0 && !empty($val)) {
                        $email = Valid::toEmail($post, $ix);
                        if (!empty($email)) {
                            // new email for member id
                            // check mail-chimp status
                            // info[ list, Member info, entry ] 
                            $info = $this->getEmailStatus($email);
                            if (!empty($info[1])) {
                                $status = 'mail-chimp';
                                // does a ChimpEntry record exist?
                                if (isset($info['entry'])) {
                                    $entry = $info['entry'];
                                    $status = $entry['status'];
                                }
                            } else {
                                $status = 'no-chimp';
                            }
                            $mem = new MemberEmail();
                            $mem['email_address'] = $email;
                            $mem['memberid'] = $mid;
                            $mem['status'] = $status;
                            $mem->save();
                        }
                    }
                } else if (strpos($ix, 'chk') === 0) {
                    // delete
                    $eid = intval(substr($ix, 3));
                    if ($eid > 0) {
                        MemberEmail::deleteId($eid);
                    }
                } else if (strpos($ix, 'chimp') === 0) {
                    $eid = intval(substr($ix, 5));
                    $status = Valid::toStr($post, 'stat' . $eid, '');
                    if ($status === 'no-chimp') {
                        $list = $this->defaultList();
                        $entry = $this->addMemberEmail($list, $eid);
                        if ( !empty($entry)) {
                            $me = MemberEmail::findFirstById($eid);
                            $me->status = $entry->status;
                            $me->update();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
       return $this->editId($mid);
    }

    public function empostAction() {
        $post = $_POST;
        $m = $this->getViewModel();
        
        self::assignPost($post, $m);
        list($rec, $isNew) = self::assignMember($m);

        $saved = false;
        try {
            if ($isNew) {
                $rec->save();
            } else {
                $rec->update();
            }
            $saved = true;
        } catch (\PDOException $e) {
            $this->errorPDO($e);
        }
        if ($saved) {
            $this->flash("Record updated");
            $this->reroute($this->url . "edit/" . $rec->id);
        } else {
            // redit same record data
            // show any errors
            $view = $this->getView();
            $view->rec = $rec;
            $view->title = "Edit Errors";
            $view->errors = $errorList;
            $this->editForm();
        }
    }
/**
     * Post ajax new donation info
     * @param type $f3
     * @param type $args
     */
    public function donateAction() {
        $post = $_POST;
        $mid = Valid::toInt($post, 'duid', 0);
        $amount = Valid::toMoney($post, 'amount', 0.0);
        $member_date = Valid::toDate($post, 'member-date');
        $purpose = Valid::toStr($post, 'purpose');
        $detail = Valid::toStr($post, 'detail');
        if ($amount > 0.0) {
            try {
                $give = new Donation();
                $give->member_date = $member_date;
                $give->amount = $amount;
                $give->purpose = $purpose;
                $give->created_at = Valid::now();
                $give->memberid = $mid;
                $give->detail = $detail;
                $give->save();
                  
            } catch (\Exception $e) {
                $this->flash($e->getMessage());
            }
           
        }
        $view = $this->getView();
        $m = $view->m;
         $m->donations = $this->getMemberDonations($mid);
        $this->noLayouts();
        return $this->render('partials','member/donations');

    }
}
