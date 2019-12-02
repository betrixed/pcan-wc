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
use Pcan\DB\MenuItem;
use Pcan\DB\BlogCat;
use Pcan\Models\MenuTree;


class InitMenus
{

    private $navbar;

    /**
     * Create a new menu item, provided array values, return new record key
     */
    static function create_record($midata)
    {
        $mi = new MenuItem();
        foreach ($midata as $fname => $fvalue) {
            $mi[$fname] = $fvalue;
        }
        $mi->save();
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
        $path = \Base::Instance()->get('sitepath') . 'schema/phub_v1.schema';
        $schema = WConfig::fromXml($path);
        
        $tdef = $schema->getTable('menu_item');
        $script = new \WC\DB\Script();
        $actions = [
                ['drop-tables' => true], 
                ['tables' => true], 
                ['alter' => true, 'indexes' => true, 'auto_inc' => true]
        ];
        foreach( $actions as $stage) {
            $tdef->toSql($script, $stage);
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

        if (!empty($logid)) {
            $this->navbar .= "<check if=\"{{ UserSession::isLoggedIn('$logid') }}\"><true>" . PHP_EOL;
        }
        $atarget = empty($target) ? '' : " target='$target'";
        $this->navbar .= "<drop-down root='$pid' title='$caption' $atarget></drop-down>" . PHP_EOL;
        if (!empty($logid)) {
            $this->navbar .= "</true></check>" . PHP_EOL;
        }
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
    function doAll($templatePath)
    {
        echo "Reset Menus . . ." . PHP_EOL;
        $this->navbar = "<wc:init></wc:init>" . PHP_EOL;
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
        $viewPath = $templatePath . '/views/generated';
        
        if (!file_exists($viewPath)) {
            mkdir($viewPath,0755,true);
        }
        file_put_contents($viewPath . '/dropdowns.phtml', $this->navbar);

        MenuTree::resetMenuCache('');
        echo "OK" . PHP_EOL;
    }

}


