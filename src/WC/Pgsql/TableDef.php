<?php

/**
 * @author Michael Rynn
 */

namespace WC\Pgsql;

use WC\NameDef;
use WC\DB\BatchInsert;

/**
 * Intermediate class for SQL table definition. 
 * Save and Load from TOML file format.
 * Generate more or less directly the 
 * table definitions used by Phalcon SQL database classes.
 */
class TableDef extends NameDef {

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
    /**
     * Get a column marked autoInc
     * For migration from MySQL
     */
    public function autoInc() {
        $cols =  $this->getColumnsByProperty('auto_inc',true);
        if (!empty($cols)) {
            return $cols[0];
        }
        return false;
    }
    // return a array of column names => seq name
    // In Postgresql which have a DEFAULT nextval(---)
    public function getSeqCols() {
        $result = [];
        foreach ($this->columns as $idx => $col) {
            $seqname = $col->getSeqName();
            if ($seqname) {
                $result[$idx] = $seqname;
            }
        }
        return $result;
    }

    /** 
     * Create nextvalue sequence for column
     * 
CREATE SEQUENCE foo_a_seq OWNED BY foo.a;
SELECT setval('foo_a_seq', coalesce(max(a), 0) + 1, false) FROM foo;
ALTER TABLE foo ALTER COLUMN a SET DEFAULT nextval('foo_a_seq'); 
     * @param type $colid
     */
    public function seqColSql($script, $colname) {
        $this->createSeqNextVal($script, $colname, $this->name . '_' . $colname . '_seq');
    }
    
    /**
     * 
     */
    public function createSeqNextVal($script, $colname, $seqname) {
        $tname = $this->name;
        $script->add("CREATE SEQUENCE $seqname OWNED BY $tname.$colname" . ';' );
        $script->add("SELECT setval('$seqname', coalesce(max($colname), 0)+1, false) FROM $tname" . ';' );
        $script->add("ALTER TABLE $tname ALTER COLUMN $colname SET DEFAULT nextval('$seqname')" . ';' );
    }
    /** 
     * Add primary key sql
     * Column name or array of column names.
     * @param type $cols
     */
    public function pkeySql($cols) {
        $tname = $this->name;
        $sql = "ALTER TABLE $tname ADD PRIMARY KEY (" ;
        if (is_array($cols)) {
            foreach($cols as $i => $name) {
                if ($i > 0) {
                    $sql .= ',';
                }
                $sql .= $name;
            }
            $sql .= ');' . PHP_EOL;
        }
        return $sql;
    }
    
    public function getKeyConstraints($db) {
        $sql = <<<EOS
SELECT pc.conname as name, 
  pg_catalog.pg_get_constraintdef(pc.oid, true) AS src,
  pc.contype
FROM pg_catalog.pg_constraint pc
WHERE
  pc.conrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname=:tname
  AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace
    WHERE nspname = :schema))
