<?php
namespace Pcan;

use Pcan\DB\Series;

class SeriesCtl extends Controller {
    public function view($f3,$args) {
        $view = $this->view;
        $view->content = 'series/view.phtml';
        $id = $args['id'];
        $view->assets(['bootstrap']);
        $series = Series::byId($id);
        $view->series = $series;
        $view->episodes = Series::orderDate($series['id']);
        echo $view->render();
    }
}