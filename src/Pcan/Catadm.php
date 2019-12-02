<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */
use WC\DB\PageInfo;
use WC\DB\BlogCat;
use WC\DB\Server;
use WC\UserSession;

class CatAdm extends Controller
{

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
        $view = $this->view;
        $view->content = 'cat_adm/edit.phtml';
        $view->title = 'New Category';
        $view->cat = new BlogCat();

        $view->url = $this->url;
        $view->assets('bootstrap');
        echo $view->render();
    }

    public function edit($f3, $args)
    {

        $id = $args['cid'];
        $view = $this->view;
        $view->content = 'cat_adm/edit.phtml';
        $view->cat = BlogCat::byId($id);
        $view->url = $this->url;
        $view->title = 'Edit Category';
        $view->assets('bootstrap');
        echo $view->render();
    }

    public function index($f3, $args)
    {
        
        $req = &$f3->ref('REQUEST');
        $numberPage = Valid::toInt($req, 'page', 1);
        $grabsize = 16;
        $start = ($numberPage - 1) * $grabsize;
        
        $view = $this->view;
        $view->content = 'cat_adm/index.phtml';
        $view->title = "Category Index";
        $view->url = '/admin/cat/';
        $db = Server::db();
        $sql = "select *, count(*) over() as full_count from blog_category" . 
                " limit :grab offset :start";
        $results = $db->exec($sql, [':start' => $start, ':grab' => $grabsize]);
        $maxrows = !empty($results) ? $results[0]['fullcount'] : 0;
        $paginator = new PageInfo($numberPage, $grabsize, $results, $maxrows);
        $view->page = $paginator;
        $view->assets('bootstrap');
        echo $view->render();
    }

    public function beforeRoute()
    {
        if (!$this->auth()) {
            return false;
        }
    }

}
