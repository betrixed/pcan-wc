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
    
    private $keeps;
    
    static public function myPath(): string {
        return __DIR__;
    }
    
    /**
     * Erase existing properties
     */
    public function loadSecrets(string $file) {
        $this->keeps = WConfig::serialCache($file);
    }
    /** 
     * Return type mixed
     */
    public function getSecrets($section = null) {
        if (!isset($this->keeps)) {
            throw new \Exception("Confidential properties not loaded");
        }
        $obj = $this->keeps;
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

}
