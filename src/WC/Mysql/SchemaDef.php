<?php

namespace WC\Mysql;

use WC\{NameDef, XmlPhp};
use WC\Db\{DbQuery, Script};
use Phalcon\Db;
use Phalcon\Db\Adapter\Pdo\Mysql;



class SchemaDef extends \WC\Db\AbstractDef {

    const SORT_FN = ['WC\NameDef', 'name_cmp'];

    public function toFile($path) {
        $text = XmlPhp::toXmlDoc($this);
        file_put_contents($path, $text);
    }

    static public function alterTableSql($name) {
        return 'ALTER TABLE `' . $name . '`';
    }

    public function execRelations($db) {
        if (!empty($this->relations)) {
            $rtables = [];
            $alter = ['alter' => true];
            foreach ($this->relations as $key => $rdef) {
                $rtables[$rdef->table][] = $rdef->name;
            }
            uksort($rtables, self::SORT_FN);
            foreach ($rtables as $name => $rels) {
                $outs = self::alterTableSql($name);
                if (count($rels) > 1) {
                    usort($rels, self::SORT_FN);
                }
                $i = 0;
                foreach ($rels as $rname) {
                    if ($i > 0) {
                        $outs .= ",";
                    }
                    $rdef = $this->relations[$rname];
                    $outs .= $rdef->toSql($alter);
                    $i++;
                }
                $db->exec($outs);
            }
        }
    }

    public function generate( $script,  $stage) {
        $tables = $this->tables;

        uksort($tables, self::SORT_FN);
        foreach ($tables as $seq => $tdef) {
             $tdef->generate($script, $stage);
        }
        if (array_key_exists('references', $stage) && !empty($this->relations)) {
            $rtables = [];

            foreach ($this->relations as $key => $rdef) {
                $rtables[$rdef->table][] = $rdef->name;
            }
            uksort($rtables, self::SORT_FN);
            foreach ($rtables as $name => $rels) {
                $script->add('-- relation ' . $name . ' references ');
                $outs = self::alterTableSql($name);
                if (count($rels) > 1) {
                    usort($rels, self::SORT_FN);
                }
                foreach ($rels as $rname) {
                    $rdef = $this->relations[$rname];
                    $outs .= $rdef->toSql($stage);
                }
                $script->add($outs);
            }
        }
    }

    public function getTable($name) {
        return $this->tables[$name];
    }

    private function execAllTables($db, $stage) {
        foreach ($this->tables as $seq => $tdef) {
            $script = new Script();
            $tdef->generate($script, $stage);
            if ($script->hasData()) {
                $db->exec($script);
            }
        }
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
        foreach ($this->tables as $seq => $tdef) {
            $this->dropTableRelations($db, $tdef->name);
        }
    }

    public function dropTableRelations($db, $tname) {
        $tdef = $this->getTable($tname);
        if (!empty($tdef) && !empty($tdef->foreignkeys)) {
            foreach ($tdef->foreignkeys as $rname) {
                $rdef = $this->relations[$rname];
                if (!empty($rdef)) {
                    $table = $this->tables[$rdef->table];
                    if (is_null($table)) {
                        continue;
                    }
                    if ($table->exists($db)) {
                        $sql = self::alterTableSql($table->name);
                        $sql .= PHP_EOL . $rdef->dropSql();
                        $db->exec($sql);
                    }
                }
            }
        }
    }
    public function newQuery($db) : DbQuery
    {
        return new DbQuery($db);
    }
    /**
     * Name needs to be set before calling.
     * @param type $db
     */
    public function readSchema($db) {
        $qry = $this->newQuery($db);
        
        //$dbname = $db->getSchemaName();
        $tsql = <<<ESQL
  SELECT table_name, table_type, engine, auto_increment, 
      table_collation, table_comment    
   FROM information_schema.tables 
       WHERE table_schema = :dbname
ESQL;
        $table_names = $qry->arraySet($tsql, [':dbname' => $qry->getSchemaName()]);
        
        foreach ( $table_names as $rec) {

            $tdef = new TableDef();

            $tdef->readSchema($qry, $rec);

            $tdefs[$tdef->name] = $tdef;
        }
        $this->adapter = 'Mysql';
        $this->tables = $tdefs;
        $this->database = $qry->getSchemaName();
        $this->date = new \DateTime();
        
        $this->readRelations($qry);
    }

    public function readRelations(DbQuery $qry) {
        $rsql = <<<ESQL
select 
       col.table_schema as 'schema',
       col.table_name as 'table',
       col.ordinal_position as col_id,
       substr(col.column_name,1) as column_name,
       case when kcu.referenced_table_schema is null
            then null
            else '>-' end as rel,
       kcu.referenced_table_schema as p_schema,
       kcu.referenced_table_name as p_table,
       kcu.referenced_column_name as p_column_name,
       kcu.constraint_name as fk_constraint_name,
       rc.update_rule, rc.delete_rule
from information_schema.columns col
join information_schema.tables tab
     on col.table_schema = tab.table_schema
     and col.table_name = tab.table_name
left join information_schema.key_column_usage kcu
     on col.table_schema = kcu.table_schema
     and col.table_name = kcu.table_name
     and col.column_name = kcu.column_name
     and kcu.referenced_table_schema is not null
left join information_schema.referential_constraints rc
       on rc.constraint_schema = kcu.constraint_schema
       and rc.constraint_name = kcu.constraint_name

where col.table_schema not in('information_schema','sys',
                              'mysql', 'performance_schema')
      and tab.table_type = 'BASE TABLE'
      and kcu.constraint_schema = :dbname
order by col.table_schema,
         col.table_name,
         col.ordinal_position
ESQL;
        $schemaName = $qry->getSchemaName();
        $data = $qry->arraySet($rsql, ['dbname' => $schemaName]);
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
