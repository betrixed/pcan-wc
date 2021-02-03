<?php
namespace WC\Controllers;
/**
 * @author Michael Rynn
 */
use WC\Link\PageInfo;
use WC\Models\BlogCategory;

use WC\Db\DbQuery;
use WC\UserSession;
use WC\Valid;

class CatAdmController extends BaseController
{
use \WC\Mixin\ViewPhalcon;
use \WC\Mixin\Auth;

    public $url = '/admin/cat/';

    
    public function getAllowRole() : string {
        return 'Editor';
    }
    public function postAction()
    {
        $post = $_POST;

        $check_id = Valid::toInt($post, 'id', null);

        $cat = !empty($check_id) ? BlogCategory::findFirstById($check_id) : new BlogCategory();

        $new_name = Valid::toStr($post, 'name', '');
        $new_nameclean = Valid::toStr($post, 'name_clean', '');
        $new_enable = Valid::toBool($post, 'enabled', false);

        if (($cat->name != $new_name) ||
                ($cat->name_clean != $new_nameclean) ||
                ($cat->enabled != $new_enable)
        ) {
            $cat->name = $new_name;
            $cat->name_clean = $new_nameclean;
            $cat->enabled = $new_enable ? 1 : 0;

            if (!empty($check_id)) {
                $cat->update();
                $this->flash("Altered category");
            } else {
                $cat->create();
                $check_id = $cat->id;
                $this->flash("Created category");
            }
        }
        if (!empty($check_id)) {
            UserSession::reroute($this->url . 'edit/' . $check_id);
        }
    }

    public function newAction()
    {
        $view = $this->getView();
        $m = $view->m;
        $m->title = 'New Category';
        $m->cat = new BlogCategory();
        $m->url = $this->url;
       
        return $this->render('cat_adm','edit');
    }

    public function editAction($id)
    {
        $view = $this->getView();
        $m = $view->m;
        $m->cat = BlogCategory::findFirstById($id);
        $m->url = $this->url;
        $m->title = 'Edit Category';
      
        return $this->render('cat_adm','edit');
    }

    public function indexAction()
    {
        $req = $_REQUEST;
        $numberPage = Valid::toInt($req, 'page', 1);
        $grabsize = 16;
        $start = ($numberPage - 1) * $grabsize;
        
        $view = $this->getView();
        $m = $view->m;
        
        $m->title = "Category Index";
        $m->url = '/admin/cat/';
        $db = new DbQuery();
        $sql = "select *, count(*) over() as full_count from blog_category " . 
                " limit :grab offset :start";
        $results = $db->arraySet($sql, [ 'grab' => $grabsize, 'start' => $start], 
                ['grab' => \PDO::PARAM_INT, 'start' => \PDO::PARAM_INTPARAM_INT]);
        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;
        $paginator = new PageInfo($numberPage, $grabsize, $results, $maxrows);
        $m->page = $paginator;
        
      
        return  $this->render( 'cat_adm', 'index');
    }

}
