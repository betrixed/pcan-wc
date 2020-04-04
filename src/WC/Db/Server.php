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

    static public $srv;
    static public $defaultName = "database";

    const DB_connect = ['host', 'port', 'charset', 'dbname'];

    /**
     * Construct connection string and return db object
     */
    static function connection($cfg): AdapterInterface
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
    static function dbconfig($name): AdapterInterface
    {
        $cfg = App::instance()->get_secrets();
        return static::connection($cfg[$name]);
    }

    static function setDefault(SQL $db)
    {
        static::$srv[static::$defaultName] = $db;
    }

    /** return database by name */
    static function db($name = null): AdapterInterface
    {
        if (empty($name)) {
            $name = static::$defaultName;
        }
        if (empty(static::$srv) || !isset(static::$srv[$name])) {
            $db = static::dbconfig($name);
            static::$srv[$name] = $db;
            return $db;
        } else {
            return static::$srv[$name];
        }
    }

}
