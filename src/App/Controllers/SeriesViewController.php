<?php
namespace App\Controllers;

use App\Models\Series;
use Phalcon\Mvc\Controller;
use WC\Db\DbQuery;
use Phalcon\Db\Column;

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
        
        $m->episodes = (new DbQuery())->objectSet(
                'select * from gallery where seriesid = :sid order by last_upload desc',
                ['sid' => $series->id],
                ['sid' => Column::BIND_PARAM_INT]);
        //$series->getRelated('Gallery');
        return  $this->render('series','view');
    }
}