<?php

namespace WC;

class App extends WConfig {

    public $f3;
    public $config;
    public $schemaDir;

    static public $instance;
    static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new App();
        }
        return self::$instance;
    }
    
    /**
     * Secrets are probably not stored in the database
     * @return configuration array or object
     */
    protected $secrets;
    public function get_secrets() {
        if (!isset($this->secrets)) {
            $this->secrets = WConfig::fromXml($this->APP . "/.secrets.xml");
        }
        return $this->secrets;
    }

    public function getSchemaDir() {
        if (!isset($this->schemaDir)) {
            $this->schemaDir = $this->APP . '/schema/';
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
    /** Assume WEB_DIR and PHP_DIR are defined */
    public function init( $folder) {
        
        
        // If running from composer vendor path, pkg_path !== php 
        // PHP5 has no depth parameter
        
        
        // temporary files and cache 
        $sitepath = PHP_DIR . '/sites/' . $folder;
        $tempdir = $sitepath . '/tmp';
        $cachedir = $tempdir . '/cache';
        
        if (!file_exists($cachedir )) {
            mkdir($cachedir, 0777, true);
        }
        
        $src_path = dirname(dirname(__DIR__));
        
        $this->PHP = PHP_DIR;
        $this->WEB = WEB_DIR;
       
        $this->SRC = $src_path;

        if (PHP_DIR === $src_path) {
            $this->is_vendor = true;
            $this->VENDOR = $src_path;
        } else {
            // presume composer layout
            $this->is_vendor = false;
            $this->VENDOR = PHP_DIR . '/vendor';
        }

        $prefix = (php_sapi_name() === "/cli") ? '/cli_' : '/';
        $config_file = 'config.php';
        $configpath = $sitepath .  $prefix . $config_file;

        if (!file_exists($configpath)) {
            $sitepath = $src_path . '/sites/' . $folder;
            $configpath = $sitepath .  $prefix . $config_file;
            
        }
        $routes_config = $sitepath . $prefix . 'routes.php';
        $routes_cachefile = $prefix . 'routes_cache.dat';
        
        $this->APP = $sitepath;
        
        
        $cfg = WConfig::fromPhp($configpath);
        $this->config = $cfg;

        if (isset($cfg->globals)) {
            $this->addArray($cfg->globals);
        }
        if (!isset($this->UI)) {
            $this->UI =  [$sitepath . '/views/', 
                            $src_path . '/views/pcan/'];
        }
        
        $f3 = \Base::instance();
        $this->f3 = $f3;
        $src_paths = [$src_path . '/src/' , $sitepath . '/src/'];   
        $f3->set('AUTOLOAD', implode('|', $src_paths));
        
        $temp = $f3->exists('TEMP') ? $f3->get('TEMP') : $tempdir;
        if ($temp === false || $temp === 'tmp/') {
            $f3->set('TEMP', $tempdir);
            $temp = $f3->get('TEMP');
        }
        $this->TEMP = $temp;

        $f3->set('LOG', $temp . 'log/');
        
        $dsn = $f3->exists('CACHE') ? $f3->get('CACHE') : false;
        if ($dsn === false) {
            $dsn = "folder=" . $cachedir;
            $f3->set('CACHE', $dsn);
        }
        $this->CACHE = $dsn;
        
        self::clear_cache($f3, '@');

        if (isset($cfg->globals['cache_routes'])) {
            $cache_routes = $cfg->globals['cache_routes'];
        } else {
            $cache_routes = false;
            $f3->set('cache_routes', false);
        }

        if ($cache_routes) {
            $routes_cache = $temp . $routes_cachefile;
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

        $site_init = $sitepath . "bootstrap.php";
        if (file_exists($site_init)) {
            require $site_init;
        }
        
        return true;
    }


    static public function run_app($folder) {
        try {
            $app = \WC\App::instance();
           if ( $app->init($folder) ) {
               $app->run();
           }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function __construct() {
        $now = microtime(true);
        $this->f3 = \Base::Instance();
        parent::__construct([
            'start_time' => $now, 
            'ctrl_time' => $now, 
            'render_time' => $now]);
    }
    /** simple stats of request 
     * setup  = ctrl_time - start_time
     * handler = render_time - ctrl_time
     * response = end_time - render_time
     * 
     * @param type $f3
     * @return type
     */
   public function end_stats() {
        $end_time = microtime(true);
        $setup_time = ($this->ctrl_time - $this->start_time) * 1000.0;
        $handler_time = ($this->render_time - $this->ctrl_time) * 1000.0;
        $render_time = ($end_time - $this->render_time) * 1000.0;
        $total = ($end_time - $this->start_time) * 1000.0;
        $memory = memory_get_peak_usage() / 1024 / 1024;
        return sprintf('Setup %.2f Handle %.2f Render %.2f Total %.2f ms, Memory %.2f MB',
                        $setup_time, $handler_time, $render_time, $total, $memory);
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
