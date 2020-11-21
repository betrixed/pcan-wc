<?php

namespace WC\Db;

class BatchInsert {

    public $pdo;
    public $fields;
    public $stmt;
    public $cdefs;
    public $nulls;
    public $cols;
    public $driver;
    
    public function begin($db, $tdef) {
        $this->pdo = $db->getInternalHandler ();
        $this->driver = $db->getType();
        $this->cdefs = $tdef->columns;
        $this->fields = array_keys($this->cdefs);
        if ($this->driver === 'mysql') {
            $nqt = '`';
        }
        else {
            $nqt = '';
        }
        $sql = 'INSERT INTO ' . $nqt . $tdef->name . $nqt . ' (';
        $vsql = ') VALUES (';
        $i = 0;
        $this->nulls = [];
        $this->cols = [];
        foreach ($this->fields as $i => $col) {
            if ($i > 0) {
                $sql .= ', ';
                $vsql .= ',';
            }
            $sql .= $nqt . strtolower($col) . $nqt;
            $vsql .= '?';
            $cdef = $this->cdefs[$col];
            $this->nulls[] = $cdef['null'] ?? false;
            $this->cols[] = $cdef;
        }
        $sql .= $vsql . ')';
        
        $this->stmt = $this->pdo->prepare($sql);
        if (empty($this->stmt)) {
            throw new \Exception('Prepare failed for batch: ' . $sql);
        }
        $this->pdo->beginTransaction();
    }

    public function rollback() {
        $this->pdo->rollback();
    }

    public function insert($row) {
        $vals = $row;

        foreach ($vals as $i => $data) {
            if ($this->nulls[$i] && strtolower($data) === 'null') {
                $row[$i] = null;
            }
            $cdef = $this->cols[$i];
            if ($cdef->type === 'timestamp' && $row[$i] === 
                    '0000-00-00 00:00:00') {
                $row[$i] = date(\Valid::DATE_TIME_FMT, 0);
            }
        }
        $this->stmt->execute($row);
    }

    public function end() {
        $this->pdo->commit();
    }

}

