<?php

namespace Pcan\DB;
use WC\DB\Server;

/**
 *
 * @author Michael Rynn
 */
class ResetCode extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'reset_code', NULL, 1.0e8); // 100 second
    }
    
        /** delete all reset codes for all users older than 1 day
     * 
     * @return type exec result
     */
    static public function deleteOldCodes()
    {
        $db = Server::db();
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval("P2D"));
        $ystr = $yesterday->format(DATETIME_FORMAT);
        $result = $db->exec("delete from reset_code where created_at < ?", $ystr);
        return $result;
    }
    
    static  public function byCode($id) {
        $result = new ResetCode();
        return $result->load([ 'code = ?', $id ]);
    }  
    
}