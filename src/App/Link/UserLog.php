<?php

namespace App\Link;

/**
 *
 * @author Michael Rynn
 */
use WC\Valid;
use WC\DB\{
    Server,
    DbQuery
};
use App\Models\{
    UserEvent,
    ResetCode,
    Users
};

use Phalcon\Db\Column;

class UserLog
{

    const PW_LOGIN = "PW_LOGIN";
    const PW_RESET = "PW_RESET";
    const PW_CHANGE = "PW_ALTER";
    const EMAIL_CK = "EMAIL_CK";
    const PW_TOKEN = "PW_TOKEN";

    protected $db;
    protected $qry;

    
    static public function getEvents($etype, $id) : array
    {
        global $container;
        $qry = $container->get('dbq');
        $sql = <<<EOD
select * from user_event where event_type = ? and user_id = ?
EOD;

        return $qry->objectSet($sql, [$etype, $id]);
    }

    static public function login($id, $ip, $agent) 
    {
        $event = new UserEvent();
        $event->status_ip = $ip;
        $event->data = $agent;

        $event->created_at = Valid::now();
        $event->event_type = self::PW_LOGIN;
        $event->user_id = $id;
        $event->create();        
    }
    static public function logPwdChange($id, $ip, $agent)
    {
        $event = new UserEvent();
        $event->status_ip = $ip;
        $event->data = $agent;

        $event->created_at = Valid::now();
        $event->event_type = self::PW_CHANGE;
        $event->user_id = $id;
        $event->create();
    }

    static public function deleteOldCodes()
    {
        global $container;
        
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval("P2D"));
        $ystr = $yesterday->format(Valid::DATE_TIME_FMT);
        $db = $container->get('db');
        $result = $db->execute("delete from reset_code where created_at < :cdate", ['cdate' => $ystr], ['cdate' => Column::BIND_PARAM_STR] );
        return $result;
    }
    
    /**
     * Reset password is almost same as new user confirm
     * @param Users $user
     * @param type $event_type
     * @param type $request
     * @return boolean
     */
    static function newUserConfirm(Users $user, $event_type, string $ip, string $data) : string
    {
        global $container;
        
        self::deleteOldCodes();

        // Make a user event, and a resetcode
        $event = new UserEvent();

        $event->event_type = $event_type;

        $event->status_ip = $ip;
        $event->data = $data;

        $stamp = Valid::now();
        $event->created_at = $stamp;
        $code = Valid::randomStr();
        $resetcode = new ResetCode();
        $resetcode->code = $code;
        $resetcode->created_at = $stamp;

        $db = $container->get('db');
        
        try {
            $db->begin();
            if (empty($user->id)) {
                $user->create();
            }
            $id = $user->id;
            $resetcode->user_id = $id;
            $event->user_id = $id;
            $resetcode->create();
            $event->create();
            $db->commit();
        } catch (\PDOException $pd) {
            throw new \Exception('Error in Confirm Code', $pd->getCode(), $pd);
        }
        return $code;
    }

}
