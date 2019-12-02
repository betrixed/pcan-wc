<?php

namespace WC\DB;

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
        return new \DB\SQL(
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
    static function secrets($name) {
        $f3 = \Base::instance();
        $cfg = &$f3->ref('secrets.' . $name);
        return static::connection($cfg);
    }

    static function db($name = null) {
        if (empty($name)) {
            $name = static::$defaultName;
        }
        if (empty(static::$srv)) {
            $db = static::secrets($name);
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
