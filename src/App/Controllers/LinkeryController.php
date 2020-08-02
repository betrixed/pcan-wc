<?php
namespace App\Controllers;
/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use WC\Valid;

use App\Models\Linkery;
use App\Link\PageInfo;


class LinkeryController extends \Phalcon\Mvc\Controller {
use \WCMixin\ViewPhalcon;
use \App\Link\LinkeryData;
    protected $url = '/linkery/';
    
    public function viewAction($path) {
        $params = $_SERVER['argv'];
        $vname = 'linkery/view.phtml';
        $view = $this->getView();
        $m = $view->m;
        $sub = isset($params['sub']) ? intval($params['sub']) : 0;
        $m->params = "";
        
        $show = Valid::toStr($params,'show','grid');
        $m->params = "?show=" . $show;
        $m->url = $this->url . 'view/' . $path;
        
        switch($show) {
            case 'grid' : $vname = 'linkery/grid';
                break;
            case 'table' : $vname = 'linkery/view';
                break;
            default: ;
                break;
        }
        $m->vname = $vname;
        $m->show = $show;
        if (is_numeric($path)) {
            $gal = Linkery::findFirstById($path);
        }
        else {
            $gal = Linkery::findFirstByName($path);
        }
        
        if (!empty($gal)) {
            $m->links = $this->getAllLinks($gal->id);
            $m->linkery = $gal;
            $m->title = $gal->name;
        }
        
        $m->requri = $_SERVER['REQUEST_URI'];
        $m->sub = $sub;

        switch($sub) {
            case 1:
            case 2:
                $vid = ($sub===1) ? "article" : "gview";
                $m->subjs = "relayout(this,'$vid');return false;";
                $m->suburl = $url . "?sub=" . $sub;
                $this->noLayouts();
                return $this->render('partials', 'linkery/title');
                break;
            default:
                $m->subjs = "relayout(this,'gview');return false;";
                $m->suburl = $url . "?sub=2";
                return $this->render('linkery', 'view');
                break;
        }
    }


    public function indexAction() {
        $view = $this->getView();
        $this->linkeryPage($view->m);
        return $this->render('linkery','index');
    }

}