EOS;
   $rows = $db->exec($sql,[':tname' => $this->name, ':schema' => $this->schema ]);
   
        $this->constraints = $rows;
        foreach($rows as $ix => $cdef) {
            if ($cdef['contype'] === 'p') {
                $name = $cdef['name'];
                foreach ($this->indexes as $ixname => $idx) {
                     if ($ixname === $name) {
                         $idx->type = 'CONSTRAINT PRIMARY KEY';
                     }
                }
            }
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

    public function getForeignKeys($db) {
        $sql = <<<EOS
SELECT
    tc.table_schema, 
    tc.constraint_name, 
    tc.table_name, 
    kcu.column_name, 
    ccu.table_schema AS foreign_table_schema,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name 
FROM 
    information_schema.table_constraints AS tc 
    JOIN information_schema.key_column_usage AS kcu
      ON tc.constraint_name = kcu.constraint_name
      AND tc.table_schema = kcu.table_schema
    JOIN information_schema.constraint_column_usage AS ccu
      ON ccu.constraint_name = tc.constraint_name
      AND ccu.table_schema = tc.table_schema
WHERE tc.constraint_type = 'FOREIGN KEY'  AND tc.table_name= :tname
EOS;
        $rows = $db->exec($sql, [':tname' => $this->name]);
        $contraints = [];
        foreach($rows as $row) {
            $constraints[] = $row['constraint_name'];
        }
        return $constraints;
    }
    
    public function dropFkeyConstraints($db, $constraints) {
        foreach($constraints as $c) {
            $sql = 'ALTER TABLE ' . $this->name  . 
                    ' DROP CONSTRAINT ' . $c;
            
            $db->exec($sql);
        }
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
        $this->name = $rec['tablename'];
        $this->schema = $rec['schemaname'];


        //$src = $db->name() . '.' . $this->name;
$sql = <<<ESQL
  SELECT * FROM information_schema.columns 
  WHERE 
      columns.table_name = :tname
      and columns.table_schema = :schema
ESQL;
        $data = $db->exec($sql,[':tname' => $this->name, ':schema' => $this->schema ]);
        $this->columns = [];
        foreach ($data as $i => $row) {
            $cdef = new ColumnDef();
            $cdef->setSchema($row);
            $this->columns[$cdef->name] = $cdef;
        }
        
        
$sql = <<<ESQL
SELECT indexname, indexdef from pg_indexes
        WHERE tablename = :tname
ESQL;
        $data = $db->exec($sql,[':tname' => $this->name ]);
        $this->indexes = [];

        $keyname = '';
        $idef = null;
        foreach ($data as $i => $row) {
            $ixname = $row['indexname'];
            if ($ixname !== $keyname) {
                $idef = new IndexDef();
                $idef->fromDef($row['indexdef']);
                $this->indexes[$idef->name] = $idef;
                $keyname = $ixname;
            } else {
                $idef->columns[] = $row['Column_name'];
            }
        }
        $this->getKeyConstraints($db);
    }
    public function makeCreate($stage) {
        $outs = 'CREATE TABLE ' . $this->name . ' (' . PHP_EOL;
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
    public function toSql($script, $stage) {
        if (array_key_exists('drop-tables', $stage)) {
            $sql =  'DROP TABLE IF EXISTS "' . $this->name . '"';
            $script->add($sql);
            return;
        }

        if (array_key_exists('tables', $stage)) {
            $script->add('-- table ' . $this->name . ' create');
            $outs = $this->makeCreate($stage);
            
            if (!empty($this->options)) {
                /*
                foreach ($this->options as $key => $value) {
                    if ($key === 'auto_increment') {
                        $allow = $stage['auto_inc'] ?? false;
                        if (!$allow)
                            continue;
                    }
                    if ($key === 'comment') {
                        $value = '\'' . str_replace('\'', "''", $value) . '\'';
                    }
                    $outs .= ' ' . $key . '=' . $value;
                }
                 
                 */
            }
            
            $script->add($outs . ';' . PHP_EOL);
            return;
        }

        if (array_key_exists('alter', $stage)) {
            $outs = [];
            // check for primary key constraint
            $script->add('-- table ' . $this->name . ' indexes');
            
            if (isset($this->constraints)) {
                foreach ($this->constraints as $cix => $cdef) {
                    if ($cdef['contype'] === 'p') {
                        $s = 'ALTER TABLE ONLY ' . $this->name . ' ADD CONSTRAINT '
                             . $cdef['name'] . ' ' . $cdef['src'] . ';';
                        $script->add($s);        
                        break;
                    }
                }
            }

            if (array_key_exists('indexes', $stage) && !empty($this->indexes)) {
                $ct = 0;
                $indexes = $this->indexes;
                ksort($indexes);
                
                foreach ($indexes as $key => $ix) {
                     $ix->toSql($script, $stage, $this);
                }
            }
            if (array_key_exists('auto_inc', $stage)) {
                $seqcols = $this->getSeqCols();
                if (!empty($seqcols)) {
                    $script->add('--' . PHP_EOL . '-- table ' . $this->name . ' auto_inc');
                    foreach($seqcols as $colname => $seqname) {
                        $this->createSeqNextVal($script,$colname, $seqname);
                    }
                }
                else {
                    // migration from mysql?
                    $col = $this->autoInc();
                    if ($col) {
                        $this->seqColSql($script,$col->name);
                    }
                }
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
        $statement = $pdo->query('SELECT * from "' . $this->name . '"');

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
        $linect = 0;
        $tableName = $this->name;
        if ($tableName === 'image') {
            $debug = 1;
        }
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
                $linect++;
            }

            fclose($import);
            $batch->end();
        } catch (\Exception $e) {
           
            $batch->rollback();
            
            
            throw new \Exception($tableName . ' line ' . $linect . PHP_EOL . $e->getMessage());
        }
    }

}
