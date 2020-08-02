<?php

namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use WC\Valid;
use WC\Assets;


use Phalcon\Mvc\Controller;

class EventListController extends Controller
{
use \WC\Mixin\ViewPhalcon;
use \App\Link\EventOps;
    public function indexAction()
    {
        $view = $this->getView();
        $assets = Assets::instance();
        
        $assets->add('bootstrap');
        
        $m = $view->m;
        $m->title = "Events";
        $m->events = $this->getPending();
        
        return $this->render('index','event');
    }
}