<?php
namespace WC;
/**
 * Collection object accessed as global.
 * Bridge compatibility for previous framework classes.
 * @author michael
 */

class App extends WConfig
{
    static protected $instance;
    
    
    static public function mypath() {
        return __DIR__;
    }
    static public function instance() : App {
        if (!isset(self::$instance)) {
            // case insensitive keys
            self::$instance = new App();
        }
        return self::$instance;
    }
    
    protected $secrets;
    public function get_secrets() {
        if (!isset($this->secrets)) {
            $this->secrets = WConfig::fromXml($this->APP . "/.secrets.xml");
        }
        return $this->secrets;
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
    
    static public function run_app($folder) {
    
    }
}
