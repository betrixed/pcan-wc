<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */


use WC\DB\Server;
use Pcan\DB\PageInfo;
use Pcan\DB\User;
use Pcan\DB\UserAuth;
use Pcan\DB\UserEvent as UEvt;
use Pcan\Html;
use WC\UserSession;
use WC\Valid;


class UserAdm extends Controller
{

    public $url = '/admin/user/';

    public function post($f3, $args)
    {
        
        $req = &$f3->ref('REQUEST');
        
        $id = Valid::toInt($req, 'id');
        $status = Valid::toStr($req, 'status');
        $email = Valid::toEmail($req, 'email');
        $name = Valid::toStr($req, 'name');
        $user = ($id > 0) ? User::byId($id) : null;
        if (!empty($user)) {
            $user['name'] = $name;
            $user['email'] = $email;
            $user['status'] = $status;
            try {
                $user->update();
            } catch (Exception $ex) {
                $this->flash('Failed to update user');
            }
        }
        UserSession::reroute($this->url . 'edit/' . $id);
        return false;
    }

    public function index($f3, $args)
    {
        $req = &$f3->ref('REQUEST');
        
        $numberPage = Valid::toint($req,'page',1);
        $grabsize = 16;
        $start = ($numberPage - 1) * $grabsize;
        //SQL_CALC_FOUND_ROWS
        $sql =<<< EOD
select   u.* ,
    count(*) over() as full_count         
 from users u order by u.name limit :ct offset :start
EOD;
        $db = Server::db();
        $results = $db->exec($sql, [':start' => $start, ':ct' => $grabsize ]);
        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;
        $paginator = new PageInfo($numberPage, $grabsize, $results, $maxrows);
        $view = $this->view;
        $view->page = $paginator;
        $view->title = "User Index";
        $view->url = $this->url;
        $view->assets(['bootstrap', 'fontawesome']);
        $view->content = 'user/index.phtml';
        echo $view->render();
    }
    
    public function edit($f3, $args) 
    {
        $view = $this->view;
        $req = &$f3->ref('REQUEST');
        
        $id = $args['id'];
        $user = User::byId($id);
        if (!$user) {
            $this->flash("User was not found");
            UserSession::reroute($this->url);
        }
        
        $view->groups = $user->getGroups();
        
        $view->successLogins = UEvt::getEvents(UEvt::PW_LOGIN, $id);

        // passwordChanges   
        $view->passwordChanges = UEvt::getEvents(UEvt::PW_CHANGE, $id);

        // resetPasswords 
        $view->resetPasswords = UEvt::getEvents(UEvt::PW_RESET, $id);

        $view->user = $user;
        $view->url = $this->url;
        $view->assets(['bootstrap']);
        $view->content = 'user/edit.phtml';
        echo $view->render();
    }
    private function errorPDO($e) {
        $err = $e->errorInfo;
        $this->flash($err[0] . ": " . $err[1]);
    }
    
    public function makeNewUser($user_name, $user_email, $user_pwd, $groups) {
        $user = new User();
        $user['name'] = $user_name;
        $user['email'] = $user_email;
        
        $crypt = \Bcrypt::instance();
        $user['password'] = $crypt->hash($user_pwd);
        
        $user['status'] = 'C';
        
        try {
            $user->save();
            $db = Server::db();
            $pdo = $db->pdo();
            
            $grouplist = '(';
            
            foreach($groups as $ix => $g) {
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
            $db->exec($sql, [':uid' => $use['id']]);
        }
        catch(\PDOException $e) {
            return $this->errorPDO($e);
        }
        
    }
    protected function addUserGroup($userid, $groupid)
    {
        $role = new UserAuth();
        $role['groupid']= $groupid;
        $role['userid'] = $userid;
        try {
            $role->save();
        }
        catch (\PDOException $e) {
            return $this->errorPDO($e);
        }
        return true;
    }
    
    public function gpost($f3,$args)
    {
        $post = &$f3->ref('POST');
        
        $id = Valid::toInt($post,'id',0);
        
        $user = User::byId($id);
        if ($user !== false)
        {
            foreach($post as $key => $change)
            {
                if (Valid::startsWith($key,'dgp'))
                {
                    $gid = intval(substr($key,3));
                    UserAuth::delGroup($user->id, $gid);
                }
                else if (Valid::startsWith($key,'agp'))
                {
                    $gid = intval(substr($key,3));
                    $this->addUserGroup($user->id, $gid);
                }
            }
            $this->showUserGroups($id);
        }
    }
    
    protected function showUserGroups($userid) {
        $user = User::byId($userid);
        $view = $this->view;
        if (!$user)
        {
            $view->user = null;
            $view->groups = [];
        }
        else {
            $view->user = $user;
            $view->groups = User::groupList($userid);
            $view->others = User::otherGroups($userid);
        }
        $view->assets(['bootstrap']);
        $view->content = 'user/groups.phtml';
        $view->url = $this->url;
        echo $view->render();       
    }
    public function groups($f3, $args) 
    {
        $view = $this->view;
        $req = &$f3->ref('REQUEST');
        
        $id = $args['id'];
        $this->showUserGroups($id);
    }
    
    public function beforeRoute()
    {
        if (!$this->auth()) {
            return false;
        }
    }

}
