<?php
namespace Pcan;

use Pcan\DB\Series;

class SeriesCtl extends Controller {
use Mixin\ViewPlates;

    public function view($f3,$args) {
        $view = $this->getView();
        $view->content = 'series/view';
        $id = $args['id'];
        $view->assets(['bootstrap']);
        $series = Series::byId($id);
        
        $m = $view->model;
        $m->series = $series;
        $m->episodes = Series::orderDate($series['id']);
        echo $view->render();
    }
}