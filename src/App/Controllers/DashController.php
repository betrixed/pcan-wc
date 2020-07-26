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
            $this->assets->clearCache();
           return "Asset cache cleared";
        }
        else if ($fn === "model_cache") {
            $this->file_cache->clear();
            return "Model cache cleared";
        }
        else if ($fn === "metadata_cache") {
            $ns = "App\\Models\\";
            $reflector = new \ReflectionClass($ns . 'Blog');
            $dir = dirname( $reflector->getFileName());
            $ct = 0;
            foreach(glob($dir . '/' . '*') as $model_file) {
                if (substr($mode_file,0,1) !== '.') {
                    $classfile = basename($model_file, ".php");
                    $model_class = $ns . $classfile;
                    $model = new $model_class();
                    $meta_data = $model->getModelsMetadata();
                    $meta_data->reset();
                    $ct++;
                }
            }
            return "$ct model metadata classes reset";
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

}
