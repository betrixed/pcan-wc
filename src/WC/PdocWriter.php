<?php
namespace WC;


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
class PdocWriter {
    
    protected array $obj_tags = [];
    protected array $tag_objs = [];
    const DATETIME_FMT = "Y-m-d H:i:s";
    
    public function write($ref, array $objtags = []) : string {
        $this->obj_tags = $objtags;
        $this->tag_objs = array_flip($objtags);
        return $this->outer($ref);
    }

    /**
     * 
     * @param type $ref - reference to array or object
     * @param type $outs - reference to string
     * @param type $doKey
     * @param string $indent
     */
    private function iterate($ref, bool $doKey = true, string $indent = "") : string {
        $indent .= "  ";
        $outs = "";
        foreach ($ref as $k => $v) {
            $keyPart = $doKey ? " k=\"$k\"" : "";
            if (is_array($v)) {
                // string keyed arrays are assumed to have no 0-key value
                if (count($v) > 0) {
                    $hasKey = !isset($v[0]);
                    $tb = $hasKey ? "tb" : "a";
                    $outs .= $indent . "<$tb$keyPart>" . PHP_EOL;
                    $outs .= $this->iterate($v, $hasKey, $indent);
                    $outs .= $indent . "</$tb>" . PHP_EOL;
                }
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
                    $ctag = $this->obj_tags[$class] ?? null;
                    if ($ctag !== null) {
                        $tag = $ctag;
                        $cattr = '';
                    }
                    else {
                        $tag = 'root';
                        $cattr = ' c="' . $class . '"';
                    }
                    $outs .= $indent . "<$tag$keyPart$cattr>";
                    if ($v instanceof \DateTime ) {
                        $outs .= $v->format(self::DATETIME_FMT);
                        $outs .= "</$tag>" . PHP_EOL;
                    }
                    else {
                        $outs .= PHP_EOL;
                        $outs .= $this->iterate($v, true, $indent);
                        $outs .= $indent . "</$tag>" . PHP_EOL;
                    }
            }
            
        }
        return $outs;
    }

    /**
     * $ref - object with nested properties, or array
     * @param type $ref
     * $objtags array $key is tagname, $value is class name
     * @param array $objtags
     * @return string
     */
    function objectStart($ref) : string  {
        $ctag = $this->obj_tags[get_class($ref)] ?? null;
        if ($ctag !== null) {
            $tag = $ctag;
            $class_attr = ''; // class implied by tag
        }
        else {
            $tag = 'root';
            $class_attr = ' c="' . get_class($ref) . '"';
        }
        $outs = "<$tag$class_attr>" . PHP_EOL;
        $outs .= $this->iterate($ref);
        $outs .= "</$tag>" . PHP_EOL;
        return $outs;
    }
    function outer($ref) : string {
        $outs = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>" . PHP_EOL;
        $outs .= "<pdoc>" . PHP_EOL;
        if (!empty($this->tag_objs)) {
            $outs .= "<xtag>" . PHP_EOL;
            foreach($this->tag_objs as $tagname => $classname) {
                $outs .= "<$tagname>" . $classname . "</$tagname>" . PHP_EOL;
            }
            $outs .= "</xtag>" . PHP_EOL;
        }
        // root is anonymous array or object
        if (is_object($ref)) {
            $outs .= $this->objectStart($ref);
        }
        else { // plain array
            $outs .= "<tb>" . PHP_EOL;
            $outs .= $this->iterate($ref, $outs);
            $outs .= "</tb>" . PHP_EOL;
        }
        $outs .= "</pdoc>";
        return $outs;
    }
    
    static public function fromFile($filename) 
    {
        return (new XmlPhp())->parseFile($filename);
    }
}
