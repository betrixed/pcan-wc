<?php
namespace WC;

class WConfig extends \stdClass implements \ArrayAccess {
    static function replaceDefines(WConfig $f) {
        $map = get_defined_constants();
        $f->replaceVars($map);
    }
    
    function toArray() : array {
        return get_object_vars($this);
    }
    function exists($name) {
        return isset($this->$name);
    }
    
    function has($key) : bool 
    {
         return isset($this->$key);
    }
    
    function get($key, $default = null) {
        return isset($this->$key) ? $this->$key : $default;
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

    public function __construct($init = null) {
        if (!empty($init)) {
            $this->addArray($init);
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

    static public function fromToml( $filename )
    {
        return \Toml\Input::parseFile($filename);
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
    /**
     * Could return array, or some configuration object
     * @param type $filename
     * @return array or object
     * @throws Exception
     */
    static function serialCache($filename) {
        $pinfo = pathinfo($filename);
        if (!file_exists($filename)) {
            throw new Exception("File " . $filename . " not found");
        }
        $cache_name = $pinfo['filename'];
        if (substr($cache_name,0,1) !== '.') {
            $cache_name = '.' . $cache_name;
        }
        $cache_file = $pinfo['dirname'] . '/' . $cache_name
                 . '_' . $pinfo['extension'] . '.ser';
        
        if (file_exists($cache_file)) {
            if (filemtime($cache_file) > filemtime($filename)) {
                return unserialize(file_get_contents($cache_file));
            }
        }
        $data = null;
        switch($pinfo['extension']) {
            case 'xml' : 
                $data = static::fromXml($filename);
                break;
            case 'php' :
                $data = static::fromPhp($filename);
                break;
            /* case 'toml' :
                $data = static::fromToml($filename);
                break; */
        }
        if (!empty($data)) {
            file_put_contents($cache_file, serialize($data));
            return $data;
        }
        else {
            throw new Exception("Read error from " . $filename);
        }     
    }
}