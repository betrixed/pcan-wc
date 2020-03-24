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

    static public function cache_file_list($f3, $suffix) {
        $dsn = $f3->get('CACHE'); // the dsn
        $parts = $parts = explode('=', $dsn, 2);
        if ($parts[0] === 'folder') {
            
        }
    }
    static public function clear_cache($f3, $suffix) {
        $value = $f3->get('CACHE_CLEAN');
        if (!empty($value)) {
            $f3->set('CACHE_VALID', true);
            return;
        }
        // store the hour timeout value, and as a timeout in seconds.
        $f3->set('CACHE_CLEAN', 3600, 3600);
        // record that cache timed out
        $f3->set('CACHE_VALID', false);
        $dsn = $f3->get('CACHE'); // the dsn
        $parts = $parts = explode('=', $dsn, 2);
        if ($parts[0] === 'folder') {
            $regex = '/' . '.*' . preg_quote('.', '/') . '.*' .
                    preg_quote($suffix, '/') . '/';
            $glob = @\glob($parts[1] . '*');
            if (!empty($glob)) {
                $files = [];
                foreach ($glob as $file) {
                    if (preg_match($regex, basename($file))) {
                        $files[] = $file;
                    }
                }
                if (!empty($files)) {
                    \WC\Dos::rm_old($files,  60 * 60 * 24);
                }
            }
        }
    }

    public function __construct() {
        $this->f3 = \Base::Instance();
    }

    // Load routes from .php config file and pre-process for cache
    static public function load_routes($f3, $path) {
        $routes = include $path;
        if (isset($routes)) {
            foreach ($routes as $k => $v) {
                $f3->route($k, $v);
            }
        }
        if (empty($f3->get('ROUTES'))) {
            throw new Exception('No Routes were loaded');
        }
    }

    // return bare error message page
    static public function error_page($msg) {
        $page = <<<EDOC
<!DOCTYPE html>
<html>
<body>
<p>$msg</p>
</body>
</html>
EDOC;
        return $page;
    }

    public function init($webpath,   $src,  $folder) {
        $php = $src . '/';
        $sitepath = $php . "sites/" . $folder . '/';

        // If running from composer vendor path, pkg_path !== php 
        // PHP5 has no depth parameter
        $pkg_path = dirname(dirname(__DIR__)) . '/';  //  <path>/src/

        if (!file_exists($sitepath)) {
            // assume a setup scenario, from inside a composer package
            // this file is in src, in the package, root/src/WC/App
            // 2 levels up
            $sitepath = $pkg_path . "sites/" . $folder . '/';
        }
        $f3 = \Base::instance();
        $f3->set('pkg', $pkg_path);
        $f3->set('web', $webpath . '/');
        $f3->set('php', $php);
        if ($php === $pkg_path) {
            // running site folder from vendor path
            $f3->set('is_vendor', true);
            $f3->set('vendor_path', dirname(dirname($pkg_path)) . '/');
        } else {
            // presume composer layout
            $f3->set('is_vendor', false);
            $f3->set('vendor_path', $php . 'vendor/');
        }

        $f3->set('sitepath', $sitepath);
        $f3->set('AUTOLOAD', $php . 'src/|' . $sitepath . 'src/');
        $this->f3 = $f3;
        $cli = php_sapi_name();
        if ($cli === "cli") {
            $cfg = WConfig::fromPhp($sitepath . 'cli_config.php');
            $routes_config = $sitepath . "cli_routes.php";
            $routes_cachefile = "cli_routes_cache.dat";
        } else {
            $cfg = WConfig::fromPhp($sitepath . 'config.php');
            $routes_config = $sitepath . "routes.php";
            $routes_cachefile = "routes_cache.dat";
        }

        $this->config = $cfg;

        if (isset($cfg->globals)) {
            if (!isset($cfg->globals['UI'])) {

                $cfg->globals['UI'] = $sitepath . 'views/|'
                        . $f3->get('pkg') . 'views/pcan/';
            }
            foreach ($cfg->globals as $k => $v) {
                $f3->set($k, $v);
            }
        }

        $temp = $f3->exists('TEMP') ? $f3->get('TEMP') : false;
        if ($temp === false || $temp === 'tmp/') {
            $f3->set('TEMP', $sitepath . 'tmp/');
            $temp = $f3->get('TEMP');
        }
        $f3->set('LOG', $temp . 'log/');

        $dsn = $f3->exists('CACHE') ? $f3->get('CACHE') : false;
        if ($dsn === false) {
            $dsn = "folder=" . $temp . 'cache/';
            $f3->set('CACHE', $dsn);
        }

        self::clear_cache($f3, '@');

        $start_time = microtime(true);
        $f3->set('start_routes', $start_time);
        if (isset($cfg->globals['cache_routes'])) {
            $cache_routes = $cfg->globals['cache_routes'];
        } else {
            $cache_routes = false;
            $f3->set('cache_routes', false);
        }

        if ($cache_routes) {
            $routes_cache = $temp . "/" . $routes_cachefile;
            if (!file_exists($routes_cache) || (filemtime($routes_config) > filemtime($routes_cache))) {
                static::load_routes($f3, $routes_config);
                $f3->sort_routes();
                file_put_contents($routes_cache, serialize($f3->get('ROUTES')));
            } else {
                // seems to be factor of 10x faster, 0.08 ms vs 0.8 ms for routes parse
                $f3->set('ROUTES', unserialize(file_get_contents($routes_cache)));
                // flag as pre-sorted
                $f3->set('sorted_routes', true);
            }
        } else {
            static::load_routes($f3, $routes_config);
            // delay sorting for fatfree
        }
        $end_time = microtime(true);
        $f3->set('routes_load_time', $end_time - $start_time);

        $site_init = $sitepath . "bootstrap.php";
        if (file_exists($site_init)) {
            require $site_init;
        }
        
        return true;
    }

    static public function run_app($webpath, $src, $folder) {
        try {
            $app = \WC\App::Instance();
           if ( $app->init($webpath, $src, $folder) ) {
               $app->run();
           }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
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
    static public function end_stats($f3) {
        $end_time = microtime(true);
        $render_start = $f3->get('render_time');
        $ctrl_time = $f3->get('ctrl_time');
        $handler_time = $f3->get('handler_found');
        $start_routes = $f3->get('start_routes');
        $route_time = ($handler_time - $start_routes) * 1000.0;
        $ctrl = ($render_start - $handler_time) * 1000.0;
        $render = ($end_time - $render_start) * 1000.0;
        $total = ($end_time - $start_routes) * 1000.0;
        $routes = $f3->get('routes_load_time') * 1000.0;
        return sprintf('%.2f MB, ', memory_get_peak_usage() / 1024 / 1024)
                . sprintf('Time LR %.2f H %.2f C %.2f V %.2f Total %.2f ms', $routes, $route_time, $ctrl, $render, $total);
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
