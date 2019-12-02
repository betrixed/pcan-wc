<?php

namespace WC\DB;

class BatchInsert {

    public $pdo;
    public $fields;
    public $stmt;
    public $cdefs;
    public $nulls;
    public $cols;
    
    public function begin($db, $tdef) {
        $this->pdo = $db->pdo();
        $this->cdefs = $tdef->columns;
        $this->fields = array_keys($this->cdefs);
        $sql = 'INSERT INTO "' . $tdef->name . '" (';
        $vsql = ') VALUES (';
        $i = 0;
        $this->nulls = [];
        $this->cols = [];
        foreach ($this->fields as $i => $col) {
            if ($i > 0) {
                $sql .= ', ';
                $vsql .= ',';
            }
            $sql .= strtolower($col);
            $vsql .= '?';
            $cdef = $this->cdefs[$col];
            $this->nulls[] = $cdef['null'] ?? false;
            $this->cols[] = $cdef;
        }
        $sql .= $vsql . ')';
        
        $this->stmt = $this->pdo->prepare($sql);
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

