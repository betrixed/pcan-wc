<?php


namespace WC\Link;
use WC\Db\DbQuery;
/**
 * Description of UserRoles
 *
 * @author michael
 */
class UserRoles
{
    static function getRoleList($db, $userId) {
       $qry = new DbQuery($db);
       
       $sql = <<<EOD
SELECT  G.name from user_group G
join user_auth A on A.userid = $userId
and G.id = A.groupid and G.active = 1
EOD;
        $result = $qry->arrayColumn($sql, null);

        return $result;
    }
}
