<?php

use League\Plates\Engine;
use WC\DB\Server;
use WC\DB\Script;
use WC\Dos;
use WC\XmlPhp;
use iamcal\SQLParser;
use WC\App;
use WC\Assets;
use WC\Valid;
use Pcan\HtmlPlates;
use Pcan\DB\UserAuth;

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

class Schema extends \Pcan\Controller {

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

    private function db_params($p) {
        $dbname = Valid::toStr($p, 'dbname');
        $dbuser = Valid::toStr($p, 'dbuser');
        $passwd = Valid::toStr($p, 'passwd');
       
        $adapter = Valid::toStr($p, 'adapter');
        $port = Valid::toInt($p, 'port');
        $hostname = Valid::toStr($p, 'hostname');
        $unix_socket = Valid::toBool($p, 'unix_socket');

        $sdb = ['dbname' => $dbname, 'adapter' => strtolower($adapter),
                'username' => $dbuser, 'password' => $passwd];

        if ($adapter === 'Mysql') {
            if (empty($port)) {
                $port = 3306;
            }
            if (empty($hostname)) {
                $hostname = 'localhost';
            }
            $sdb += ['port' => $port, 'charset' => 'utf8', 'host' => $hostname];
        } else if ($adapter === 'Pgsql') {
            if (empty($unix_socket)) {
                if (empty($port)) {
                    $port = 5432;
                }
                if (empty($hostname)) {
                    $hostname = 'localhost';
                }
                $sdb += ['port' => $port, 'host' => $hostname];
            }
        }
        return $sdb;
    }
    public function make_db($f3, $p) {

        
        $datadir = App::instance()->getSchemaDir();
        $schema = Valid::toStr($p, 'schema');
        $path = $datadir . $schema . '.schema';

        $sdb = $this->db_params($p);
        
        
        // Alter schema interpreter
        
        if ($sdb['adapter'] === 'mysql') {
            $rdr = new AdaptXml('Pgsql', 'Mysql');
        } else {
            $rdr = new AdaptXml('Mysql', 'Pgsql');
        }

        $cfg = $rdr->parseFile($path);

        $db = null;


        try {
            
            $db = Server::connection($sdb);

            Server::setDefault($db);

            //$db = new DiffReport();
            $cfg->execStages($db, [
                'drop-fkeys' => true,
                'drop-tables' => true,
                'tables' => true,
                'alter' => true,
                'indexes' => true,
                'load-data' => $datadir . $schema . '_dir',
                'alter' => true,
                'auto_inc' => true,
                'options' => true,
                'add-fkeys' => true
            ]);

            $rows = $db->exec('select count(*) as gs from user_group');
            if (empty($rows) || $rows[0]['gs'] === 0) {
                $msg = 'Data load fail';
            } else {
                $msg = 'Database created';
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            if (!empty($db)) {
                $msg .= PHP_EOL . $db->log();
            }
            $this->flash($msg);
            throw $e;
        }
        
        

        return $sdb;
        
    }
    /** Apply a schema to a new database, with given connection parameters,
     *  and setup an admin account.
     * 
     * @param type $f3
     * @param type $params
     */
    public function initdb($f3, $params) {
        $view = new HtmlPlates($f3);
        $view->layout = 'schema';

        $p = &$f3->ref('POST');
        
        
        $dbname = Valid::toStr($p, 'dbname');
        
        if (!empty($dbname)) {
            $sdb = $this->make_db($f3, $p);

        }
        else {
            $sdb = null;
        }
        
        $admin_user = Valid::toStr($p, 'admin_user');

        if (!empty($admin_user)) {
             $admin_pwd = Valid::toStr($p, 'admin_pwd');
             $admin_email = Valid::toEmail($p, 'admin_email');
             UserAuth::makeNewUser($admin_user, $admin_email, $admin_pwd, ['Admin', 'User', 'Editor', 'Guest']);
             $this->flash('User created');
        }

        $site_dir = Valid::toStr($p, 'site_dir');
       
        if (!empty($site_dir)) {
            $php = $f3->get('php');
            $sitepath = $php . 'sites/' . $site_dir . '/';
            // make site folder if it doesn't already exist
            Dos::makedir($sitepath);

            $pkg = $f3->get('pkg');
            $web = $f3->get('web');

            $setup =  $pkg . 'sites/default/template/';

            // Copy template index.php to webroot

            $content = file_get_contents($setup . 'index.php');
            $content = str_replace('$$_SITE_$$', $site_dir, $content);
            file_put_contents($web . 'index.php', $content);

            // Copy template config.php to root of $sitepath
            $content = file_get_contents($setup . 'config.php');
            $content = str_replace('$$_SITE_$$', $site_dir, $content);
            file_put_contents($sitepath . 'config.php', $content);

            // assets.xml
            copy($setup . 'assets.xml', $sitepath . 'assets.xml');
            // Copy Home.php to src in sitepath

            Dos::makedir($sitepath . 'src');
            copy($setup . 'Home.php', $sitepath . 'src/Home.php');

            Dos::makedir($sitepath . 'views');
            copy($setup . 'index.phtml', $sitepath . 'views/index.phtml');
            // Copy common locations, build a images and theme assets in webroot, using $site_dir
            Dos::copyall( $pkg . 'web/js', $web . 'js');
            Dos::copyall( $pkg . 'web/css', $web . 'css');
            Dos::copyall( $pkg . 'web/image', $web . $site_dir);


            // create a .secrets.xml
            if (!empty($sdb)) {
                file_put_contents($sitepath . '.secrets.xml', XmlPhp::toXmlDoc([ "database" => $sdb ]));
            }


        }
            
            // make a new user (email is essential)
        
        $view->values['script'] = $msg;

        echo $view->render();
    }

}
