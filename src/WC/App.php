<?php

namespace WC;

class App extends \Prefab {

    public $f3;
    public $config;
    public $schemaDir;
    
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

}
