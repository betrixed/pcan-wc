<?php
namespace WC\Link;

use WC\Db\Server;
/**
 * Description of member
 *
 * @author michael
 */
class MemberEmail  {

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

