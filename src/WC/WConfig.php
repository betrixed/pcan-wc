<?php
namespace WC;

class WConfig extends \stdClass implements \ArrayAccess {
    static function replaceDefines(WConfig $f) {
        $map = get_defined_constants();
        $f->replaceVars($map);
    }
    
    function exists($name) {
        return isset($this->$name);
    }
    
    public function __construct($init = null) {
        if (!empty($init)) {
            $this->addArray($init);
        }
    }
    
    static function updateValue(&$value, $map) {
        $matches = null;
        if (preg_match('/\${(\w+)}/', $value, $matches)) {
            $value = str_replace($matches[0], $map[$matches[1]], $value);
        }
    }
    
    static function replaceValues(&$arr, $map) {
        foreach($arr as $key => &$value) {
            if (is_string($value)) {
                self::updateValue($value, $map);
            }
            else if (is_array($value) || is_object($value)) {
                self::replaceValues($value, $map);
            } 
        }
    }

    // create 
    static public function fromPhp( $filename) {
        $values = require $filename;
        return self::fromArray($values);
    }
    static public function fromArray( $a) {
        $cfg = new WConfig();
        return $cfg->addArray($a);
    }
    static public function fromXml( $filename)
    {
        $xml = new XmlPhp();
        return $xml->parseFile($filename);
    }
    public function addXml( $filename) {
        $xml = new XmlPhp($this);
        return $xml->parseFile($filename);
    }
    public function addArray( $root) {
        foreach($root as $key => $val) {
            $this->$key = $val;
        }
        return $this;
    }
    
    public function offsetExists( $offset ) {
        return isset($this->$offset);
    }
    
    public function offsetGet( $offset ) {
        return $this->$offset;
    }
    
    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }
    
    public function offsetUnset($offset) {
        $this->$offset = null;
    }
}