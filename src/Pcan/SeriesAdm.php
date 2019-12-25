<?php

namespace Pcan;
use WC\DB\Server;
/**
 * Controller for series Table
 *
 * @author Michael Rynn
 */
class SeriesAdm extends Controller {
use Mixin\ViewF3;
use Mixin\Auth;

    public function index($f3,$args) {
        $db = Server::db();
        $view = $this->getView();
        $view->content = 'series/index.phtml';
        $view->assets(['bootstrap']);
        $view->series = $db->exec('select * from series');
        echo $view->render();
    }
}
