<?php

use League\Plates\Engine;
use WC\DB\Server;
use WC\WConfig,
    WC\XmlPhp;
use WC\Mysql\ColumnDef;
use WC\Mysql\IndexDef;
use WC\Mysql\ReferenceDef;
use WC\Mysql\TableDef;
use WC\Mysql\SchemaDef;
use iamcal\SQLParser;
use WC\Assets;
use WC\App;

use Pcan\PlatesForm;

class Home {


    public function getSchemaDir() {
        return App::instance()->getSchemaDir();
    }

    public function getSchemaList() {
        $files = [];
        foreach (glob($this->getSchemaDir() . '*.schema') as $filename) {
            $files[] = pathinfo($filename, PATHINFO_FILENAME);
        }
        return $files;
    }

    public function index($f3, $params) {
        $vdir = $f3->get('sitepath');
        $engine = new Engine($vdir . 'views', 'phtml');
        $engine->loadExtension(new PlatesForm());

        $list = $this->getSchemaList();

        // use each value as key
        $keyed = [];
        foreach ($list as $value) {
            $keyed[$value] = $value;
        }
        $adapters = ['Mysql' => 'Mysql', 'Pgsql' => 'Pgsql'];
        
        Assets::instance()->add('bulma');
        echo $engine->render('index', ['list' => $list, 'keyed' => $keyed, 'adapters' => $adapters]);
    }

    public function meta($f3, $params) {
        $post = &$f3->ref('POST');
        $dbname = Valid::toStr($post, 'dbname');
        $dbuser = Valid::toStr($post, 'dbuser');
        $passwd = Valid::toStr($post, 'passwd');
        $version = Valid::toStr($post, 'version');
        $adapter = Valid::toStr($post, 'adapter');
    
                                                                                                                                              
        $db = Server::$adapter($dbname, $dbuser, $passwd);
        $dbschema = 'WC\\' .  $adapter .  '\SchemaDef';       
        $schema = new $dbschema();
           
        $schema->readSchema($db);
        $schema->setName($version);

        $path = $this->getSchemaDir() . $version . '.schema';
        $schema->toFile($path);

        $exportData = true;

        if ($exportData) {
            $folderName = $this->getSchemaDir() . $version . '_dir';
            if (!file_exists($folderName)) {
                mkdir($folderName);
            }

            foreach ($schema->tables as $tdef) {
                $tdef->exportDataToCSV($db, $folderName . '/' . $tdef->name . '.csv');
            }
        }
        $f3->reroute('/schema/script/' . $version);
    }

    public function schema($f3, $params) {
        $vdir = $f3->get('sitepath');
        $engine = new Engine($vdir . 'views', 'phtml');
        $engine->register(new PlatesForm());

        echo $engine->render('schema');
    }

}
