<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use WC\Valid;

use Pcan\DB\Linkery;
use Pcan\DB\PageInfo;

class LinkeryCtl extends Controller {
    /* navigate the images in a particular folder , or a folder indexed with a name */

    /* to start off with, just the images in /image/upload/ */

    /**
     *  url
     *  /gallery/index/
     *  /gallery/new/
     *  /gallery/edit/
     * 
     */
    protected $viewPost = "/linkery/";
    
    public function view($f3, $args) {
        $path = $args['name'];
        
        $params = &$f3->ref('REQUEST');
        $vname = 'linkery/view.phtml';
        $view = $this->view;
        $sub = isset($params['sub']) ? intval($params['sub']) : 0;
        $view->params = "";
        $show = isset($params['show']) ? $params['show'] : 'grid';
        $view->params = "?show=" . $show;
        $view->url = '/linkery/view/' . $path;
        
        switch($show) {
            case 'grid' : $vname = 'linkery/grid.phtml';
                break;
            case 'table' : $vname = 'linkery/view.phtml';
                break;
            default: ;
                break;
        }
        $view->vname = $vname;
        $view->show = $show;
        if (is_numeric($path)) {
            $gal = Linkery::byId($path);
        }
        else {
            $gal = Linkery::byName($path);
        }
        
        if ($gal) {
            $view->links = Linkery::getAllLinks($gal['id']);
            $view->linkery = $gal;
            $view->title = $gal->name;
        }
        
        $view->requri = $f3->get('SERVER.REQUEST_URI');
        $view->sub = $sub;
        $url = "/linkery/view/" . $path;
        $view->url = $url;
        switch($sub) {
            case 1:
            case 2:
                $vid = ($sub===1) ? "article" : "gview";
                $view->subjs = "relayout(this,'$vid');return false;";
                $view->suburl = $url . "?sub=" . $sub;
                $view->layout = 'linkery/title.phtml';
                echo $view->render();
                break;
            default:
                $view->content = 'linkery/title.phtml';
                $view->subjs = "relayout(this,'gview');return false;";
                $view->suburl = $url . "?sub=2";
                $view->assets(['bootstrap','grid']);
                echo $view->render();
                break;
        }
    }

    protected function listPageNum($numberPage, $pageRows, $orderby) {
        $start = ($numberPage - 1) * $pageRows;
        $sql =  <<<EOD
select b.*, count(*) over() as full_count
    from link_gallery b
    order by  $orderby
    limit  $pageRows offset $start
EOD;

        $db = Server::db();
        $results = $db->exec($sql);
        
        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;

        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }

    protected function linkeryIndex($f3, $template) {
        $numberPage = $f3->get('REQUEST.page');
        $orderby = 'name';
        $order_field = 'b.name desc';
        if (!isset($numberPage)) {
            $numberPage = 1;
        } else {
            $numberPage = intval($numberPage);
        }

        $view = $this->view;
        $view->content = $template;
        $view->orderby = $orderby;
        $view->page = $this->listPageNum($numberPage, 12, $order_field);
        $view->url = $this->viewPost ;
        return $view;
    }
    public function index($f3, $args) {
        $view = $this->linkeryIndex($f3, 'linkery/index.phtml');
        $view->assets('bootstrap');
        echo $view->render();
    }

}
