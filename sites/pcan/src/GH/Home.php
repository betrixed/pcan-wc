<?php

namespace GH;

use Pcan\DB\Links;
use WC\DB\Server;
use WC\Valid;

//! Front-end processorg


class Home extends \Pcan\Controller {
use \Pcan\Mixin\ViewPlates;

    private function query($qry) {
        $pdo = Server::db()->pdo();
        $stmt = $pdo->query($qry);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function events() {
        $db = Server::db();
        if ($db->driver() === 'sqlite') {
            $nowfn1 = "datetime( B.fromtime) > datetime('now')";
            $nowfn2 = "datetime( B.totime) > datetime('now')";
        }
        else {
            $nowfn1 = "B.fromtime > NOW()";
             $nowfn2 = "B.totime > NOW()";
        }
        $qry = <<<EOQ
SELECT A.id, A.title, B.fromtime as  date1, B.totime as date2,
      A.article, A.style, A.title_clean, C.content
 from blog A join event B on A.id = B.blogid and A.enabled = 1
 and (
 	((B.fromtime is NOT NULL) AND ( $nowfn1 ))
 	OR ((B.totime  is NOT NULL) AND ( $nowfn2 ))
 )
 join 
 (select MC.blog_id, MC.content from blog_meta MC join meta M on MC.meta_id = M.id
  where M.meta_name = 'og:description') C on C.blog_id = A.id
   order by B.fromtime  
EOQ;

        return $this->query($qry);
    }

    private function main() {

        $qry = <<<EOQ
select links.id, links.url, links.title,
  links.sitename, links.summary, links.urltype, links.date_created 
  from links
  where (links.urltype='Remote' or links.urltype='Front' 
      or links.urltype='Blog' or links.urltype='Event') 
  and links.enabled = 1
  order by links.date_created desc
 limit 20        
EOQ;

        return $this->query($qry);
    }

    private function sides() {
        $qry = <<<EOQ
select id, url, title, date_created, summary from links
 where urltype='Side' and enabled = 1
 order by date_created desc
EOQ;
        return $this->query($qry);
    }

    function show($f3, $args) {
        $view = $this->getView();
        $m = $view->model;
        
        $m->sides = $this->sides();
        $m->main = $this->main();
        $m->events = $this->events();

        $m->title = "PCAN Home";
        $panels = Links::byType('Panel');
        if ($panels['ct'] > 0) {
            $m->topPanels = &$panels['rows'];
        } else {
            $m->topPanels = [];
        }

        $view->assets('bootstrap');
        $view->content = 'front/home.phtml';
        $view->layout = 'front/layout.phtml';
        $view->nav = 'front/nav.phtml';
        echo $view->render();
    }

    function links($f3, $args) {
        $view = $this->getView();
        $req = &$f3->ref('REQUEST');
        $m = $view->model;

        if (isset($req['k'])) {
            $linkType = $req['k'];
            $m->links = Links::byType($linkType);
            $select = ['Remote' => 'Web', 'Event' => 'Events', 'Blog' => 'Here'];
            $m->title = $select[$linkType];
        } else {
            $m->links = Links::homeLinks();
            $m->title = "All Links";
        }
        $view->assets(['bootstrap', 'grid']);
        $view->content = 'home/links.phtml';
        echo $view->render();
    }

}