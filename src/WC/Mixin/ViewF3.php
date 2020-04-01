<?php

namespace Pcan\Mixin;

use Pcan\Html;
use \WC\UserSession;
/**
 * Fat Free Template Views for Controller
 *
 * @author michael rynn
 */

trait ViewF3 {
    public $view;
    
    public function getView() {
        if (is_null($this->view)) {
            $this->init_View($this->f3);
        }
        return $this->view;
    }
    public function init_View($f3)  {
        $view = new Html($f3);
        $f3->set('view', $view);
        $view->usrSess = UserSession::read();
        $this->view = $view;
        return $view;
    }
}
