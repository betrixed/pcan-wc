<?php
namespace WC\Controllers;

use WC\Db\{Server, Script, DbQuery};
use WC\{Dos, XmlPhp, App, Assets, Valid, AdaptXml};
use Phalcon\Mvc\Controller;
use WC\Link\SiteBuild;

class SchemaController extends BaseController
{
    const schema_prefix = '/admin/schema';
    use \WC\Mixin\Auth;
    use \WC\Mixin\ViewPhalcon;

    public $sitedir;
    public $schema_dir;
    
    public function getAllowRole() {
        return 'Admin';
    }

    public function indexAction()
    {
        $builder = new SiteBuild($this);
        
        $list = $builder->getSchemaList();

        // use each value as key
        $keyed = [];
        foreach ($list as $value) {
            $keyed[$value] = $value;
        }
        $adapters = ['Mysql' => 'Mysql', 'Pgsql' => 'Pgsql', 'Sqlite' => 'Sqlite'];

        $m = $this->getViewModel();
        $m->post_prefix = self::schema_prefix;

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
        $this->response->redirect(self::schema_prefix . '/script/' . $version);
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
        $req = $this->request->getQuery();
        
        $build = new SiteBuild($this);
        $params = ['schema' => $version];
        $params['adapter'] = $req['adapt'] ?? '';
        
        $script = $build->scriptBuild($params);
        
        return $this->render('schema', 'schema',['script' => $script]);  

    }


    public function make_db(array $p)
    {


        $build = new SiteBuild($this);
        $script = $build->schemaBuild($p);
        
        return $this->render('schema', 'schema', ['script' => $m->script]); 
        
    }
 public function initdbAction()
    {
        $p = $_POST;
        $dbname = Valid::toStr($p, 'dbname');

        if (!empty($dbname)) {
            return $this->make_db($p);
        } 
        
    }
    
    /**
     * parameters: admin_user, admin_pwd, admin_email 
     *    site_folder name
     * @param type $p 
     */
    function createSiteAction()
    {
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
 

}
