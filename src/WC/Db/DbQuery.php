<?php

namespace WC\Db;
use \WC\Db\IQuery;

use WC\Db\Server;
use Phalcon\Db;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Db\Column;

class DbQuery  {
    public AdapterInterface $db;
    
    private $order;
    private $where;
    private $params;
    private $binds;
    
    public function __construct(AdapterInterface $db = null) {
        if (empty($db)) {
            $db =  Server::db();
        }
        $this->db =$db;
    }
    
    public function getSchemaName() : string {
        $data = $this->db->getDescriptor();
        if (isset($data['dbname']))
        {
            return $data['dbname'];
        }
    }
    
    public function cursor(string $sql, array $params = null)
    {
        return $this->db->query($sql, $params);
    }
    
    public function arrayColumn(string $sql, array $params = null,array $bindtypes = null) : array
    {
        return $this->db->fetchAll($sql, Db\Enum::FETCH_COLUMN, $params,$bindtypes);
    }
    public function arraySet(string $sql, array $params = null, array $bindtypes = null) : array
    {
        return $this->db->fetchAll($sql, Db\Enum::FETCH_ASSOC, $params, $bindtypes);
    }
    public function objectSet(string $sql, array $params = null, $bindtypes = null) : array
    {
        return $this->db->fetchAll($sql, Db\Enum::FETCH_OBJ, $params, $bindtypes);
    }
    
    public function order(string $order) {
        $this->order = $order;
    }
    
    //**return as improper SQL - no quotes for values */
    public function getCriteria() : string
    {
        if (empty($this->params)) {
            return "All";
        }
        $result = $this->where;
        foreach( $this->params as $key => $value) {
            $result = str_replace(':' . $key, $value, $result);
        }
        return $result;
    }
    
    public function getCondition() : string 
    {
        return $this->where;
    }
    
    public function queryAA(string $sql) : array {
        if (!empty($this->params)) {
            $params = $this->params;
            $binds = $this->binds;
        }
        else {
            $params = [];
            $binds = [];
        }
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->where;
        }
        if (!empty($this->order)) {
            $sql .= ' ORDER BY ' . $this->order;
        }
        return $this->arraySet($sql, $params, $binds);
    }
    /** Add a simple condition, must have a '?'  for replacement  */
    public function bindCondition(string $condition,  $value) {
        if (empty($this->params)) {
            $pname = "p1";
        }
        else {
            $pname = 'p' . (count($this->params) + 1);
        }
        if (!empty($this->where)) {
            $this->where .= ' and ';
        }
        $this->where .= str_replace('?', ':' . $pname, $condition);
        $this->params[$pname] = $value;
        $bind_type = is_integer($value) ? Column::BIND_PARAM_INT : Column::BIND_PARAM_STR;
        $this->binds[$pname] = $bind_type;
    }
}
