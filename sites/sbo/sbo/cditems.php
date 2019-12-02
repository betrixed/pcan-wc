<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SBO;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;

class CDItems extends \DB\SQL\Mapper
{

    public function __construct()
    {
        $db = Server::db();
        parent::__construct($db, 'cditems', NULL, 1.0e8); // 100 second
    }

    static function byId($id) {
        $rec = new CDItems();
        return $rec->load(['id = ?', $id]);
    }
}
