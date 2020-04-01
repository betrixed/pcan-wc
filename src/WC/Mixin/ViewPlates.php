<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Mixin;
use WC\HtmlPlates;
use WC\UserSession;
use WC\App;

/**
 * Template Views using League\Plates\Engine
 *
 * @author michael rynn
 */
trait ViewPlates {
    public $view;
    public $layoutsDir;
    public $outer_view;
    
    public function getView() {
        if (is_null($this->view)) {
            $this->init_View();
        }
        return $this->view;
    }
    public function init_View() {
        $view = new HtmlPlates();
        $view->usrSess = UserSession::read();
        $this->view = $view;
        $app = $view->app;
        $this->layouts_dir = $app->plates->layoutsDir;
        $this->outer_view = $app->plates->outer_view;
    }
    public function initialize() {
        $this->app->ctrl_time = microtime(true);
    }
    public function render($controller, $action, $params = []) : string {
        $view = $this->getView();
        if (!empty($params)) {
            $view->add($params);
        }
        $view->content = $controller . '/' . $action;
        $view->layout = $this->layouts_dir . '/' . $controller;
        $view->outer_view = $this->layouts_dir . '/' . $this->outer_view;
        return $view->render();
    }
    static public function renderView($model, $viewName) {
        $v = new HtmlPlates(); 
        $v->setModel($model); // replace the model
        $v->content = $viewName;
        return $v->renderView();
    }
}
