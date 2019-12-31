<?php

namespace Pcan;

use WC\UserSession;
use League\Plates\Engine;

class Controller {

    public $f3;
    protected $php;
    protected $ui;
    private $webdir;
    private $us;


    /*
      function reroute($url) {
      if ( \Registry::exists('UserSession')) {
      $us = UserSession::instance();
      $us->write(); // finalize session now
      }
      $this->f3->reroute($url);
      }
     */

    /**
     * Try to return existing UserSession object
     * @return type
     */
    public function getUserSession()  {
        if (is_null($this->us)) {
            $this->us = UserSession::read();
        }
        return $this->us;
    }

    function denied() {
        $view = $this->view;
        $view->content = 'home/error.phtml';
        $view->assets('bootstrap');
        echo $this->view->render();
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

    public function __construct() {
        $ctrl_time = microtime(true);
        $f3 = \Base::instance();
        $this->f3 = $f3;
        $this->php = $f3->get('php');
        $f3->set('ctrl_time', $ctrl_time);
        $this->getUserSession();
    }

}
