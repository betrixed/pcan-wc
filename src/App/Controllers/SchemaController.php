<?php
namespace App\Controllers;

use WC\Db\{Server, Script};
use WC\{Dos, XmlPhp, App, Assets, Valid, AdaptXml};
use Phalcon\Mvc\Controller;

class SchemaController extends BaseController
{
    use \WC\Mixin\Auth;
    use \WC\Mixin\ViewPhalcon;

    public $sitedir;
    public $schema_dir;
    
    public function getAllowRole() {
        return 'Admin';
    }
    
    public function getSchemaDir()
    {
        if (!isset($this->schema_dir)) {
            $app = $this->app;
            $this->schema_dir = $app->replace_in($app->module_cfg['schema_path']);
        }
        return $this->schema_dir . '/';
    }
    
    public function getSchemaList()
    {
        $files = [];
        $path = $this->getSchemaDir() . '/*.schema';
        foreach (glob($path) as $filename) {
            $files[] = pathinfo($filename, PATHINFO_FILENAME);
        }
        return $files;
    }

    public function indexAction()
    {
        $list = $this->getSchemaList();

        // use each value as key
        $keyed = [];
        foreach ($list as $value) {
            $keyed[$value] = $value;
        }
        $adapters = ['Mysql' => 'Mysql', 'Pgsql' => 'Pgsql', 'Sqlite' => 'Sqlite'];

        $m = $this->getViewModel();
        $m->post_prefix = '/admin/schema';

        $params = ['list' => $list, 'keyed' => $keyed, 'adapters' => $adapters];
        
        return $this->render('schema', 'input',$params);   
    }

    public function metaAction()
    {
        $post = $_POST;
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
        $servers = $this->server;
        $servers->addConfig('schema', $cfg);
        
        $db = $servers->db('schema');
        
        $dbschema = 'WC\\' . $adapter . '\SchemaDef';
        $schema = new $dbschema();
        $schema->setName($dbname);
        
        $schema->readSchema($db);
        $schema->setName($version);

        $schemaDir = $this->getSchemaDir();

        $path = $schemaDir . $version . '.schema';

        $schema->toFile($path);

        $exportData = true;

        if ($exportData) {
            $folderName = $schemaDir . $version . '_dir';
            if (!file_exists($folderName)) {
                mkdir($folderName);
            }

            foreach ($schema->tables as $tdef) {
                $tdef->exportDataToCSV($schema->newQuery($db), $folderName . '/' . $tdef->name . '.csv');
            }
        }
        $this->response->redirect('/schema/script/' . $version);
    }

    public function compare($f3, $params)
    {
        $p = &$f3->ref('POST');
        $s1 = \Valid::toStr($p, 'sel1');
        $s2 = \Valid::toStr($p, 'sel2');


        if ($s1 === $s2) {
            // must be different
        }
        $report = new DiffReport();
        $report->doCompareSchema($s1, $s2);

        $view = $this->getView();
        $view->content = 'compare';
        $view->add(['script' => $report->log]);

        echo $view->render();
    }

    public function scriptAction($version)
    {
        //$view = $this->getView();
        

        $path = $this->getSchemaDir() . $version . '.schema';
        $req = $this->request->getQuery();
        
        if (isset($req['adapt'])) {
            $adapter = $req['adapt'];
            $rdr = static::get_adapt_to($adapter);
            $cfg = $rdr->parseFile($path);
        } else {
            $cfg = XmlPhp::fromFile($path);
        }
        $script = new Script();

        $cfg->generate($script, ['tables' => 'create', 'auto_inc' => true]);

        $cfg->generate($script, ['alter' => true, 'indexes' => true ]);



        $params = ['script' => $script];
        return $this->render('schema', 'schema',$params);  

    }

    private function db_params($p)
    {
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

    static function get_adapt_to($adapter)
    {
        switch ($adapter) :
            case 'mysql':
                $rdr = new AdaptXml(['Sqlite', 'Pgsql'], 'Mysql');
                break;
            case 'pgsql':
                $rdr = new AdaptXml(['Mysql', 'Sqlite'], 'Pgsql');
                break;
            case 'sqlite':
                $rdr = new AdaptXml(['Mysql', 'Pgsql'], 'Sqlite');
                break;
        endswitch;
        return $rdr;
    }

    public function make_db($f3, $p)
    {


        $datadir = $this->getSchemaDir();
        $schema = Valid::toStr($p, 'schema');
        $path = $datadir . $schema . '.schema';

        $sdb = $this->db_params($p);

        $rdr = static::get_adapt_to($sdb['adapter']);

        if ($sdb['adapter'] === 'sqlite' && isset($sdb['dbname'])) {
            $dbpath = $f3->get('sitepath') . $sdb['dbname'];
            $sdb['dbname'] = $dbpath;
        }

        $cfg = $rdr->parseFile($path);
        $db = null;
        try {

            $db = Server::connection($sdb);

            Server::setDefault($db);
            $script = new Script();

            $cfg->generate($script, ['tables' => 'create']);

            $cfg->generate($script, ['alter' => true, 'indexes' => true]);

            $cfg->generate($script, ['alter' => true]);

            $script->run($db);
            
            // load data after the tables and relationions setup
            $cfg->loadData($db, $datadir . $schema . '_dir');
            
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
    public function initdb($f3, $params)
    {
        $view = $this->getView();
        $view->content = 'schema';

        $p = &$f3->ref('POST');


        $dbname = Valid::toStr($p, 'dbname');

        if (!empty($dbname)) {
            $sdb = $this->make_db($f3, $p);
        } else {
            $sdb = null;
        }

        
    }
/*
    function create_site($f3, $args)
    {
        $p = &$f3->ref('POST');
            
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

            $setup = $pkg . 'sites/default/template/';

            // Copy template index.php to webroot

            $content = file_get_contents($setup . 'index.php');
            $content = str_replace('$$_SITE_$$', $site_dir, $content);
            file_put_contents($web . 'index.php', $content);

            // Copy template config.php to root of $sitepath
            $content = file_get_contents($setup . 'config.php');
            $content = str_replace('$$_SITE_$$', $site_dir, $content);
            $content = str_replace('$$_DOMAIN_$$', $f3->get('domain'), $content);
            file_put_contents($sitepath . 'config.php', $content);

            // assets.xml
            copy($setup . 'assets.xml', $sitepath . 'assets.xml');
            // routes.php
            copy($setup . 'routes.php', $sitepath . 'routes.php');
            // Copy Home.php to src in sitepath
            // duplicate all the existing framework views, for alterations
            Dos::copyall($pkg . 'views', $php . 'views');
            Dos::makedir($sitepath . 'src');
            copy($setup . 'Home.php', $sitepath . 'src/Home.php');

            Dos::makedir($sitepath . 'views');
            copy($setup . 'index.phtml', $sitepath . 'views/index.phtml');
            // Copy common locations, build a images and theme assets in webroot, using $site_dir
            Dos::copyall($pkg . 'web/js', $web . 'js');
            Dos::copyall($pkg . 'web/css', $web . 'css');
            Dos::copyall($pkg . 'web/image', $web . $site_dir);


            // create a .secrets.xml
            if (!empty($sdb)) {
                file_put_contents($sitepath . '.secrets.xml', XmlPhp::toXmlDoc(["database" => $sdb]));
            }
        }

        // make a new user (email is essential)

        $view->add(['script' => $msg]);

        echo $view->render();        
    }
 
 */
}
