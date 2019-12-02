<?php

namespace WC\DB;
use DB\SQL;
use WC\App;
/**
 * @author Michael Rynn
 */
class Server {

    static public $srv;
    static public $defaultName = "database";

    const DB_connect = ['host', 'port', 'charset', 'dbname'];

    
    static function add_params($param) {
        $s = $param['adapter'] . ':';
        foreach (Server::DB_connect as $p) {
            if (isset($param[$p])) {
                $s .= $p . '=' . $param[$p] . ';';
            }
        }
        return $s;
    }

    /**
     * Construct connection string and return db object
     */
    static function connection($cfg) {
        $str = static::add_params($cfg);
        return new SQL(
                $str,
                $cfg['username'],
                $cfg['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Database object from configuration name
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
    
    static function db($name = null) {
        if (empty($name)) {
            $name = static::$defaultName;
        }
        if (empty(static::$srv)) {
            $db = static::dbconfig($name);
            static::$srv[$name] = $db;
            \Base::instance()->set('DB', $db);
            return $db;
        } else {
            if (isset(static::$srv[$name])) {
                return static::$srv[$name];
            }
            $db = static::secrets($name);
            static::$srv[$name] = $db;
            return $db;
        }
    }

}
