<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WC\Tasks;
use WC\Db\ARModels;
/**
 * Regenerate all the Active Record Models 
 * for a database connection.
 *
 * @author michael
 */
class ModelsTask {
    //put your code here
    public function generateAction()
    {
        global $APP;
        $fileCount = 0;
        $db_config_name = $APP->module_cfg['database'];
        $fn = function($fpath) use (&$fileCount) {
            echo "Created: " . $fpath . PHP_EOL;
            $fileCount++;
        };
        ARModels::makeModelFiles($db_config_name, $APP->ar_models, $fn);

        echo "Created " . $fileCount . " model files". PHP_EOL;
    }
}
