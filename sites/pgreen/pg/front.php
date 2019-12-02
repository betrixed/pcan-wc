<?php
namespace PG;

//! Front-end processorg
use WC\DB\Links;
use WC\DB\Server;

class Front extends \WC\Controller {

    private function main() {
        $sql = <<<EOD
select id, url, title, sitename, summary, urltype, date_created 
  from links
  where (urltype='Remote' or urltype='Front' or urltype='Blog') 
  and enabled = 1
  order by date_created desc
 limit 0, 20
EOD;
        $db = Server::db();
        return $db->exec($sql);
    }

    private function sides() {
        $db = Server::db();

        $asides = $db->exec(
                "select id, url, title, date_created, summary from links"
                . " where urltype='Side' and enabled = 1"
                . " order by date_created desc"
        );

        return $asides;
    }

    function show($f3, $args) {
        $view = $this->view;
        $view->sides = $this->sides();
        $view->main = $this->main();
        $view->nav = "front/nav.phtml";
        $view->title = "Parramatta Greens";
        $panels = Links::byType('Panel');
        if ($panels['ct'] > 0) {
            $view->topPanels = &$panels['rows'];
        } else {
            $view->topPanels = [];
        }
        $view->assets('bootstrap');
        $view->layout = 'front/layout.phtml';
        $view->content = 'front/home.phtml';
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
