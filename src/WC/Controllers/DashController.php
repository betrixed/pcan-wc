<?php
namespace WC\Controllers;
/**
 *
 * @author Michael Rynn
 */

use WC\UserSession;

use WC\Dos;
use WC\FileCache;
use WC\Models\Users;

class DashController extends BaseController {
    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
    
    public function getAllowRole() : array {
        return ['Admin','Editor','Finance'];
    }
    public function cmdAction($fn) {
        $test = Users::findFirstByName('Michael Rynn');
        $this->logger->info("Test user " . $test->name);
        if ($fn === "asset_cache") {
            $this->assets->clearCache();
           return "Asset cache cleared";
        }
        else if ($fn === "model_cache") {
            $this->file_cache->clear();
            return "Model cache cleared";
        }
        else if ($fn === "metadata_cache") {
            $ns = "WC\\Models\\";
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
        $m = $this->getViewModel();
        $m->title = "Dash";
        $m->extensions = get_loaded_extensions();
        $m->need_extensions = ['intl','pdo_mysql','gd'];
        foreach($m->need_extensions as $name) {
            if (!in_array($name, $m->extensions)) {
                $this->flash("Extension " . $name . " not loaded");
            }
        }
        return $this->render('admin','dash');
    }
    
    function infoAction() {
        echo "<a href='/admin/dash'>DASH</a> &gt;&gt; PHP Info<br>";
        //foreach (['gc_probability','gc_divisor','gc_maxlifetime'] as $k)
        //    echo $k,'=',ini_get("session.$k"),'<br>';
        phpinfo();

    }

}
