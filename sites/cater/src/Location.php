<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Location extends \Pcan\Controller {
use \Pcan\Mixin\ViewF3;

    public function index($f3, $args) {
        $view = $this->getView();
        $view->title = 'Julie\'s Catering';
        $view->assets(['bootstrap']);
        $view->content = 'map/index.phtml';
        echo $view->render();
    }

}
