<?php

namespace Pcan\Mixin;

/**
 * Add to controller if login required
 *
 * @author michael rynn
 */
use \WC\UserSession;

trait Auth {

    public function getAllowRole(): string {
        return 'Editor';
    }

    public function auth():  bool {
        if (!UserSession::auth($this->getAllowRole())) {
            $this->denied();
            return false;
        }
        return true;
    }

    public function beforeRoute() : bool {
        return $this->auth();
    }
}
