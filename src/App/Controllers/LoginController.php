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
use App\Link\UserLog;
use WC\Db\Server;
use WC\UserSession;
use WC\Valid;
use WC\SwiftMail;
use Phalcon\Mvc\Controller;
use WC\Assets;
use WC\WConfig;

class LoginController extends Controller
{

    use \WC\Mixin\Captcha;
    use \WC\Mixin\ViewPhalcon;

    private $username;
    private $model;

    function indexAction()
    {
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }
        $view = $this->getView();
        if ($this->user_session->isLoggedIn('User')) {
            return $this->render('login', 'details');
        }
        $m = $view->m;
        $m->title = 'Login';
        $m->password = '';
        $m->email = '';
        $m->alias = '';
        $this->setForm($m);
        return $this->render('login', 'index');
    }

    function endAction()
    {
        $this->user_session->nullify();
        $this->response->redirect('/', true);
    }

    function checkoutAction()
    {
        $ud = $this->user_session;
        $view = $this->getView();
        $ud->read();
        if (!$ud->isEmpty()) {
            $ud->wipe();
            return $this->render('login', 'logout');
        } else {
            return $this->render('login', 'error');
        }
    }

    private function setForm($m)
    {
        $m->formid = 'user-login';
        $this->captchaView($m);
        $this->xcheckView($m);
    }

    // POST from login form

    function errorLogin($msg)
    {
        /** $logger = new \Log('login.log');
          $logger->write('Fail login - ' . $msg);
         */
        $m = $this->getViewModel();
        $m = $view->m;

        if (!$m->has('email')) {
            $m->email = '';
        }
        if (!$m->has('alias')) {
            $m->alias = '';
        }
        $this->logger->info("Login fail: " . $msg);
        $m->password = '';
        $this->setForm($m);
        $req = $this->request;
        if ($req->isAjax()) {
            $this->noLayouts();
            return $this->render('partials', 'login/fields');
        }
    }

    function errorForgot($msg)
    {
        $logger = $this->logger;
        $logger->error('Fail Forgot - ' . $msg);
        $this->flash($msg);
        return $this->forgotView();
    }

    function errorChangePwd($msg)
    {
        $logger = new \Log('login.log');
        $logger->write('Password change - ' . $msg);
        $this->flash($msg);
        $this->changePwdView();
    }

    function changePwdView()
    {
        return $this->render('login','changePWD');
    }

    function changePwdPostAction()
    {
        $post = $_POST;
        $newpwd = Valid::toStr($post, 'new_pwd', null);
        $chkpwd = Valid::toStr($post, 'confirm_pwd', null);
        $email = Valid::toEmail($post, 'email');

        if (empty($newpwd) || ($newpwd !== $chkpwd)) {
            return $this->errorChangePwd("Password not confirmed");
        }
        $user = Users::findFirstByEmail($email);

        if (empty($user)) {
            return $this->errorChangePwd("User not found for $email");
        }

        $db = $this->db;
        $db->begin();
        $crypt = $this->security;
        try {
            $user->password = $crypt->hash($newpwd);
            $user->update();
            UserLog::logPwdChange($user->id);
            $db->commit();
        } catch (\PDOException $perr) {
            return $this->errorChangePwd($perr->getMessage());
        }
        return $this->render('login', 'pwd_changed');
    }

    function changePwd()
    {
        if (!$this->app->https()) {
            return;
        }
        $req = $_REQUEST;
        $view = $this->view;
        $view->header = "Change password";
        $this->changePwdView();
    }

    function defaultError()
    {
        $view = $this->view;
        $view->content = 'home/error';
        $view->layout = 'minimal';
        $view->assets(['bootstrap']);
        echo $view->render();
    }
    /** handle a returned reset password URL */
    function resetPwdAction($code,$email)
    {
        if ($this->need_ssl()) {
            return $this->secure_connect();
        }
        $req = $_REQUEST;

        UserLog::deleteOldCodes();
        $valid = ResetCode::findFirstByCode($code);

        if (!empty($valid)) {
            $userid = $valid->user_id;
            $user = Users::findFirstById($userid);
            if (empty($user) || ($user->email !== $email)) {
                return $this->defaultError();
            }
            $m = $this->getViewModel();
            $m->email = $email;
            $m->header = "Change password for " . $email;
            $m->url = "/login/";
            return $this->changePwdView();
        } else {
            $this->flash('Code is invalid or expired');
            $this->reroute('\login\changepwd');
        }
    }

    function forgotView()
    {
        $m = $this->getViewModel();
        if (!isset($m->email)) {
            $m->email = '';
        }
        $this->xcheckView($m);
        $this->captchaView($m);
        return $this->render('login', 'forgot');
    }

    function forgotAction()
    {
        return $this->forgotView();
    }

    public function forgotPostAction()
    {
        $post = $_POST;
        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            $this->errorForgot('Google error ' . $verify['errorcode']);
            return;
        }

        $email = Valid::toEmail($post, 'email');
        $m = $this->getViewModel();
        $m->email = $post['email'];
        if (empty($email)) {
            $this->errorForgot('Need valid email');
            return;
        }

        $user = Users::findFirstByEmail($m->email);


        if (empty($user)) {
            $this->errorForgot('No match was found for email');
            return;
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $evt_data = $_SERVER['HTTP_USER_AGENT'];
        $code = UserLog::newUserConfirm($user, UserLog::PW_RESET, $ip, $evt_data);
        if ($code === false) {
            $this->errorForgot('Reset code generation failure');
            return;
        }
        $name = $user->name;
        $app = $this->app;
        $m->domain = $app->domain;
        $m->confirmUrl = '/reset-password/' . $code . '/' . $user->email;
        $m->sendDate = Valid::now();
        $m->site = $app->organization;
        $m->link = $m->domain . $m->confirmUrl;

        $params = ['m' => $m, 'app' => $app];
        $textMsg = static::simpleView( 'email/reset_text',$params);
        $htmlMsg = static::simpleView( 'email/reset',$params);

        $mailer = new SwiftMail($app);

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
            return $this->errorForgot($isValid['errors']);
        } else {
            $m->email = $email;
            $this->noLayouts();
            return $this->render('login', 'reset_sent');
        }
    }

    /**
     * Confirm login post
     */
    function checkAction()
    {
        $post = $_POST;
        $m = $this->getViewModel();
        $m->email = Valid::toEmail($post, "email");
        $m->alias = Valid::toStr($post, "alias");
        $logger = $this->logger;
        $s = "";
        if (!empty($m->email)) {
            $s .= "Email: " . $m->email;
        }
        if (!empty($m->alias)) {
            $s .= "Alias: " . $m->alias;
        }
        $logger->info("Login Attempt: " . $s);
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

        try {
            if (!empty($m->email)) {
                $user = Users::findFirstByEmail($m->email);
            } else if (!empty($m->alias)) {
                $user = Users::findFirstByName($m->alias);
            } else {
                $user = null;
            }
        } catch (\Exception $ex) {
            return $this->errorLogin($ex->getMessage());
        }
        if (is_null($user) || empty($user)) {
            return $this->errorLogin("Not found");
        }
       
        $name = $user->name;
        
        $secure = $this->security;
        $stored = $user->password;
        $m->password = Valid::toStr($post, "password");
        $good = $secure->checkHash($m->password, $stored);
        if (!$good) {
            return $this->errorLogin('Authentication Failure');
        } else {
            $roles = UserRoles::getRoleList($this->db, $user->id);
            $user_session->setUser($user, $roles);

            $this->flash("Logged in as " . $user_session->getUserName());

            UserLog::login($user->id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            $this->noLayouts();
            return $this->render('login', 'details');
        }
    }

    function errorSignup($msg)
    {
        $logger = new \Log('login.log');
        $logger->write('Fail Signup - ' . $msg);
        $f3 = $this->f3;
        $f3->set('message', $msg);
        $this->signupView();
    }

    function signupPost()
    {
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


        $crypt = $this->security;

        $user['password'] = $crypt->hash($pwd1);
        $ip = $_SERVER['REMOTE_ADDR'];
        $evt_data = $_SERVER['HTTP_USER_AGENT'];
        $code = UserEvent::newUserConfirm($user, UserEvent::EMAIL_CK, $ip, $evt_data);
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

    function signupView()
    {
        if (!$this->app->https()) {
            return;
        }
        $m = $this->getViewModel();
        $m->title = "Register";

        $this->captchaView($m);
        $this->xcheckView($m);
        return $this->render('login', 'signup');
    }

    function signup()
    {
        $view = $this->view;
        $view->rec = new User();
        $view->message = '';
        $this->signupView();
    }

}
