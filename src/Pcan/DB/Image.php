<?php

namespace Pcan\DB;
use WC\DB\Server;
/**
 *
 * @author Michael Rynn
 */
class Image extends \DB\SQL\Mapper
{

    public function __construct()
    {
        $db = Server::db();
        parent::__construct($db, 'image', NULL, 1.0e8); // 100 second
    }

    static public function byId($id)
    {
        $img = new Image();
        $found = $img->load("id = " . $id);
        return $found;
    }

    static public function getGalleryCount($imageid) {
        $db = Server::db();
        $result = $db->exec("select count(*) as gct from img_gallery g where g.imageid = :id"
                , [':id' => $imageid]);
        return $result[0]['gct'];
    }
    static public function updateDescription($imageid, $new_description)
    {
        $db = Server::db();
        return $db->exec("update image set description = :ndesc where id = :id", [':ndesc' => $new_description, ':id' => $imageid]);
    }
    static public function updateThumbExt($img_id, $thumb_ext)
    {
        $db = Server::db();
        return $db->exec("update image set thumb_ext = :ext where id = :imgid", [':imgid' => $img_id, ':ext' => $thumb_ext]);
    }
}
