<?php

namespace Pcan;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use Pcan\DB\BlogCat;
use Pcan\DB\Blog;
use Pcan\DB\Linkery;
use Pcan\Models\MenuTree;
use WC\Valid;

class CatView extends Controller {
use Mixin\ViewF3;
    public function index($f3, $args) {
        $catclean = BlogCat::bySlug($args['catid']);
        $view = $this->getView();
        if (!empty($catclean)) {
            $req = &$f3->ref("REQUEST");
            $isSub = Valid::toInt($req, 'sub', 0);

            $results = Blog::listCategoryId($catclean->id);
            $view->blogs = $results;

            $reqid = Valid::toInt($req, 'id', 0);

            if ($reqid)
                $view->firstId = $reqid;
            else
                $view->firstId = (count($results) > 0) ? $results[0]['id'] : "";
            $view->catclean = $catclean;
            $title = $catclean['name'];
            $view->cattitle = $title;
            $view->title = $title;
            $view->assets([ 'bootstrap', 'cat-fetch']);
            if ($isSub) {
                $view->layout = 'cat/subindex.phtml';
            } else {
                $view->content = 'cat/index.phtml';
            }
            echo $view->render();
        }
    }

    private function menuView($mit) {
        $view = $this->getView();
        $menu = empty($mit) ? null : MenuTree::getIdParent($mit);
        if (!empty($menu)) {
            $items = $menu->submenu;
            $view->title = $menu->caption;
            foreach ($items as $item) {
                $item->itemUrl = '/' . $item->controller . '/' . $item->action;
            }
            $view->list = $items;
        }
        else {
            $this->flash("Menu not found: " . $mit);
            $view->list = null;
        }
        if (!empty($view->list)) {
            // The id is used by javascript as 'f' + id to identify the DOM element
            // used first item to return, since it must be visible,
            // and the 'first_link' is hidden, and has no visual properties

            $view->firstId = $mit;
        }
        $view->content = 'cat/menu.phtml';
        $view->assets(['bootstrap','grid', 'cat-menu']);
        echo $view->render();
    }

    public function linkery($f3, $args) {
        $title = $args['title'];
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        $show = isset($req['show']) ? $req['show'] : 'grid';

        $view = $this->getView();
        if ($isSub > 0) {

            $view->layout = "cat/linkery.phtml";
            $view->vname = "linkery/grid.phtml";


            if (is_numeric($title)) {
                $gal = Linkery::byId($title);
            } else {
                $gal = Linkery::byName($title);
            }


            if ($gal) {
                $view->links = Linkery::getAllLinks($gal['id']);
                $view->linkery = $gal;
                $view->title = $gal->name;
            }
            echo $view->render();
        } else {
            $mit = Valid::toInt($req, 'mit', null);
            $this->menuView($mit);
            return;
        }
    }

    private function eventArticle() {
        $db = Server::db();
        $sql = <<<EOD
        SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
            from blog A where A.title_clean = 'past-events';
EOD;
        return Server::db()->exec($sql);
    }

    private function past() {
        $db = Server::db();
        $sql = <<<EOD
    SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
     from blog A join event B on A.id = B.blogid
     where B.toTime < NOW() and A.enabled = 1
     order by B.fromTime desc
EOD;
        return Server::db()->exec($sql);
    }
    private function future() {
        $db = Server::db();
        $sql = <<<EOD
    SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
     from blog A join event B on A.id = B.blogid
     where B.toTime >= NOW() and A.enabled = 1
     order by B.fromTime asc
EOD;
        return Server::db()->exec($sql);
    }
    public function pastEvents($f3, $args) {
        $view = $this->getView();
        $view->list = $this->past();
        $view->old = $this->eventArticle();
        
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        
        if ($isSub > 0) {
            $view->layout = 'home/past.phtml';
            echo $view->render();   
        }
        else {
             $mit = Valid::toInt($req, 'mit', null);
            $this->menuView($mit);
            return;
        }   
    }
    public function events($f3, $args) {
        $view = $this->getView();
        $view->list = $this->future();
        if (count($view->list) <= 0) {
            $this->flash('No events returned');
        }
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        
        if ($isSub > 0) {
            $view->layout = 'home/future.phtml';
            echo $view->render();   
        }
        else {
             $mit = Valid::toInt($req, 'mit', null);
            $this->menuView($mit);
            return;
        }   
    }
    public function article($f3, $args) {
        $title = $args['title'];
        $db = Server::db();
        $blog = new Blog();
        $active = $blog->load(['title_clean = :tc', ':tc' => $title]);
        if (!$active) {
            $blog['title'] = "Article with link { $title } not found";
            $blog['article'] = "The link was incorrect";
        }

        $v = $this->view;
        $v->blog = $blog;

        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        if ($isSub > 0) {
            $v->layout = "cat/fetch.phtml";
            echo $v->render();
        } else {
            $mit = Valid::toInt($req, 'mit', null);
            $this->menuView($mit);
            return;
        }
    }

    public function fetch($f3, $args) {
        $blog = Blog::findFirstByid($args['bid']);
        $view = $this->getView();
        $view->blog = $blog;
        $view->layout = 'cat/fetch.phtml';
        echo $view->render();
    }

    public function menu($f3, $args) {
        $menuCaption = $args['cap'];

        $view = $this->getView();
        $view->content = 'cat/menu.phtml';
        $tree = \Models\MenuTree::getMainMenu($menuCaption);
        $q = null;
        $items = $tree->submenu;
        foreach ($items as $item) {
            $url = parse_url('/' . $item->controller . '/' . $item->action);
            if (isset($url['query'])) {
                parse_str($url['query'], $q);
                if (isset($q['item'])) {
                    $item->itemUrl = $q['item'];
                }
            }
        }
        $view->title = $tree->caption;

        if (empty($view->firstId) && !empty($items)) {
            $view->firstId = $items[0]->id;
        } else {
            $req = &$f3->ref("REQUEST");
            $view->firstId = Valid::toStr($req, 'mit', null);
        }
        $view->list = $items;
        echo $view->render();
    }

}
