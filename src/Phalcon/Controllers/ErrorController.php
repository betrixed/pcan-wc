<?php

namespace App\Controllers;
use Phalcon\Mvc\Controller;
use WC\App;
use WC\Assets;
/**
 * Description of ErrorController
 *
 * @author michael
 */
class ErrorController extends Controller
{
use \WC\Mixin\ViewPhalcon;

    public function blockAction() {
         $view = $this->getView();
         $view->m->handledUri = $this->app->handledUri;
        return $this->render('error','block');
    }
    public function route404Action() {
        $view = $this->getView();
        $view->m->handledUri = $this->app->handledUri;
        return $this->render('error','route404');
    }
}
