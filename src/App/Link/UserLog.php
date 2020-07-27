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

class UserLog
{

    const PW_LOGIN = "PW_LOGIN";
    const PW_RESET = "PW_RESET";
    const PW_CHANGE = "PW_ALTER";
    const EMAIL_CK = "EMAIL_CK";
    const PW_TOKEN = "PW_TOKEN";

    protected $db;
    protected $qry;

    public function __construct($db)
    {
        $this->db = $db;
        $this->qry = null;
    }

    public function getEvents($etype, $id)
    {

        $sql = <<<EOD
select * from user_event where event_type = ? and user_id = ?
EOD;
        if (!$this->qry) {
            $this->qry = new DbQuery($this->db);
        }
        return $this->qry->objectSet($sql, [$etype, $id]);
    }

    static public function logPwdChange($id, $ip, $agent)
    {
        $event = new UserEvent();
        $event->status_ip = $ip;
        $event->data = $agent;

        $event->created_at = Valid::now();
        $event->event_type = UserEvent::PW_CHANGE;
        $event->user_id = $id;
        $event->create();
    }

    public function deleteOldCodes()
    {
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval("P2D"));
        $ystr = $yesterday->format(Valid::DATE_TIME_FMT);
        $result = $this->db->execute("delete from reset_code where created_at < ?", $ystr);
        return $result;
    }

    public function newUserConfirm(Users $user, $event_type, $request = null)
    {
        $this->deleteOldCodes();

        // Make a user event, and a resetcode
        $event = new UserEvent();

        $event->event_type = $event_type;


        if (is_object($request)) {
            $event->status_ip = $request->getServerAddress();
            $event->data = $request->getUserAgent();
        } else if (is_string($request)) {
            $event->status_ip = "Confirm Email";
            $event->data = $request;
        }
        $stamp = Valid::now();
        $event->created_at = $stamp;

        $resetcode = new ResetCode();
        $resetcode->code = Valid::randomStr();
        $resetcode->created_at = $stamp;

        $db = $this->db;
        $db->begin();
        try {
            $user->create();
            $id = $user->id;
            $resetcode->user_id = $id;
            $event->user_id = $id;
            $resetcode->create();
            $event->create();
            $db->commit();
        } catch (\PDOException $pd) {
            return false;
        }
        return true;
    }

}
