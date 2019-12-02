<?php
namespace SBO;

use WC\DB\Links;
use WC\DB\Server;
use WC\DB\Blog;
use WC\Valid;

//! Front-end Show past events in order, and also the older past events article list


class PastEvents extends \WC\Controller {
    
    private function eventArticle() {
        $db = Server::db();
                $sql = <<<EOD
    SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
        from blog A where A.title_clean = 'past-events';
EOD;
        return Server::db()->exec($sql);
    }
    private function pastEvents() {
        $db = Server::db();
        $sql = <<<EOD
SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
 from blog A join event B on A.id = B.blogid
 where B.toTime < NOW() and A.enabled = 1
 order by B.fromTime
EOD;
        return Server::db()->exec($sql);
    }
    
    public function show($f3, $args) {
        $view = $this->view;
        $view->list = $this->pastEvents();
        $view->old = $this->eventArticle();
        $view->content = 'home/past.phtml';
        echo $view->render();
    }
    public function eventlist($f3, $args) {
        $view = $this->view;
        $view->list = $this->pastEvents();
        $view->old = $this->eventArticle();
        
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        
        if ($isSub) {
            $view->layout = 'home/past.phtml';
        }
        else {
            $view->content = 'home/past.phtml';
        }
        $view->assets('bootstrap');
        echo $view->render();
    }
}
