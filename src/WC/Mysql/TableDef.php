<?php

/**
 * @author Michael Rynn
 */

namespace WC\Mysql;

use WC\NameDef;
use WC\DB\Script;
use WC\DB\BatchInsert;
/**
 * Intermediate class for SQL table definition. 
 * Save and Load from TOML file format.
 * Generate more or less directly the 
 * table definitions used by Phalcon SQL database classes.
 */
class TableDef extends \WC\DB\AbstractDef {

    public $columns;
    public $indexes;
    public $references;
    public $options;

    public function __construct() {
        $this->columns = [];
        $this->indexes = [];
        $this->references = [];
        $this->options = [];
        $this->foreignkeys = [];
    }

    /**
     * Return list of columns that has parameter $key with value $value
     * @param type $key
     * @param type $value
     */
    public function getColumnsByProperty($key, $value) {
        $list = [];
        foreach ($this->columns as $cdef) {
            $test = $cdef[$key];
            if ($test == $value) {
                $list[] = $cdef;
            }
        }
        return $list;
    }

    /** Get list of column names in order of columns
     * 
     * @return array of string
     */
    public function getFieldNames() {
        return array_keys($this->columns);
    }

    /**
     * Get the integer offsets for each column name, indexed by name
     * 
     * @return array of name => integer offsets from 0
     */
    public function getColumnOffsets() {
        $lookup = [];
        foreach ($this->columns as $idx => $col) {
            $lookup[$col->getName()] = $idx;
        }
        return $lookup;
    }

    public function autoInc() {
        $cols = $this->getColumnsByProperty('auto_inc', true);
        if (!empty($cols)) {
            return $cols[0];
        } else {
            return null;
        }
    }

    public function getIndexNames() {
        $list = [];
        foreach ($this->indexes as $idx) {
            $list[$idx->getName()] = $idx;
        }
        return $list;
    }

    public function getIndexesByType($type) {
        $list = [];
        foreach ($this->indexes as $idx) {
            if ($idx->getIndexType() === $type) {
                $list[] = $idx;
            }
        }
        return $list;
    }

    public function getNonPrimaryIndexes() {
        $list = [];
        foreach ($this->indexes as $idx) {
            if ($idx->getIndexType() != 'PRIMARY') {
                $list[] = $idx;
            }
        }
        return $list;
    }

    public function getFieldDataTypes() {
        $result = [];
        foreach ($this->columns as $coldef) {
            $result[$coldef->getName()] = $coldef->getValue('type');
        }
        return $result;
    }

    /**
     * Set a table 'option'
     * @param string $optionName
     * @param type $optionValue
     */
    public function setOption(string $optionName, $optionValue) {
        $this->options[$optionName] = $optionValue;
    }

    public function exists($db) {
        $sql = <<<EOS
SELECT * 
FROM information_schema.tables
WHERE table_schema = :dbname
    AND table_name = :tname
LIMIT 1
EOS;
        $rows = $db->exec($sql, ['dbname' => $db->name(), 'tname' => $this->name]);
        return !empty($rows);
    }

    public function readSchema($db, $rec) {
        $this->name = $rec['table_name'];
        $options = [];
        $check = ['table_comment' => 'comment',
            'auto_increment' => 'auto_increment',
            'engine' => 'engine',
            'table_collation' => 'collate',
            'table_schema' => 'table_schema'
        ];

        foreach ($check as $opt => $value) {
            if (!empty($rec[$opt])) {
                $options[$value] = $rec[$opt];
            }
        }

        $this->options = $options;

        $src = $db->name() . '.' . $this->name;

        $data = $db->exec('SHOW FULL COLUMNS from ' . $src . ' ');
        $this->columns = [];
        foreach ($data as $i => $row) {
            $cdef = new ColumnDef();
            $cdef->setSchema($row);
            $this->columns[$cdef->name] = $cdef;
        }
        $data = $db->exec('SHOW INDEXES from ' . $src . ' ');
        $this->indexes = [];


        $keyname = '';
        $idef = null;
        foreach ($data as $i => $row) {
            $ixname = $row['Key_name'];
            if ($ixname !== $keyname) {
                $idef = new IndexDef();
                $idef->setSchema($row);
                $this->indexes[$idef->name] = $idef;
                $keyname = $ixname;
            } else {
                $idef->columns[] = $row['Column_name'];
            }
        }
    }

