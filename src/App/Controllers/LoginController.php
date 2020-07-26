<?php

namespace App\Controllers;

/**
 * Description of login
 *
 * @author Michael Rynn
 */
use App\Models\UserEvent;
use App\Link\UserRoles;
use App\Models\Users;
use App\Models\ResetCode;
use WC\Db\Server;
use WC\UserSession;
use WC\Valid;
use WC\SwiftMail;
use Phalcon\Mvc\Controller;
use WC\Assets;
use WC\WConfig;

class LoginController extends Controller {

    use \WC\Mixin\Captcha;
    use \WC\Mixin\ViewPhalcon;

    private $username;
    private $model;
    
    function indexAction() {
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }
        $view = $this->getView();
        if ($this->user_session->isLoggedIn('User')) {
            return $this->render('login','details');
        }
        $m = $view->m;
        $m->title = 'Login';
        $m->password = '';
        $m->email = '';
        $m->alias = '';
        $this->setForm($m);
        return $this->render('login','index');
        
    }
   function endAction() {
       $this->user_session->nullify();
       $this->response->redirect('/',true);
   }
    function checkoutAction() {
        $ud = $this->user_session;
        $view = $this->getView();
        if (!$ud->isEmpty()) {
            $ud->wipe();
            return $this->render('login','logout');
        } else {
            return $this->render('login','error');
        }
    }

    private function setForm($m) {
        $m->formid =  'user-login';
        $this->captchaView($m);
        $this->xcheckView($m);
    }
    // POST from login form

    function errorLogin($msg) {
        /** $logger = new \Log('login.log');
              $logger->write('Fail login - ' . $msg);
         */
        $view = $this->getView();
        $m = $view->m;
        $m->message = $msg;
       
        $this->setForm($m);
        $req = $this->request;
        if ($req->isAjax()) {
            $this->noLayouts();
            return $this->render('partials','login/fields');
        }
    }

    function errorForgot($msg) {
        $logger = new \Log('login.log');
        $logger->write('Fail Forgot - ' . $msg);
        $this->flash($msg);
        $this->forgotView();
    }

    function errorChangePwd($msg) {
        $logger = new \Log('login.log');
        $logger->write('Password change - ' . $msg);
        $this->flash($msg);
        $this->changePwdView();
    }

    function changePwdView() {
        $view = $this->view;
        $view->content = 'home/changePWD';
        $view->assets(['bootstrap']);
        echo $this->view->render();
    }

    function changePwdPost() {
        $post = $_POST;
        $newpwd = Valid::toStr($post, 'new_pwd', null);
        $chkpwd = Valid::toStr($post, 'confirm_pwd', null);
        $email = Valid::toEmail($post, 'email');

        if (empty($newpwd) || ($newpwd !== $chkpwd)) {
            return $this->errorChangePwd("Password not confirmed");
        }
        $crypt = \Bcrypt::instance();

        $user = User::byEmail($email);

        if ($user === false) {
            return $this->errorChangePwd("User not found for $email");
        }

        $db = Server::db();
        $db->begin();
        try {
            $user['password'] = $crypt->hash($newpwd);
            $user->update();
            UserEvent::logPwdChange($user['id']);
            $db->commit();
        } catch (\PDOException $perr) {
            return $this->errorChangePwd($perr->getMessage());
        }
        $view = $this->view;
        $view->content = 'home/pwd_changed';
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    function changePwd() {
        if (!$this->app->https()) {
            return;
        }
        $req = $_REQUEST;
        $view = $this->view;
        $view->header = "Change password";
        $this->changePwdView();
    }

    function defaultError() {
        $view = $this->view;
        $view->content = 'home/error';
        $view->layout = 'minimal';
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    function resetPwd($code) {
        $req = $_REQUEST;

        ResetCode::deleteOldCodes();
        $valid = ResetCode::byCode($code);

        if ($valid !== false) {
            $email = trim($args['email']);

            $userid = $valid['user_id'];
            $user = User::byId($userid);


            if ($user === false || $user['email'] !== $email) {
                return $this->defaultError();
            }
            $view = $this->getView();
            $m = $view->model;
            $m->email = $email;
            $m->header = "Change password for " . $email;
            $m->url = "/login/";
            $this->changePwdView();
        } else {
            $this->flash('Code is invalid or expired');
            $this->user_session->reroute('\login\changepwd');
        }
    }

    function forgotView() {
        $view = $this->getView();
        $m = $view->model;
        if (!isset($m->email)) {
            $m->email = '';
        }
        $this->captchaView($m);
        $view->content = 'home/forgotPassword';
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    function forgot() {
        $this->forgotView();
    }

    function forgotPost() {
        $post = $_POST;
        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            $this->errorForgot('Google error ' . $verify['errorcode']);
            return;
        }

        $email = Valid::toEmail($post, 'email');
        $view = $this->getView();
        $view->assets(['bootstrap']);
         
        $m = $view->model;
        $m->email = $post['email'];
        if (empty($email)) {
            $this->errorForgot('Need valid email');
            return;
        }

        $user = new User();
        $found = $user->load(["email = ?", $email]);

        if ($found === false) {
            $this->errorForgot('No match was found for email');
            return;
        }
        $code = UserEvent::newUserConfirm($found, UserEvent::PW_RESET, true);
        if ($code === false) {
            $this->errorForgot('Reset code generation failure');
            return;
        }
        $name = $found['name'];

        $m->domain = $f3->get('domain');
        $m->confirmUrl = '/reset-password/' . $code . '/' . $found['email'];
        $m->sendDate = Valid::now();
        $m->site =  $f3->get('organization');
        $m->link = $m->domain . $m->confirmUrl;
        
        $textMsg = static::renderView($m, 'email/reset_text');
        $htmlMsg = static::renderView($m, 'email/reset');

        $mailer = new SwiftMail($f3);

        $msg = [
            "subject" => 'Password Reset from ' . $m->site,
            "text" => $textMsg,
            "html" => $htmlMsg,
            "to" => [
                "email" => $email,
                "name" => $name
            ]
        ];

        $isValid = $mailer->send($msg);

        if ($isValid['success'] === false) {
            $this->errorForgot($isValid['errors']);
        } else {
            $view->content = 'home/resetSent';
            $m->email = $email;
           
            echo $view->render();
        }
    }

    /**
     * Confirm login post
     */
    function checkAction() {
        $post = $_POST;
        $view = $this->getView();
        $m = $view->m;
        $user_session = $this->user_session;
        $user_session->read(); 
        
        if (!$this->xcheckResult($post)) {
            return $this->errorLogin('Cross script protection failure');

        }

        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            return $this->errorLogin('Google error ' . $verify['errorcode']);
        }

        if (!$user_session->isEmpty()) {
            $user_session->wipe();
        }
         
        $m->email = Valid::toEmail($post, "email");
        $m->alias = Valid::toStr($post, "alias");
        $m->password = Valid::toStr($post, "password");
        $user = null;
         if (!empty($m->email)) {
            $user = Users::findFirstByEmail($m->email);
        } else {
            $user = Users::findFirstByName($m->alias);
        }
        if (!empty($user)) {
            $secure = $this->security;
            $stored = $user->password;
            $good = $secure->checkHash($m->password, $stored);
            if (!$good) {
                return $this->errorLogin('Authentication Failure');

            } else {
                $roles = UserRoles::getRoleList($this->db, $user->id);
                $user_session->setUser($user, $roles);
                $user_session->addFlash("Logged in as " . $user_session->getUserName());
                $this->noLayouts();
                return $this->render('login','details');
            }
        } else {
            
            return $this->errorLogin("Not found");
        }
    }

    function errorSignup($msg) {
        $logger = new \Log('login.log');
        $logger->write('Fail Signup - ' . $msg);
        $f3 = $this->f3;
        $f3->set('message', $msg);
        $this->signupView();
    }

    function signupPost() {
        $post = $_POST;
        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            $this->errorSignup('Google error ' . $verify['errorcode']);
            return;
        }

        if (!$this->xcheckResult($post)) {
            $this->errorSignup('Not current token');
        }

        $user = new User();
        $name = Valid::toStr($post, 'username', null);
        $email = Valid::toEmail($post, 'email', null);
        $pwd1 = Valid::toStr($post, 'pass1', null);
        $pwd2 = Valid::toStr($post, 'pass2', null);
        $view = $this->view;
        $user = new User();
        $user['name'] = $name;
        $user['email'] = $email;
        $view->rec = $user;
        if (empty($email)) {
            $this->errorSignup('Invalid email');
            return;
        }
        if (empty($pwd1) || ($pwd1 !== $pwd2)) {
            $this->errorSignup('None matching or empty passwords');
            return;
        }
        if (empty($name)) {
            $this->errorSignup('Empty user name');
            return;
        }
        $match = User::byEmail($email);
        if ($match) {
            $this->errorSignup('Email already exists');
            return;
        }


        $crypt = \Bcrypt::instance();

        $user['password'] = $crypt->hash($pwd1);
        $code = UserEvent::newUserConfirm($user, UserEvent::EMAIL_CK, false);
        if (empty($code)) {
            $this->errorSignup('New User failed');
            return;
        }
        $view = $this->view;
        $view->userName = $name;
        $view->publicUrl = $f3->get('domain');
        $view->confirmUrl = '/confirm/' . $code . '/' . $email;

        $textMsg = TagViewHelper::render('form/signup_text.txt');
        $htmlMsg = TagViewHelper::render('form/signup_html');
        $mailer = new SwiftMail($f3);
        $msg = [
            "subject" => 'Signup to SBO',
            "text" => $textMsg,
            "html" => $htmlMsg,
            "to" => [
                "email" => $email,
                "name" => $name
            ]
        ];
        $isValid = $mailer->send($msg);
        if ($isValid['success']) {
            $this->flash('successful post');
        } else {
            $this->errorSignup($isValid['errors']);
            return;
        }
    }

    function signupView() {
        if (!$this->app->https()) {
            return;
        }
        $view = $this->view;
        $view->title = "Register";

        $this->captchaView();
        $this->xcheckView();
        $view->content = 'home/signup';
        $view->assets('bootstrap');

        echo $view->render();
    }

    function signup() {
        $view = $this->view;
        $view->rec = new User();
        $view->message = '';
        $this->signupView();
    }

}
