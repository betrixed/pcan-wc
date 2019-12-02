<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */

use Pcan\DB\MenuItem;

use WC\DB\Server;
use WC\UserSession;
use WC\Valid;

class MenuAdm extends Controller
{

    protected $url = '/admin/menu/';

    protected function setView($action)
    {
        $view = $this->view;
        $view->content = $action . ".phtml";
        $view->url = $this->url;
    }

    public function beforeRoute() {
        if (!$this->auth()) {
            return false;
        }
    }
    
    protected function buildAssets()
    {
        $this->view->assets('bootstrap');
    }

    public function resetCache($name)
    {
        \Models\MenuTree::resetMenuCache($name);
    }

    /**
     * Post a menu item record
     */
    public function postItem($f3, $args)
    {
        $post = &$f3->ref('POST');

        $id = Valid::toInt($post, 'id', 0);
        if (!empty($id)) {
            $item = MenuItem::findById($id);
        } else {
            $item = new MenuItem();
        }
        $item['caption'] = Valid::toStr($post, 'caption', null);
        $item['controller'] = Valid::toStr($post, 'controller', null);
        $item['action'] = Valid::toStr($post, 'action', null);
        $item['class'] = Valid::toStr($post, 'class', null);
        $item['user_role'] = Valid::toStr($post, 'user_role', null);
        $item['parent'] = Valid::toInt($post, 'parent', null);
        $item['serial'] = Valid::toInt($post, 'serial', 0);
        $item['lang_id'] = 1;
        try {
            if (empty($id)) {
                $item->save();   
            } else {
                $item->update();
            }
            $this->flash("Saved Menu Item");
            $ok = true;
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
            $ok = false;
        }
        if ($ok) {
            UserSession::reroute($this->url . 'item/' . $id);
            return false;
        }

        $view = $this->view;
        $view->item = $item;
        $this->setView('menu/submenu');
        $this->buildAssets();
        echo $view->render();
    }

    /**
     * Get a new Menu form
     */
    public function itemNew($f3, $args)
    {
        $request = &$f3->ref('REQUEST');
        $rootid = Valid::toInt($request, 'm0', null);
        $view = $this->view;
        $view->rootid = $rootid;
        $this->setView('menu/submenu');
        $view->item = new MenuItem();
        if (!empty($rootid)) {
            $view->item['parent'] = $rootid;
        }
        $this->buildAssets();
        echo $view->render();
    }

    // Set up for subitem new or edit
    public function subitem()
    {

        $view = $this->view;

        if ($this->request->isPost()) {
            $req = $this->request;

            $textId = $req->getPost('id', 'int');

            $id = is_numeric($textId) ? intval($textId) : 0;
            $isMenu = false;

            $controller = $req->getPost('controller', 'striptags');
            $mclass = $req->getPost('class', 'striptags');
            if ($id > 0) {
                $item = MenuItem::findFirst($id);
                $isMenu = is_null($item->controller);
            } else {
                $item = new MenuItem();
                $isMenu = (is_null($controller) || (strlen($controller) == 0)) ? true
                            : false;
            }
            if (is_null($mclass) || strlen($mclass) == 0)
                $mclass = 'noclass';
            if (!$isMenu) {
                $item->setAction($req->getPost('action', 'striptags'));
                $item->setController($controller);
            }
            $item->setClass($mclass);
            $item->setUserRole($req->getPost('user_role', 'striptags'));
            $item->setCaption($req->getPost('caption', 'striptags'));
            $item->setLangId($req->getPost('lang_id', 'int'));

            if (!$item->save()) {
                $messages = $item->getMessages();

                foreach ($messages as $message) {
                    $this->flash->error($message);
                }
            } else {
                $msg = ($id > 0) ? 'Menu updated' : 'Menu Created';
                $this->flash->success($msg);
            }
            $view->isMenu = $isMenu;
            $view->menuId = $id;
            $menuView = $isMenu ? 'menu/submenu' : 'menu/subitem';
            $this->setView($menuView);
            $form = new MenuItemForm($item, ['isMenu' => $isMenu]);
        } else {
            $isMenu = false;
            $view->menuId = null;
            $view->isMenu = $isMenu;
            $this->setView('menu/item');
            $form = new MenuItemForm(null, ['isMenu' => $isMenu]);
        }
        $this->buildAssets();

        $view->form = $form;
    }

    /**
     * List menu root items
     */
    public function menuList()
    {
        $db = Server::db();
        $sql = <<<DOD
select distinct  caption as name,  id from menu_item M 
   where parent = -1 and id <> -1 order by name
DOD;
        $qres = $db->exec($sql);
        $view = $this->view;
        $view->menulist = $qres;
        $this->setView('menu/index');
        $this->buildAssets();
        echo $view->render();
    }

    public function reset($f3, $args)
    {
        $put = @$f3->ref('REQUEST');
        $rootid = Valid::toInt($put, 'm0', null);
        if (!empty($rootid)) {
            $menu = MenuItem::findById($rootid);
            if ($menu) {
                $this->resetCache($menu->caption);
            }
        }
        UserSession::reroute($this->url . 'edit?m0=' . $rootid);
    }

    protected function getMenuQuery($sql)
    {
        $db = $this->db;
        $stmt1 = $db->query($sql);
        $stmt1->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        $results1 = $stmt1->fetchAll();
        return $results1;
    }

    private function newMenuLinkForm()
    {
        $form = new MenuLinkForm();

        $msql1 = <<<EOD
    select M.id, M.caption 
        from menu_item M 
        
EOD;

        $results1 = $this->getMenuQuery($msql1);
        $form->makeSelectList($results1, "menu_item_id", "Menu");

        $msql2 = <<<EOD
    select M.id, M.caption from menu_item M 
        where M.controller is null or M.controller='' 
EOD;
        $results2 = $this->getMenuQuery($msql2);
        $form->makeSelectList($results2, "menu_top_id", "Parent");
        return $form;
    }

