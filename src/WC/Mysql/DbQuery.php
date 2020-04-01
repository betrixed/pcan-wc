<?php

namespace WC\Mysql;
use \WC\Db\IQuery;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db;


class DbQuery implements  IQuery {
    public SchemaDef $schemaDef;
    public Mysql $db;
    
    public function __construct(SchemaDef $def, Mysql $db) {
        $this->schemaDef = $def;
        $this->db = $db;
    }
    
    public function getSchemaName() : string {
        return $this->schemaDef->getName();
    }
    
    public function cursor(string $sql, array $params = null)
    {
        return $this->db->query($sql, $params);
    }
    
    public function queryAll(string $sql, array $params = null) : array
    {
        return $this->db->fetchAll($sql, Db\Enum::FETCH_ASSOC, $params);
    }
    
    
}
