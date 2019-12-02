<?php

namespace Pcan;

use Pcan\DB\Donation;
use Pcan\DB\Member;
use Pcan\DB\MemberEmail;
use WC\DB\Server;
use Pcan\DB\PageInfo;
use WC\Valid;
use Chimp\DB\ChimpEntry;
use Chimp\DB\ChimpLists;


class MemberAdm extends Controller {

    public $url = "/admin/member/";

    public function beforeRoute() {
        if (!$this->auth()) {
            return false;
        }
    }

    public function index($f3, $args) {
        $db = Server::db();
        $req = &$f3->ref('REQUEST');
        $pageAll = Valid::toStr($req, 'page', 'all');
        if (is_numeric($pageAll)) {
            $page = Valid::toint($req, 'page', 1);
        } else {
            $page = 0;
        }
        $orderby = Valid::toStr($req, 'orderby', null);
        $order_field = Member::viewOrderBy($this->view, $orderby);
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
        $results = $db->exec($sql, $params);
        $total = !empty($results) ? $results[0]['full_count'] : 0;
        if ($page === 0) {
            $pgsize = $total;
            $page = 1;
        }
        $paginator = new PageInfo($page, $pgsize, $results, $total);
        $view = $this->view;
        $view->page = $paginator;
        $view->url = "/admin/member/list";
        $view->pgsize = $pgsize;

        $view->content = 'member/list.phtml';
        $view->assets(['bootstrap']);

        echo $view->render();
    }

    private function editForm() {
        $view = $this->view;
        $view->content = 'member/fields.phtml';
        $view->url = $this->url;
        $view->assets(['bootstrap','member-js']);
        $rec = $view->rec;
        $id = $rec['id'];
        if ($id > 0) {
            $view->emails = Member::getEmails($id);
            $view->donations = Member::getDonations($id);
        } else {
            $view->emails = [];
            $view->donations = [];
        }
        echo $view->render();
    }

    public function newMember($f3, $args) {
        $view = $this->view;
        $view->rec = new Member();
        $view->title = "New member";
        $this->editForm();
    }

    protected function editId($mid) {
        $view = $this->view;
        $view->rec = Member::byId($mid);
        $view->title = "Member edit";
        $this->editForm();
    }

    public function edit($f3, $args) {
        $mid = $args['mid'];
        $this->editId($mid);
    }

    /**
     * Get latest mail chimp status for all emails, and redisplay
     * @param type $f3
     * @param type $args
     */
    public function update($f3, $args) {
        $post = &$f3->ref('POST');
        $mid = Valid::toInt($post, 'uid', 0);
        try {
            $emails = Member::getEmails($mid);
            // see if chimpentry exists //

            foreach ($emails as $ix => $val) {
                $entry = ChimpEntry::byMemberEmailId($val['id']);
                // get currently recorded status
                if ($entry === false) {
                    $status = 'no-chimp';
                } else {
                    
                    $status = $entry['status'];
                }
                // Go to mail chimp for current status
                list($list, $info, $entry) = ChimpLists::getEmailStatus($val['email_address']);
                
                if ($info !== false) {
                    if ($status !== $info->status) {
                        $status = $info->status;
                    }                  
                }
                
                if ($val['status'] !== $status) {
                    Member::setStatus($val['id'], $status);
                }
                if ($entry !== false && $entry['status'] !== $status) {
                    $entry['status'] = $status;
                    $entry->update();
                }
            }
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
       $f3->reroute($this->url . 'edit/' . $mid);
    }

    /**
     * Email posts done for existing member
     * @param type $f3
     * @param type $args
     */
    public function empost($f3, $args) {
        $post = &$f3->ref('POST');
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
                            $info = ChimpLists::getEmailStatus($email);
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
                        $entry = ChimpEntry::addMemberEmail($eid);
                        if ($entry !== false) {
                            $me = MemberEmail::byId($eid);
                            $me['status'] = $entry['status'];
                            $me->update();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
        $this->editId($mid);
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
            $view = $this->view;
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
        $view = $this->view;
        $view->donations = Member::getDonations($mid);
        $view->layout = 'member/donations.phtml';
        $view->content = null;
        echo $view->render();
    }
}
