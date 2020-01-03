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

    /**
     * Install initial details  of google recaptcha in passed object properties 
     */
    public function captchaView($model) {
        $f3 = $this->f3;
        $captcha = &$f3->ref('secrets.ReCaptcha');
        if (UserSession::isLoggedIn('User')) {
            $captcha['enabled'] = false;
        }
        $model->google = $captcha;
    }
    /**
     * Install cross script attack protection string
     * in passed object properties, write to a user session.
     * Make a Guest Session if no current session.
     */
    public function xcheckView($model) {
        $f3 = $this->f3;
        $us = UserSession::read();
        if (is_null($us)) {
            $us = UserSession::guestSession();
        }
        $model->us = $us;
        $model->xcheck = UserSession::session()->csrf();
        $us->setKey("signup-xcheck", $model->xcheck);
    }
    /** 
     * Check result of form submission for cross scripting
     * against value stored in current persisted session
     * @param type $post Reference to Fat Free Post array
     * @return boolean
     */
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
