<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Link;

use WC\Models\{
    EmailConfirmations,
    UserAuth
};
use WC\{
    SwiftMail,
    WConfig
};
use function debugLine;

/**
 * Description of UserAdm
 *
 * @author michael
 */
trait UserAdm {

    public function otherGroups($id) {
        $qry = $this->dbq;
        $sql = <<<EOD
SELECT  G.name, G.id  from user_group G
 where G.name <> 'Guest' AND 
 G.name NOT IN (select U.name from 
     user_auth A join user_group U on U.id = A.groupid
     where A.userid = :uid) 
 order by name
EOD;
        $results = $qry->objectSet($sql, ['uid' => intval($id)]);
        return $results;
    }

    public function getGroups($user) {
        $qry = $this->dbq;
        $sql = <<<EOD
select A.groupid, G.name, A.status,A.created_at, A.changed_at 
    from user_auth A join user_group G on G.id = A.groupid and G.active = 1
    where A.userid = ?;                
EOD;
        $data = $qry->objectSet($sql, [$user->id]);
        return $data;
    }

    public function getRoleList($user) {
        $qry = $this->dbq;

        $sql = <<<EOD
SELECT  G.name from user_group G
join user_auth A on A.userid = :uid
and G.id = A.groupid and G.active = 1
EOD;
        $result = [];
        $rows = $qry->objectSet(
                $sql, ['uid' => $user->id]
        );
        foreach ($rows as $row) {
            $result[] = $row->name;
        }
        return $result;
    }

    public function addUserGroup($userid, $groupid) {
        $role = new UserAuth();
        $role->groupid = $groupid;
        $role->userid = $userid;
        try {
            $role->create();
        } catch (\PDOException $e) {
            return $this->errorPDO($e);
        }
        return true;
    }

    public function delUserGroup($userid, $groupid) {
        $db = $this->db;

        $db->execute("delete from user_auth where userid = $userid and groupid = $groupid");
    }

    public static function makeCode(int $size = 24): string {
        return preg_replace('/[^a-zA-Z0-9]/', '', base64_encode(openssl_random_pseudo_bytes($size)));
    }

    public function sendConfirm($user) {
        $ec = new EmailConfirmations();

        $ec->usersId = $user->id;
        $ec->createdAt = date('Y-m-d H:i:s');
        $ec->modifiedAt = $ec->createdAt;
        $ec->code = self::makeCode(24);
        $ec->confirmed = 'N';

        $app = $this->app;
        // create email content
        $params = [
            'url' => 'https://' . $app->domain . "/confirm/" . $ec->code . "/" . $user->email,
            'userName' => $user->name
        ];

        $htmlMsg = $textMsg = static::simpleView('email/confirm', $params);
        $textMsg = static::simpleView('email/confirm_text', $params);

        $mailer = new SwiftMail($app);
        $msg = [
            "subject" => 'Parramatta Greens Website - Login Confirmation Request',
            "text" => $textMsg,
            "html" => $htmlMsg,
            "to" => [
                    "email" => $user->email,
                    "name" => $user->name
                ]
        ];
        $mok = $mailer->send($msg, "email_confirm");

        if ($mok && $ec->create()) {

            $this->flash('A confirmation mail has been sent to ' . $user->email);
        } else {
            if (!$mok) {
                $this->flash('A mailer error occurred');
            } 
            else {
                foreach ($ec->getMessages() as $message) {
                    debugLine("Message: ", $message->getMessage());
                    debugLine("Field: ", $message->getField());
                    debugLine("Type: ", $message->getType());
                }
            }
        }
    }

}
