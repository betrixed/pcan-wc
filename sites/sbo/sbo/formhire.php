<?php
namespace SBO;

/**
 * @author Michael Rynn
 */

use WC\DB\Server;
use WC\Valid;

class FormHire  extends \DB\SQL\Mapper  {
       public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'form_booking', NULL, 1.0e8); // 100 second
         }
         
         static function findById($id) {
             $result = new FormHire();
             return $result->load(["id = ?", intval($id) ]);
         }
         static function setFromPost(&$post, $rec) {
            $rec['fullname'] = Valid::toStr($post, 'fullname', null);
             $rec['telephone'] = Valid::toStr($post, 'telephone', null);
              $rec['email'] = Valid::toEmail($post, 'email', null);
              $rec['venue'] = Valid::toStr($post, 'venue', null);
            $rec['date'] = Valid::toDateTime($post, 'date', null);
            $rec['message'] = Valid::toStr($post, 'message', null);
            $rec['created_on'] = Valid::toDateTime($post, 'created_on');
         }
}
