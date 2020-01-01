<?php

namespace Pcan\Mixin;

/**
 * Add to controller if login required
 *
 * @author michael rynn
 */
use \WC\UserSession;
use \WC\App;

trait Auth {
    /**
     * 
     * @return string
     */
    function denied() {
        // Assume a view trait exists
        echo App::error_page("Page access is not authorized");
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
