<?php

namespace Pcan\DB;

use WC\DB\Server;

/**
 * Node of menu system.
 *
 * @author Michael Rynn
 */
class MenuItem extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'menu_item', NULL, 1.0e8); // 100 second
    }
    
    static public function findById($id) {
        $item = new MenuItem();
        $item->load([ 'id = ?', $id ]);
        return $item;
    }
}
