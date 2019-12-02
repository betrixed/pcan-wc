<?php

namespace GH;

use Pcan\DB\Links;
use WC\DB\Server;
use WC\Valid;

//! Front-end processorg


class Home extends \Pcan\Controller {

    private function query($qry) {
        $pdo = Server::db()->pdo();
        $stmt = $pdo->query($qry);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function events() {
        $qry = <<<EOQ
       SELECT A.id, A.title, A.date_updated as sysdate, 
      A.article, A.style, A.title_clean, C.content
 from blog A join event B on A.id = B.blogid
 left outer join (select BM.blog_id, BM.content from 
     blog_meta BM join meta M on BM.meta_id = M.id
     where M.meta_name = 'og:description') C on blog_id = A.id
    where B.toTime > NOW() and A.enabled = 1
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

        $view = $this->view;
        $view->sides = $this->sides();
        $view->main = $this->main();
        $view->events = $this->events();

        $view->title = "PCAN Home";
        $panels = Links::byType('Panel');
        if ($panels['ct'] > 0) {
            $view->topPanels = &$panels['rows'];
        } else {
            $view->topPanels = [];
        }

        $view->assets('bootstrap');
        $view->content = 'front/home.phtml';
        $view->layout = 'front/layout.phtml';
        $view->nav = 'front/nav.phtml';
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
