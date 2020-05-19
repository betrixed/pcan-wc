<?php
namespace App\Controllers;
/**
 *
 * @author Michael Rynn
 */

use WC\UserSession;

use WC\Dos;
use Phalcon\Mvc\Controller;
use WC\FileCache;

class DashController extends Controller {
    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
    
    public function getAllowRole() {
        return 'Admin';
    }
    public function cmdAction($fn) {
        if ($fn === "asset_cache") {
            \WC\Assets::instance()->clearCache();
           return "Asset cache cleared";
        }
        else if ($fn === "model_cache") {
            (FileCache::modelCache()->clear());
            return "Model cache cleared";
        }
    }
    public function showAction() {  
        $view = $this->getView();
        $view->title = "Dash";
        return $this->render('admin','dash');
    }
    
    function infoAction() {
        echo "<a href='/admin/dash'>DASH</a> &gt;&gt; PHP Info<br>";
        //foreach (['gc_probability','gc_divisor','gc_maxlifetime'] as $k)
        //    echo $k,'=',ini_get("session.$k"),'<br>';
        phpinfo();

    }
    
    function redirect($f3, $args) {
        $this->flash('Redirected');
        UserSession::reroute('/dash');
    }
}
