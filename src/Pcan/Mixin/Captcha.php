<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Pcan\Mixin;
use WC\UserSession;
use WC\App;
/**
 * Captcha validation of forms for Controller
 *
 * @author michael
 */
trait Captcha {
/**
 * Retrieve site settings for recaptcha result from post as array
 * @return array 
 */
    public function captchaSettings() {
        if (UserSession::isLoggedIn('User')) {
             return ['enabled' => false];
        }
        $cfg =  App::instance()->get_secrets();
        if ( isset($cfg) && isset($cfg['ReCaptcha'])) {
           return $cfg['ReCaptcha'];
        }
        return ['enabled' => false];
    }
    /**
      * verify form post for recaptcha OK
     * @param array $post
     * @return array ['success', 'errorcode']
     */
    public function captchaResult(&$post) {
        $google = $this->captchaSettings();
        if ($google['enabled']) {
            return Valid::recaptcha($google['secret'], $post['g-recaptcha-response']);
        }
        else { // return everything OK
            return ['success' => true, 'errorcode' => 0];
        }
    }

    /**
     * Install initial details  of google recaptcha in passed object properties 
     */
    public function captchaView($model) {
        $model->google = $this->captchaSettings();
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
