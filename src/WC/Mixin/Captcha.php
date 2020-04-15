<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Mixin;
use WC\UserSession;
use WC\App;
use WC\Valid;
use Phalcon\Http\Response;
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
            $ip = $this->request->getClientAddress();
            return Valid::recaptcha($google['secret'], $post['g-recaptcha-response'],$ip);
        }
        else { // return everything OK
            return ['success' => true, 'error-codes' => 0];
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
        
        $us = UserSession::read();
        // Its never null after a read!
        if (is_null($us) || empty($us->userName)) {
            $us = UserSession::guestSession();
        }
        $model->us = $us;
        $security = $this->security;
        $value =  $security->getToken();
        $key = $security->getTokenKey();
        
        $xcheck = ['key' => $key, 
            'value' => $value];  
        $model->xcheck = $xcheck;
        $us->setKey("xcheck", $xcheck);
    }
    /** 
     * Check result of form submission for cross scripting
     * against value stored in current persisted session
     * @param type $post Reference to Fat Free Post array
     * @return boolean
     */
    public function xcheckResult() : bool{
        $us = UserSession::read();
        if (is_null($us)) {
            return false;
        }
        $xcheck = $us->getKey("xcheck");
        if (!empty($xcheck) && isset($xcheck['key']) && isset($xcheck['value'])) {
            $key = $xcheck['key'];
            if (isset($_POST[$key])) {
                return ($_POST[$key] === $xcheck['value']) ? true : false;
            }
        }
        return false;
    }
    
    public function need_ssl() : bool {
        return !$this->request->isSecure();
    }
    public function secure_connect() {
        $url = $this->https_url();
        if (App::instance()->hasValidSSL) {
            $response = new Response();
            $response->redirect($url,true, 301);
            return false;
        }
        else {
            $v = $this->view;
            $v->m->url = $url;
            $v->setTemplateAfter('redirect');
            return $this->render('secure','https');
        }
    }
    
    public function https_url() {
        $server = $_SERVER;
        $ssl_host = App::instance()->get('ssl_host',null);
        $host = $server['HTTP_HOST'];
            // This is because a ssl certificate required a www.NAME
        if (!empty($ssl_host)) {
            $ssl_host = $ssl_host . '.';
            if (strpos($host, $ssl_host) !== 0) {
                $host = $ssl_host . $host;
            }
        }
        return 'https://' . $host . $server['REQUEST_URI'];
    }
}
