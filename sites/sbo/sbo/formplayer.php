<?php
namespace SBO;

/**
 * @author Michael Rynn
 */

use WC\DB\Server;
use WC\Valid;

class FormPlayer  extends \DB\SQL\Mapper  {
       public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'form_player', NULL, 1.0e8); // 100 second
         }
         
         static function findById($id) {
             $result = new FormPlayer();
             return $result->load(["id = ?", intval($id) ]);
         }
         static function setFromPost(&$post, $rec) {
             

            $rec['instrument'] = Valid::toStr($post, 'instrument', null);
            $rec['experience'] = Valid::toStr($post, 'experience', null);
            $rec['history'] = Valid::toStr($post, 'history', null);

             $rec['phone'] = Valid::toStr($post, 'phone', null);
            $rec['name'] = Valid::toStr($post, 'name', null);
            $rec['email'] = Valid::toEmail($post, 'email', null);
            $rec['created_on'] = Valid::toDateTime($post, 'created_on');
            
         }
}
