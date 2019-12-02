<?php
namespace Pcan;
/**
 * Description of dash
 *
 * @author Michael Rynn
 */

use WC\UserSession;

class Dash extends Controller {
    function show($f3, $args) {
        if (!$this->auth($f3)) {
            return false;
        }
        
        $view = $this->view;
        $view->assets('bootstrap');
        $view->content = 'home/dash.phtml';
        $view->title = "Dash";
        echo $view->render();
    }
    
    function info($f3, $args) {
        echo "<a href='/dash'>DASH</a> &gt;&gt; PHP Info<br>";
        //foreach (['gc_probability','gc_divisor','gc_maxlifetime'] as $k)
        //    echo $k,'=',ini_get("session.$k"),'<br>';
        phpinfo();
    }
    
    function redirect($f3, $args) {
        if (!$this->auth($f3)) {
            return false;
        }
        $this->flash('Redirected');
        UserSession::reroute('/dash');
    }
}
