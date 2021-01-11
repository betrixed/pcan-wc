<?php

namespace WC\Controllers;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use WC\Valid;
use WC\Assets;


use Phiz\Mvc\Controller;

class EventListController extends Controller
{
use \WC\Mixin\ViewPhalcon;
use \WC\Link\EventOps;
    public function indexAction()
    {
        $m = $this->getViewModel();
        $m->title = "Events";
        $m->events = $this->getPending();
        
        return $this->render('index','event');
    }
}