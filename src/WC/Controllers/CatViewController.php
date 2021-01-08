<?php

namespace WC\Controllers;

/**
 * @author Michael Rynn
 */
use WC\Db\Server;
use WC\Models\BlogCat;
use WC\Models\Blog;
use WC\Models\Linkery;
use WC\Link\MenuTree;
use WC\Link\BlogView;
use WC\Valid;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class CatViewController extends Controller {
use \WC\Mixin\ViewPhalcon;
use \WC\Link\RevisionOp;
   /** Do not get a new view here, use the existing one
    * Menu id passed
    * @param type $mit
    */

    private function menuView($mit) {
        $m = $this->getViewModel(); // current controller view
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
        $assets = $this->assets;


        return $this->render('cat', 'menu');
    }
    
    public function articleAction($title) {
        $req = $this->request;
        $isSub = $req->getQuery('sub', 'int', 0);
        if (!($isSub > 0)) {
             $mit = $req->getQuery('mit', 'int', null);
             return $this->menuView($mit);
        } 

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
        $m->revision = $this->getLinkedRevision($blog);
        return $this->render('cat', 'fetch');
    }

    public function fetchAction($bid) {
        $blog = Blog::findFirstByid($bid);
        $view = $this->getView();
        $m = $view->m;
        $m->blog = $blog;
        $m->revision = $this->getLinkedRevision($blog);
        
        return $view->render('cat','fetch');
    }
    
}
