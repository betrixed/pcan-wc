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
    function denied() {
        // Assume a view trait exists
        $view = $this->getView();
        $view->content = 'home/error.phtml';
        $view->title = 'Error';
        echo $this->view->render();
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
