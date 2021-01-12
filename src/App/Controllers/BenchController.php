<?php
namespace App\Controllers;

/**
 * A nothing controller,
 * No Authentication, No database requests
 *
 * @author Michael Rynn
 */
class BenchController extends BaseController {
    use \WC\Mixin\ViewPhalcon;
    //put your code here
    public function indexAction() {
        //echo "Nothing";
        $m = $this->getViewModel();
        $m->title = "Bench";
        return $this->render('bench','index');

    }
}
