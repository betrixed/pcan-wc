<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use WC\Db\Server;
use WC\Html;
use WC\Assets;

class IndexController extends Controller  {
use \WC\Mixin\ViewPhalcon;
//use \WC\Mixin\ViewPlates;
    

    public function indexAction () {
        Assets::instance()->add('bootstrap');
        $view = $this->getView();
        echo $this->render('index','index');
    }
}    