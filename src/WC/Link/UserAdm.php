<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Link;

/**
 * Description of UserAdm
 *
 * @author michael
 */
trait UserAdm
{
    
    public function otherGroups($id)
    {
        $qry = $this->dbq;
$sql =<<<EOD
SELECT  G.name, G.id  from user_group G
 where G.name <> 'Guest' AND 
 G.name NOT IN (select U.name from 
     user_auth A join user_group U on U.id = A.groupid
     where A.userid = :uid) 
 order by name
EOD;
        $results = $qry->objectSet( $sql, ['uid' => intval($id) ]);
        return $results;        
    }
    
    public function getGroups($user) {
        $qry = $this->dbq;
        $sql = <<<EOD
select A.groupid, G.name, A.status,A.created_at, A.changed_at 
    from user_auth A join user_group G on G.id = A.groupid and G.active = 1
    where A.userid = ?;                
EOD;
        $data = $qry->objectSet($sql, [$user->id]);
        return $data;
    }
    public function getRoleList($user) {
       $qry = $this->dbq;

       $sql = <<<EOD
SELECT  G.name from user_group G
join user_auth A on A.userid = :uid
and G.id = A.groupid and G.active = 1
EOD;
        $result = [];
        $rows = $qry->objectSet(
                $sql, ['uid' => $user->id]
        );
        foreach ($rows as $row) {
            $result[] = $row->name;
        }
        return $result;
    }

}
