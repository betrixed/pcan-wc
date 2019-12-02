<?php
namespace SBO;

use WC\DB\Links;
use WC\DB\Server;
use WC\DB\Blog;
use WC\Valid;

//! Front-end Show past events in order, and also the older past events article list


class FutureEvents extends \WC\Controller {
    
    private function events() {
        $db = Server::db();
        $sql = <<<EOD
SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
 from blog A join event B on A.id = B.blogid
 where B.fromTime >= NOW() and A.enabled = 1
 order by B.fromTime
EOD;
        return Server::db()->exec($sql);
    }
    public function show($f3, $args) {
        $view = $this->view;
        $view->list = $this->events();
        if (empty($view->list)) {
            $this->flash('No events published the moment');
        }
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        
        if ($isSub) {
            $view->layout = 'home/future.phtml';
        }
        else {
            $view->content = 'home/future.phtml';
        }
        $view->assets('bootstrap');
        echo $view->render();
    }
}
