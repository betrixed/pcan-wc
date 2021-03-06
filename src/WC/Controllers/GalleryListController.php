<?php
namespace WC\Controllers;
/**
 * @author Michael Rynn
 */
use WC\Link\PageInfo;
use WC\Models\Series;
use WC\Models\Gallery;
use WC\Db\Server;
use WC\UserSession;
use Phiz\Mvc\Controller;
use WC\App;
use WC\Valid;
use WC\Db\DbQuery;

class GalleryListController extends Controller {
    use \WC\Mixin\ViewPhalcon;
    use \WC\Link\GalleryView;
    /* navigate the images in a particular folder , or a folder indexed with a name */

    /* to start off with, just the images in /image/upload/ */
    protected $galleryPath; // directory path from web root
 
    protected $viewPost = "/gallery/"; // url path root

    protected function getGalleryPath() {
        if (empty($this->galleryPath)) {
            $app = $this->app;
            $this->galleryPath = $app->WEB . $app->gallery . "/";
        }
        return $this->galleryPath;
    }
    /**
     * 
     * @param type $id
     * id is a path, subdirectory of /image/gallery/
     */

    
    

    public function showAction($name) {
        $params = $_GET;
        //$vname = 'gallery/view.phtml';
       
        $sub = isset($params['sub']) ? intval($params['sub']) : 0;
        $show = isset($params['show']) ? $params['show'] : 'grid';
        $view = $this->getView();
         $m = $view->m;
        $m->params = "?show=" . $show;
        $m->viewOptions = ['grid' => 'grid',
                     'table' => 'view',
                     'slider' => 'carousel'];
        $m->show = $show;
        $m->title = 'Gallery';
        $gal = $this->getGalleryName($name);
        
        if ($gal) {
            $m->images = $this->getImages($gal->id);
            $m->gallery = $gal;
            $prevlink = $gal->leva_path;
            $nextlink = $gal->prava_path;
            $m->prevlink = empty($prevlink) ? null : $this->getGalleryName($prevlink);
            $m->nextlink = empty($nextlink) ? null : $this->getGalleryName($nextlink);
            if (!empty($gal->seriesid)) {
                $series = Series::findFirstById($gal->seriesid);
                $m->indexlink = '/series/' . $series->tinytag;
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
        
        $m->requri = $_SERVER['REQUEST_URI'];
        $m->sub = $sub;
        $url = "/gallery/view/" . $name;
        $m->url = $url;
        switch($sub) {
            case 1:
            case 2:
                $vid = ($sub===1) ? "article" : "gview";
                $m->subjs = "relayout(this,'$vid');return false;";
                $m->suburl = $url . "?sub=" . $sub;
                $this->noLayouts();
                $content = $this->render('gallery', $m->viewOptions[$show]);
                break;
            default:
                $m->subjs = "relayout(this,'gview');return false;";
                $m->suburl = $url . "?sub=2";
                $content =  $this->render('gallery','title');
                break;
        }
        return $content;
    }

    

    protected function galleryIndex() {
        $view = $this->getView();
        $m = $view->m;
        $this->pageList($m,Valid::toInt($_GET,'page',1));
        
        $m->title = 'Gallery Index';
        $m->url = $this->viewPost . 'edit/';   

    }
    public function indexAction() {
        $this->galleryIndex();
        return $this->render('gallery','index');
    }

}
