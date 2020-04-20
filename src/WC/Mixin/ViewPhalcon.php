<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Mixin;
use WC\UserSession;
use WC\App;
use Phalcon\Mvc\View\Simple;
use Phalcon\Mvc\View;
/**
 * Same functions, including constructor, as WC\Controller
 *
 * @author michael
 */
trait ViewPhalcon {

    
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
        $m = $view->m;
        if (!isset($m->theme)) {
            $m->theme = App::instance()->theme;
        }
        
        if (UserSession::hasInstance()){
            $us = UserSession::read();
        }

        $flash = !empty($us) ? $us->getFlash() : [];
        
        UserSession::save();
        $view->setVars(['sessUser'=>$us, 'flash' => $flash]);
        $view->start();
        $view->render($controller, $action, $params);
        $view->finish();
        return $view->getContent();
    }
    
    static function  simpleView($path, $params) {
        $view = new Simple();
        // ViewDir must be string!
        $view->setViewsDir(App::instance()->plates->UI[0]);
        return $view->render($path,$params);
    }
    public function noLayouts() {
        $view = $this->view;
        $view->disableLevel(
            [
                View::LEVEL_LAYOUT      => true,
                View::LEVEL_AFTER_TEMPLATE => true,
                View::LEVEL_MAIN_LAYOUT => true,
            ]);
    }

}
