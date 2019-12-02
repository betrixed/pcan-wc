<?php

namespace Pcan\DB;

use WC\Valid;
use WC\DB\Server;
use Pcan\DB\Member;
/**
 * Description of Donation
 *
 * @author michael
 */
class Donation extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'donation', NULL, 1.0e8); // 100 second
    }

    static public function byId($id) {
        $result = new Donation();
        return $result->load(['donateId = ?', $id]);
    }
}