<?php

namespace Pcan;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use WC\Valid;

class EventList extends Controller
{
use Mixin\ViewPlates;

    private function events() {
        $eventSql = <<<EOD
SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style, A.title_clean
 from blog A join event B on A.id = B.blogid
 where B.toTime > ? and A.enabled = 1
 order by B.fromTime
EOD;
     return Server::db()->exec($eventSql, [Valid::now()]);
    }
    
    public function index($f3, $args)
    {
        $view = $this->getView();
        $view->assets('bootstrap');
        $view->content = 'events/list.phtml';
        
        $m = $view->model;
        $m->title = "Events";
        $m->events = $this->events();
        
        echo $view->render();
    }
}