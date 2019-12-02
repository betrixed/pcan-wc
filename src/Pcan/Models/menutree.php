<?php

namespace WC\Models;

use WC\Models\Menu;
use WC\DB\MenuItem;
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
        $menufile = $tmp . "/menu_" . $menuName . ".menu";
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
            $menuid = intval($menuName);
$menu_sql = <<<EOD
select 0 as level, M.* from menu_item M
    join (select distinct L1.id as h1, L1.serial
        from  menu_item L1  
        where L1.id = :c1
    ) J1 on M.id = h1
UNION
select 1 as level, M.* from menu_item M
    join (select distinct L2.id as h1, L1.id as h0, L2.serial
        from menu_item L1
        left outer join menu_item L2 on L2.parent = L1.id
        where L1.id = :c2
    ) J1 on M.id = h1
UNION
select 2 as level, M.* from menu_item M
    join (select distinct L3.id as h1, L2.id as h0, L3.serial
        from menu_item L1
        left outer join menu_item L2 on L2.parent = L1.id
        left outer join menu_item L3 on L3.parent = L2.id
        where L1.id = :c3
    ) J1 on M.id = h1      
UNION
select 3 as level, M.* from menu_item M 
    join (select distinct L4.id as h1, L3.id as h0, L4.serial
        from menu_item L1
        left outer join menu_item L2 on L2.parent = L1.id
        left outer join menu_item L3 on L3.parent = L2.id
        left outer join menu_item L4 on L4.parent = L3.id
         where L1.id = :c4
    ) J1 on M.id = h1         
EOD;

    $params = [':c1'=> $menuid, ':c2'=> $menuid,
                ':c3'=> $menuid, ':c4'=> $menuid ];            
        }
        else {
$menu_sql = <<<EOD
select 0 as level, M.* from menu_item M
    join (select distinct L1.id as h1, L1.serial
        from  menu_item L1  
        where L1.parent = -1 and L1.caption = :c1
    ) J1 on M.id = h1
UNION
select 1 as level, M.* from menu_item M
    join (select distinct L2.id as h1, L1.id as h0, L2.serial
        from menu_item L1
        left outer join menu_item L2 on L2.parent = L1.id
        where L1.parent = -1 and L1.caption = :c2
    ) J1 on M.id = h1
UNION
select 2 as level, M.* from menu_item M
    join (select distinct L3.id as h1, L2.id as h0, L3.serial
        from menu_item L1
        left outer join menu_item L2 on L2.parent = L1.id
        left outer join menu_item L3 on L3.parent = L2.id
        where L1.parent = -1 and L1.caption = :c3
    ) J1 on M.id = h1      
UNION
select 3 as level, M.* from menu_item M 
    join (select distinct L4.id as h1, L3.id as h0, L4.serial
        from menu_item L1
        left outer join menu_item L2 on L2.parent = L1.id
        left outer join menu_item L3 on L3.parent = L2.id
        left outer join menu_item L4 on L4.parent = L3.id
         where L1.parent = -1 and L1.caption = :c4
    ) J1 on M.id = h1         
EOD;

    $params = [':c1'=> $menuName, ':c2'=> $menuName,
                ':c3'=> $menuName, ':c4'=> $menuName ];
        }
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
