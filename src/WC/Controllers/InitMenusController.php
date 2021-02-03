<?php
namespace Pcan;
/** class and functions for (re-)creating menus
 * sql menu_item table, 
 * creating generated drop down menus
 * and populating menu_item from xml file
 */
use WC\DB\Server;
use WC\UserSession;
use WC\WConfig;
use WC\AdaptXml;

use Pcan\DB\MenuItem;
use Pcan\DB\BlogCat;
use Pcan\Models\MenuTree;

/**
 * Use in CLI mode, 
 * pre-configure menu views
 */
class InitMenusController extends \Pcan\Controller
{

    private $navbar_f3;
    private $navbar_plates;
    private $drop_down_params;
    
    /**
     * CLI complete menu reset
     * @param type $f3
     * @param type $args
     */
    public function configure($f3, $args) {
        $path = $f3->get('sitepath');
        $this->doAll($path);
    }

    /**
     * Create a new menu item, provided array values, return new record key
     */
    static function create_record($midata)
    {
        $mi = new MenuItem();
        foreach ($midata as $fname => $fvalue) {
            $mi[$fname] = $fvalue;
        }
        $mi->create();
        return $mi['id'];
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


    function create_menu_table()
    {
        $db = $this->db;
        $adapter = $db->driver();
        
        if ($adapter === 'mysql') {
            $rdr = new AdaptXml('Pgsql', 'Mysql');
        } else {
            $rdr = new AdaptXml('Mysql', 'Pgsql');
        }
        $pkg_path = $this->app->vendor_dir;
        $path =  $pkg_path . '/sites/default/schema/initdb.schema';
        $schema = $rdr->parseFile($path);
        
        $tdef = $schema->getTable('menu_item');
        $script = new \WC\DB\Script();
        $actions = [
                ['drop-tables' => true], 
                ['tables' => true, 'auto_inc' => true], 
                ['alter' => true, 'indexes' => true, 'auto_inc' => true]
        ];
        foreach( $actions as $stage) {
            $tdef->generate($script, $stage);
        }
        echo $script;
        $script->run(Server::db());
        
        static::create_record(
                ['id' => -1, 'caption' => 'ANCHOR']);
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



    function create_cat_menu($cat, $generate, $pid, $limit = 0)
    {
        // get all articles with category news, order by recency
        $db = Server::db();

        $catrec = BlogCat::bySlug($cat);

        if (empty($catrec)) {
            return;
        }
        $rowslimit = ($limit !== 0) ? " LIMIT $limit" : "";
        $sql = <<<EOD
 select b.title_clean, b.title, b.date_published as pdate
     from blog b join blog_to_category bc on bc.blog_id = b.id
     where bc.category_id = ? 
     order by pdate desc $rowslimit
EOD;
        $rset = $db->exec($sql, $catrec['id']);
        if (empty($rset)) {
            return;
        }
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


    
    function doExtend($mt) {
        
    }
    public function buildAction($templatePath)
    {
        
        $this->create_menu_table();

        $data = WConfig::fromXml($templatePath . "/menus.xml");
       
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
        $code = TagViewHelper::init() . PHP_EOL;
        
        $this->navbar_f3 = $code;
        $this->navbar_plates = $code;
        
        foreach($this->drop_down_params as $params) {
            $logid = isset($params['role']) ? $params['role'] : null;
            if (!empty($logid)) {
                $code = "<?php if(UserSession::isLoggedIn('$logid')): ?>" . PHP_EOL;
                $this->navbar_f3 .= $code;
                $this->navbar_plates .= $code;
                unset($params['role']);
            }
            $this->navbar_f3 .= TagViewHelper::dropDown($params) . PHP_EOL;
            $this->navbar_plates .= PlatesForm::dropDown($params) . PHP_EOL;
            if (!empty($logid)) {
                $code = "<?php endif ?>" . PHP_EOL;
                $this->navbar_f3 .= $code;
                $this->navbar_plates .= $code;
            }       
        }
        $jobs = ['/views/generated' => $this->navbar_f3, '/views/plates_generated' => $this->navbar_plates];
        foreach( $jobs as $dir => $content)
        {
            $viewPath = $templatePath . $dir;
            if (!file_exists($viewPath)) {
                mkdir($viewPath,0755,true);
            }    
            file_put_contents($viewPath . '/dropdowns.phtml', $content);
        }
        MenuTree::resetMenuCache('');
    }

}


