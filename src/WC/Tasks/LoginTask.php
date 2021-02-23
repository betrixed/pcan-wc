<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Tasks;
use WC\Models\Users;
/**
 * Description of Login
 *
 * @author michael
 */
class LoginTask extends \Phalcon\Cli\Task {
    //put your code here
    public function passwdAction(array $params) {
        $email = $params[0];
        $newpwd = $params[1];
        
        $user = Users::findFirstByEmail($email);
        
        if (empty($user)) {
            throw new \Exception("User $email not found");
        }
        
        $crypt = $this->security;

        $user->password = $crypt->hash($newpwd);
        $user->mustchangepassword = 'N';
        $user->update();
    }
}
