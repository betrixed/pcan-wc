<?php

namespace WC\DB;

/**
 * Accumulate SQL statements in order,
 * as script. Each item is either executabe sql,
 * or a comment beginning with '--'
 *
 * @author michael
 */
class Script {
    public $log;
    
    public function __contruct() {
        $this->log = [];
    }
    public function add($sql = null, $newline = PHP_EOL) {
        if (is_string($sql)) {
            $this->log[] = $sql . $newline;
        }
        else if (is_array($sql)) {
            foreach($sql as $text) {
                $this->log[] = $text;
            }
        }
        else if (is_null($sql)) {
            $this->log[] = $newline;
        }
    }
    
    public function hasData() {
        return (!empty($this->log));
    }
    public function __toString() {
        $outs = '';
        if (empty($this->log)) {
            return $outs;
        }
        foreach($this->log as $val) {
            if (!empty(trim($val))) {
                $outs .= $val;
            }
            else {
                $outs .=   PHP_EOL;
            }
            //else if (substr($val,0,strlen(PHP_EOL)) !== PHP_EOL){
            //    $outs .=  $val . PHP_EOL;
            //}
        }
        return $outs;
    }
    public function run($db) {
        $i = 0;
        if (empty($this->log)) {
            return;
        }
        try {
            foreach($this->log as $text) {
                if (substr($text,0,2) === '--') {
                    continue;
                }
                $db->exec($text);
                $i++;
            }
        } catch (\Exception $ex) {
            throw new \Exception("Error: " . $this->log[$i] . PHP_EOL . $ex->getMessage());
        }
    }
}
