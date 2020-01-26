<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */
use Pcan\DB\PageInfo;
use Pcan\DB\BlogCat;

use WC\DB\Server;
use WC\UserSession;
use WC\Valid;

class CatAdm extends Controller
{
use Mixin\ViewPlates;
use Mixin\Auth;

    public $url = '/admin/cat/';

    public function post($f3, $args)
    {
        $req = &$f3->ref('REQUEST');

        $check_id = Valid::toInt($req, 'id', null);

        $cat = !empty($check_id) ? BlogCat::byId($check_id) : new BlogCat();

        $new_name = Valid::toStr($req, 'name', '');
        $new_nameclean = Valid::toStr($req, 'name_clean', '');
        $new_enable = Valid::toInt($req, 'enabled', false);

        if (($cat['name'] != $new_name) ||
                ($cat['name_clean'] != $new_nameclean) ||
                ($cat['enabled'] != $new_enable)
        ) {
            $cat['name'] = $new_name;
            $cat['name_clean'] = $new_nameclean;
            $cat['enabled'] = $new_enable;

            if (!empty($check_id)) {
                $cat->update();
                $this->flash("Altered category");
            } else {
                $cat->save();
                $check_id = $cat['id'];
                $this->flash("Created category");
            }
        }
        if (!empty($check_id)) {
            UserSession::reroute($this->url . 'edit/' . $check_id);
        }
    }

    public function newRec($f3, $args)
    {
        $view = $this->getView();
        $view->content = 'cat_adm/edit';
         $view->assets('bootstrap');
         
        $m = $view->model;
        $m->title = 'New Category';
        $m->cat = new BlogCat();

        $m->url = $this->url;
       
        echo $view->render();
    }

    public function edit($f3, $args)
    {

        $id = $args['id'];
        $view = $this->getView();
        $view->content = 'cat_adm/edit';
          $view->assets('bootstrap');
        $m = $view->model;
        $m->cat = BlogCat::byId($id);
        $m->url = $this->url;
        $m->title = 'Edit Category';
      
        echo $view->render();
    }

    public function index($f3, $args)
    {
        
        $req = &$f3->ref('REQUEST');
        $numberPage = Valid::toInt($req, 'page', 1);
        $grabsize = 16;
        $start = ($numberPage - 1) * $grabsize;
        
        $view = $this->getView();
        $view->content = 'cat_adm/index';
          $view->assets('bootstrap');
        $m = $view->model;
        
        $m->title = "Category Index";
        $m->url = '/admin/cat/';
        $db = Server::db();
        $sql = "select *, count(*) over() as full_count from blog_category" . 
                " limit :grab offset :start";
        $results = $db->exec($sql, [':start' => $start, ':grab' => $grabsize]);
        $maxrows = !empty($results) ? $results[0]['fullcount'] : 0;
        $paginator = new PageInfo($numberPage, $grabsize, $results, $maxrows);
        $m->page = $paginator;
        
      
        echo $view->render();
    }

}
