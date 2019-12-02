<?php

namespace WC\Pgsql;
use WC\NameDef;

class ColumnDef extends NameDef {

    static public function get_type_size($type) : array
    {
        $match = null;
        $test = strtolower($type);
        $def = [];
        if (preg_match('/(\w*)\((.*)\)\s*(\w*)|(\w*)/', $test, $match)) {
            if (count($match) === 4) {
                $def['type'] = $match[1];
                $def['size'] = intval($match[2]);
                if  ($match[3] === 'unsigned') {
                        $def['unsigned'] = true;
                }
            }
            else if (count($match)===5) {
                $def['type'] = $test;
            }
        }
        return $def;
    }
    
     
    static public function quotedType($type) : bool
    {
        $typ = strtoupper($type);
        switch($typ) {
            case 'TINYINT':
            case 'BOOLEAN':
            case 'SMALLINT':
            case 'MEDIUMINT':
            case 'INT':
            case 'INTEGER':
            case 'BIGINT':
            case 'DECIMAL':
            case 'DEC':
            case 'NUMERIC':
            case 'FIXED':
            case 'FLOAT':
            case 'DOUBLE':
            case 'DOUBLE PRECISION':
            case 'BIT':
                    return false;
            default:
                    return true;
        }
    }
    
    /**
     *  return sequence name inside nextval for default
     */
    public function getSeqName() {
        if (!isset($this->default)) {
            return false;
        }
        if (strpos($this->default, "nextval('") === false) {
            return false;
        }
        $seq = substr($this->default,9);
        $spos = strpos($seq,"'");
        if ($spos !== false) {
            return substr($seq,0,$spos);
        }
        return false;
    }
     public function setSchema($row)
     {    
        $this->name = $row['column_name'];
        $def = [];
        
        $get_size = false;
       
        switch($row['data_type']) {
            CASE 'character varying':
                $def['type'] = 'varchar';
                $get_size = true;
                break;
            CASE 'character':
                $get_size = true;
                $def['type'] = 'character';
                break;
            CASE 'timestamp without time zone' :
                $def['type'] = 'timestamp';
                break;
            CASE 'decimal':
            CASE 'numeric':
                $def['scale'] = $row['numeric_scale'];
                $def['precision'] = $row['numeric_precision'];
            DEFAULT:
                $def['type'] = $row['data_type'];
                break;
        }
        if ($get_size) {
            $def['size'] = $row['character_maximum_length'];
        }
        
        $def['order'] = $row['ordinal_position'];
        $def['null'] = ($row['is_nullable'] === 'YES') ? true : false;

        if (isset($row['column_default'])) {
            $def['default'] = $row['column_default'];
        }

        if (isset($row['collation_name'])) {
            $def['collate'] = $row['collation_name'];
        }
        
        $this->setProperties($def);
     }
    
    /**
     * Alter Schema types from MySQL
     */
    public function translateType() {
        switch(strtolower($this->type)) {
            CASE 'tinyint' :
                if (isset($this->size)) {
                        $this->size = null;
                }
                if (isset($this->unsigned) || (isset($this->default) && is_numeric($this->default))) {
                    $this->type = 'smallint';
                }
                else {
                    $this->type = 'boolean';
                }
                break;
            CASE 'smallint' :
                if (isset($this->size)) {
                    $this->size = null;
                }
                break;
            CASE 'datetime' :
                $this->type = 'timestamp';
                if (isset($this->default)) {
                    if (strtolower($this->default) === 'current_timestamp()') {
                        $this->default = 'now()';
                    }
                }
                break;
            CASE 'int' :
                $this->type = "integer";
                 if (isset($this->size)) {
                    $this->size = null;
                }
                break;
            CASE 'tinytext':
                $this->type = 'text';
                break;
        }
    }
    public function toSql($stage) {
        $name = $this->name;
        $this->translateType();
        
        $outs = '"' . strtolower($name) . '" ' . $this->type;
        $size = $this->size ?? 0;
        if ($size > 0) {
            $outs .= '(' . $size . ')';
        }
        $unsigned = $this->unsigned ?? false;
        if ($unsigned) {
            //$outs .= ' unsigned';
        }
        
        $collate = $this->collate ?? false;
        if (!empty($collate)) {
            //$outs .= ' COLLATE ' . $collate;
        }
        $allow_null = $this->null ?? false;
        if (!$allow_null) {
            $outs .= ' NOT NULL';
        }
        /*
        if (array_key_exists('auto_inc', $stage)) {
            $auto_inc = $this->auto_inc ?? false;
            if ($auto_inc) {
                $outs .= ' AUTO_INCREMENT';
            }
        }
         
         */
        
        $default = $this->default ?? null;
        if (is_string($default) && static::quotedType($this->type) 
                && (strrpos($default, '()') === false)
                && (strpos($default,'::') === false)
                ) 
        {
            if ($default !== 'NULL' || !$allow_null) 
            {
                $default = '\'' . str_replace('\'','\'\'',$default) . '\'';
            }
        }
        if (is_bool($default)) {
            $default = $default ? 'true' : 'false';
        }
        // next value sequence may not yet be defined
        if (!is_null($default) && strpos($default,'nextval') === false) {
            $outs .= ' DEFAULT ' . $default;
        }
        return $outs;
    }

}
