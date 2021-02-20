<?php


namespace WC\Link;
use Phalcon\Di\Injectable;
/** 
 * Common script objects to setup a new site 
 * @author michael
 */

use WC\Db\{Script, DbQuery};
use WC\{Dos, XmlPhp, Valid, AdaptXml};

class SiteBuild
{
    protected $container;
    protected $schema_dir;
    
    function __construct(Injectable $services) {
        $this->container = $services;
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
    
    public function getSchemaDir() 
    {
        if (!isset($this->schema_dir)) {
            $app = $this->container->app;
            $this->schema_dir = $app->replace_in($app->module_cfg['schema_path']);
        }
        return $this->schema_dir . '/';
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
    
    public function flash($msg, $extra = null, $status = 'info') {
        $this->container->user_session->flash($msg, $extra, $status);
    }
    
    public function unpackArgs(array $args) : array {
        $p = [];
        foreach($named_args as $str) {
            $duo = explode('=',$str);
            if (count($duo)===2) {
                $p[$duo[0]] = $duo[1];
            }
        }
        return $p;
    }
    
    
    public function makeAdminUser(string $pwd, string $user, string $email)
    {
       UserAuth::makeNewUser($user, $email, $pwd, ['Admin', 'User', 'Editor', 'Guest']);
       $this->flash('User created');
    }
    public function filesBuild(array $p) {
        $admin_user = Valid::toStr($p, 'admin_user');
        $admin_pwd = Valid::toStr($p, 'admin_pwd');
        $admin_email = Valid::toEmail($p, 'admin_email');
        $site_dir = Valid::toStr($p, 'site_dir');
        $app = $this->container->app;
        
        if (!empty($site_dir)) {
            $php = $app->php_dir;
            $sitepath = $php . '/sites/' . $site_dir;
            // make site folder if it doesn't already exist
            Dos::makedir($sitepath);

            $src = $app->pcan_dir;
            $web = $app->web_dir;

            $setup = $pkg . 'sites/setup/template/';

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
    }
    public function buildFiles(array $named_args) {
        $p = $this->unpackArgs($named_args);
        return $this->filesBuild($p);
    }
    public function buildDatabase(array $named_args) : ?Script {
        $p = $this->unpackArgs($named_args);

        return $this->schemaBuild($p);
    }
    
    /** 
     * require schema, adapter
     * @param array $p
     * @return Script|null
     */
    public function scriptBuild(array $p) : ?Script 
    {
        $schema = Valid::toStr($p, 'schema');
        $adapter = Valid::toStr($p, 'adapter');
        $path = $this->getSchemaDir() . $schema . '.schema';
        
        
        if (!empty($adapter)) {
            $rdr = static::get_adapt_to($adapter);
            $cfg = $rdr->parseFile($path);
        } else {
            $cfg = XmlPhp::fromFile($path);
        }
        $script = new Script();

        $cfg->generate($script, ['tables' => 'create', 'auto_inc' => true]);

        $cfg->generate($script, ['alter' => true, 'indexes' => true ]);

        return $script;
    }
    /**
     * requires dbname, dbuser, passwd, adapter, schema
     * @param array $p
     * @throws \Exception
     */
    public function schemaBuild(array $p) : ?Script
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
            $server = $this->container->server;
            
            $db = $server->connection($sdb);
            $server->setDefault($db);

            $script = new Script();

            $cfg->generate($script, ['tables' => 'create']);

            $cfg->generate($script, ['alter' => true, 'indexes' => true]);

            $cfg->generate($script, ['alter' => true]);

            $script->run($db);
            
            // load data after the tables and relationions setup
            $cfg->loadData($db, $datadir . $schema . '_dir');
            
            $query = new DbQuery($db);
            
            $rows = $query->arraySet('select count(*) as gs from user_group');
            if (empty($rows) || $rows[0]['gs'] === 0) {
                $msg = 'Data load fail';
            } else {
                $msg = 'Database created';
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            $this->flash($msg);
            throw $e;
        }
        return $script;
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
}
