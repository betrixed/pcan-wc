<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Mixin;
use WC\UserSession;
use WC\App;
use WC\WConfig;
use Phalcon\Mvc\View\Simple;
use Phalcon\Mvc\View;
/**
 * Same functions, including constructor, as WC\Controller
 *
 * @author michael
 */
trait ViewPhalcon {

    
    function afterExecuteRoute() {
        // flush session writes, becomes read only
        $this->user_session->shutdown();
    }
    
    public function setViewModel( $m ) {
        $this->view->m = $m;
    }
    public function getViewModel() : WConfig
    {
        return $this->view->m;
    }
    public function getView() {
        return $this->view; // magic view service getter in Phalcon
    }
    public function flash($msg, $extra = null, $status = 'info') {
        $this->user_session->flash($msg, $extra, $status);
    }

    public function initialize() {
        $app = $this->app;
        $app->ctrl_time = microtime(true);
        if (isset($app->route)) {
            $this->title = $app->route->getName();
        }
        else {
            $this->title = $app->site;
        }
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
        $m = $view->m;
        if (!isset($m->title)) {
            $m->title = $this->title;
        }
        $app = $this->app;
        if (!isset($m->theme)) {
            $m->theme = $app->theme;
        }
        $us = $app->user_session;
        // strip the flash data and save session
        if (!empty($us)) {
             $flash = $us->getFlash();
             $us->save();
        }
        else {
            $flash = [];
        }
        
        $view->setVars(['sessUser'=>$us, 'flash' => $flash]);
        $view->start();
        $view->render($controller, $action, $params);
        $view->finish();
        return $view->getContent();
    }
    
    function  simpleView($path, $params, ?object $plates = null) {
        $view = new Simple();
        // ViewDir must be string!
        // Simple View doesn't know about alternate paths, so try each in turn.
        
        if (empty($plates)) {
            $app = $this->app;
            $plates = $app->plates;
        }
        $ext = $plates->ext;
        $found = false;
        $basename = $path . "." . $ext;
        $UI = $plates->UI;
        
        foreach($UI as $dir) {
                $testfile = $dir . $basename;
                if (file_exists($testfile)) {
                         $view->setViewsDir($dir);
                         $found = true;
                         break;
                }
        }
        if (!$found) {
            throw new \Exception("Template file not found " . $basename);
        }
        return $view->render($path,$params);
    }
    /** disable all but main phalcon view layout
     * 
     */
    public function noLayouts() {
        $view = $this->view;
        $view->disableLevel(
            [
                View::LEVEL_LAYOUT      => true,
                View::LEVEL_AFTER_TEMPLATE => true,
                View::LEVEL_MAIN_LAYOUT => true,
            ]);
    }

    public function noAccess() {
        $app = $this->app;
        $this->user_session->flash('No access to ' . $app->arguments);
        $this->reroute('/error/block');
        return null;
    }
}
