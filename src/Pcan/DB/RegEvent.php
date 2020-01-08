<?php
namespace Pcan\DB;
use WC\DB\Server;
/**
 * @author michael
 */
class RegEvent extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'register', NULL, 1.0e8); // 100 second
        $this->beforesave(function($self,$pkeys) {
            $code = md5(strtolower($self['email']) . $self['eventid'] . strtolower($self['fname']) . strtolower($self['lname']));
            $self->set('linkcode', $code);
        });
    }
    static  public function byId($id) {
        $result = new RegEvent();
        return $result->load([ 'id = ?', $id ]);
    }

}
