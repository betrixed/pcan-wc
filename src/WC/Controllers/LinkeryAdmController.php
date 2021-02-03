<?php
namespace WC\Controllers;
/**
 * @author Michael Rynn
 */

use WC\Models\LinkGallery;
use WC\Models\LinkLinkery;

use WC\UserSession;
use WC\Valid;

class LinkeryAdmController extends BaseController
{
use \WC\Mixin\Auth;
use \WC\Mixin\ViewPhalcon;
use \WC\Link\LinkeryData;
    private $editList = [];
    protected $url = '/admin/linkery/';

    public function indexAction()
    {
        $view = $this->getView();
        $m = $view->m;
        $this->linkeryPage($m);
        $m->url = $this->url;
        return $this->render('linkery_adm', 'index');
    }

    public function addEditList($imgid)
    {
        $this->editList[] = $imgid;
    }

    public function getAllowRole() : string {
        return 'Admin';
    }
    /**
     * Return internal dir path for gallery name
     * @param type $galName
     */
    private function getDirPath($gal)
    {
        return $this->getWebDir() . $gal->path;
    }

    /**
     * 
     * all images as a file list attached to view
     *  
     */
    private function scanImages($imageExt)
    {
        $imgdir = $imageExt;
        $dh = opendir($imgdir);
        $imglist = [];
        while (true) {
// create a list of entries if image type
            $entry = readdir($dh);
            if ($entry === false) {
                break;
            }
            $ext = pathinfo($entry, PATHINFO_EXTENSION);
            if ($ext == 'jpg' || $ext == 'png') {
                $imglist[] = $entry;
            }
        }
        closedir($dh);
        $thumbsdir = $imgdir . "/thumbs";
        if (file_exists($thumbsdir) === FALSE) {
            if (mkdir($thumbsdir) == FALSE) {
                return FALSE;
            }
        }
        foreach ($imglist as $path) {
            $this->set_thumb($path, $imgdir, $thumbsdir, 100, 100);
        }
        return $imglist;
    }


    public function postAction()
    {
        $post = $_POST;
        $id = Valid::toInt($post, 'id', null);
        try {
            $ok = true;
            if (!empty($id)) {
                $gal = Linkery::byId($id);
                $this->assignFromPost($post, $gal, false);
                $gal->update();
            } else {
                $gal = new Linkery();
                $this->assignFromPost($post, $gal, true);
                $gal->create();
            }
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
            $ok = false;
        }

        if ($ok) {
            //$this->scanUnregistered($gal);
            UserSession::reroute($this->url . "edit/" . $gal['id']);
            return true;
        }

        $view = $this->getView();
        $view->content = 'linkery_adm/new.phtml';
        $view->linkery = $gal;
        $view->assets(['bootstrap']);
        echo $view->render();
    }


    private function assignFromPost($post, $gal, $isnew)
    {
        $gal['name'] = Valid::toStr($post, 'name', "");
        $gal['description'] = Valid::toStr($post, 'description', null);
    }

    public function newAction()
    {
        $view = $this->getView();
        $m = $view->m;
        $m->linkery = new Linkery();
        return $this->render('linkery_adm', 'new');
    }

    private function getModel($id)
    {
        $gal = Gallery::findFirstByid($id);
        if (!$gal) {
            $this->flash->error("Gallery was not found");

            return $this->dispatcher->forward(array(
                        "controller" => "gallery",
                        "action" => "index"
            ));
        }
        $this->setTagFromGallery($gal);
        $this->view->gal = $gal;
    }

    public function editAction($id)
    {
        $gal = LinkGallery::findFirstById($id);
        if (!empty($gal)) {
              $this->constructView($gal);

            return $this->render('linkery_adm','edit');
        } else {
            $this->flash("Linkery not found: " . $name);
        }
    }

    /**
     * Add Links record to linkery
     * @param type $f3
     * @param type $args
     */
    public function add($f3, $args)
    {
        $post = &$f3->ref('POST');
        $gallid = Valid::toInt($post, 'gallid', null);
        $linkid = Valid::toInt($post, 'linkid', null);

        $link = new LinkLinkery();
        $link['linkid'] = $linkid;
        $link['gallid'] = $gallid;
        try {
            $link->create();
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
        }
        UserSession::reroute("/admin/link/edit/" . $linkid);
    }

    private function constructView($gal, $op = "edit", $isAjax = false)
    {
        $m = $this->getViewModel();
        $m->linkery = $gal;
        $id = $gal->id;
        $m->links = $this->getAllLinks($id);

        $us = $this->user_session;
        
        $us->setKey('linkery', ['id' => $id, 'name' => $gal->name]);
        $m->post = $this->url;

        $select = [];
        $select['edit'] = 'Edit';
        $select['show'] = 'Show';
        $select['hide'] = 'Hide';
        $select['remove'] = 'Remove';

        $m->select = $select;
        $m->select_val = 'edit';
        if ($op == "edit" && !empty($m->links)) {
            $tindex = [];
            $elist = [];
            foreach ($m->links as $le) {
                $tindex[$le->id] = $le;
            }
            foreach ($m->links as $le) {
                $elist[] = $tindex[$le->id];
            }
            $m->elist = $elist;
        }
    }


}
