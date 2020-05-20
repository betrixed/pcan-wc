<?php

namespace App\Controllers;

use App\Models\Donation;
use App\Models\Member;
use App\Models\MemberEmail;
use WC\Db\Server;
use WC\Db\DbQuery;
use WC\UserSession;

use App\Link\PageInfo;
use WC\Valid;
use App\Models\ChimpEntry;
use App\Models\ChimpLists;
use Phalcon\Mvc\Controller;
use App\Link\LinkData;
use App\Chimp\ChimpData;

class MemberAdmController extends Controller {
use \WC\Mixin\Auth;
use \WC\Mixin\ViewPhalcon;

    public $url = "/admin/member/";

    public function getAllowRole() : string
    {
        return 'Admin';
    }
    public function indexAction() {
        $view = $this->getView();
        $m = $view->m;
        
        $db = new DbQuery();
        
        $req = $_REQUEST;
        
        $pageAll = Valid::toStr($req, 'page', 'all');
        if (is_numeric($pageAll)) {
            $page = Valid::toint($req, 'page', 1);
        } else {
            $page = 0;
        }
        $orderby = Valid::toStr($req, 'orderby', null);
        $order_field = LinkData::getOrderBy($m, $orderby);
        $sql = <<<EOD
select M.*, ME.email_address, ME.status as email_status,
    count(*) over() as full_count
  from member M left outer join member_email ME on ME.memberid = M.id
EOD;
        $params = [];
        $sql .= " order by " . $order_field;
        if ($page > 0) {
            $sql .= " limit :ct offset  :start";
            $pgsize = 16;
            $params[':ct'] = $pgsize;
            $params[':start'] = ($page - 1) * $pgsize;
        }
        $results = $db->arraySet($sql, $params);
        $total = !empty($results) ? $results[0]['full_count'] : 0;
        if ($page === 0) {
            $pgsize = $total;
            $page = 1;
        }
        $paginator = new PageInfo($page, $pgsize, $results, $total);

        $m->page = $paginator;
        $m->url = "/admin/member/list";
        $m->pgsize = $pgsize;

        return $this->render('member','list');
    }

    private function editForm() {
        $view = $this->getView();
        $m = $view->m;
        $m->url = $this->url;
        \WC\Assets::instance()->add(['bootstrap','member-js']);
        $rec = $m->rec;
        $id = $rec->id;
        if ($id > 0) {
            $m->emails = LinkData::getEmails($id);
            $m->donations = LinkData::getDonations($id);
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

    /**
     * Get latest mail chimp status for all emails, and redisplay
     * @param type $f3
     * @param type $args
     */
    public function updateAction() {
        $post = $_POST;
        $mid = Valid::toInt($post, 'uid', 0);
        try {
            $emails = LinkData::getEmails($mid);
            // see if chimpentry exists //

            foreach ($emails as $ix => $val) {
                $entry = ChimpEntry::findFirstByEmailid($val['id']);
                // get currently recorded status
                if (empty($entry)) {
                    $status = 'no-chimp';
                } else {
                    $status = $entry->status;
                }
                // Go to mail chimp for current status
                list($list, $info, $entry) = ChimpData::getEmailStatus($val['email_address']);
                
                if ($info !== false) {
                    if ($status !== $info->status) {
                        $status = $info->status;
                    }                  
                }
                
                if ($val['status'] !== $status) {
                    LinkData::setStatus($val['id'], $status);
                }
                if ( !empty($entry) && $entry->status !== $status) {
                    $entry->status = $status;
                    $entry->update();
                }
            }
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
        return UserSession::reroute($this->url . 'edit/' . $mid);
    }

    /**
     * Email posts done for existing member
     * @param type $f3
     * @param type $args
     */
    public function empostAction() {
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
                            $info = ChimpData::getEmailStatus($email);
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
                        $list = ChimpData::defaultList();
                        $entry = ChimpData::addMemberEmail($list, $eid);
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

    public function post($f3, $args) {
        $post = &$f3->ref('POST');

        list($rec, $isNew) = Member::assignPost($post);

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
            $f3->reroute($this->url . "edit/" . $rec['id']);
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
    public function donate($f3, $args) {
        $post = &$f3->ref('POST');
        $mid = Valid::toInt($post, 'duid', 0);
        $amount = Valid::toMoney($post, 'amount', 0.0);
        $member_date = Valid::toDateTime($post, 'member-date');
        $purpose = Valid::toStr($post, 'purpose');
        
        if ($amount > 0.0) {
            try {
                $give = new Donation();
                $give['member_date'] = $member_date;
                $give['amount'] = $amount;
                $give['purpose'] = $purpose;
                $give['created_at'] = Valid::now();
                $give['memberid'] = $mid;
                $give->save();
                  
            } catch (\Exception $e) {
                $this->flash($e->getMessage());
            }
           
        }
        $view = $this->getView();
        $view->content = 'member/donations';
        
        $m = $view->model;
        $m->donations = Member::getDonations($mid);

        echo $view->render();
    }
}