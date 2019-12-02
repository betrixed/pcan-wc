<?php

namespace Pcan\DB;
use WC\DB\Server;
/**
 *
 * @author Michael Rynn
 */
class ImgGallery extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'img_gallery', NULL, 1.0e8); // 100 second
    }
    
    static public function setVisible($galleryid, $imageid, $value) {
        $db = Server::db();
        return $db->exec("update img_gallery set visible = :val "
                . "where imageid = :imgid and galleryid = :galid", 
                [':val' => $value, ':imgid' => $imageid, ':galid' => $galleryid]);
    }
}
