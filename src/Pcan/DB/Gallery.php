<?php

/*
 * Each line should be prefixed with  * 
 */

namespace Pcan\DB;
use WC\DB\Server;

/**
 *
 * @author Michael Rynn
 */
class Gallery extends \DB\SQL\Mapper
{

    public function __construct()
    {
        $db = Server::db();
        parent::__construct($db, 'gallery', NULL, 1.0e8); // 100 second
    }

    static public function removeRef($img_id, $gallery_id)
    {
        $db = Server::db();
        return $db->exec("delete from img_gallery where imageid = :imgid and galleryid = :gid",
                    [':imgid' => $img_id, ':gid' => $gallery_id]);
    }


    static public function deleteImage($img_id)
    {
        $db = Server::db();
        return $db->exec(["delete from image where id = :imgid", ':imgid' => $img_id]);
    }

    static public function findFirst($conditions)
    {
        $gal = new Gallery();
        $found = $gal->load($conditions);
    }

    static public function byId($id)
    {
        $gal = new Gallery();
        $found = $gal->load("id = " . $id);
        return $found;
    }

    static public function getImages($id)
    {
        $mydb = Server::db();
        $sql = <<<EOD
select i.*, g.path from image i, gallery g, img_gallery a
where a.galleryid = :id and a.visible <> 0
and i.id = a.imageid
and g.id = i.galleryid  
order by i.date_upload desc
EOD;
        $results = $mydb->exec($sql, [':id' => $id]);
        return $results;
    }

    static public function byName($name)
    {
        $gal = new Gallery();
        $found = $gal->load("name = '$name'");
        return $found;
    }

}
