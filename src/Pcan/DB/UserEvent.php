<?php

namespace Pcan\DB;

/**
 *
 * @author Michael Rynn
 */

use WC\UserSession;
use WC\Valid;
use WC\DB\Server;

class UserEvent extends \DB\SQL\Mapper {
    const PW_LOGIN = "PW_LOGIN";
    const PW_RESET = "PW_RESET";
    const PW_CHANGE = "PW_ALTER";
    const EMAIL_CK = "EMAIL_CK";
    const PW_TOKEN = "PW_TOKEN";
    
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'user_event', NULL, 1.0e8); // 100 second
    }

    static public function getEvents($etype, $id) {
        $db = Server::db();
        $sql = <<<EOD
select * from user_event where event_type = ? and user_id = ?
EOD;
        return $db->exec($sql, [$etype, $id]);
    }

    
    static public function logPwdChange($userid) {
        $event = new UserEvent();
        $sess = UserSession::session();
        $event['status_ip'] = $sess->ip();
        $event['data'] = $sess->agent();
        $stamp = Valid::now();
        $event['created_at'] = $stamp;
        $event['event_type'] = UserEvent::PW_CHANGE;
        $event['user_id'] = $userid;
        $event->save();
    }
    static public function newUserConfirm($user, $event_type, $reqDetail = true)
    {
        ResetCode::deleteOldCodes();
        
        // Make a user event, and a resetcode
        $event = new UserEvent();
       
        $event['event_type'] = $event_type;
        
        
        if ($reqDetail)
        {
            $sess = UserSession::session();
            $event['status_ip'] = $sess->ip();
            $event['data'] = $sess->agent();
        }
        else {
            $event['status_ip']  = "Confirm Email";
            $event['data'] = $user->email;
        }
        $stamp = Valid::now();
        $event['created_at'] = $stamp;
        
        $resetcode = new ResetCode();
        $result = Valid::randomStr();
        $resetcode['code'] = $result;
        $resetcode['created_at'] = $stamp;
        
        $db = Server::db();
        $db->begin();

        try {
            $user->save();
            $id = $user['id'];
            $resetcode['user_id'] = $id;
            $event['user_id'] = $id;
            $resetcode->save();
            $event->save();
            $db->commit();
        } 
        catch(\PDOException $pd) {
            return false;
        }
        return $result;
    }
    
   
}
