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
         $m = $this->getViewModel();
        
         $m->handledUri = $this->app->arguments;
         if ($this->request->isAjax()) {
             $this->noLayouts();
        }
        return $this->render('error','block');
    }
    public function route404Action() {
         $m = $this->getViewModel();
        $m->handledUri = $this->app->arguments;
        if ($this->request->isAjax()) {
             $this->noLayouts();
        }
        return $this->render('error','route404');
    }
}
