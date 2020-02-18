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
use Mixin\ViewPlates;

    /* navigate the images in a particular folder , or a folder indexed with a name */

    /* to start off with, just the images in /image/upload/ */
    protected $galleryPath; // directory path from web root
 
    protected $viewPost = "/gallery/"; // url path root

    public function __construct($f3, $args) {
        parent::__construct($f3, $args);

        $this->galleryPath = $this->f3->get('gallery'); // somewhere in site settings
        
        $view = $this->getView();
        //$view->nav = null;
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
    
    

    public function show($f3, $args) {
        $path = $args['name'];
        $params = &$f3->ref('REQUEST');
        $vname = 'gallery/view.phtml';
       
        $sub = isset($params['sub']) ? intval($params['sub']) : 0;
        $show = isset($params['show']) ? $params['show'] : 'grid';
        $view = $this->view;
         $m = $view->model;
        $m->params = "?show=" . $show;

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
        $m->vname = $vname;
        $m->show = $show;
        $m->title = 'Gallery';
        $gal = $this->getGalleryName($path);
        if ($gal) {
            $m->images = Gallery::getImages($gal->id);
            $m->gallery = $gal;
            $prevlink = $gal['leva_path'];
            $nextlink = $gal['prava_path'];
            $m->prevlink = empty($prevlink) ? null : $this->getGalleryName($prevlink);
            $m->nextlink = empty($nextlink) ? null : $this->getGalleryName($nextlink);
            if (!empty($gal['seriesid'])) {
                $series = Series::byId($gal['seriesid']);
                $m->indexlink = '/series/' . $series['tinytag'];
            }
            else {
                $m->indexlink = '/gallery/';
            }
            // Get maxHeight for slider
            $maxHeight = 0;
            foreach($m->images as $img) {
                $hi = intval($img['height']);
                if ($maxHeight < $hi) {
                    $maxHeight = $hi;
                }
            }
            
            $m->maxHeight = $maxHeight;
        }
        
        $m->requri = $f3->get('SERVER.REQUEST_URI');
        $m->sub = $sub;
        $url = "/gallery/view/" . $path;
        $m->url = $url;
        switch($sub) {
            case 1:
            case 2:
                $vid = ($sub===1) ? "article" : "gview";
                $m->subjs = "relayout(this,'$vid');return false;";
                $m->suburl = $url . "?sub=" . $sub;
                $view->content = $vname;
                echo $view->render();
                break;
            default:
                $view->content = 'gallery/title.phtml';
                $m->subjs = "relayout(this,'gview');return false;";
                $m->suburl = $url . "?sub=2";
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
        $m = $view->model;
        $m->title = 'Gallery Index';
        $m->orderby = $orderby;
        $m->page = $this->listPageNum($numberPage, 12, $order_field);
        $m->url = $this->viewPost . 'edit/';
        return $view;
    }
    public function index($f3, $args) {
        
        $view = $this->galleryIndex($f3, 'gallery/index.phtml');
        $view->assets(['bootstrap','grid']);
        echo $view->render();
    }

}
