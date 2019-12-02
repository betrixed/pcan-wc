<?php

namespace Pcan\Models;

use Pcan\Models\Menu;
use Pcan\DB\MenuItem;
use WC\DB\Server;

/**
 * MenuTree is a tree of \Models\Menu
 */
class MenuTree  {
    
    static public function deleteGlob($globpath) {
        foreach(glob($globpath) as $file) {
            unlink($file); 
        }
        
    }
    static public function cleanTmp() {
        $f3 =  \Base::instance();
        $tmp = $f3->get('TEMP');
        static::deleteGlob($tmp . "*.php");
        static::deleteGlob($tmp . "*.menu");
    }
    static public function getCacheName($menuName)
    {
        $f3 =  \Base::instance();
        $tmp = $f3->get('TEMP');
        if (is_numeric($menuName)) {
            $menuName = "id_" . intval($menuName);
        }
        if (strrpos($tmp,'/',-1) !== strlen($tmp)-1) {
            $tmp .= '/';
        }
        $menufile = $tmp . "menu_" . $menuName . ".menu";
        return $menufile;

    }
    static public function resetMenuCache($menuName)
    {
        static::cleanTmp();
        /** $menufile = static::getCacheName($menuName);
        if (file_exists($menufile)) {
            unlink($menufile);
        }
        */
        
    }
    static public function getMainMenu($menuName)
    {
        $menufile = static::getCacheName($menuName);
        
        if (is_file($menufile))
        {
            return  unserialize(file_get_contents($menufile));
        }
        else {
            $menuTree = static::getMenuId($menuName);
            file_put_contents($menufile, serialize($menuTree));
            return $menuTree;
        }
        
    }
    
    static public function getIdParent($id) {
        $chm = MenuItem::findById($id);
        if ($chm) {
            return static::getMainMenu($chm['parent']);
        }
        else {
            return false;
        }
    }
    static public function getMenuId($menuName)
    {
        
        if (is_numeric($menuName)) {
            $root_field = 'id';
            $params = [':root'=> intval($menuName) ]; 
        }
        else {
            $root_field = 'caption';
            $params = [':root'=> $menuName ]; 
        }
           
$menu_sql = <<<EOD
WITH RECURSIVE submenus(id, parent, serial, caption, level) AS (
 SELECT
    id, parent, serial, caption, 0 as level
 FROM
     menu_item
 WHERE
     $root_field = :root
 UNION
    SELECT 
        m.id, m.parent, m.serial, m.caption, p.level + 1
 FROM
    menu_item m
 INNER JOIN submenus p ON p.id = m.parent and m.id <> -1
)
SELECT sm.level, mi.* from submenus sm join menu_item mi on mi.id = sm.id
EOD;

        $db = Server::db();       
        $results = $db->exec($menu_sql, $params);

        $topItem = null;
        $prevLevel = null;
        $thisLevel = [];
        $level = -1;
        $parent = -1;
        $parentMenu = null;
        foreach($results as $row)
        {
            $item = new Menu();
            $item->id = intval($row['id']);
            $rowLevel = $row['level'];
            if ($rowLevel > $level)
            {
                $level = $rowLevel;
                if ($level == 0) $topItem = $item;
                $prevLevel = $thisLevel;
                $thisLevel = [];
                $parent = 0;
            }        
            $item->action = isset($row['action']) ? $row['action'] : '';
            $item->controller = isset($row['controller']) ? $row['controller'] : '';

           $thisLevel[$item->id] = $item;

            $parentId = isset($row['parent']) ? intval($row['parent']) : 0;
            if (($parentId > 0) && !empty($prevLevel) && array_key_exists($parentId, $prevLevel))
            {
                if ($parent != $parentId)
                {
                    $parentMenu = $prevLevel[$parentId];
                }        
            }
            if ($parentMenu)
            {
               $parent = $parentId;
               $item->parent = $parentMenu;
               $parentMenu->addItem($item);
            }           
            $item->caption = isset($row['caption']) ? $row['caption'] : "";
            $item->serial = intval($row['serial']);
            $item->class = isset($row['class']) ? $row['class'] : "";
            
            if (isset($row['user_role']))
            {
                $item->restrict = $row['user_role'];
            }
        }
        if (is_null($topItem))
        {
            $topItem = new Menu();
            $topItem->caption = "Home";
            $topItem->action="index";
            $topItem->controller="index";
        }
        return $topItem;
    }
};
