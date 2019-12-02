<?php
namespace Pcan\DB;

use WC\DB\Server;
/**
 * Description of member
 *
 * @author michael
 */
class MemberEmail extends \DB\SQL\Mapper {
     public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'member_email', NULL, 1.0e8); // 100 second
    }
    static  public function byEmail($email) {
        $result = new MemberEmail();
        return $result->load([ 'email_address = ?', $email ]);
    }
    static  public function byId($id) {
        $result = new MemberEmail();
        return $result->load([ 'id = ?', $id ]);
    }
    static public function deleteId($id) {
         $db = Server::db();
         $db->exec('delete from member_email where id = ?', $id);
    }
}

