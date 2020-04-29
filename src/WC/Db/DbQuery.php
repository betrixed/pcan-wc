<?php

namespace WC\Db;
use \WC\Db\IQuery;

use WC\Db\Server;
use Phalcon\Db;
use Phalcon\Db\Adapter\AdapterInterface;

class DbQuery  {
    public AdapterInterface $db;
    
    public function __construct() {
        $this->db = Server::db();
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
    
    public function arrayColumn(string $sql, array $params = null) : array
    {
        return $this->db->fetchAll($sql, Db\Enum::FETCH_COLUMN, $params);
    }
    public function arraySet(string $sql, array $params = null, array $bindtypes = null) : array
    {
        return $this->db->fetchAll($sql, Db\Enum::FETCH_ASSOC, $params, $bindtypes);
    }
    public function objectSet(string $sql, array $params = null) : array
    {
        return $this->db->fetchAll($sql, Db\Enum::FETCH_OBJ, $params);
    }
    
}
