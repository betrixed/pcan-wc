<?php
namespace Pcan;
/**
 * Description of dash
 *
 * @author Michael Rynn
 */

use WC\UserSession;

use WC\Dos;


class Dash extends Controller {
    use Mixin\Auth;
    use Mixin\ViewPlates;
    
    public function h_ajax($f3, $args) {
        $cmd = $args['cmd'];
        if ($cmd === "asset_cache") {
            \WC\Assets::instance()->clearCache();
            echo("Asset cache cleared");
        }
        else if ($cmd === "schema_cache") {
            $cache =  \Cache::instance();
            if (!empty($cache)) {
                $cache->reset('schema');
            }
        }
        else if ($cmd === "metadata_cache") {
            $ns = "App\\Model\\";
            $reflector = new \ReflectionClass($ns . 'Blog');
            $dir = dirname( $reflector->getFileName());
            foreach(glob($dir . '/' . '*') as $model_file) {
                if (substr($mode_file,0,1) !== '.') {
                    $classfile = basename($model_file, ".php");
                    $model_class = $ns . $classfile;
                    $model = new $model_class();
                    $meta_data = $model->getMetaData();
                    $meta_data->reset();
                }
            }
        }
    }
    public function show($f3, $args) {  
        \WC\Assets::instance()->add('bootstrap');
        $view = $this->getView();
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
