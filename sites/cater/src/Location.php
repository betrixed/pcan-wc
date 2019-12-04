<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Location extends \Pcan\Controller {

    public function index($f3, $args) {
        $view = $this->view;
        $view->title = 'Julie\'s Catering';
        $view->assets(['bootstrap']);
        $view->content = 'map/index.phtml';
        echo $view->render();
    }

}
