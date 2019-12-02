<?php

use League\Plates\Engine;
use WC\DB\Server;
use WC\DB\Script;
use WC\XmlPhp;
use iamcal\SQLParser;
use WC\App;
use WC\Assets;
use WC\Valid;
use Pcan\HtmlPlates;

class AdaptXml extends XmlPhp {

    public $orig;
    public $adapt;

    public function __construct($orig, $adapt) {
        $this->orig = $orig;
        $this->adapt = $adapt;
    }

    public function makeClass($c) {
        $c = str_replace($this->orig, $this->adapt, $c);
        return new $c();
    }

}

class Schema {

    public $sitedir;
    public $engine;

    public function getSchemaList() {
        $files = [];
        foreach (glob(App::instance()->getSchemaDir() . '*.schema') as $filename) {
            $files[] = pathinfo($filename, PATHINFO_FILENAME);
        }
        return $files;
    }

    public function index($f3, $params) {
        $list = $this->getSchemaList();

        // use each value as key
        $keyed = [];
        foreach ($list as $value) {
            $keyed[$value] = $value;
        }
        $adapters = ['Mysql' => 'Mysql', 'Pgsql' => 'Pgsql'];

        Assets::instance()->add('bulma');

        $view = new HtmlPlates($f3);
        $view->layout = 'index';
        $view->add(['list' => $list, 'keyed' => $keyed, 'adapters' => $adapters]);

        echo $view->render();
    }

    public function meta($f3, $params) {
        $post = &$f3->ref('POST');
        $dbname = Valid::toStr($post, 'dbname');
        $dbuser = Valid::toStr($post, 'dbuser');
        $passwd = Valid::toStr($post, 'passwd');
        $version = Valid::toStr($post, 'version');
        $adapter = Valid::toStr($post, 'adapter');


        $cfg = ['dbname' => $dbname, 'adapter' => strtolower($adapter),
            'username' => $dbuser, 'password' => $passwd];

        if ($adapter === 'Mysql') {
            $cfg += ['port' => 3306, 'charset' => 'utf8', 'host' => 'localhost'];
        }
        $db = Server::connection($cfg);
        $dbschema = 'WC\\' . $adapter . '\SchemaDef';
        $schema = new $dbschema();

        $schema->readSchema($db);
        $schema->setName($version);

        $schemaDir = App::instance()->getSchemaDir();

        $path = $schemaDir . $version . '.schema';

        $schema->toFile($path);

        $exportData = true;

        if ($exportData) {
            $folderName = $schemaDir . $version . '_dir';
            if (!file_exists($folderName)) {
                mkdir($folderName);
            }

            foreach ($schema->tables as $tdef) {
                $tdef->exportDataToCSV($db, $folderName . '/' . $tdef->name . '.csv');
            }
        }
        $f3->reroute('/schema/script/' . $version);
    }

    public function compare($f3, $params) {
        $p = &$f3->ref('POST');
        $s1 = \Valid::toStr($p, 'sel1');
        $s2 = \Valid::toStr($p, 'sel2');


        if ($s1 === $s2) {
            // must be different
        }
        $report = new DiffReport();
        $report->doCompareSchema($s1, $s2);

        $engine = $this->init($f3);
        echo $engine->render('compare', ['script' => $report->log]);
    }

    public function generate($f3, $params) {
        $view = new HtmlPlates($f3);
        $view->layout = 'schema';

        $version = $params['v'] ?? null;

        $path = App::instance()->getSchemaDir() . $version . '.schema';

        $cfg = XmlPhp::fromFile($path);
        $script = new Script();

        $cfg->generate($script, ['tables' => 'create']);

        $cfg->generate($script, ['alter' => true, 'indexes' => true, 'auto_inc' => true]);

        $cfg->generate($script, ['alter' => true, 'references' => true]);

        $view->values['script'] = $script;

        echo $view->render();
    }

    public function initdb($f3, $params) {
        $view = new HtmlPlates($f3);
        $view->layout = 'schema';

        $p = &$f3->ref('POST');
        $dbname = Valid::toStr($p, 'dbname');
        $dbuser = Valid::toStr($p, 'dbuser');
        $passwd = Valid::toStr($p, 'passwd');
        $schema = Valid::toStr($p, 'schema');
        $adapter = Valid::toStr($p, 'adapter');
        $script = Valid::toBool($p, 'script');


        $path = App::instance()->getSchemaDir() . $schema . '.schema';

        if ($adapter === 'Mysql') {
            $rdr = new AdaptXml('Pgsql', 'Mysql');
        } else {
            $rdr = new AdaptXml('Mysql', 'Pgsql');
        }

        $cfg = $rdr->parseFile($path);

        $db = null;

        if (!$script) {
            try {
                $sdb = ['dbname' => $dbname, 'adapter' => strtolower($adapter),
                    'username' => $dbuser, 'password' => $passwd];

                if ($adapter === 'Mysql') {
                    $sdb += ['port' => 3306, 'charset' => 'utf8', 'host' => 'localhost'];
                }
                $db = Server::connection($sdb);
                //$db = new DiffReport();
                $cfg->execStages($db, [
                    'drop-fkeys' => true,
                    'drop-tables' => true,
                    'tables' => true,
                    'alter' => true,
                    'indexes' => true,
                    'load-data' => $this->sitedir . 'schema/' . $schema . '_dir',
                    'alter' => true,
                    'auto_inc' => true,
                    'options' => true,
                    'add-fkeys' => true
                ]);
                $msg = 'OK';
            } catch (\Exception $e) {
                $msg = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
                if (!empty($db)) {
                    $msg .= PHP_EOL . $db->log();
                }
            }
            $view->values['script'] = $msg;
        } else {
            $log = new Script();
            $cfg->generate($log, ['drop-fkeys' => true, 'drop-tables' => true]);

            $cfg->generate($log, ['tables' => 'create']);

            //    $cfg->generate($log, ['load-data' => $this->sitedir . 'schema/' . $schema . '_dir']);
            $cfg->generate($log, ['alter' => true, 'indexes' => true, 'auto_inc' => true]);

            $cfg->generate($log, ['alter' => true, 'references' => true]);
            $view->values['script'] = $log;
        }
        echo $view->render();
    }

}
