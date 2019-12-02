<?php

namespace WC\Mysql;
use WC\NameDef;
use WC\DB\Script;
use WC\DB\AbstractDef;

class ColumnDef extends NameDef  {

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
    
    public function setSchema($row)
     {    
        $this->name = $row['Field'];
        
        $def = static::get_type_size($row['Type']);

        
        $def['null'] = ($row['Null'] === 'YES') ? true : false;

        if (isset($row['Default'])) {
            $def['default'] = $row['Default'];
        }

        if (isset($row['Collation'])) {
            $def['collate'] = $row['Collation'];
        }
        if (isset($row['Extra'])) {
            if ($row['Extra'] === 'auto_increment') {
                $def['auto_inc'] = true;
            }
        }
        
        $this->setProperties($def);
     }
    
    public function toSql(array $stage) {
        $name = $this->name;
        $outs = '`' . $name . '` ' . $this->type;
        $size = $this->size ?? 0;
        if ($size > 0) {
            $outs .= '(' . $size . ')';
        }
        $unsigned = $this->unsigned ?? false;
        if ($unsigned) {
            $outs .= ' unsigned';
        }
        
        $collate = $this->collate ?? false;
        if (!empty($collate)) {
            $outs .= ' COLLATE ' . $collate;
        }
        $allow_null = $this->null ?? false;
        if (!$allow_null) {
            $outs .= ' NOT NULL';
        }
        if (array_key_exists('auto_inc', $stage)) {
            $auto_inc = $this->auto_inc ?? false;
            if ($auto_inc) {
                $outs .= ' AUTO_INCREMENT';
            }
        }
        
        $default = $this->default ?? null;
        if (is_string($default) && static::quotedType($this->type) && (strrpos($default, '()') === false)) 
        {
            if ($default !== 'NULL' || !$allow_null) 
            {
                $default = '\'' . str_replace('\'','\'\'',$default) . '\'';
            }
        }
        if (!is_null($default)) {
            $outs .= ' DEFAULT ' . $default;
        }
        return $outs;
    }

}
