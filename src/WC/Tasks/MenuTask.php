<?php


namespace WC\Tasks;
/**
 * @author michael
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use WC\Link\MenuBuild;
use WC\Link\Path;
use WC\App;


class MenuTask extends \Phiz\Cli\Task 
{
    public function mainAction()
    {
        global $app;
        $site = $app->pcan_dir . "/sites/default";
        $plates = $app->plates;
        $ui = $plates->UI;

        $builder = new MenuBuild([
            'output_dir' => Path::endSep($ui[0]) .  Path::endSep($plates->partialsDir),
            'schema' => $site . '/schema/initdb.schema'
            
        ]);
        $builder->create_menu_table();
       $files  = ['menus', 'menus_admin'];
        foreach($files as $f) {
            echo $f . PHP_EOL;
            $builder->fromMenuConfig($f);
            $builder->generate_view($f);
        }
        echo "MenuTasks done" . PHP_EOL;
    }
    
}
