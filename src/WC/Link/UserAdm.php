<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Link;

use WC\Models\{
    EmailConfirmations,
    UserAuth,
    Users,
    FbookUser,
    FbookDeluser
};
use WC\{
    SwiftMail,
    WConfig,
    Valid
};
use stdClass;
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

    public function newPlainUser(string $name, string $email) : ?Users {
        return $this->makeNewUser($name, $email, ['User']);      
    }
    
    protected function makeNewUser(string $user_name, string $user_email, array $groups): ?Users
    {
        $user = new Users();
        $user->name = $user_name;
        $user->email = $user_email;
        $user->mustChangePassword = 'Y';
        $user->status = 'N';
        $user->created_at = Valid::now();
        $user->changed_at = $user->created_at;
                        
        $pwd = $this->security->hash(self::makeCode(16));
        $user->password = $this->security->hash($pwd);


        try {
            $user->create();
            $db = $this->db;
            $pdo = $db->getInternalHandler();

            $grouplist = '(';

            foreach ($groups as $ix => $g) {
                if ($ix > 0) {
                    $grouplist .= ',';
                }
                $grouplist .= $pdo->quote($g);
            }
            $grouplist .= ')';
            $sql = <<<EOS
insert into user_auth (userid, groupid) select :uid, ug.id from user_group ug
   where ug.name in $grouplist
EOS;
            $db->execute($sql, ['uid' => $user['id']]);
        } catch (\PDOException $e) {
            $this->flash($e->getMessage());
        }
        return $user;
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

    /**
     * Assume Facebook userid already extracted,
     * Assume "connected" status is true
     * @param array $post
     * @return stdClass
     */
    public function validFBLogin(int $fb_userid, array $post) : stdClass
    {
        $fbdata = new stdClass();
        $fbdata->user_id = $fb_userid;
        $fbdata->connected = true;
        // svr_token is passed back users id
        $fbdata->svr_token = Valid::toInt($post,"svr_token");
        $fbdata->signed_req = Valid::toStr($post,"signed_req");
        $fbdata->graph_domain = Valid::toStr($post, "graph_domain");
        $fbdata->exp_time = Valid::toInt($post,"exp_time");
        $fbdata->access_exp = Valid::toInt($post,"access_exp");
        $fbdata->access_token = Valid::toStr($post,"access_token");  
        return $fbdata;
    }
    
    
    /**
     * New FbookUser from standard user id, and $fbdata class
     * Two different primary keys -   fbuser->id is facebooks userid
     * $userid is users record id
     */
    public function createFbookUser(stdClass $fbdata, int $id) : FbookUser
    {
        /* make a FbookUser for counting purposes */
        $fbuser = new FbookUser();
        $fbuser->id = $fbdata->user_id;
        $fbuser->userid = $id;
        $fbuser->fb_email = $fbdata->email;
        $fbuser->fb_name = $fbdata->name;
        $fbuser->created_at = Valid::now();
        $fbuser->modified_at = $fbuser->created_at;
        $fbuser->update_count = 0;
        $fbuser->create();
        return $fbuser;
    }
    
    /** 
     * Helper function for facebook user delete 
     */
    
    static  public function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }
    /** 
     * parse FbookUser deletion $request 
     * return $data
     */
    static public  function parse_deletion_request(string $request) : string
    {
         list($encoded_sig, $payload) = explode(',', $request, 2);
         $app = $this->app;
         $app_data = $app->getSecrets('facebook');
         
         $sig = base64_url_decode($encoded_sig);
         $data = json_decode(base64_url_decode($payload), true);
         $expected_sig = hash_hmac('sha256', $payload, $app_data->app_secret,$raw = true);
         if ($sig !== $expected_sig) {
             $this->error_log('Bad Signed JSON signature');
             return null;
         }
         return $data;
    }
    
    /**
     * Remove what little data there is for facebook user record
     * @param int $userid
     */
    public function fbUserDelete(int $userid) : int {
        $rec = FbookUser::findFirstByid($userid);
        if ($rec !== null) {
            // create a deletion entry
            $del = new FbookDeluser();
            $del->fb_user = $userid;
            $del->created_at = Valid::now();
            if ($del->create()) {
                $result = $del->id;
            }
            $rec->delete();
            return $result;
        }
        return 0;
    }
}
