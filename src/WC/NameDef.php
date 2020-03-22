<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC;

/**
 * This seems too simple to bother, but it seems like a useful place for
 * a tiny piece of common functionality
 *
 * @author Michael Rynn
 */
class NameDef implements \ArrayAccess {

    //put your code here
    public $name;

    public function getName() {
        return $this->name;
    }

    
    public function setName($val) {
        $this->name = $val;
    }

    /**
     * 
     * @param type $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetGet($offset) {
        return is_null($this->$offset) ?  null : $this->$offset;
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)  {
        $this->$offset = null;
    }

    public function setProperties($def) {
        foreach($def as $key => $value) {
            $this->$key = $value;
        }
    }
    /**
     * Return list of quoted names in outer brackets
     * @param array $names
     */
    static public function name_list(array $names, $qoute = '`') {
        $outs = '(';
        foreach ($names as $i => $name) {
            if ($i > 0) {
                $outs .= ',';
            }
            $outs .= $qoute . $name . $qoute;
        }
        $outs .= ')';
        return $outs;
    }

    

    /**
     * Examine array references and return difference as array.
     * Does not modify referenced arrays.
     * @param array $a
     * @param array $b
     * @return array
     */
    static public function array_recurse_diff(array &$a, array &$b) {
        $aReturn = [];
        foreach ($a as $aKey => $aValue) {
            if (isset($b[$aKey])) { // array_key_exists better?
                if (is_array($aValue)) {
                    $aRecDiff = array_recurse_diff($aValue, $b[$aKey]);
                    if (count($aRecDiff)) {
                        $aReturn[$aKey] = $aRecDiff;
                    }
                } else {
                    if ($aValue != $b[$aKey]) {
                        $aReturn[$aKey] = $aValue;
                    }
                }
            } else {
                $aReturn[$aKey] = $aValue;
            }
        }
        return $aReturn;
    }
    /**
     * Input must be array of arrays, each has index $key
     * return array of values for $key
     * @param type $input
     * @param type $key
     * @return array
     */
    static public function keyval($input, $key)  {
        $result = [];
        foreach ($input as $val) {
            $result[] = $val[$key];
        }
        return $result;
    }

    /**
     * 
     * @param array $a
     * @param array $b
     * @return boolean if a and b match all the way
     */
    static public function arraytree_equal(array &$a, array &$b) {
        if (!is_array($a) || !is_array($b) || (count($a) != count($b))) {
            return false;
        }
        return (count(self::array_recurse_diff($a, $b)) == 0) && (count(self::array_recurse_diff($b, $a)) == 0) ? true : false;
    }

    /**
     * Change ascii ordering of underscore character in sort
     * @param type $a
     * @param type $b
     * @return integer
     */
    static public function name_cmp($a, $b) {
        $a2 = str_replace('_', '|', $a);
        $b2 = str_replace('_', '|', $b);
        return strcmp($a2, $b2);
    }

}
