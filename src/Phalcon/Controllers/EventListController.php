<?php

namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use WC\Valid;
use WC\Assets;
use App\Link\EventOps;

use Phalcon\Mvc\Controller;

class EventListController extends Controller
{
use \WC\Mixin\ViewPhalcon;

    public function indexAction()
    {
        $view = $this->getView();
        $assets = Assets::instance();
        
        $assets->add('bootstrap');
        
        $m = $view->m;
        $m->title = "Events";
        $m->events = EventOps::getPending();
        
        return $this->render('index','event');
    }
}