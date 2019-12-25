<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */

use Pcan\DB\Linkery;
use Pcan\DB\LinkLinkery;
use WC\UserSession;
use WC\Valid;

class LinkeryAdm extends LinkeryCtl
{
use Mixin\Auth;

    private $editList = [];
    protected $url;

    public function __construct()
    {
        parent::__construct();
        $this->viewPost = '/admin' . $this->viewPost;
        $this->url = '/admin/linkery/';
    }

    public function index($f3, $args)
    {
        $view = $this->linkeryIndex($f3, 'linkery_adm/index.phtml');
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    public function addEditList($imgid)
    {
        $this->editList[] = $imgid;
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


    public function post($f3, $args)
    {
        $post = &$f3->ref('POST');
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
                $gal->save();
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


    private function assignFromPost(&$post, $gal, $isnew)
    {
        $gal['name'] = Valid::toStr($post, 'name', "");
        $gal['description'] = Valid::toStr($post, 'description', null);
    }

    public function newRec()
    {
        $view = $this->getView();
        $view->content = 'linkery_adm/new.phtml';
        $view->linkery = new Linkery();
        $view->assets('bootstrap');
        echo $view->render();
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

    public function edit($f3, $args)
    {
        $id = $args['lid'];
        $gal = Linkery::byId($id);
        if ($gal) {
            $view = $this->getView();
            $view->content = 'linkery_adm/edit.phtml';

            $this->constructView($gal);
            $view->assets(['bootstrap']);
            echo $this->view->render();
        } else {
            $this->flash("Linkery not found: " . $name);
        }
    }

    private function constructEdit($galid)
    {
        $image_set = [];
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
            $link->save();
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
        }
        UserSession::reroute("/admin/link/edit/" . $linkid);
    }

    private function constructView($gal, $op = "edit", $isAjax = false)
    {
        $view = $this->getView();
        $view->linkery = $gal;
        $id = $gal['id'];
        $view->links = Linkery::getAllLinks($gal['id']);

        $us = UserSession::instance();
        $us->setKey('linkery', ['id' => $id, 'name' => $gal['name']]);
        $view->post = $this->viewPost;

        $select = [];
        $select['edit'] = ['Edit', 0];
        $select['show'] = ['Show', 0];
        $select['hide'] = ['Hide', 0];
        $select['remove'] = ['Remove', 0];
        $select[$op][1] = 1;

        $view = $this->getView();
        $view->select = $select;



        if ($op == "edit" && count($this->editList) > 0) {
            $tindex = [];
            $elist = [];
            foreach ($image_set as $img) {
                $tindex[$img->id] = $img;
            }
            foreach ($this->editList as $imgid) {
                $elist[] = $tindex[$imgid];
            }
            $view->elist = $elist;
        }
    }


}
