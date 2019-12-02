<?php
namespace WC;

class DStack {
    public $ref;
    public $dataType;
}

function intercept_error($errno, $errstr, $errfile, $errline) {
    throw new \Exception($errstr . "  at line " . $errline . " in " . $errfile);
}
/** Read specific xml file format into a nested php array 
 *   Elements are 
 * tb - associative PHP array
 * a - sequential PHP array
 * s - string
 * i - integer
 * d - date
 * t - datetime
 * b - boolean
 * 
 * Attributes are
 * k - string key for associative array  
 */

class XmlPhp extends \XMLReader {
    const XC_ARRAY = 0; // none indexed array
    const XC_TABLE = 1; // indexed array
    const XC_CONFIG = 2; // WConfig
    const XC_VALUE = 3; // a value?
    
    public $addRoot;
    public $root;
    public $table; // reference to array
    public $config; // config object
    public $path;
    public $dataType;

    public function __construct($add = null) {
        $this->addRoot = $add;
        $this->dataType = null;
    }
    /**
     * Return array by reference
     * @param type $path
     * @return type
     * @throws type
     * @return array
     */
    public function parseFile($path) {
        $old_error_handler = set_error_handler("\WC\intercept_error");
        $toThrow = null;
        try {
            $this->open($path);
            while ($this->read()) {
                if ($this->nodeType === \XMLReader::ELEMENT) {
                    $this->element();
                } else if ($this->nodeType === \XMLReader::TEXT) {
                    
                } else if ($this->nodeType === \XMLReader::END_ELEMENT) {
                    if ($this->name === "tb" || $this->name === "a" || $this->name === "root") {
                        $this->popTable();
                    }
                }
            }
        } catch (\Throwable $e) {
            $toThrow = new \Exception($e->getMessage() . PHP_EOL . "Failed to read " . $path);
        } finally {
            $this->close();
            set_error_handler($old_error_handler);
        }
        if ($toThrow) {
            throw $toThrow;
        }
        return $this->root;
    }

    public function popTable() {
        $ct = count($this->path);
        if ($ct > 0) {
            array_pop($this->path);
            $ct--;
            if ($ct > 0) {
                $dstack = $this->path[$ct - 1];
                $ptype = $dstack->dataType;
                switch($ptype) {
                    CASE self::XC_TABLE:
                    CASE self::XC_ARRAY:
                        $this->table = &$dstack->ref;
                        break;
                    CASE self::XC_CONFIG: 
                        $this->config = $dstack->ref;
                        break;
                    
                }
                $this->dataType = $ptype;
            }
        } else {
            throw new \Exception("Pop on empty table stack");
        }
    }

    public function makeClass($c) {
        return new $c();
    }
    public function newRoot() {
        $class = $this->getAttribute('c');
        if (!empty($class)) {
            $nroot = $this->makeClass($class);
        }
        else {
            $nroot = new WConfig();
        }    
        return $nroot;
    }
    public function pushRoot($k = null) {
        $ptype = $this->dataType;
        if (is_null($ptype)) {
            if ($this->addRoot) {
                $nroot = $this->addRoot;
            }
            else {
                $nroot = $this->newRoot();
            }
            $this->root = $nroot;
        } else {
             $nroot = $this->newRoot();
            // check out type of current "table"
            if (!empty($k)) {
                // use key
                switch($ptype) {
                    CASE self::XC_CONFIG:
                        $this->config->$k = $nroot;
                        break;
                    CASE self::XC_TABLE:
                        $this->table[$k] = $nroot;
                        break;
                    DEFAULT:
                        throw new \Exception("Parent not indexed");
                        break;
                }   
            } else {
                // push end array
                if ($ptype !== self::XC_ARRAY) {
                    throw new \Exception("Parent not a list");
                }
                //$this->table->pushBack($ntb);
                
                $this->table[] = $nroot;
            }
        }
        //$this->table = null;
        //If ->table is a reference, assigning to it does weird sxxx
        $this->config = $nroot;
        $this->dataType = self::XC_CONFIG;
        $dstack = new DStack();
        $dstack->ref = $nroot;
        $dstack->dataType = self::XC_CONFIG;
        $this->path[] = $dstack;
    }
    public function pushTable($atype = null, $k = null) {
        // PHP arrays will require & for assignment
        $ntb = [];
        $ptype = $this->dataType;
        if (is_null($this->root)) {
            $this->root = &$ntb;
            $this->isTable = true;
        } else {
            if (!empty($k)) {
                switch($ptype) {
                    CASE self::XC_CONFIG:
                        $this->config->$k =  &$ntb;
                        break;
                    CASE self::XC_TABLE:
                        $this->table[$k] = &$ntb;
                        break;
                    DEFAULT:
                        throw new \Exception("Parent is not indexed");
                        break;
                }
            } else {
                // push end array
                if (self::XC_ARRAY !== $ptype) {
                    throw new \Exception("Parent is not list");
                }
                //$this->table->pushBack($ntb);
                $this->table[] = &$ntb;
            }
        }
        //$this->table = $ntb;
        $this->table = &$ntb;
        $this->dataType = $atype;
        $dstack = new DStack();
        $dstack->ref = &$ntb;
        $dstack->dataType = $atype;
        $this->path[] = $dstack;
    }

