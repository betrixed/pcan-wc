<?php

/**
 * @author Michael Rynn
 */

namespace WC\Pgsql;

use WC\NameDef;

/**
 * Intermediate type to and from on-file TOML table definition. 
 * Match read and write key names and format.
 */
class IndexDefParser {

    public $name;
    public $schema;
    public $table;
    public $type;
    public $columns;
    public $using;

    private function fields($flist) {
        $p1 = strpos($flist,'(');
        $p2 = strrpos($flist,')');
        $test = explode(',', substr($flist,$p1+1, $p2 - $p1 -1));
        foreach($test as $val) {
            $this->columns[] = trim($val);
        }
    }
    private function ptable($tname) {
        $parts = explode('.', $tname);
        $this->table = $parts[1];
        $this->schema = $parts[0];
    }

    public function parse($def) {
        $s = explode(' ', $def);
        $lim = count($s);
        $ix = 0;
        $unique = false;
        $ontable = false;
        $using = false;
        $index = false;
        while ($ix < $lim) {
            $part = $s[$ix];
            switch ($part) {
                CASE 'CREATE':
                    break;
                CASE 'UNIQUE':
                    $unique = true;
                    break;
                CASE 'INDEX':
                    if ($unique) {
                        $this->type = 'UNIQUE';
                    } else {
                        $this->type = 'INDEX';
                    }
                    $index = true;
                    break;
                CASE 'ON':
                    $ontable = true;
                    break;
                CASE 'USING':
                    $using = true;
                    break;
                DEFAULT:
                    if ($ontable) {
                         $this->ptable($part);
                        $ontable = false;
                    }
                    else if ($using) {
                        $this->using = $part;
                        $using = false;
                    }
                    else if ($index) {
                        $this->name = $part;
                        $index = false;
                    }
                    else if (strpos($part,'(') === 0) {
                        $ix++;
                        while($ix < $lim) {
                            $part .= $s[$ix];
                            $ix++;
                        }
                        $this->fields($part);
                    }    
            }
            $ix++;
        }
    }

}

class IndexDef extends NameDef {

    public function setSchema($row) {
        $this->name = $row['constraint_name'];
        $def['name'] = $this->name;
        if ($row['Non_unique']) {
            $def['type'] = 'INDEX';
        } else {
            if ($this->name === 'PRIMARY') {
                $def['type'] = $this->name;
            } else {
                $def['type'] = 'UNIQUE';
            }
        }
        $columns[] = $row['Column_name'];
        $def['columns'] = $columns;
        $this->setProperties($def);
    }

    public function toSql($script, $stage, $tdef) {
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
        switch ($this->type) {
            case 'PRIMARY':
                $outs = 'ALTER TABLE ' . $tdef->name . ' ADD PRIMARY KEY ' . $clist;
                break;
            case 'UNIQUE':
                $outs = 'CREATE UNIQUE INDEX ' . $qt . $name . $qt . ' ON ' . $tname . ' ' . $clist;
                break;
            case 'INDEX':
                $outs = 'CREATE INDEX ' . $qt . $name . $qt . ' ON ' . $tname . ' ' . $clist;
                break;
            DEFAULT:
                $outs = null;
        }
        if (!empty($outs)) {
            $script->add($outs . ';' );
        }
    }

    public function fromDef($def) {
        $this->def = $def;
        // pattern CREATE { UNIQUE INDEX | INDEX } keyname ON schema.table
        //    USING btree (column_list)
        $parser = new IndexDefParser();
        $parser->parse($def);
        foreach($parser as $p => $val) {
            $this->$p = $val;
        }
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
        return !isset($this->columns) ? null : $this->columns;
    }

}
