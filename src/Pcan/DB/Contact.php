<?php

namespace Pcan\DB;
use WC\DB\Server;
/**
 *
 * @author Michael Rynn
 */
class Contact extends \DB\SQL\Mapper {

    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'contact', NULL, 1.0e8); // 100 second
    }
    
    static  public function byId($id) {
        $result = new Contact();
        return $result->load([ 'id = ?', $id ]);
    }
}
