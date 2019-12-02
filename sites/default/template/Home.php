<?php

use WC\Assets;
use Pcan\HtmlPlates;

class Home {
    public function index($f3, $args) {
        Assets::instance()->add('bulma');
        $view = new HtmlPlates($f3);
        $view->layout = 'index';
        echo $view->render();
    }
}