    public function setValue($val, $k = null) {
        switch($this->dataType) {
            CASE self::XC_CONFIG:
                $this->config->$k =  $val;
                break;
            CASE self::XC_TABLE:
                $this->table[$k] = $val;
                break;
            CASE self::XC_ARRAY:
                $this->table[] = $val;
                break;
            DEFAULT:
                throw new \Exception("Parent is not indexed");
                break;
        }
    }

    public function element() {
        $k = $this->getAttribute('k');
        if (is_null($k) && !empty($this->path)) {
            $ct = count($this->path);
            if ($ct > 0) {
                $dstack = $this->path[$ct - 1];
                if ($dstack->dataType === null) {
                    throw new \Exception("Missing key (k) attribute for " . $this->name);
                }
            }
        }
        switch ($this->name) {
            case "root":
                $this->pushRoot($k);
                break;
            case "tb" :
                $this->pushTable(self::XC_TABLE, $k);
                break;
            case "a" :
                // start array, which means no key for elements
                $this->pushTable(self::XC_ARRAY, $k);
                break;
            case "i" :
                $this->setValue(intval($this->readString()), $k);
                break;
            case "_n":
                $this->setValue(null, $k);
                break;
            case "s" :
                $this->setValue($this->readString(), $k);
                break;
            case "b" :
                $s = strtolower($this->readString());
                $btemp = ($s === '1' || $s === 'true' || $s === 'y') ? true : false;
                $this->setValue($btemp, $k);
                break;
            case "f":
                $this->setValue(floatval($this->readString()), $k);
                break;
        }
    }

    static function isAssoc(&$arr) {
        if ([] === $arr)
            return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    /**
     * 
     * @param type $ref - reference to array
     * @param type $outs - reference to string
     * @param type $doKey
     * @param string $indent
     */
    static function arrayToXml(&$ref, &$outs, $doKey = true, $indent = "") {
        $indent .= "  ";
        foreach ($ref as $k => $v) {
            $keyPart = $doKey ? " k=\"$k\"" : "";
            if (is_array($v)) {
                $hasKey = static::isAssoc($v);
                $tb = $hasKey ? "tb" : "a";
                $outs .= $indent . "<$tb$keyPart>" . PHP_EOL;
                static::arrayToXml($v, $outs, $hasKey, $indent);
                $outs .= $indent . "</$tb>" . PHP_EOL;
            } else if (is_integer($v)) {
                $outs .= $indent . "<i$keyPart>" . $v . "</i>" . PHP_EOL;
            } else if (is_null($v)) {
                $outs .= $indent . "<_n$keyPart></_n>" . PHP_EOL;
            } else if (is_bool($v)) {
                $outs .= $indent . "<b$keyPart>" . intval($v) . "</b>" . PHP_EOL;
            }  else if (is_float($v)) {
                $outs .= $indent . "<f$keyPart>" . $v . "</f>" . PHP_EOL;
            } 
            else if (is_string($v)) {
                if (is_numeric($v)) {
                    if (strpos($v,'.') !== false) {
                        $outs .= $indent . "<f$keyPart>" . $v . "</f>" . PHP_EOL;
                    }
                    else {
                        $outs .= $indent . "<i$keyPart>" . $v . "</i>" . PHP_EOL;
                    }
                }
                else {
                    $outs .= $indent . "<s$keyPart>" . $v . "</s>" . PHP_EOL;
                }
            }
            else if (is_object($v)){
                $class = get_class($v);
                $cattr = 'c="' . $class . '"';
                $outs .= $indent . "<root$keyPart $cattr>" . PHP_EOL;
                static::arrayToXml($v, $outs, true, $indent);
                $outs .= $indent . "</root>" . PHP_EOL;
            }
        }
    }

    static function toXmlDoc($ref) {
        $outs = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>" . PHP_EOL;
        if (is_object($ref)) {
            $tag = 'root';
            $class_attr = ' c="' . get_class($ref) . '"';
        }
        else {
            $tag = 'tb';
            $class_attr = '';
        }
        $outs .= "<$tag$class_attr>" . PHP_EOL;
        static::arrayToXml($ref, $outs);
        $outs .= "</$tag>" . PHP_EOL;
        return $outs;
    }
    
    static public function fromFile($filename) 
    {
        return (new XmlPhp())->parseFile($filename);
    }
}
