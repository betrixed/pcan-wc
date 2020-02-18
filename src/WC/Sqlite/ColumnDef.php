<?php

namespace WC\Sqlite;
use WC\NameDef;
use WC\DB\Script;
use WC\DB\AbstractDef;

class ColumnDef extends NameDef  {

    /**
     * 
     * @param type $type
     * @return array
     */
    static public function get_type_size($type) 
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
    
     /**
      * 
      * @param type $type
      * @return bool
      */
    static public function quotedType($type) 
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
        $size = !isset($this->size) ? 0 : $this->size;
        if ($size > 0) {
            $outs .= '(' . $size . ')';
        }
        $unsigned = !isset($this->unsigned) ? false : $this->unsigned;
        if ($unsigned) {
            $outs .= ' unsigned';
        }
        
        $collate = !isset($this->collate ) ? false : $this->collate;
        if (!empty($collate)) {
            $outs .= ' COLLATE ' . $collate;
        }
        $allow_null = !isset($this->null) ? false : $this->null;
        if (!$allow_null) {
            $outs .= ' NOT NULL';
        }
        $auto_inc = false;
        if (array_key_exists('auto_inc', $stage)) {
            $auto_inc = isset($this->auto_inc) ? false : $this->auto_inc;
            
        }

        $default = !isset($this->default) ? null : $this->default;
        
        if (is_string($default) && !empty($default)) 
        {
             /** Filter out some PgSQL nextval into AUTO_INCREMENT 
              */
        // ^([\w]*)\((\'[\w]*\'::\w*)\)
            $matches = null;
            // a function
            if (preg_match('/^([\w]*)\((\'[\w]*\'::\w*)\)/', $default, $matches)===1) {
                if ($matches[1] === 'nextval') {
                    $auto_inc = true;
                    $default = null;
                }
                
            }
            // a quoted type value
            else if (preg_match('/^\'(.+)\'::(.*)/', $default, $matches) ===1){
                $default = $matches[1];
                if ( strpos($matches[2],'char') !== false ) {
                    // a character constant type, double any inner single quotes.
                    $default = '\'' . str_replace('\'','\'\'',$default) . '\'';
                }
            }
            else if ($default !== 'NULL' || !$allow_null) 
            {
                $default = '\'' . str_replace('\'','\'\'',$default) . '\'';
            }
        }
        if (!is_null($default)) {
            $outs .= ' DEFAULT ' . $default;
        }
        else if ($auto_inc) {
                $outs .= ' PRIMARY KEY AUTO_INCREMENT';
        }
        return $outs;
    }

}
