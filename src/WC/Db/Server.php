<?php

namespace WC\Db;

use WC\App;
Use Phalcon\Db;
use Phalcon\Db\Adapter\PdoFactory;
use Phalcon\Db\Adapter\AdapterInterface;

/**
 * @author Michael Rynn
 */
class Server
{

    protected $dbparams = [];
    protected $srv = [];
    protected $defaultName = "database";

    const DB_connect = ['host', 'port', 'charset', 'dbname'];

    
    /**
     * Construct connection string and return db object
     */
    
    public function __construct(string $defaultName = "database", array $dbparams = [])
    {
        $this->defaultName = $defaultName;
        $this->dbparams = $dbparams;
    }
    
    function addConfig(string $name, array $params) {
        $this->dbparams[$name] = $params;
    }
    function connection(array $cfg): AdapterInterface
    {
        $factory = new PdoFactory();
        $adapter = $cfg['adapter'];
        unset($cfg['adapter']);
        return $factory->newInstance($adapter, $cfg);
    }

    /**
     * Create Database object from configuration name
     * @param type $name
     * @return type
     */
    function dbconfig(array $cfg): AdapterInterface
    {
        return  $this->connection($cfg);
    }

    function setDefault(SQL $db)
    {
       $this->srv[$this->defaultName] = $db;
    }

    /** return database by configuration name */
    function db($name = null): AdapterInterface
    {
        if (empty($name)) {
            $name = $this->defaultName;
        }
        $config = $this->dbparams[$name] ?? null;
        if (is_null($config)) {
            throw new \Exception('Database configuration "' . $name . '" not found');
        }
        if (empty(static::$srv) || !isset($this->srv[$name])) {
            $db = $this->dbconfig($config);
            $this->srv[$name] = $db;
            return $db;
        } else {
            return  $this->srv[$name];
        }
    }
}
