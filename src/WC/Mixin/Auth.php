<?php

namespace WC\Mixin;
use Phiz\Mvc\Dispatcher;
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
     * @return boolean
     */
    public function beforeExecuteRoute($dispatcher) : bool  {
        if (!$this->user_session->auth($this->getAllowRole())) {
            $dispatcher->forward(['controller' => 'error', 'action' => 'block']);
            return false;
        }
        return true;
    }

}
