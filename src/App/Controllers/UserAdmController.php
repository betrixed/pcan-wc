<?php

namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use WC\Db\{
    Server,
    DbQuery
};
use App\Link\{PageInfo,UserLog};
use App\Models\{
    Users,
    UserAuth,
    UserEvent
};
use WC\{
    UserSession,
    Valid
};
use Phalcon\Mvc\Controller;
use Phalcon\Db\Column;

class UserAdmController extends Controller
{

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
    use \App\Link\UserAdm;

    public $url = '/admin/user/';

    public function postAction()
    {

        $req = $_POST;

        $id = Valid::toInt($req, 'id');
        $status = Valid::toStr($req, 'status');
        $email = Valid::toEmail($req, 'email');
        $name = Valid::toStr($req, 'name');
        $user = ($id > 0) ? Users::findFirstById($id) : null;
        if (!empty($user)) {
            $user->name = $name;
            $user->email = $email;
            $user->status = $status;
            try {
                $user->update();
            } catch (Exception $ex) {
                $this->flash('Failed to update user');
            }
        }
        $this->reroute($this->url . 'edit/' . $id);
        return false;
    }

    public function indexAction()
    {
        $req = $_REQUEST;

        $numberPage = Valid::toint($req, 'page', 1);
        $grabsize = 16;
        $start = ($numberPage - 1) * $grabsize;
        //SQL_CALC_FOUND_ROWS
        $sql = <<< EOD
select   u.* ,
    count(*) over() as full_count         
 from users u order by u.name LIMIT :ct OFFSET :start
EOD;
        $qry = $this->dbq;
        $results = $qry->objectSet($sql, ['start' => intval($start), 'ct' => intval($grabsize) ], ['start' => Column::BIND_PARAM_INT, 'ct' => Column::BIND_PARAM_INT]);
        $maxrows = !empty($results) ? $results[0]->full_count : 0;
        $paginator = new PageInfo($numberPage, $grabsize, $results, $maxrows);
        $m = $this->getViewModel();
        $m->page = $paginator;
        $m->title = "User Index";
        $m->url = $this->url;
        $this->assets->add(['bootstrap', 'fontawesome']);
        return $this->render('user','index');
    }

    public function editAction($id)
    {
        $m = $this->getViewModel();
        $req = $_REQUEST;
        $user = Users::findFirstById($id);
        if (!$user) {
            $this->flash("User was not found");
            $this->reroute($this->url);
        }

        $m->groups = $this->getGroups($user);
        $userlog = new UserLog($this->db);
        
        $m->successLogins = $userlog->getEvents(UserLog::PW_LOGIN, $id);

        // passwordChanges   
        $m->passwordChanges = $userlog->getEvents(UserLog::PW_CHANGE, $id);

        // resetPasswords 
        $m->resetPasswords = $userlog->getEvents(UserLog::PW_RESET, $id);

        $m->user = $user;
        $m->url = $this->url;
        $this->assets->add(['bootstrap']);
        return $this->render('user','edit');
    }

    protected function errorPDO($e)
    {
        $err = $e->errorInfo;
        $this->flash($err[0] . ": " . $err[1]);
    }

    protected function makeNewUser($user_name, $user_email, $user_pwd, $groups)
    {
        $user = new User();
        $user->name = $user_name;
        $user->email = $user_email;

        
        $user->password = $this->security->hash($user_pwd);

        $user->status = 'C';

        try {
            $user->create();
            $db = $this->db;
            $pdo = $db->pdo();

            $grouplist = '(';

            foreach ($groups as $ix => $g) {
                if ($ix > 0) {
                    $grouplist .= ',';
                }
                $grouplist .= $pdo->quote($g);
            }
            $grouplist .= ')';
            $sql = <<<EOS
insert into user_auth (userid, groupid) select :uid, ug.id from user_group ug
   where ug.name in $grouplist
EOS;
            $db->execute($sql, ['uid' => $use['id']]);
        } catch (\PDOException $e) {
            return $this->errorPDO($e);
        }
    }

    protected function addUserGroup($userid, $groupid)
    {
        $role = new UserAuth();
        $role->groupid = $groupid;
        $role->userid = $userid;
        try {
            $role->create();
        } catch (\PDOException $e) {
            return $this->errorPDO($e);
        }
        return true;
    }

    public function gpostAction()
    {
        $post = $_POST;

        $id = Valid::toInt($post, 'id', 0);

        $user = Users::findFirstById($id);
        if (!empty($user)) {
            foreach ($post as $key => $change) {
                if (str_starts_with($key, 'dgp')) {
                    $gid = intval(substr($key, 3));
                    UserAuth::delGroup($user->id, $gid);
                } else if (str_starts_with($key, 'agp')) {
                    $gid = intval(substr($key, 3));
                    $this->addUserGroup($user->id, $gid);
                }
            }
            $this->showUserGroups($id);
        }
    }

    protected function showUserGroups($userid)
    {
        $user = User::findFirstById($userid);
        $m = $this->getViewModel();
        if (!$user) {
            $m->user = null;
            $m->groups = [];
        } else {
            $m->user = $user;
            $m->groups = User::groupList($userid);
            $m->others = User::otherGroups($userid);
        }
        $this->assets->add(['bootstrap']);
        $m->url = $this->url;
        return $this->render('user'.'groups');
    }

    public function groups($id)
    {
        $m = $this->getViewModel();
        $req = $_REQUEST;

        return $this->showUserGroups($id);
    }

    public function getAllowRole() : string
    {
        return 'Admin';
    }

}
