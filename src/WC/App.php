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

    static public function load_routes($f3, $path) {
        $start_time = microtime(true);
        $routes = include $path;
        if (isset($routes)) {
            foreach ($routes as $k => $v) {
                $f3->route($k, $v);
            }
        }
        $end_time = microtime(true);
        $f3->set('routes_load_time', $end_time - $start_time);
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


        // check for pre-processed routes cache
        $cache_routes =  $cfg->globals['routes_cache'] ?? true;
        $routes_config = $sitepath . "/routes.php";
        if ($cache_routes) {
            
            $routes_cache = $temp . "/routes_cache.dat";
            if (!file_exists($routes_cache) || (filemtime($routes_config) > filemtime($routes_cache))) {
                static::load_routes($f3, $routes_config);
                file_put_contents($routes_cache, $f3->get('ROUTES'));
            } else {
                $start_time = microtime(true);
                // seems to be factor of 10x faster, 0.08 ms vs 0.8 ms for routes parse
                $f3->set('ROUTES',unserialize(file_get_contents($routes_cache)));
                $end_time = microtime(true);
                
                $f3->set('routes_load_time', $end_time - $start_time);
            }
        }
        else {
            static::load_routes($f3, $routes_config);
        }
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

        $sitepath = $php . "sites/" . $folder . '/';

        // If running from composer vendor path, pkg_path !== php 
        $pkg_path = dirname(__DIR__, 2) . '/';  //  <path>/src/WC

        if (!file_exists($sitepath)) {
            // assume a setup scenario, from inside a composer package
            // this file is in src, in the package, root/src/WC/App
            // 2 levels up
            $sitepath = $pkg_path . "sites/" . $folder . '/';
        }
        $f3->set('pkg', $pkg_path);
        $f3->set('web', $webpath . '/');
        $f3->set('php', $php);
        $f3->set('is_vendor', ($php !== $pkg_path));
        $f3->set('sitepath', $sitepath);
        $f3->set('AUTOLOAD', $php . 'src/|' . $sitepath . 'src/');

        $app = \WC\App::Instance();
        $app->init($f3, $sitepath);
        return $app;
    }

    /**
     * String for timing stats during request response
     * LR - load routes
     * R - Routing till controller called
     * C - Render view called
     * V - View render
     * Total -  request to end render
     * @return string
     * 
     */
    static public function end_stats($f3) : string
    {
        $end_time = microtime(true);
        $render_start = $f3->get('render_time');
        $ctrl_time = $f3->get('ctrl_time');
        $request_start = $f3->get('SERVER.REQUEST_TIME_FLOAT');
        $route_time = ($ctrl_time - $request_start)*1000.0;
        $ctrl = ($render_start - $ctrl_time) * 1000.0;
        $render = ($end_time - $render_start) * 1000.0;
        $total = ($end_time - $request_start) * 1000.0;
        $routes = $f3->get('routes_load_time')*1000.0;
        return sprintf('%.2f MB, ', memory_get_peak_usage() / 1024 / 1024) 
            . sprintf('Time LR %.2f R %.2f C %.2f V %.2f Total %.2f ms',  $routes, $route_time, $ctrl, $render, $total);
        
    }
    /** For hiding try-catch for run */
    public function run() {
        try {
            $this->f3->run();
        } catch (Exception $ex) {
            echo "<html><body>" . PHP_EOL;
            echo "<p>Flashed Messages</p>" . PHP_EOL;
            $msgs = \WC\UserSession::instance()->getMessages();
            echo "<pre>" . PHP_EOL;
            if (!empty($msgs)) {
                foreach ($msgs as $m) {
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
