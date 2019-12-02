<?php
namespace Pcan;

use WC\UserSession;
use League\Plates\Engine;

class Controller {
    public $view;
    public $f3;
    protected $php;
    protected $ui;
    private $webdir;
    
    public function getAllowRole() {
        return 'Editor';
    }
    
    function denied() {
        $view = $this->view;
        $view->content = 'home/error.phtml';
        $view->assets('bootstrap');
        echo $this->view->render();
    }
    function afterRoute() {
        // session becomes read only
        UserSession::shutdown();
    }
    /*
    function reroute($url) {
        if ( \Registry::exists('UserSession')) {
            $us = UserSession::instance();
            $us->write(); // finalize session now
        }
        $this->f3->reroute($url);
    }
    */
    function auth() {
        if (!UserSession::auth($this->getAllowRole())) {
            $this->denied();
            return false;
        }
        return true;
    }
    // retrieve google recaptch result from post array reference
    public function captchaResult(&$post) {
        $f3 = $this->f3;
        $captcha = &$f3->ref('secrets.Recaptcha');
        if (UserSession::isLoggedIn('User')) {
            $captcha['enabled'] = false;
        }
        if ($captcha['enabled']) {
            return Valid::recaptcha($google['secret'], $post['g-recaptcha-response']);
        }
        else { // fake it
            return ['success' => true, 'errorcode' => 0];
        }
    }
    // setup google recaptch data for view object
    public function captchaView() {
        $f3 = $this->f3;
        $captcha = &$f3->ref('secrets.ReCaptcha');
        if (UserSession::isLoggedIn('User')) {
            $captcha['enabled'] = false;
        }
        $this->view->google = &$captcha;
    }
    
    public function getUserSession() {
        if (empty($this->us)) {
            $us = UserSession::instance();
            $us->setGuest();
            $us->write();
            $this->us = $us;
        }
        return $this->us;
    }
    public function xcheckView() {
        $f3 = $this->f3;
        $view = $this->view;
        $us = $this->getUserSession();
        $view->us = $us;
        $view->xcheck = UserSession::session()->csrf();
        $us->setKey("signup-xcheck", $view->xcheck);
    }
    // check result of form submission for cross scripting
    public function xcheckResult(&$post) {
        $us = $this->getUserSession(); 
        $xcheck = $us->getKey("signup-xcheck");
        if (!empty($xcheck)) {
            return ($xcheck === $post['xcheck']);
        }
        else {
            return false;
        }
    }
    public function getWebDir() {
        if (!isset($this->webdir)) {
            $this->webdir = \Base::instance()->get('ROOT') . "/";
        }
        return $this->webdir;
    }
    
    public function flash($msg, $extra = null, $status = 'info') {
        UserSession::flash($msg,$extra,$status);
    }

    public function __construct() {
        $f3 = \Base::instance();
        $this->f3 = $f3;
        $this->us = UserSession::read();
        $this->php = $f3->get('php');
        $view = new Html($f3);
        $f3->set('view', $view);
        $view->usrSess = $this->us;
        $this->view = $view;
    }

}
