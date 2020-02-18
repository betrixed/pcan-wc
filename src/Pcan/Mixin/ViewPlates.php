<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Pcan\Mixin;
use Pcan\HtmlPlates;
use WC\UserSession;
/**
 * Template Views using League\Plates\Engine
 *
 * @author michael rynn
 */
trait ViewPlates {
    public $view;
   
    public function getView() {
        if (is_null($this->view)) {
            $this->init_View($this->f3);
        }
        return $this->view;
    }
    public function init_View($f3, $path = null, $ext = null) {
        $view = new HtmlPlates($f3, $path, $ext);
        //$f3->set('view', $view); // not going to rely on this
        $view->usrSess = UserSession::read();
        $this->view = $view;
    }
    
    static public function renderView($model, $viewName) {
        $f3 = \Base::instance();
        $v = new HtmlPlates($f3); 
        $v->setModel($model); // replace the model
        $v->content = $viewName;
        return $v->renderView();
    }
}
