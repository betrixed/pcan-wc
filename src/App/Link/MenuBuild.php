<?php

namespace App\Link;

/**
 * Build menus for views
 *
 * @author michael
 */

use WC\Db\Server;
use WC\AdaptXml;
use WC\Db\Script;
use App\Models\MenuItem;
use App\Link\MenuTree;
use WC\HtmlGem;
use App\Link\Path;
use WC\App;
use WC\WConfig;

use Phalcon\Db;
use Phalcon\Db\Column;
use App\Models\BlogCategory;

class MenuBuild
{
    private $navbar_plates;
    private $drop_down_params;
    private $output_dir;
    private $config_files;
    private $schema_file;
    
/**
     * Create a new menu item, provided array values, return new record key
     */
    
    public function __construct(array $options)
    {
        $this->output_dir = $options['output_dir'];
        $this->schema_file = $options['schema'];
        
    }
    static function create_record($midata)
    {
        $mi = new MenuItem();
        foreach ($midata as $fname => $fvalue) {
            $mi->$fname = $fvalue;
        }
        $mi->save();
        return $mi->id;
    }
    static function insert_menus(&$data, $pid)
    {
        $serial = 0;
        foreach ($data as $row) {
            $serial += 10;
            $ct = count($row);
            $caption = ($ct > 0) ? $row[0] : '-';
            $controller = ($ct > 1) ? $row[1] : '';
            $action = ($ct > 2) ? $row[2] : '';
            static::create_record([
                'parent' => $pid, 
                'serial' => $serial,
                'controller' => $controller, 
                'action' => $action,
                'caption' => $caption]);
        }
    }
    function create_link($title, $ref) {
         $split = explode('/', $ref);
         echo " " . print_r($split,true) . PHP_EOL;
         if (count($split) > 1) {
             $controller = $split[0];
             $action = $split[1];
         }
         else  {
             
             $controller = $ref;
             $action = '';
         }
         static::create_record([
                'parent' => -1, 
                'serial' => 0,
                'controller' => $controller, 
                'action' => $action,
                'caption' => $title]); 
         $url = "/" . $ref;
         $this->navbar .= "<li class=\"nav-item\">" . PHP_EOL;
         $this->navbar .= "<a class=\"nav-link\" href=\"$url\">$title</a>" . PHP_EOL;
         $this->navbar .= "</li>" . PHP_EOL;
         
    }
    
    function create_cat_menu($cat, $generate, $pid, $limit = 0)
    {
        // get all articles with category news, order by recency
        $db = Server::db();

        $catrec = BlogCategory::findFirst([
                 'conditions' => 'name_clean = :str:', 
                 'bind' => [ 'str' => $cat],
                 'bindTypes' => [
                     Column::BIND_PARAM_STR
                 ] ]);
        if (empty($catrec)) {
            return;
        }
        $rowslimit = ($limit !== 0) ? " LIMIT $limit" : "";
        $sql = <<<EOD
 select B.title_clean, B.title, R.date_saved as pdate
     from blog B 
     join blog_to_category BC on BC.blog_id = B.id and BC.category_id = :id 
     join blog_revision R on R.blog_id = B.id and R.revision = B.revision
     order by pdate desc $rowslimit
EOD;
        $result = $db->query($sql, ["id" => $catrec->id], ["id" => Column::BIND_PARAM_INT]);
        
        if (empty($result)) {
            return;
        }
        
        $result->setFetchMode(Db\ENUM::FETCH_ASSOC);
        $rset = $result->fetchAll();
        $data = [];
        
        foreach ($rset as $mitem) {
             $title = $generate[0];
             $controller = $generate[1];
             $action = $generate[2];    
            
            if (strpos($title,"{title}") !== false ) {
                $title = str_replace("{title}",$mitem['title'],  $title);
            }
            if (strpos($controller,"{title_clean}")  !== false) {
                $controller = str_replace("{title_clean}",$mitem['title_clean'],$controller);
            }
            if (strpos($action,"{title_clean}")  !== false) {
                $action = str_replace("{title_clean}",$mitem['title_clean'], $action);
            }       
            $data[] = [$title, $controller, $action];
        }
        static::insert_menus($data, $pid);
    }
    
