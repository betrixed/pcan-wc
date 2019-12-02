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
        $sql = <<<EOD
 delete from user_auth where userid = :uid and groupid = :gid
EOD;
        return $db->exec($sql, [':uid' => $userid, ':gid' => $groupid]);
    }

    static function makeNewUser($user_name, $user_email, $user_pwd, $groups) {
        $user = new User();
        $user['name'] = $user_name;
        $user['email'] = $user_email;

        $crypt = \Bcrypt::instance();
        $user['password'] = $crypt->hash($user_pwd);

        $user['status'] = 'C';

        $user->save();
        $db = Server::db();
        $pdo = $db->pdo();

        $grouplist = '(';

        foreach ($groups as $ix => $g) {
            if ($ix > 0) {
                $grouplist .= ',';
            }
            $grouplist .= $pdo->quote($g);
        }
        $grouplist .= ')';
        $sql = <<<EOS
insert into user_auth (userid, groupid) select :uid, ug.id from user_group ug
   where ug.name in $grouplist
EOS;
        $db->exec($sql, [':uid' => $user['id']]);
    }

}
