<?php

namespace WC\Db;
Use Phalcon\Db;
use Phalcon\Db\Adapter\PdoFactory;
use WC\App;

/**
 * @author Michael Rynn
 */
class Server {

    static public $srv;
    static public $defaultName = "database";

    const DB_connect = ['host', 'port', 'charset', 'dbname'];

    /**
     * Construct connection string and return db object
     */
    static function connection($cfg) {
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
    static function dbconfig($name) {
        $cfg = App::instance()->get_secrets();
        return static::connection($cfg[$name]);
    }

    static function setDefault(SQL $db) {
        static::$srv[static::$defaultName] = $db;
    }
    
    /** return database by name */
    static function db($name = null) {
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