    private function validatorLinkForm()
    {
        $form = new MenuLinkForm();
        $form->makeText("menu_item_id", "Menu");
        $form->makeText("menu_top_id", "Menu");
        $form->setValidation($form::getLinkValid());
        return $form;
    }

    public function deleteItem($f3, $args)
    {
        $form = @$f3->ref('POST');
        $id = Valid::toInt($form, 'id', 0);
        if ($id > 0) {
            $db = Server::db();
            $ok = true;
            $params[':id'] = $id;
            try {
                $db->exec("delete from menu_link where menu_item_id = :id", $params);
                $db->exec("delete from menu_item where id = :id", $params);
            } catch (\PDOException $e) {
                $err = $e->errorInfo;
                $this->flash($err[0] . ": " . $err[1]);
                $ok = false;
            }
            if ($ok) {
                $this->flash('Menu item deleted ' . $id);
                UserSession::reroute($this->url);
                return false;
            } else {
                $this->flash('Deletion error');
                UserSession::reroute($this->url . 'item/' . $id);
            }
        }
    }

    public function linkAction()
    {
        $this->buildAssets();
        $view = $this->view;
        $this->setView('menu/link');

        $view->allowUnlink = false;
        if ($this->request->isGet()) {
            //* list of menus 

            $idtext = $this->request->get('id', 'int');
            $linktext = $this->request->get('link', 'int');
            $form = $this->newMenuLinkForm();

            if (strlen($idtext) > 0 && strlen($linktext) > 0) {
                $rec = MenuLink::findFirst(
                                ['conditions' => 'menu_item_id = ?1 and menu_top_id = ?2',
                                    'bind' => [
                                        1 => intval($idtext),
                                        2 => intval($linktext)
                                    ]]
                );
                if ($rec) {
                    $form->setEntity($rec);
                    $this->view->allowUnlink = true;
                    $this->view->id = $rec->menu_item_id;
                    $this->view->link = $rec->menu_top_id;
                }
            } else {
                if (strlen($idtext) > 0) {
                    $element = $form->get('menu_item_id');
                    $element->setDefault($idtext);
                }
            }

            $view->form = $form;
        } else if ($this->request->isPost()) {
            $form = $this->validatorLinkForm();
            $view->form = $form;
            $link = new MenuLink();
            $form->bind($_POST, $link);
            if ($form->isValid()) {
                $link->save();
            }
        }
    }

    public function unlinkAction()
    {

        $child = intval($this->request->get('id', 'int'));
        $parent = intval($this->request->get('link', 'int'));

        if (is_int($child) && is_int($parent)) {
            $db = $this->db;

            $success = $db->execute(
                    'delete from menu_link where menu_item_id = ? and menu_top_id = ?', [$child, $parent]);
        }
        $this->buildAssets();
        $this->setView('menu/list');
        $this->listAction();
    }

    protected function getMenuTree($rootid)
    {
        $sql = <<<DOD
select 0 as level, M.* from menu_item M
    join (select  distinct L1.id as h1 from menu_item L1 where L1.id = :r1 and L1.parent = -1 ) J1 on M.id = h1
UNION
    select 1 as level,  M.* from menu_item M 
    join (select distinct L2.id as h1, L1.id as h0, L2.serial
        from menu_item  L1
        left outer join menu_item L2 on L2.parent = L1.id
        where L1.parent = -1  and L1.id = :r2
    ) J1 on M.id = h1
UNION
select 2 as level, M.* from menu_item M
    join (select distinct L3.id as h1, L2.id as h0, L3.serial
        from menu_item L1 
        left outer join menu_item L2 on L2.parent = L1.id
        left outer join menu_item L3 on L3.parent = L2.id
        where L1.parent = -1 and L1.id = :r3
    ) J1 on M.id = h1      
UNION
select 3 as level, M.* from menu_item M 
    join (select distinct L4.id as h1, L3.id as h0, L4.serial
        from menu_item L1 
        left outer join menu_item L2 on L2.parent = L1.id
        left outer join menu_item L3 on L3.parent = L2.id
        left outer join menu_item L4 on L4.parent = L3.id
        where L1.parent = -1 and L1.id = :r3
    ) J1 on M.id = h1   
DOD;

        $db = Server::db();
        $params = [':r1' => $rootid, ':r2' => $rootid, ':r3' => $rootid, ':r4' => $rootid];

        $results = $db->exec($sql, $params);
        $view = $this->view;
        if (count($results) > 0) {
            $view->menuName = $results[0]->caption;
        }

        $view->rootid = $rootid;
        $view->menulist = $results;
    }

    /**
     * Access to all links regardless of which menu tree, by item
     */
    public function listAll($f3, $args)
    {
        $menu_sql = <<<FOD
select * from menu_item
    order by parent, serial
FOD;
        $db = Server::db();
        $results = $db->exec($menu_sql);
        $view = $this->view;
        $view->menulist = $results;
        $this->setView('menu/list');
        $this->buildAssets();
        echo $view->render();
    }

    public function itemEdit($f3, $args)
    {
        $id = $args['id'];
        $item = MenuItem::findById($id);
        if ($item) {
            $isMenu = is_null($item->controller);
            $view = $this->view;
            $view->item = $item;
            $this->setView('menu/submenu');
            $this->buildAssets();
            echo $view->render();
        }
    }

    public function editTree($f3, $args)
    {
        $request = &$f3->ref('REQUEST');
        $rootid = Valid::toInt($request, 'm0', -1);
        $this->getMenuTree($rootid);
        $this->setView('menu/edit');
        $this->buildAssets();
        echo $this->view->render();
    }

}
