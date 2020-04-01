<?php
namespace App\Controllers;

use Phalcon\Mvc\Controller;
use WC\Assets;

class ModelController extends Phalcon\Mvc\Controller {
use \WC\Mixin\F3Controller;
use \WC\Mixin\ViewPlates;
    
    public function indexAction () {
        $view = $this->getView();
        $view->content = 'empty';
        Assets::instance()->add('bootstrap');
        echo $view->render();
    }
}
