<?php
namespace Pcan\DB;
use WC\DB\Server;
/**
 * @author michael
 */
class Series extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'series', NULL, 1.0e8); // 100 second
    }
    static  public function byId($id) {
        $result = new Series();
        if (is_numeric($id)) {
            return $result->load([ 'id = ?', $id ]);
        }
        else {
            return $result->load([ 'tinytag = ?', $id ]);
        }
    }
    
    static public function orderDate($id) {
        $db = Server::db();
        return $db->exec('select g.* from gallery g where g.seriesid = ? order by g.last_upload desc',[$id]);
    }

}

