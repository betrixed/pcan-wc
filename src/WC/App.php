<?php
namespace WC;
/**
 * Collection object accessed as global.
 * Bridge compatibility for previous framework classes.
 * @author michael
 */

use Phalcon\Response;

class App extends WConfig
{
    
    protected $secrets;
    
    static public function myPath(): string {
        return __DIR__;
    }
    
    public function getSecrets($section = null) {
        if (!isset($this->secrets)) {
            $this->secrets = WConfig::serialCache($this->site_dir . "/.secrets.xml");
        }
        $obj = $this->secrets;
        if (!empty($section)) {
            if (!isset($obj[$section])) {
                throw new \Exception("App section $section is missing");
            }
            return $obj[$section];
        }
        return $obj;
    }
    
    public function __construct() {
        $now = microtime(true);
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

    /**
     * URL protocol and host
     * @return string
     */
    public function urlPrefix(): string {
        return $_SERVER['REQUEST_SCHEME']
                . '://' . $_SERVER['HTTP_HOST'];
}

    /**
     * 
     * @return string Full URL with Query String
     */
    public function getURL(): string {
        return $_SERVER['REQUEST_SCHEME'] . '://'
                . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function https() {
        $server = $_SERVER;
        if ($server['REQUEST_SCHEME'] !== 'https') {
            $ssl_host = $this->get('ssl_host', null);
            $host = $server['HTTP_HOST'];
            // This is because a ssl certificate required a www.NAME
            if (!empty($ssl_host)) {
                $ssl_host = $ssl_host . '.';
                if (strpos($host, $ssl_host) !== 0) {
                    $host = $ssl_host . $host;
                }
            }
            static::reroute('https://' . $host . $server['REQUEST_URI']);
            return false;
        }
        return true;
    }

    public function notAuthorized() {
        $this->user_session->flash('No access to ' . $this->handledUri);
        $this->reroute('/error/block');
    }
    
    public function reroute($url) {
        $this->user_session->save();

        if (strpos($url, 'http') !== 0) {
            $url = $this->urlPrefix() . $url;
        }
        $response = $this->services->get('response');
        $response->redirect($url, true);
        return $response;
    }
}
