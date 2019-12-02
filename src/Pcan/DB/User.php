<?php

namespace Pcan\DB;
use WC\DB\Server;
/**
 * Session, User authorization
 *
 * @author Michael Rynn
 */
class User extends \DB\SQL\Mapper {

    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'users', NULL, 1.0e8);  
    }
    static function groupList($id)
    {
        $db = Server::db();
$sql =<<<EOD
SELECT  G.name, G.id, G.active from user_group G
 join user_auth A on A.userid = :uid
 and G.id = A.groupid and G.active = 1
 order by name
EOD;
        $results = $db->exec( $sql, [':uid' => $id]);
        return $results;
    }
    
    static function otherGroups($id)
    {
        $db = Server::db();
$sql =<<<EOD
SELECT  G.name, G.id  from user_group G
 where G.name <> 'Guest' AND 
 G.name NOT IN (select U.name from 
     user_auth A join user_group U on U.id = A.groupid
     where A.userid = :uid) 
 order by name
EOD;
        $db = Server::db();
        $results = $db->exec( $sql, [':uid' => $id]);
        return $results;        
    }
    
    public function getGroups() {
        $db = $this->db;
        $sql = <<<EOD
select A.groupid, G.name, A.status,A.created_at, A.changed_at 
    from user_auth A join user_group G on G.id = A.groupid and G.active = 1
    where A.userid = ?;                
EOD;
        $id = $this['id'];
        $data = $db->exec($sql, [$id]);
        return $data;
    }
    public function getRoleList() {
       $db = Server::db();
       $id = $this->get('id');
       $sql = <<<EOD
SELECT  G.name from user_group G
join user_auth A on A.userid = :uid
and G.id = A.groupid and G.active = 1
EOD;
        $result = [];
        $rows = $db->exec(
                $sql, [':uid' => $id]
        );
        foreach ($rows as $row) {
            $result[] = $row['name'];
        }
        return $result;
    }
     static public function byId($id) {
         $result = new User();
         return $result->load(['id = ?', $id]);
    }   
    static public function byEmail($email) {
         $result = new User();
         return $result->load(['email = ?', $email]);
    }
}
    