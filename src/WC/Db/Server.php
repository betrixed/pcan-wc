<?php

namespace WC\Db;

use WC\App;
use ActiveRecord\{Connection, ConnectionManager, Config, Singleton};

/**
 * @author Michael Rynn
 * Centralize and share database configuration(s) with
 * ActiveRecord setup.
 * Active record has ConnectionManager for named live connections,
 * Config for named connection strings.
 */
class Server 
{

    protected ?Config $con_data = null;
    protected ?ConnectionManager $con_live = null;
    
    const DB_connect = ['host', 'port', 'charset', 'dbname'];

    
    /**
     * Construct connection string and return db object
     */
    
    public function __construct(string $defaultName = "database", array $dbparams = [])
    {
        $this->dbparams = $dbparams;
        $cfg = Config::instance();
        $this->con_data = $cfg;
        foreach($dbparams as $name => $conn) {
            $cfg->add_connection($name, $conn);
        }
        $cfg->set_default_name($defaultName);
        $this->con_live = ConnectionManager::instance();
        // TODO: is deprecated!
        Connection::$datetime_format = 'Y-m-d H:i:s';
        // dont forget $cfg->set_model_directory($APP->ar_models);
    }
    
    function addConfig(string $name, array $params) {
        $this->con_data->add_connection($name, $params);
    }
    
    /**
     * return connection data as string or array
     */
    function getConfig(string $name)
    {
        return $this->con_data->get_connection($name);
    }

    /**
     * Create Database object from configuration name
     * @param type $name
     * @return type
     */
    function dbconfig(string $name): Connection
    {
        return  Connection::instance($name);
    }

    function setDefault(string $name)
    {
       $this->con_strings->set_default_connect($name);
    }

    /** return database by configuration name */
    function db($name = null): Connection
    {
        return $this->con_live->get_connection($name);
    }
}
