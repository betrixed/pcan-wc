<?php

use WC\Assets;

class Home extends \Pcan\Controller {
    use \Pcan\Mixin\ViewPlates;
    public function index($f3, $args) {
        $view = $this->getView();
        $view->assets('bulma');
        $view->content = 'index';
        echo $view->render();
    }
}