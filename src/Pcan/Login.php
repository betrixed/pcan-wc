<?php
namespace Pcan;
/**
 * Description of login
 *
 * @author Michael Rynn
 */
use Pcan\DB\UserEvent;
use Pcan\DB\User;
use Pcan\DB\ResetCode;
use WC\DB\Server;
use WC\UserSession;
use WC\Valid;

class Login extends Controller
{

    private $username;

    function index($f3, $args)
    {
        if (!UserSession::https($f3)) {
            return;
        }
        $this->captchaView();
        $this->xcheckView();
        
        $view = $this->view;
        $view->assets('bootstrap');
        
        $view->content = 'home/login.phtml';
        $view->title = 'Login';
        echo $view->render();
    }

    function checkout($f3, $args)
    {
        $ud = UserSession::read();
        $view = $this->view;
        if (!empty($ud)) {
            $ud->wipe();
            $view->content = 'home/logout.phtml';
        } else {
            $view->content = 'home/error.phtml';
        }
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    // POST from login form

    function errorLogin($msg)
    {
        $logger = new \Log('login.log');
        $logger->write('Fail login - ' . $msg);
        $f3 = $this->f3;
        $f3->set('message', $msg);
        $f3->set("POST.password", '');
        $this->index($f3, $args);
    }

    function errorForgot($msg)
    {
        $logger = new \Log('login.log');
        $logger->write('Fail Forgot - ' . $msg);
        $f3 = $this->f3;
        $this->flash($msg);
        $this->forgotView();
    }

    function errorChangePwd($msg)
    {
        $logger = new \Log('login.log');
        $logger->write('Password change - ' . $msg);
        $f3 = $this->f3;
        $this->flash($msg);
        $this->changePwdView();
    }

    function changePwdView()
    {
        $view = $this->view;
        $view->content = 'home/changePWD.phtml';
        $view->assets(['bootstrap']);
        echo $this->view->render();
    }

    function changePwdPost($f3, $args)
    {
        $post = &$f3->ref('POST');
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
        $view->content = 'home/pwd_changed.phtml';
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    function changePwd($f3, $args)
    {
        if (!UserSession::https($f3)) {
            return;
        }
        $req = &$f3->ref('REQUEST');
        $view = $this->view;
        $view->header = "Change password";
        $this->changePwdView();
    }

    function defaultError()
    {
        $view = $this->view;
        $view->content = 'home/error.phtml';
        $view->layout = 'minimal.phtml';
        $view->assets(['bootstrap']);
        echo  $view->render();
    }

    function resetPwd($f3, $args)
    {
        $req = &$f3->ref('REQUEST');
        $code = $args['code'];

        ResetCode::deleteOldCodes();
        $valid = ResetCode::byCode($code);

        if ($valid !== false) {
            $email = trim($args['email']);
            
            $userid = $valid['user_id'];
            $user = User::byId($userid);


            if ($user === false || $user['email'] !== $email) {
                return $this->defaultError();
            }
            $view = $this->view;
            $view->email = $email;
            $view->header = "Change password for " . $email;
            $view->url = "/login/";
            $this->changePwdView();
        } else {
            $this->flash('Code is invalid or expired');
            UserSession::reroute('\login\changepwd');
        }
    }

    function forgotView()
    {
        $view = $this->view;
        if (!isset($view->email)) {
            $view->email = '';
        }
        $this->captchaView();
        $view->content = 'home/forgotPassword.phtml';
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    function forgot($f3, $args)
    {
        $this->forgotView();
    }

    function forgotPost($f3, $args)
    {
        $post = &$f3->ref('POST');
        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            $this->errorForgot('Google error ' . $verify['errorcode']);
            return;
        }

        $email = Valid::toEmail($post, 'email');
        $view = $this->view;
        $view->email = $post['email'];
        if (empty($email)) {
            $this->errorForgot('Need valid email');
            return;
        }

        $user = new User();
        $found = $user->load(["email = ?", $email]);

        if ($found === false) {
            $this->errorForgot('Match not found for email');
            return;
        }
        $code = UserEvent::newUserConfirm($found, UserEvent::PW_RESET, true);
        if ($code === false) {
            $this->errorForgot('Reset code generation failure');
            return;
        }
        $name = $found['name'];

        $view->publicUrl = $f3->get('domain');
        $view->confirmUrl = '/reset-password/' . $code . '/' . $found['email'];

        $textMsg = TagViewHelper::render('email/reset.txt');
        $htmlMsg = TagViewHelper::render('email/reset.phtml');

        $mailer = new SwiftMail();

        $msg = [
            "subject" => 'Password Reset from ' . $view->publicUrl,
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
            $view->content = 'home/resetSent.phtml';
            $view->email = $email;
            $view->assets(['bootstrap']);
            echo $view->render();
        }
    }

    /**
     * Confirm login post
     */
    function check($f3, $args)
    {
        $post = &$f3->ref('POST');

        if (!$this->xcheckResult($post)) {
            $this->errorLogin('Cross script protection failure');
            return;
        }

        $verify = $this->captchaResult($post);
        if (!$verify['success']) {
            $this->errorLogin('Google error ' . $verify['errorcode']);
            return;
        }


        $ud = UserSession::read();
        if (!empty($ud)) {
            $ud->wipe();
        }

        $username = $f3->get("POST.email");
        $password = $f3->get("POST.password");
        $user = new User();


        if (strpos($username, '@') !== false) {
            $this->username = filter_var($username, FILTER_SANITIZE_EMAIL);
            $found = $user->load("email = '" . $this->username . "'");
        } else {
            $this->username = filter_var($username, FILTER_SANITIZE_STRING);
            $found = $user->load("name = '" . $this->username . "'");
        }
        if ($found) {
            $crypt = \Bcrypt::instance();
            $good = $crypt->verify($password, $user->get('password'));
            if (!$good) {
                $this->errorLogin('Authentication Failure');
                return;
            } else {
                $data = UserSession::instance();
                $data->setUser($user);
                $data->addMessage("Logged in as " . $data->userName);
                UserSession::reroute('/dash');
                return;
            }
        } else {
            $this->errorLogin("Not found");
        }
        return;
    }

    function errorSignup($msg)
    {
        $logger = new \Log('login.log');
        $logger->write('Fail Signup - ' . $msg);
        $f3 = $this->f3;
        $f3->set('message', $msg);
        $this->signupView();
    }

    function signupPost($f3, $args)
    {
        $post = &$f3->ref('POST');
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
        $htmlMsg = TagViewHelper::render('form/signup_html.phtml');
        $mailer = new SwiftMail();
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
        $f3 = $this->f3;
        if (!UserSession::https($f3)) {
            return;
        }
        $view = $this->view;
        $view->title = "Register";

        $this->captchaView();
        $this->xcheckView();
        $view->content = 'home/signup.phtml';
        $view->assets('bootstrap');

        echo $view->render();
    }

    function signup($f3, $args)
    {
        $view = $this->view;
        $view->rec = new User();
        $view->message = '';
        $this->signupView();
    }

}
