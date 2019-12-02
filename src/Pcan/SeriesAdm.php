<?php

namespace Pcan;
use WC\DB\Server;
/**
 * Controller for series Table
 *
 * @author Michael Rynn
 */
class SeriesAdm extends Controller {
    public function beforeRoute() {
        if (!$this->auth()) {
            return false;
        }
    }
    
    public function index($f3,$args) {
        $db = Server::db();
        $view = $this->view;
        $view->content = 'series/index.phtml';
        $view->assets(['bootstrap']);
        $view->series = $db->exec('select * from series');
        echo $view->render();
    }
}
