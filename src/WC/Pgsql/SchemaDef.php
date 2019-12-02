<?php

namespace WC\Pgsql;

use WC\NameDef;
use WC\XmlPhp;
use WC\DB\Script;
use WC\DB\AbstractDef;
/**
 * Array of TableDef and various properties
 * 
 */
class SchemaDef extends  AbstractDef 
{

    const SORT_FN = ['WC\NameDef', 'name_cmp'];

    public function toFile($path) {
        $text = XmlPhp::toXmlDoc($this);
        file_put_contents($path, $text);
    }

    static public function alterTableSql($name) {
        return 'ALTER TABLE ' . $name . ' ';
    }

    public function execRelations($db) {
        if (!empty($this->relations)) {
            $script = new Script();
            $rtables = [];
            $alter = ['alter' => true];
            foreach ($this->relations as $key => $rdef) {
                $rtables[$rdef->table][] = $rdef->name;
            }
            uksort($rtables, self::SORT_FN);
            foreach ($rtables as $name => $rels) {
               
                if (count($rels) > 1) {
                    usort($rels, self::SORT_FN);
                }
                $i = 0;
                foreach ($rels as $rname) {
                    $rdef = $this->relations[$rname];
                    $rdef->toSql($script, $alter);
                    $i++;
                }
            }
            $script->run($db);
        }
    }

    public function getForeignKeys($db) {
        $sql = <<<EOS
SELECT
    tc.table_schema, 
    tc.constraint_name, 
    tc.table_name, 
    tc.constraint_type,
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
    WHERE tc.constraint_type = 'FOREIGN KEY'
EOS;
        $rows = $db->exec($sql);
        $contraints = [];
        foreach($rows as $row) {
            $constraints[$row['constraint_name']] = $row['table_name'];
        }
        return $constraints;
    }
    
    public function dropFkeyConstraints($db, $constraints) {
        foreach($constraints as $c => $t) {
            $sql = 'ALTER TABLE ' . $t  . 
                    ' DROP CONSTRAINT ' . $c;
            
            $db->exec($sql);
        }
    }
    public function generate($script, $stage) {
        $tables = $this->tables;
        
        uksort($tables, self::SORT_FN);
        foreach ($tables as $seq => $tdef) {
            
            $tdef->toSql($script, $stage);
            
        }
        if (array_key_exists('references', $stage)) {
            
            // Primary keys apply first
            
            // Foreign keys across tables
            
            $script->add('--' . PHP_EOL . '-- Foreign Keys ');
            foreach($tables as $tname => $tdef) {
                if (isset($tdef->constraints)) {
                    foreach ($tdef->constraints as $cname => $cdef) {
                        if ($cdef['contype'] === 'f') {
                            $s = 'ALTER TABLE ONLY ' . $tname . ' ADD CONSTRAINT '
                                 . $cdef['name'] . ' ' . $cdef['src'] . ';';
                            $script->add($s);        
                            break;
                        }
                    }
                }
            }            
        }
    }

    public function getTable($name) {
        return $this->tables[$name];
    }

    private function execAllTables($db, $stage) {
        $script = new Script();
        foreach ($this->tables as $seq => $tdef) {            
            $tdef->toSql($script,$stage); 
        }
        $script->run($db);
    }

    public function loadData($db, $datadir) {
        foreach ($this->tables as $seq => $tdef) {
            $path = $datadir . '/' . $tdef->name . '.csv';
            $tdef->importDataFromCSV($db, $path);
        }
    }

    public function execStages($db, $stage) {
        if (array_key_exists('drop-fkeys', $stage)) {
            $this->dropRelations($db);
        }
        if (array_key_exists('drop-tables', $stage)) {
            $this->execAllTables($db, ['drop-tables' => true]);
        }
        if (array_key_exists('tables', $stage)) {
            $this->execAllTables($db, ['tables' => true]);
        }
        if (array_key_exists('load-data', $stage)) {
            $this->loadData($db, $stage['load-data']);
        }
        if (array_key_exists('alter', $stage)) {
            if (array_key_exists('indexes', $stage)) {
                $this->execAllTables($db, ['alter' => true, 'indexes' => true]);
            }
            if (array_key_exists('auto_inc', $stage)) {
                $this->execAllTables($db, ['alter' => true, 'auto_inc' => true]);
            }
        }
        if (array_key_exists('add-fkeys', $stage)) {
            $this->execRelations($db);
        }
    }

    public function dropRelations($db) {
        /*
        foreach ($this->tables as $seq => $tdef) {
            $this->dropTableRelations($db, $tdef->name);
        }
        */
        $constraints = $this->getForeignKeys($db);
        if (!empty($constraints)) {
            $this->dropFkeyConstraints($db, $constraints);
        }
    }

    public function dropTableRelations($db, $tname) {
        $tdef = $this->getTable($tname);
        if (!empty($tdef) && !empty($tdef->foreignkeys)) {
            foreach ($tdef->foreignkeys as $rname) {
                $rdef = $this->relations[$rname];
                if (!empty($rdef)) {
                    $table = $this->tables[$rdef->table];
                    if ($table->exists($db)) {
                        $sql = self::alterTableSql($table->name);
                        $sql .= PHP_EOL . $rdef->dropSql();
                        $db->exec($sql);
                    }
                }
            }
        }
    }

    public function readSchema($db) {
        $dbname = $db->name();
        $tsql = <<<ESQL
select schemaname, tablename 
  from pg_tables 
  where schemaname not in ('information_schema','pg_catalog') 
  order by tablename
ESQL;
        $table_names = $db->exec($tsql);
        $tdefs = [];
        foreach ($table_names as $rec) {

            $tdef = new TableDef();

            $tdef->readSchema($db, $rec);

            $tdefs[$tdef->name] = $tdef;
        }
        $this->adapter = 'Pgsql';
        $this->tables = $tdefs;
        $this->database = $dbname;
        $this->date = new \DateTime();
        
        $this->readRelations($db);
    }

    public
            function readRelations($db) {
        $rsql = <<<ESQL
 SELECT conname AS constraint_name, conrelid::regclass AS table_name, ta.attname AS column_name,
       confrelid::regclass AS foreign_table_name, fa.attname AS foreign_column_name
  FROM (
   SELECT conname, conrelid, confrelid,
          unnest(conkey) AS conkey, unnest(confkey) AS confkey
     FROM pg_constraint
    WHERE conname = 'comment_name_fkey'
      --and contype = 'f'
  ) sub
  JOIN pg_attribute AS ta ON ta.attrelid = conrelid AND ta.attnum = conkey
  JOIN pg_attribute AS fa ON fa.attrelid = confrelid AND fa.attnum = confkey
ESQL;
        $data = $db->exec($rsql);
        $relations = [];
        $constraint = '';
        $rdef = null;
        foreach ($data as $r => $row) {
            if ($constraint !== $row['fk_constraint_name']) {
                $constraint = $row['fk_constraint_name'];
                $rdef = new ReferenceDef();
                $rdef->setSchema($row);
                $relations[$rdef->name] = $rdef;
            } else {
                $rdef->columns[] = $row['column_name'];
                $rdef->p_columns[] = $row['p_column_name'];
            }
        }
        $this->relations = $relations;

        foreach ($relations as $rdef) {
            $tdef = $this->getTable($rdef->table);
            if (!empty($tdef)) {
                $tdef->references[] = $rdef->name;
            }
            $tdef = $this->getTable($rdef->p_table);
            if (!empty($tdef)) {
                $tdef->foreignkeys[] = $rdef->name;
            }
        }
    }

}
