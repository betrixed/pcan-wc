<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */
use Pcan\DB\PageInfo;
use Pcan\DB\Series;
use Pcan\DB\Gallery;
use WC\DB\Server;
use WC\UserSession;

class GalleryCtl extends Controller {
    /* navigate the images in a particular folder , or a folder indexed with a name */

    /* to start off with, just the images in /image/upload/ */
    protected $galleryPath; // directory path from web root
 
    protected $viewPost = "/gallery/"; // url path root

    public function __construct() {
        parent::__construct();
        
        $f3 = \Base::instance();
        $this->f3 = $f3;
        $this->us = UserSession::read();
        $this->php = $f3->get('php');
        $this->galleryPath = $f3->get('gallery'); // somewhere in site settings
        
        $view = $this->view;
        $view->nav = null;
    }

    protected function getGalleryPath() {
        return $this->getWebDir() . $this->galleryPath . "/";
    }
    /**
     * 
     * @param type $id
     * id is a path, subdirectory of /image/gallery/
     */
    private function getGalleryName($name) {
        // see if path exists, is registered, if not, make it
        $gal =  Gallery::byName($name);
        if (!$gal ) {
            $this->flash("gallery not registered : " . $name);
            return null;
        } else {
            $imageExt = $gal->path;
            $imgdir = $this->getWebDir() . $imageExt;
            if (!file_exists($imgdir)) {
                $this->flash("cannot find folder : " . $imageExt);
                return null;
            }
            return $gal;
        }
    }
    
    

    public function view($f3, $args) {
        $path = $args['name'];
        $params = &$f3->ref('REQUEST');
        $vname = 'gallery/view.phtml';
        $view = $this->view;
        $sub = isset($params['sub']) ? intval($params['sub']) : 0;
        $view->params = "";
        $show = isset($params['show']) ? $params['show'] : 'grid';
        $view->params = "?show=" . $show;

        switch($show) {
            case 'grid' : $vname = 'gallery/grid.phtml';
                break;
            case 'table' : $vname = 'gallery/view.phtml';
                break;
            case 'slider' : $vname = 'gallery/carousel.phtml';
                break;
            default: ;
                break;
        }
        $view->vname = $vname;
        $view->show = $show;
        $view->title = 'Gallery';
        $gal = $this->getGalleryName($path);
        if ($gal) {
            $view->images = Gallery::getImages($gal->id);
            $view->gallery = $gal;
            $prevlink = $gal['leva_path'];
            $nextlink = $gal['prava_path'];
            $view->prevlink = empty($prevlink) ? null : $this->getGalleryName($prevlink);
            $view->nextlink = empty($nextlink) ? null : $this->getGalleryName($nextlink);
            if (!empty($gal['seriesid'])) {
                $series = Series::byId($gal['seriesid']);
                $view->indexlink = '/series/' . $series['tinytag'];
            }
            else {
                $view->indexlink = '/gallery/';
            }
            // Get maxHeight for slider
            $maxHeight = 0;
            foreach($view->images as $img) {
                $hi = intval($img['height']);
                if ($maxHeight < $hi) {
                    $maxHeight = $hi;
                }
            }
            
            $view->maxHeight = $maxHeight;
        }
        
        $view->requri = $f3->get('SERVER.REQUEST_URI');
        $view->sub = $sub;
        $url = "/gallery/view/" . $path;
        $view->url = $url;
        switch($sub) {
            case 1:
            case 2:
                $vid = ($sub===1) ? "article" : "gview";
                $view->subjs = "relayout(this,'$vid');return false;";
                $view->suburl = $url . "?sub=" . $sub;
                $view->layout = $vname;
                echo $view->render();
                break;
            default:
                $view->content = 'gallery/title.phtml';
                $view->subjs = "relayout(this,'gview');return false;";
                $view->suburl = $url . "?sub=2";
                $view->layout = 'minimal.phtml';
                $view->assets(['bootstrap','grid']);
                echo $view->render();
                break;
        }
    }

    protected function listPageNum($numberPage, $pageRows, $orderby) {
        $start = ($numberPage - 1) * $pageRows;
        $sql =  <<<EOD
select b.*,
    count(*) over() as full_count             
    from gallery b
    order by  $orderby
    limit $pageRows offset $start
EOD;

        $db = Server::db();
        $results = $db->exec($sql);
        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;

        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }

    protected function galleryIndex($f3, $template) {
        $numberPage = $f3->get('REQUEST.page');
        $orderby = 'path';
        $order_field = 'b.last_upload desc';
        if (!isset($numberPage)) {
            $numberPage = 1;
        } else {
            $numberPage = intval($numberPage);
        }

        $view = $this->view;
        $view->content = $template;
        $view->title = 'Gallery Index';
        $view->orderby = $orderby;
        $view->page = $this->listPageNum($numberPage, 12, $order_field);
        $view->url = $this->viewPost . 'edit/';
        return $view;
    }
    public function index($f3, $args) {
        
        $view = $this->galleryIndex($f3, 'gallery/index.phtml');
        $view->assets(['bootstrap','grid']);
        echo $view->render();
    }

}
