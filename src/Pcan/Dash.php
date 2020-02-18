<?php
namespace Pcan;
/**
 * Description of dash
 *
 * @author Michael Rynn
 */

use WC\UserSession;

class Dash extends Controller {
    use Mixin\Auth;
    use Mixin\ViewPlates;
    
    function show($f3, $args) {  
        $view = $this->getView();
        $view->assets('bootstrap');
        $view->content = 'path0::home/dash';
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
        $this->flash('Redirected');
        UserSession::reroute('/dash');
    }
}
