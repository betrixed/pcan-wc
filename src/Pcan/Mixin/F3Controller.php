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
 * Same functions, including constructor, as WC\Controller
 *
 * @author michael
 */
trait F3Controller {

    /**
     * Override this and use Mixin\Auth
     * to limit access to UserSession role name.
     * @return string
     */
    public function getAllowRole() {
        return 'Editor';
    }
    
    function afterRoute() {
        // session becomes read only
        \WC\UserSession::shutdown();
    }

    public function getWebDir() {
        if (!isset($this->webdir)) {
            $this->webdir = \Base::instance()->get('ROOT') . "/";
        }
        return $this->webdir;
    }

    public function flash($msg, $extra = null, $status = 'info') {
        \WC\UserSession::flash($msg, $extra, $status);
    }

    public function __construct($f3, $args) {
        $this->f3 = $f3;
        $this->args = $args;
        $this->app = \WC\App::instance();
        $this->app->ctrl_time = microtime(true);
    }
}
