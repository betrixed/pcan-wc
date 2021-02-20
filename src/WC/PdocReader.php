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

/**
 * Version 2.0
 * Enhancement for the heck of it.
 * The document element always <pdom>, no attributes
 * List of tags that alias a <root> class, defines the PHP class name only once
 * <meta>
 *     <tdef>WC\Mysql\TableDef</tdev>
 *     <rdef>WC\Mysql\ReferenceDef<rdef>
 * </meta>
 */
class PdocReader extends \XMLReader {

    const XC_ARRAY = 0; // none indexed array
    const XC_TABLE = 1; // indexed array
    const XC_CONFIG = 2; // WConfig
    const XC_VALUE = 3; // a value?
    const XC_CLASSTAG = 4; // mapped tag to class
    const XC_DOCTAG = 5; // pdoc tag

    public $addRoot;
    public $root;
    public $table; // reference to array
    public $config; // config object
    public $path;
    public $dataType;
    public array $tag_objs; //

    public function __construct($add = null) {
        $this->addRoot = $add;
        $this->dataType = null;
        $this->tag_objs = [];
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
                    switch ($this->name) {
                        case "tb":
                        case "a" :
                        case "root":
                            $this->popTable();
                            break;
                        case "s" :                      
                        case "a":
                        case "i":
                        case "f":
                        case "_n":
                        case "b" :
                        case "pdoc":
                            break;
                        default:
                            if (array_key_exists($this->name, $this->tag_objs)) {
                                $this->popTable();
                            }
                            else {
                                throw new \Exception("Unknown tag end " . $this->name);
                            }
                            break;
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

    /**
     * process <tagname>namespace\class</tagname>
     */
    public function tagsTable() {
        while ($this->read()) {
            if ($this->nodeType === \XMLReader::ELEMENT) {
                $tagname = $this->name;
                $this->tag_objs[$tagname] = $this->readString();
            } else if ($this->nodeType === \XMLReader::TEXT) {
                
            } else if ($this->nodeType === \XMLReader::END_ELEMENT) {
                switch ($this->name) {
                    case "xtag":
                        return;
                    default:
                        break;
                }
            }
        }
    }

    public function popTable() {
        $ct = count($this->path);
        if ($ct > 0) {
            array_pop($this->path);
            $ct--;
            if ($ct > 0) {
                $dstack = $this->path[$ct - 1];
                $ptype = $dstack->dataType;
                switch ($ptype) {
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
        } else {
            $nroot = new WConfig();
        }
        return $nroot;
    }

    public function pushClass(string $classname, $k = null) {
        $ptype = $this->dataType;
        $nroot = new $classname();
        if (is_null($ptype)) {
            $this->root = $nroot;
        }
        else {
            $this->linkRoot($k, $nroot);
        }
        $this->stackRoot($nroot);
    }

    protected function linkRoot(string $k, object $nroot) {
        $ptype = $this->dataType;
        if (!empty($k)) {
            // use key
            switch ($ptype) {
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

    public function stackRoot(object $nroot) {
        $this->config = $nroot;
        $this->dataType = self::XC_CONFIG;
        $dstack = new DStack();
        $dstack->ref = $nroot;
        $dstack->dataType = self::XC_CONFIG;
        $this->path[] = $dstack;
    }

    public function pushRoot($k = null) {
        $ptype = $this->dataType;
        if (is_null($ptype)) {
            if ($this->addRoot) {
                $nroot = $this->addRoot;
            } else {
                $nroot = $this->newRoot();
            }
            $this->root = $nroot;
        } else {
            $nroot = $this->newRoot();
            $this->linkRoot($k, $nroot);
            // check out type of current "table
        }
        //$this->table = null;
        //If ->table is a reference, assigning to it does weird sxxx
        $this->stackRoot($nroot);
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
                switch ($ptype) {
                    CASE self::XC_CONFIG:
                        $this->config->$k = &$ntb;
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
        switch ($this->dataType) {
            CASE self::XC_CONFIG:
                $this->config->$k = $val;
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
                $ds = $this->readString();
                $this->setValue($ds, $k);
                break;
            case "b" :
                $s = strtolower($this->readString());
                $btemp = ($s === '1' || $s === 'true' || $s === 'y') ? true : false;
                $this->setValue($btemp, $k);
                break;
            case "f":
                $this->setValue(floatval($this->readString()), $k);
                break;
            case "pdoc":
                break;
            case "xtag":
                $this->tagsTable();
                break;
            default:
                $classname = $this->tag_objs[$this->name] ?? null;
                if ($classname) {
                    $this->pushClass($classname,$k);
                }
                else {
                    throw new \Exception("Unmapped tag " . $this->name);
                }
                break;
        }
    }

    static function isAssoc(&$arr) {
        if ([] === $arr)
            return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    static public function fromFile($filename) {
        return (new PdocReader())->parseFile($filename);
    }

}