    public function makeCreate($stage) {
        $outs = 'CREATE TABLE `' . $this->name . '` (' . PHP_EOL;
        $first = true;
        $indent = '    ';
        foreach ($this->columns as $key => $cdef) {
            if ($first) {
                $first = false;
            } else {
                $outs .= ',' . PHP_EOL;
            }
            $outs .= $indent . $cdef->toSql($stage);
        }
        $outs .= PHP_EOL . ')';
        return $outs;
    }

    public function generate( $script,  $stage) {
        if (array_key_exists('drop-tables', $stage)) {
            $script->add('DROP TABLE IF EXISTS `' . $this->name . '`' );
            return;
        }
        $outs = '';

        if (array_key_exists('tables', $stage)) {
            $script->add('-- create table ' . $this->name . PHP_EOL);
            $outs = $this->makeCreate($stage);
            if (!empty($this->options)) {
                foreach ($this->options as $key => $value) {
                    if ($key === 'auto_increment') {
                        $allow = is_null($stage['auto_inc']) ? false : $stage['auto_inc'];
                        if (!$allow)
                            continue;
                    }
                    if ($key === 'comment') {
                        $value = '\'' . str_replace('\'', "''", $value) . '\'';
                    }
                    $outs .= ' ' . $key . '=' . $value;
                }
            }
            $outs .= ';' . PHP_EOL;
            $script->add($outs);
        }

        if (array_key_exists('alter', $stage)) {
            $auto_inc_col = $this->autoInc();
            if (array_key_exists('indexes', $stage) && !empty($this->indexes)) {
                $indexes = $this->indexes;
                ksort($indexes);
                $script->add('-- table ' . $this->name . ' indexes' . PHP_EOL);
                foreach ($indexes as $key => $ix) {
                    if ($ix->type === 'PRIMARY' && !empty($auto_inc_col)) {
                        continue;
                    }
                    $ix->toSql($script, $stage, $this); 
                }
            }
            if (array_key_exists('auto_inc', $stage)) {
                /*  if (!empty($auto_inc_col)) {
                   $outs = SchemaDef::alterTableSql($this->name);
                    $outs .= ' MODIFY ' . $auto_inc_col->toSql($stage);
                    $outs .= ';';
                    $script->add('-- table ' . $this->name . ' auto_increment column');
                    $script->add($outs);
                    
                } */
                if (isset($this->options['auto_increment'])) {
                    $value = $this->options['auto_increment'];
                    $outs = SchemaDef::alterTableSql($this->name);
                    $outs .= ' AUTO_INCREMENT = ' . $value . ';';
                    $script->add($outs);
                }
                /* else look for next value */
            }
        }
    }

    /**
     * Convert table data into CSV file
     * @param type $db
     * @param type $fileName
     * @return int
     */
    public function exportDataToCSV($db, $fileName) {

        $pdo = $db->pdo();
        $statement = $pdo->query('SELECT * from `' . $this->name . '`');

        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $result = 0;


        $fileHandler = fopen($fileName, 'w');
        $columns = $this->columns;

        while ($row = $statement->fetch()) {
            $data = [];
            $result += 1;
            foreach ($row as $key => $value) {
                $cdef = $columns[$key];
                if (ColumnDef::quotedType($cdef->type)) {
                    if ($value === '' || is_null($value)) {
                        $data[] = 'NULL';
                    } else {
                        $data[] = addslashes($value);
                    }
                } else {
                    $data[] = is_null($value) ? "NULL" : addslashes($value);
                }
            }
            fputcsv($fileHandler, $data);
        }
        fclose($fileHandler);
        return $result;
    }

    public function importDataFromCSV($db, string $fileName) {
        if (!file_exists($fileName)) {
            return; // nothing to do
        }
        $tableName = $this->name;
        $import = fopen($fileName, 'r');

        $batch = new BatchInsert();
        $batch->begin($db, $this);
        try {

            while (($line = fgetcsv($import)) !== false) {
                $values = array_map(
                        function ($value) {
                    return null === $value ? null : stripslashes($value);
                }, $line
                );
                $batch->insert($values);
                unset($line);
            }

            fclose($import);
            $batch->end();
        } catch (\Exception $e) {
            $batch->rollback();
            throw $e;
        }
    }

}
