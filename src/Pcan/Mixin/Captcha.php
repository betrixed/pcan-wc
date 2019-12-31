<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Pcan\Mixin;
use WC\UserSession;
/**
 * Captcha validation of forms for Controller
 *
 * @author michael
 */
trait Captcha {
/// Retrieve google recaptch result from post array reference
    public function captchaResult(&$post) {
        $f3 = $this->f3;
        $captcha = $f3->get('secrets.Recaptcha');
        if (UserSession::isLoggedIn('User')) {
            $captcha['enabled'] = false;
        }
        if ($captcha['enabled']) {
            return Valid::recaptcha($google['secret'], $post['g-recaptcha-response']);
        }
        else { // fake it, already logged in verified
            return ['success' => true, 'errorcode' => 0];
        }
    }
/// Setup view google variable with recaptcha data
    public function captchaView($view) {
        $f3 = $this->f3;
        $captcha = &$f3->ref('secrets.ReCaptcha');
        if (UserSession::isLoggedIn('User')) {
            $captcha['enabled'] = false;
        }
        $view->google = &$captcha;
    }
    
    public function xcheckView() {
        $f3 = $this->f3;
        $view = $this->view;
        $us = UserSession::read();
        if (is_null($us)) {
            $us = UserSession::guestSession();
        }
        $view->us = $us;
        $view->xcheck = UserSession::session()->csrf();
        $us->setKey("signup-xcheck", $view->xcheck);
    }
    // check result of form submission for cross scripting
    public function xcheckResult(&$post) {
        $us = UserSession::read();
        if (is_null($us)) {
            return false;
        }
        $xcheck = $us->getKey("signup-xcheck");
        if (!empty($xcheck)) {
            return ($xcheck === $post['xcheck']);
        }
        else {
            return false;
        }
    }
}
