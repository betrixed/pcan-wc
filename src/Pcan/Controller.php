<?php

namespace Pcan;

use WC\UserSession;

class Controller {
    // fat-free provides for __constructor
    public $f3;
    public $args;

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
        UserSession::shutdown();
    }

    public function getWebDir() {
        if (!isset($this->webdir)) {
            $this->webdir = \Base::instance()->get('ROOT') . "/";
        }
        return $this->webdir;
    }

    public function flash($msg, $extra = null, $status = 'info') {
        UserSession::flash($msg, $extra, $status);
    }

    public function __construct($f3, $args) {
        $ctrl_time = microtime(true);
        $this->f3 = $f3;
        $this->args = $args;
        $f3->set('ctrl_time', $ctrl_time);
    }

}
