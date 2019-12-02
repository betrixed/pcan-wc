<?php

namespace Pcan\DB;
use WC\DB\Server;
/**
 *
 * @author Michael Rynn
 */
class LinkLinkery extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'linktogallery', NULL, 1.0e8); // 100 second
    }
    
    
    static public function setVisible($gallid, $linkid, $value) {
        $db = Server::db();
        return $db->exec("update linktogallery set visible = :val "
                . "where linkid = :lid and gallid = :gid", 
                [':val' => $value, ':imgid' => $linkid, ':gid' => $gallid]);
    }
}
