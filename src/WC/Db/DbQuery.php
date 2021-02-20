<?php

namespace WC\Db;

use ActiveRecord\Connection;

class DbQuery {

    public Connection $db;
    private $order;
    private $limit;
    private $where;
    private $params;
    private $binds;

    public function __construct(Connection $db) {
        $this->db = $db;
    }

    public static function getBindType($value) : int {
        switch(gettype($value)) {
            case 'integer' : 
                return \PDO::PARAM_INT;
            case 'string' : 
            default :
                return \PDO::PARAM_STR;
        }
    }
    /**
         * 
         * @param string $sql
         * @param int $fetch_style PDO::FETCH_?  ASSOC | COLUMN | OBJ
         * @param array $params
         * @param array $bindtypes
         * @return array
         */
    public function fetchAll(string $sql, int $fetch_style, array $params=null, array $bindtypes = null) : array 
    {
        $db = $this->db->connection;
        $sth = $db->prepare($sql);
        if (is_array($params)) {
            foreach($params as $key => $value) {
                $btype = $bindtypes[$key] ?? self::getBindType($value);
                $sth->bindValue($key, $value, $btype);
            }
        }
        if ($sth->execute()) {
            return $sth->fetchAll($fetch_style); // 
        }
        else  {
            return []; // todo: throw something
        }
    }
    public function getSchemaName(): string {
        return $this->db->getSchema();
    }

    private function reset() {
        $this->order = null;
        $this->limit = null;
        $this->where = null;
        $this->params = null;
        $this->binds = null;
    }

    public function cursor(string $sql, array $params = null) {
        return $this->db->query($sql, $params);
    }

    public function arrayColumn(string $sql, array $params = null, array $bindtypes = null): array {
        $result = $this->fetchAll($sql, \PDO::FETCH_COLUMN, $params, $bindtypes);
        $this->reset();
        return $result;
    }

    public function arraySet(string $sql, array $params = null, array $bindtypes = null): array {
        
        $result = $this->fetchAll($sql, \PDO::FETCH_ASSOC, $params, $bindtypes);
        $this->reset();
        return $result;
    }

    // return result as array of firstcolumn value -> second column value 
    
    public function simpleMap(string $keycol, string $valcol, string $table) {
        $sql = "select $keycol, $valcol from $table";
        $rows = $this->arraySet($sql);
        $result = [];
        foreach($rows as $row) {
            $result[$row[$keycol]] = $row[$valcol];
        }
        return $result;
    }
    public function objectSet(string $sql, array $params = null, $bindtypes = null): array {
        $result = $this->fetchAll($sql, \PDO::FETCH_OBJ, $params, $bindtypes);
        $this->reset();
        return $result;
    }

    public function order(string $order) {
        $this->order = $order;
    }

    //**return as improper SQL - no quotes for values */
    public function getCriteria(): string {
        if (empty($this->params)) {
            return "All";
        }
        $result = $this->where;
        foreach ($this->params as $key => $value) {
            $result = str_replace(':' . $key, $value, $result);
        }
        return $result;
    }

    public function getCondition(): string {
        return $this->where;
    }

    public function getParams(): ?array {
        return $this->params;
    }

    public function getBinds(): ?array {
        return $this->binds;
    }

    public function queryAA(string $sql): array {
        return $this->arraySet($this->buildSql($sql), $this->params, $this->binds);
    }

    public function queryOA(string $sql): array {
        return $this->objectSet($this->buildSql($sql), $this->params, $this->binds);
    }

    public function buildSql(string $sql): string {
        return $sql . $this->paramSQL();
    }

    public function paramSQL(): string {
        $sql = "";
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->where;
        }
        if (!empty($this->order)) {
            $sql .= ' ORDER BY ' . $this->order;
        }
        if (!empty($this->limit)) {
            $sql .= $this->limit;
        }
        return $sql;
    }

    private function newParamName(): string {
        if (empty($this->params)) {
            $pname = "p1";
        } else {
            $pname = 'p' . (count($this->params) + 1);
        }
        return $pname;
    }

    /** Must not already have a where clause in sql.
     * Add a simple where condition, must have a '?'  for replacement of value, eg "size = ?"  
     */
    public function whereCondition(string $condition, $value = null) {

        if (!empty($this->where)) {
            $this->where .= ' and ';
        }
        $pname = $this->newParamName();
        
        if ($value !== null) {
            $this->where .= str_replace('?', ':' . $pname, $condition);
            $this->params[$pname] = $value;
            $bind_type = is_integer($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $this->binds[$pname] = $bind_type;
        }
        else {
            $this->where .= $condition;
        }
    }

    /** Value BIND_PARAM_XX deduced from PHP type, so may need cast like (int) */
    public function bindParam(string $pname, $value) {
        $this->params[$pname] = $value;
        $bind_type = is_integer($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
        $this->binds[$pname] = $bind_type;
    }

    public function bindLimit(int $rows, int $offset) {
        if ($rows > 0) {
            $pname = $this->newParamName();
            $this->limit = ' LIMIT :' . $pname;
            $this->params[$pname] = $rows;
            $this->binds[$pname] = \PDO::PARAM_INT;
            if ($offset > 0) {
                $p2 = $this->newParamName();
                $this->limit .= ' OFFSET :' . $pname;
                $this->params[$p2] = $offset;
                $this->binds[$p2] = \PDO::PARAM_INT;
            }
        }
    }

}
