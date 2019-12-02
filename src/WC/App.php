<?php

namespace WC;


class App extends \Prefab {

    public $f3;
    public $config;
    public $schemaDir;
    
    /**
     * Secrets are probably not stored in the database
     * @return configuration array or object
     */
    function get_secrets() {
        $cfg = $this->f3->get('secrets');
        if (is_null($cfg)) {
              $path = $this->f3->get('sitepath');
              $cfg = WConfig::fromXml($path . ".secrets.xml");
              $this->f3->set('secrets', $cfg);
        }
        return $cfg;
    }
    
    public function getSchemaDir() {
        if (!isset($this->schemaDir)) {
             $f3 = \Base::Instance();
             $this->schemaDir = $f3->get('sitepath') . 'schema/';
        }
        return $this->schemaDir;
    }
    
    static public function clear_cache($f3, $suffix) {
        $value = $f3->get('CACHE_CLEAN');
        if (!empty($value)) {
            return;
        }
        // store the hour timeout value, and as a timeout in seconds.
        $f3->set('CACHE_CLEAN', 3600, 3600);
        $dsn = $f3->get('CACHE'); // the dsn
        $parts = $parts = explode('=', $dsn, 2);
        if ($parts[0] === 'folder') {
            $regex = '/' . '.*' . preg_quote('.', '/') . '.*' .
                    preg_quote($suffix, '/') . '/';
            if ($glob = @glob($parts[1] . '*')) {
                $now = time();
                foreach ($glob as $file) {
                    if (preg_match($regex, basename($file))) {
                        $filemtime = filemtime($file);
                        if ($now - $filemtime >= 60 * 60 * 24) {
                            @unlink($file);
                        }
                    }
                }
            }
        }
    }


    public function __construct() {
        $this->f3 = \Base::Instance();
    }

    public function init($f3, $sitepath) {
        $this->f3 = $f3;
        $cfg = WConfig::fromPhp($sitepath . 'config.php');
        $this->config = $cfg;
        $f3->set('gallery', '/image/gallery/');

        $f3->set('gallery', '/image/gallery/');
        
        if (isset($cfg->globals)) {
            if (isset($cfg->globals['TEMP'])) {
                WConfig::updateValue($cfg->globals['TEMP'], $f3);
            }
            foreach ($cfg->globals as $k => $v) {
                $f3->set($k, $v);
            }
        }
        if (isset($cfg->routes)) {
            foreach ($cfg->routes as $k => $v) {
                $f3->route($k, $v);
            }
        }

        
        
        $temp = $f3->exists('TEMP') ? $f3->get('TEMP') : false;
        if ($temp === false || $temp === 'tmp/') {
            $f3->set('TEMP', $sitepath . '/tmp/');
            $temp = $f3->get('TEMP');
        }   
        $f3->set('LOG', $temp . 'log/');

        $dsn = $f3->exists('CACHE') ? $f3->get('CACHE') : false;
        if ($dsn === false) {
            $dsn = "folder=" . $temp . 'cache/';
            $f3->set('CACHE', $dsn);
        }
        
        self::clear_cache($f3, '@');
        
        $site_init = $sitepath . "/bootstrap.php";
        if (file_exists($site_init)) {
            require $site_init;
        }
    }
    /**
     * Index.php to call this
     * @param type $webpath - __DIR__ of index.php
     * @param type $src -  parent of src/, composer root
     * @param type $folder - name of site folder to execute
     * @return type
     */
    static public function init_app($webpath, $src, $folder) {

        $f3 = \Base::Instance();
        
        $php = $src . '/';
        
        $sitepath = $php . "sites/" . $folder  . '/';

        // If running from composer vendor path, pkg_path !== php 
        $pkg_path = dirname(__DIR__,2) . '/';  //  <path>/src/WC

        if (!file_exists($sitepath)) {
            // assume a setup scenario, from inside a composer package
            // this file is in src, in the package, root/src/WC/App
            // 2 levels up
            $sitepath = $pkg_path . "sites/"  . $folder  . '/';
        }
        $f3->set('pkg',$pkg_path); 
        $f3->set('web', $webpath . '/');
        $f3->set('php', $php);
        $f3->set('is_vendor', ($php !== $pkg_path));
        $f3->set('sitepath', $sitepath);
        $f3->set('AUTOLOAD', $php . 'src/|' . $sitepath . 'src/');

        $app = \WC\App::Instance();
        $app->init($f3, $sitepath);
        return $app;
    }

    /** For hiding try-catch for run */
    public function run() {
        try {
            $this->f3->run();
        }
        catch (Exception $ex) {
            echo "<html><body>" . PHP_EOL;
            echo "<p>Flashed Messages</p>" . PHP_EOL;
            $msgs = \WC\UserSession::instance()->getMessages();
            echo "<pre>" . PHP_EOL;
            if (!empty($msgs)) {
                foreach($msgs as $m) {
                    echo $m . PHP_EOL;
                }
            }
            echo "</pre>" . PHP_EOL;
            echo "<p>Exception</p><pre>";
            echo $ex->getMessage();
            echo "</pre></body></html>" . PHP_EOL;
        }
    }
}
