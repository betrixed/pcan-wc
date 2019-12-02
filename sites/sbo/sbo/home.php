<?php
namespace SBO;

use WC\DB\Links;
use WC\DB\Server;
use WC\Valid;
use WC\Html;

class Home extends \WC\Controller {


    private function main() {
        $sql = <<<EOD
select id, url, title, sitename, summary, urltype, date_created 
  from links
  where (urltype='Remote' or urltype='Front' or urltype='Blog' or urltype='Event') 
  and enabled = 1
  order by date_created desc
 limit 0, 20
EOD;
        $db = Server::db();
        return $db->exec($sql);
    }

    private function events() {
        $eventSql = <<<EOD
SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
 from blog A join event B on A.id = B.blogid
 where B.toTime > ? and B.enabled = 1
 order by B.fromTime
EOD;
     return Server::db()->exec($eventSql, [Valid::now()]);
    }
    private function sides() {
        $sideSQL = <<<EOD
SELECT id, url, title, date_created as sysdate, summary 
 from links where urltype='Side' and enabled = 1
 order by date_created desc
EOD;
        return Server::db()->exec($sideSQL);
    }

    function show($f3, $args) {

        $view = $this->view;
        $view->sides = $this->sides();
        $view->main = $this->main();
        $view->events = $this->events();
        
        $view->title = "SBO Home";
        $panels = Links::byType('Panel');
        if ($panels['ct'] > 0) {
            $view->topPanels = &$panels['rows'];
        } else {
            $view->topPanels = [];
        }
        $agent = &Html::$browser;
        
        
        
        $view->agent = &$agent;
        
        if (($agent['name'] === 'Apple Safari') && ($agent['version'] === '5.0.6')) {
            $view->nav = 'home/simple_nav.phtml';
            $view->assets('simple');
        }
        else {
            $view->nav = 'home/home_nav.phtml'; // 'home/home_nav.phtml';
            $view->assets('bootstrap');
        }

        $view->content = 'home/home.phtml';
        $view->layout = 'home/home_layout.phtml';
        
        echo $view->render();
    }

    function links($f3, $args) {
        $view = $this->view;
        $req = &$f3->ref('REQUEST');


        if (isset($req['k'])) {
            $linkType = $req['k'];
            $view->links = Links::byType($linkType);
            $select = ['Remote' => 'Web', 'Event' => 'Events', 'Blog' => 'Here'];
            $view->title = $select[$linkType];
        } else {
            $view->links = Links::homeLinks();
            $view->title = "All Links";
        }
        $view->assets(['bootstrap', 'grid']);
        $view->content = 'home/links.phtml';
        echo $view->render();
    }

}
