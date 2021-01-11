<?php
namespace WC\Controllers;

use WC\Models\Series;
use Phiz\Mvc\Controller;
use WC\Db\DbQuery;
use Phiz\Db\Column;

class SeriesViewController extends Controller {
use \WC\Mixin\ViewPhalcon;

    public function viewAction($id) {
        $view = $this->getView();
        if (is_numeric($id)) {
           $series = Series::findFirstById($id);
        }
        else {
             $series = Series::findFirstByTinytag($id);
        }
        $m = $view->m;
        $m->series = $series;
        
        $m->episodes = ($this->dbq)->objectSet(
                'select * from gallery where seriesid = :sid order by last_upload desc',
                ['sid' => $series->id],
                ['sid' => Column::BIND_PARAM_INT]);
        //$series->getRelated('Gallery');
        return  $this->render('series','view');
    }
}