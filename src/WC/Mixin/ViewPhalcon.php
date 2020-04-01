<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Mixin;
use WC\UserSession;

/**
 * Same functions, including constructor, as WC\Controller
 *
 * @author michael
 */
trait ViewPhalcon {

    /**
     * Override this and use Mixin\Auth
     * to limit access to UserSession role name.
     * @return string
     */
    public function getAllowRole() {
        return 'Editor';
    }
    
    function afterExecuteRoute() {
        // session becomes read only
        UserSession::shutdown();
    }
    public function getView() {
        return $this->view; // magic view service getter in Phalcon
    }
    public function flash($msg, $extra = null, $status = 'info') {
        UserSession::flash($msg, $extra, $status);
    }

    public function initialize() {
        $this->app->ctrl_time = microtime(true);
    }
   /**
    * For Application implicit view is false.
    * Explicit view processing requires same sequence
    * used in Application.zep
    * @param type $controller
    * @param type $action 
    */
    public function render($controller, $action, array $params = []) : string 
    {
        $view = $this->view;
        $view->start();
        $view->render($controller, $action, $params);
        $view->finish();
        return $view->getContent();
    }
}
