<?php

namespace App\Controllers;

use WC\Db\DbQuery;
use Phalcon\Mvc\Controller;
use App\Models\Series;
/**
 * Controller for series Table
 *
 * @author Michael Rynn
 */
class SeriesAdmController extends Controller {
    use \WC\Mixin\Auth;
    use \WC\Mixin\ViewPhalcon;

    public function getAllowRole() {
        return 'Admin';
    }

    public function indexAction() {
        $view = $this->getView();
        $m = $view->m;
        $m->series = Series::find();
      
        return $this->render('admin', 'series');
    }

}
