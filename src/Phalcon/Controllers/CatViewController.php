<?php

namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use WC\Db\Server;
use App\Models\BlogCat;
use App\Models\Blog;
use App\Models\Linkery;
use App\Link\MenuTree;
use WC\Valid;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class CatViewController extends Controller {
use \WC\Mixin\ViewPhalcon;
    /*
    public function index($catid) {
        
        $catclean = BlogCat::bySlug($catid);
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
            $assets = \WC\Assets::instance();
            $assets->add( [ 'bootstrap',  'cat-fetch' ] );
            $assets->minify("cat-fetch");
            if ($isSub) {
                $view->layout = 'cat/subindex.phtml';
            } else {
                $view->content = 'cat/index.phtml';
            }
            echo $view->render();
        }
    } */

   /** Do not get a new view here, use the existing one
    * Menu id passed
    * @param type $mit
    */

    private function menuView($mit) {
        $view = $this->view; // current controller view
        $m = $view->m;
        $menu = empty($mit) ? null : MenuTree::getIdParent($mit);
        if (!empty($menu)) {
            $items = $menu->submenu;
            $m->title = $menu->caption;
            foreach ($items as $item) {
                $item->itemUrl = '/' . $item->controller . '/' . $item->action;
            }
            $m->list = $items;
        }
        else {
            $this->flash("Menu not found: " . $mit);
            $m->list = null;
        }
        if (!empty($m->list)) {
            // The id is used by javascript as 'f' + id to identify the DOM element
            // used first item to return, since it must be visible,
            // and the 'first_link' is hidden, and has no visual properties

            $m->firstId = $mit;
        }
        $assets = \WC\Assets::instance();
        $assets->add( [ 'bootstrap','grid', 'cat-menu', 'styles'] );
        $assets->minify("cat-menu");

        return $this->render('cat', 'menu');
    }
    
/*
    public function linkery($f3, $args) {
        $title = $args['title'];
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        $show = isset($req['show']) ? $req['show'] : 'grid';

        $view = $this->getView();
        $view->content = "cat/linkery.phtml";
        if ($isSub > 0) {

            //$view->layout = "cat/linkery.phtml";
            $view->layout = null;
            $view->vname = "linkery/grid.phtml";


            if (is_numeric($title)) {
                $gal = Linkery::byId($title);
            } else {
                $gal = Linkery::byName($title);
            }
            $m = $view->model;

            if ($gal) {
                $m->links = Linkery::getAllLinks($gal['id']);
                $m->linkery = $gal;
                $m->title = $gal->name;
            }
            echo $view->render();
        } else {
            $mit = Valid::toInt($req, 'mit', null);
            $this->menuView($mit);
            return;
        }
    }
    */

    /*
    private function eventArticle() {
        $db = Server::db();
        $sql = <<<EOD
        SELECT A.id, A.title, A.date_updated as sysdate, A.article, A.style
            from blog A where A.title_clean = 'past-events';
EOD;
        return Server::db()->exec($sql);
    }
    */
    /*
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
     
     */
    /*
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
     
     */
/*
    public function pastEvents($f3, $args) {
        $view = $this->getView();
        $m = $view->model;
        
        $m->list = $this->past();
        $m->old = $this->eventArticle();
        
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        $view->content = 'home/past.phtml';
        
        if ($isSub > 0) {
            $view->layout = null;
            
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
        $m = $view->model;
        $m->list = $this->future();
        if (count($m->list) <= 0) {
            $this->flash('No events returned');
        }
        $req = &$f3->ref("REQUEST");
        $isSub = Valid::toInt($req, 'sub', 0);
        
        if ($isSub > 0) {
            $view->content = 'home/future.phtml';
            echo $view->render();   
        }
        else {
             $mit = Valid::toInt($req, 'mit', null);
            $this->menuView($mit);
            return;
        }   
    }*/

    public function articleAction($title) {
        $req = $this->request;
        $isSub = $req->getQuery('sub', 'int', 0);
        if (!($isSub > 0)) {
             $mit = $req->getQuery('mit', 'int', null);
             return $this->menuView($mit);
        } 

        $db = Server::db();
        $blog = Blog::findFirst(['title_clean = :tc:', 'bind' => ['tc' => $title]]);

        if (empty($blog)) {
            $blog = new Blog();
            $blog->title = "Article with link { $title } not found";
            $blog->article = "The link was incorrect";
        }
        $this->noLayouts();
        
        $v = $this->getView();
        $m = $v->m;
        $m->blog = $blog;
        return $this->render('cat', 'fetch');
    }

    public function fetchAction($bid) {
        $blog = Blog::findFirstByid($bid);
        $view = $this->getView();
        $m = $view->m;
        $m->blog = $blog;
        return $view->render('cat','fetch');
    }
    
    /*
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
    */
}
