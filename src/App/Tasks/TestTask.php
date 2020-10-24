<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Tasks;

use App\Models\Users;
/**
 * Description of TestTask
 *
 * @author michael
 */
class TestTask
{
    public function mainAction($name) {
        echo "Unit Test "  . PHP_EOL;
        $user = Users::findFirstByName($name);
        if (!empty($user)) {
        echo "User id " . $user->id . PHP_EOL;
        }
    }
}
