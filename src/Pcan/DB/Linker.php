<?php

/*
 * Each line should be prefixed with  * 
 */

namespace WC\DB;

/**
 *
 * @author Michael Rynn
 */
class Linkery extends \DB\SQL\Mapper
{

    public function __construct()
    {
        $db = Server::db();
        parent::__construct($db, 'link_gallery', NULL, 1.0e8); // 100 second
    }

    static public function removeRef($linkid, $gallid)
    {
        $db = Server::db();
        return $db->exec(["delete from link_gallery where linkid = :lid and gallid = :gid",
                    ':lid' => $linkid, ':gid' => $gallid]);
    }

    static public function deleteImage($img_id)
    {
        $db = Server::db();
        return $db->exec(["delete from image where id = :imgid", ':imgid' => $img_id]);
    }

    static public function findFirst($conditions)
    {
        $gal = new Linkery();
        $found = $gal->load($conditions);
    }

    static public function byId($id)
    {
        $gal = new Linkery();
        $found = $gal->load("id = " . $id);
        return $found;
    }
    static public function byLink ( $linkid ) {
            $db = Server::db();
            return $db->exec("select G.* from link_gallery G "
                    . " join linktogallery L on G.id = L.gallid "
                    . " where L.linkid = :lid",
                    [':lid' => $linkid]);
    }
    
    static public function getAllLinks($id)
    {
        $mydb = Server::db();
        $sql = <<<EOD
select i.* from links i
    join linktogallery k on i.id = k.linkid and k.gallid = :id    
EOD;
        $results = $mydb->exec($sql, [':id' => $id]);
        return $results;
    }

    static public function byName($name)
    {
        $gal = new Linkery();
        $found = $gal->load("name = '$name'");
        return $found;
    }

}
