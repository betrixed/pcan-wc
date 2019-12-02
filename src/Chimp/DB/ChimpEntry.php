<?php
namespace Chimp\DB;

use WC\DB\Server;
use Chimp\DB\ChimpLists;
use Pcan\DB\MemberEmail;

/**
 * Description of chimpentry
 *
 * @author michael
 */
class ChimpEntry extends \DB\SQL\Mapper {
     public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'chimp_entry', NULL, 1.0e8); // 100 second
    }
    
    static  public function byUniqueId($id, $uid) {
        $result = new ChimpEntry();
        return $result->load([ 'listid = ? and uniqueid = ?', $id, $uid ]);
    }
    
    static public function byMemberEmailId($eid) {
        $result = new ChimpEntry();
        return $result->load([ 'emailid = ?', $eid ]);
    }
    
    
    static public function addMemberEmail($eid) {
        // ensure that record doesn't exist already
        $entry = static::byMemberEmailId($eid);
        if ($entry === false) {
            $list = ChimpLists::defaultList();
            $entry = $list->addMemberEmail($eid);
        }
        return $entry;
    }
}
