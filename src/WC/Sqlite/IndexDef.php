<?php

/**
 * @author Michael Rynn
 */

namespace WC\Sqlite;

use WC\NameDef;
use WC\DB\Script;


/**
 * Intermediate type to and from on-file TOML table definition. 
 * Match read and write key names and format.
 */
class IndexDef extends \WC\DB\AbstractDef {


    public function setSchema($row) {
        $this->name = $row['Key_name'];
        $def['name'] = $this->name;
        if ($row['Non_unique']) {
            $def['type'] = 'INDEX';
        }
        else {
            if ($this->name === 'PRIMARY') {
                $def['type'] = $this->name;
            }
            else {
                $def['type'] = 'UNIQUE';
            }
        }
        $columns[] = $row['Column_name'];
        $def['columns'] = $columns;
        $this->setProperties($def);
    }
    
    public function toSql(Script $script, array $stage, TableDef $tdef) {
        $name = $this->name;
        $qt = '';
        $columns = $this->columns;
        if (strpos($name, 'UNIQUE') !== false) {
            $synth = true;
        } else {
            $synth = false;
            foreach ($columns as $col) {
                if (strpos($name, $col) !== false) {
                    $synth = true;
                    break;
                }
            }
        }
        $tname = $tdef->name;
        if ($synth) {
            $kname = implode('_', $columns);
            if ($this->type === 'UNIQUE') {
                $name = $tname . '_uk_' . $kname;
            } else {
                $name = $tname . '_ix_' . $kname;
            }
        }
        $clist = NameDef::name_list($columns, $qt);
        $outs = null;
        switch ($this->type) {
            case 'PRIMARY':
                if (isset($stage['primary']) && $stage['primary']) {
                    $outs = 'ALTER TABLE ' . $tdef->name . ' ADD PRIMARY KEY ' . $clist;
                }
                break;
            case 'UNIQUE':
                $outs = 'CREATE UNIQUE INDEX ' . $qt . $name . $qt . ' ON ' . $tname . ' ' . $clist;
                break;
            case 'INDEX':
                $outs = 'CREATE INDEX ' . $qt . $name . $qt . ' ON ' . $tname . ' ' . $clist;
                break;
        }
        if (!empty($outs)) {
            $script->add($outs . ';' . PHP_EOL);
        }
    }
    
    public function generate( $script, $stage) {
        $outs = '';
        $name = $this->name;
        switch($this->type) {
            case 'PRIMARY':
                $outs .= 'PRIMARY KEY ' . NameDef::name_list($this->columns);
                break;
            case 'UNIQUE':
                $outs .= 'UNIQUE KEY `' . $name . '` ' . NameDef::name_list($this->columns);
                break;
            case 'INDEX':
                $outs .= 'KEY `' . $name . '` ' . NameDef::name_list($this->columns);
                break;
        }
        return $outs;
    }
   

    /**
     * May be 'UNIQUE or PRIMARY or null.
     * @return string 
     */
    public function getIndexType() {
        return !isset($this->type) ? null : $this->type;
    }

    /**
     * List of column names
     * @return array
     */
    public function getIndexColumns() {
        return !isset($this->columns) ? null : $this->column;
    }

}
