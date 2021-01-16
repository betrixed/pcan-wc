<?php

namespace WC\Controllers;

use WC\Db\DbQuery;
use WC\Models\Series;
/**
 * Controller for series Table
 *
 * @author Michael Rynn
 */
class SeriesAdmController extends BaseController {
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
