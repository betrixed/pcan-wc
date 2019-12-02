<?php
namespace Pcan\DB;
use WC\DB\Server;

/**
 * Session, User authorization
 *
 * @author Michael Rynn
 */
class UserAuth extends \DB\SQL\Mapper {
    
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'user_auth', NULL, 1.0e8);  
    }
    
    static public function delGroup($userid, $groupid) {
         $db = Server::db();
$sql =<<<EOD
 delete from user_auth where userid = :uid and groupid = :gid
EOD;
        return $db->exec($sql,[':uid' => $userid, ':gid' => $groupid]);    
    }
}
