<?php

namespace Pcan\Mixin;

/**
 * Add to controller if login required
 *
 * @author michael rynn
 */
use \WC\UserSession;

trait Auth {

    /**
     * 
     * @return string
     */
    public function getAllowRole() {
        return 'Editor';
    }
    /**
     * 
     * @return boolean
     */
    public function auth() {
        if (!UserSession::auth($this->getAllowRole())) {
            $this->denied();
            return false;
        }
        return true;
    }
    /**
     * 
     * @return boolean
     */
    public function beforeRoute() {
        return $this->auth();
    }
}