    function add_drop_down($caption, $target = null, $logid = null)
    {

        $pid = static::create_record(
                        ['parent' => -1, 'caption' => $caption]);

        // defer drop_down_generation
        $params = ['root'=>$pid,'title'=>$caption];
        if (!empty($target)) {
            $params['target'] = $target;
        }
        if (!empty($logid)) {
            $params['role'] = $logid;
        }
        $this->drop_down_params[] = $params;
        return $pid;
    }
    function fromMenuConfig($config_file) {
        $app = $this->app;
        $src = Path::endSep($app->SITE_DIR) . $config_file . '.xml';
        $data = WConfig::fromXml($src);
       
        $list = &$data['list'];
        $items = &$data['items'];
        
        foreach($list as $title) {
            $tabs = &$items[$title];
            if (is_null($tabs)) 
            {
                continue;
            }
            
            // $menu is an array of tables if not set 'type'
            $pid = null;
            foreach($tabs as $menu) {
                $role = isset($menu['role']) ?$menu['role'] : null;
                $target = isset($menu['target']) ? $menu['target'] : null;
                if ($menu['type'] === 'list') {
                    if ( is_null($pid)) {
                        $pid = $this->add_drop_down($title, $target, $role);
                    }
                    $mlist = &$menu['list'];
                    static::insert_menus($mlist, $pid);
                }
                else if ($menu['type'] === 'category') {
                    if ( is_null($pid)) {
                        $pid = $this->add_drop_down($title, $target, $role);
                    }
                    $generate = $menu['generate'];
                    $category = $menu['value'];
                    $limit = isset($menu['limit']) ? $menu['limit'] : 0;
                    $this->create_cat_menu($category, $generate, $pid, $limit);
                }
                else if ($menu['type'] === 'item') {
                    $value = $menu['value'];
                    $this->create_link($title, $value);
                    break;
                }
            }
        }
    }
    
    function generate_view($config_file) {
        $code = '<?php ' . PHP_EOL .
                'Use WC\Assets; ' . PHP_EOL .
                'use WC\UserSession ?>' . PHP_EOL;
        
        $this->navbar_plates = $code;
        
        foreach($this->drop_down_params as $params) {
            $logid = isset($params['role']) ? $params['role'] : null;
            if (!empty($logid)) {
                $code = "<?php if(UserSession::isLoggedIn('$logid')): ?>" . PHP_EOL;
                $this->navbar_plates .= $code;
                unset($params['role']);
                $params['class'] = 'dropdown-menu-right';
            }
            $this->navbar_plates .= HtmlGem::dropDown($params) . PHP_EOL;
            if (!empty($logid)) {
                $code = "<?php endif ?>" . PHP_EOL;
                $this->navbar_plates .= $code;
            }       
        }
        if (!file_exists($this->output_dir)) {
            mkdir($this->output_dir);
        }
        $output = $this->output_dir . $config_file . '.phtml';
        file_put_contents($output, $this->navbar_plates);

        MenuTree::resetMenuCache('');
        $this->navbar_plates = null;
        $this->drop_down_params = [];
    }
    function create_menu_table()
    {
        $db = Server::db();
        
        $adapter = $db->getDialectType();

        
        //$adapter = $dbcfg['adapter'];
        
        if ($adapter === 'mysql') {
            $rdr = new AdaptXml('Pgsql', 'Mysql');
        } else {
            $rdr = new AdaptXml('Mysql', 'Pgsql');
        }
        

        $schema = $rdr->parseFile($this->schema_file);
        
        $tdef = $schema->getTable('menu_item');
        $script = new  Script();
        $actions = [
                ['drop-tables' => true], 
                ['tables' => true, 'auto_inc' => true], 
                ['alter' => true, 'indexes' => true]
        ];
        foreach( $actions as $stage) {
            $tdef->generate($script, $stage);
        }
        echo $script;
        $script->run(Server::db());
        
        static::create_record(
                ['id' => -1, 'caption' => 'ANCHOR']);
    }
}
