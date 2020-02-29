<?php

namespace Pcan;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;
use Pcan\DB\Image;
use Pcan\DB\ImgGallery;
use Pcan\DB\Gallery;
use WC\UserSession;
use WC\Valid;

class ImageOp {

    protected $controller;
    protected $galleryid;
    public $imageid;
    public $rowid;

    public function init($cont, $galid) {
        $this->controller = $cont;
        $this->galleryid = $galid;
    }

    public function doThing() {
        
    }

}

class EditOp extends ImageOp {

    protected $post;

    public function __construct(&$post) {
        $this->post = &$post;
    }

    public function doThing() {
        // description field changed?
        $new_description = Valid::toStr($this->post, 'desc' . $this->rowid, "");
        $update = Valid::toDateTime($this->post, 'date' . $this->rowid);

        $image = Image::byId($this->imageid);
        $image['description'] = $new_description;
        $image['date_upload'] = $update;
        $image->update();
    }

}

class VisibleOp extends ImageOp {

    public $value;

    public function doThing() {
        ImgGallery::setVisible($this->galleryid, $this->imageid, $this->value);
    }

}

class DeleteOp extends ImageOp {

    public function doThing() {
        $controller = $this->controller;
        $img_id = $this->imageid;
        $gallery_id = $this->galleryid;
        $galcount = Image::getGalleryCount($img_id);

        if ($galcount > 1) {
// remove reference
            Gallery::removeRef($img_id, $gallery_id);
            $msg = "Removed reference to image: id = " . $img_id;
        } else {
// remove reference, image and file
            Gallery::removeRef($img_id, $gallery_id);
            $myimg = Image::byId($img_id);
            $mygal = Gallery::byId($myimg['galleryid']);
            $dpath = $controller->getWebDir() . $mygal->path . "/";
            $path = $dpath . $myimg->name;
            $thumbpath = $dpath . "thumbs" . DIRECTORY_SEPARATOR . $myimg->name;
            $wasDeleted = false;
            if (is_file($path)) {
                $wasDeleted = unlink($path);
// also delete thumbs
                if ($wasDeleted && is_file($thumbpath))
                    unlink($thumbpath);
            } else if (!file_exists($path)) {
                $wasDeleted = true;
            }
            if ($wasDeleted) {
                $msg = "Deleted image " . $myimg->name;
                $myimg->erase();
            } else {
                $msg = "Problem with delete of " . $myimg->name;
            }
        }
    }

}

class GalleryAdm extends GalleryCtl {

    use Mixin\Auth;

    private $layout = "layout_admin";
    private $syncUrl = 'http://parracan.org';
    private $editList = [];
    protected $url;

    public function __construct($f3, $args) {
        parent::__construct($f3, $args);

        $this->viewPost = '/admin' . $this->viewPost;
        $this->url = '/admin/gallery/';

        $view = $this->view;
        $view->nav = "nav_admin";
        $view->layout = $this->layout;
    }

    /** Handle actions for  GET from routes.php for '/admin/gallery/*'    */
    public function h_get($f3, $args) {
        if (!isset($args['*'])) {
            $args['*'] = 'index';
        }
        $url = $args['*'];
        $parts = explode('?', $url);
        if (!empty($parts)) {
            $action = explode('/', $parts[0]);
            if (!empty($action)) {
                $fn = $action[0];
                switch ($fn) {
                    case 'index' :
                        $this->index($f3, $args);
                        return;
                    case 'images':
                    case 'scan':
                    case 'sync':
                    case 'edit':
                        if (count($action) > 1) {
                            $this->$fn($f3, $action[1]);
                        } else {
                            $this->flash('Name required');
                            $this->invalid($f3, $args);
                        }
                        return;
                    case 'new':
                        $this->newRec($f3, $args);
                        return;
                }
            }
        }
        $this->invalid($f3, $args);
    }

     /** Handle actions for POST [ajax] from routes.php for '/admin/gallery/*'    */
    public function h_ajax($f3, $args) {
        if (!$f3->get('AJAX')) {
            return $this->invalid($f3, $args);
        }
        $url = $args['*'];
        $parts = explode('?', $url);
        if (!empty($parts)) {
            $action = explode('/', $parts[0]);
            if (!empty($action)) {
                $fn = $action[0];
                switch ($fn) {
                    case 'imageList' :
                    case 'upload' :
                        $this->$fn($f3);
                        return;
                }
            }
        }
        $this->invalid($f3, $args);
    }
    
    public function index($f3, $args) {
        $view = $this->galleryIndex($f3, 'gallery_adm/index.phtml');
        $view->layout = $this->layout;
        $view->assets(['bootstrap', 'grid']);
        echo $view->render();
    }

    public function addEditList($imgid) {
        $this->editList[] = $imgid;
    }

    /**
     * Return internal dir path for gallery name
     * @param type $galName
     */
    private function getDirPath($gal) {
        return $this->getWebDir() . $gal->path;
    }

    static private function dirFileList($imgdir) {
        $dh = opendir($imgdir);
        $imglist = [];
        while (true) {
// create a list of entries if image type
            $entry = readdir($dh);
            if ($entry === false) {
                break;
            }
            $ext = pathinfo($entry, PATHINFO_EXTENSION);
            if ($ext == 'jpg' || $ext == 'png' || $ext == 'pdf' || $ext == 'gif') {
                $imglist[] = $entry;
            }
        }
        closedir($dh);
        return $imglist;
    }

    /**
     * 
     * all images as a file list attached to view
     *  
     */
    private function scanImages($imgdir) {
        $imglist = static::dirFileList($imgdir);
        $thumbsdir = $imgdir . "/thumbs";
        if (file_exists($thumbsdir) === FALSE) {
            if (mkdir($thumbsdir) == FALSE) {
                return FALSE;
            }
        }
        $imgdir1 = $imgdir . DIRECTORY_SEPARATOR;
        $thumbsdir1 = $thumbsdir . DIRECTORY_SEPARATOR;
        foreach ($imglist as $path) {
            $this->set_thumb($path, $imgdir1, $thumbsdir1, 200, 100);
        }
        return $imglist;
    }

    private function scanMissing($gal) {
        $dpath = $this->getDirPath($gal);
        $imglist = Gallery::getImages($gal->id);
        foreach ($imglist as $img) {
            $ipath = $dpath . DIRECTORY_SEPARATOR . $img['name'];
            if (!file_exists($ipath)) {
                $url = $this->syncUrl . "/" . $gal['path'] . "/" . $img['name'];
                file_put_contents($ipath, fopen($url, 'r'));
            }
        }
    }

    static function findThumbExt($dpath, $file) {
        $filename = pathinfo($file, PATHINFO_FILENAME);

        $fpath = $dpath . DIRECTORY_SEPARATOR . $file;
        $thumb = $dpath . DIRECTORY_SEPARATOR . "thumbs" . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($thumb . ".png")) {
            $thumb_ext = "png";
        } elseif (file_exists($thumb . ".jpg")) {
            $thumb_ext = "jpg";
        } else {
            $thumb_ext = "";
        }
        return $thumb_ext;
    }

    private function scanUnregistered($gal) {
        $dpath = $this->getDirPath($gal);
        if (file_exists($dpath) && is_dir($dpath)) {
            //$fileset = $this->scanImages($dpath);

            $fileset = static::dirFileList($dpath);
            $imglist = Gallery::getImages($gal->id); //should be empty?

            $lookup = [];
            foreach ($imglist as $r) {

                $lookup[$r->name] = $r;
            }
            // go through file list, and add any 'unregistered'
            foreach ($fileset as $file) {
                if (!array_key_exists($file, $lookup)) {
                    $thumb_ext = static::findThumbExt($dpath, $file);

                    $irec = $this->registerImage($gal, $dpath . DIRECTORY_SEPARATOR . $file, $thumb_ext);
                    if ($irec) {
                        $lookup[$irec->name] = $irec;
                    }
                }
            }
            // check for null, or incorrect thumb_ext, but existing thumb file
            foreach ($imglist as $r) {
                $thumb_ext = static::findThumbExt($dpath, $r['name']);
                if ($r['thumb_ext'] !== $thumb_ext) {
                    Image::updateThumbExt($r['id'], $thumb_ext);
                }
            }
            $filename = pathinfo($r->name, PATHINFO_FILENAME);
        }
    }

    public function post($f3, $args) {
        $post = &$f3->ref('POST');
        $id = Valid::toInt($post, 'id', null);
        $isnew = empty($id);
        $ok = true;
        try {
            if ($isnew) {
                $gal = new Gallery();
            } else {
                $gal = Gallery::byId($id);
            }
            $this->assignFromPost($post, $gal, $isnew);
            if ($isnew) {
                $gal->save();
            } else {
                $gal->update();
            }
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
            $ok = false;
        }

        if ($ok) {
            $this->scanUnregistered($gal);
            UserSession::reroute($this->url . "edit/" . $gal->name);
            return true;
        } else {
            if ($isnew) {
                $view = $this->view;
                $view->content = 'gallery_adm/new.phtml';
                $m = $view->model;
                $m->gallery = $gal;
                $view->assets(['bootstrap', 'grid', 'DateTime']);
                echo $view->render();
            }
        }
    }

    /*
     * Create Image Record for the image file name, found in gallery
     * $gal existing record. 
     * $imageFileName existing image.
     */

    private function registerImage($gal, $imgfile, $thumb_ext = "jpg") {
        $info = pathinfo($imgfile);
        $bname = $info['basename'];
        $img = new Image();
        $active = $img->load(["galleryid = :galid AND name = :bname", ':galid' => $gal->id, ':bname' => $bname]);
        if ($active) {
            $image_id = $active->id;
        } else {
            $img->galleryid = $gal->id;
            $img->name = $bname;
            // construct real path, get $file_size, $mimi_type, $date_upload, width , height
            if (file_exists($imgfile)) {
                $sizeinfo = getimagesize($imgfile);
                $img['mime_type'] = $sizeinfo['mime'];
                $img['width'] = $sizeinfo[0];
                $img['height'] = $sizeinfo[1];
                $img['file_size'] = filesize($imgfile);
                $img['date_upload'] = date('Y-m-d H:i:s', filemtime($imgfile));
                $img['thumb_ext'] = $thumb_ext;
                $active = $img->save();
                $image_id = $active->id;
            } else {
                return false;
            }
        }
        $gid = $gal->id;
        $link = new ImgGallery();
        $valid = $link->load(['imageid = :mid AND galleryid = :gid', ':mid' => $image_id, ':gid' => $gid]);
        if (!$valid) {
            $link->imageid = $image_id;
            $link->galleryid = $gid;
            $link->visible = 1;
            $link->save();
        }
        return $active;
    }

    private function assignFromPost(&$post, $gal, $isnew) {
        if ($isnew) {
            $name = Valid::toStr($post, 'name', "");
            $gal['name'] = $name;
            $gal['path'] = Valid::endSlash(Valid::noFrontSlash($this->galleryPath)) . $name;
        }
        $gal['seriesid'] = Valid::toInt($post, 'seriesid', null);
        $gal['last_upload'] = Valid::toDateTime($post, 'last_upload');
        $gal['leva_path'] = Valid::toStr($post, 'leva_path', null);
        $gal['prava_path'] = Valid::toStr($post, 'prava_path', null);
        $gal['description'] = Valid::toStr($post, 'description', null);
        $gal['view_thumbs'] = Valid::toBool($post, 'view_thumbs');
    }

    /**
     * Make new gallery record
     * @param type $f3
     * @param type $args
     */
    public function newRec($f3, $args) {
        $view = $this->view;
        $view->content = 'gallery_adm/new.phtml';
        $view->gallery = new Gallery();
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    private function getModel($id) {
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

    /**
     * 
     * @param type $id
     * id is a path, subdirectory of /image/gallery/
     */
    private function getGalleryFiles($name) {
// see if path exists, is registered, if not, make it
        $db = Server::db();
        $rec = new Gallery();
        $gal = $rec->load("name = '$name'");
        if ($gal === false) {
            $this->flash("Gallery not registered : " . $name);
//$this->response->redirect("gallery/index");
        } else {
            $imageExt = $gal->path;
            $imgdir = $this->getWebDir() . $imageExt;
            if (!file_exists($imgdir)) {
                if (!mkdir($imgdir, 0775, true)) {
                    $this->flash("Cannot make path : " . $imageExt);
//$this->response->redirect("gallery/index");
                    return;
                }
                $fileset = [];
            } else {
// scan files, setup thumbs
                $fileset = $this->scanImages($imageExt);
            }

            $this->view->fileset = $fileset;
            return $gal;
        }
    }

    private function registerMissing($file, $reg, $gal) {
        foreach ($reg as $r) {
            if ($r . name == $file)
                return;
        }
    }

    /**
     * register files that have been unregistered from gallery
     * @param type $name
     */
    public function registerAction($name) {
        
    }

    // pull missing images from master site
    public function sync($f3, $name) {
        $gal = $this->getGalleryFiles($name);
        if ($gal) {
            $this->scanMissing($gal);
        }
        UserSession::reroute($this->url . 'images/' . $name);
    }

    public function scan($f3, $name) {
        $gal = $this->getGalleryFiles($name);
        if ($gal) {
            $this->scanUnregistered($gal);
            UserSession::reroute($this->url . 'images/' . $name);
        } else {
            $this->flash("Gallery not found: " . $name);
            $this->invalid($f3, []);
        }
    }

    /**
     * Edit just the gallery record
     */
    public function edit($f3, $name) {
        $gal = $this->getGalleryFiles($name);
        if ($gal) {
            $view = $this->view;
            $view->content = 'gallery_adm/editgal.phtml';
            $m = $view->model;
            $m->gallery = $gal;
            $m->series = $this->getSerieSelect();
            $view->assets(['bootstrap', 'grid', 'jquery-form', 'gallery-progress', 'imagelist']);
            echo $view->render();
        } else {
            $this->flash("Gallery not found: " . $name);
            $this->invalid($f3);
        }
    }

    /** Edit the gallery image list */
    public function images($f3, $name) {
        $gal = $this->getGalleryFiles($name);
        if ($gal) {
            $view = $this->view;
            $view->content = 'gallery_adm/edit.phtml';
            $this->constructView($gal);
            $view->assets(['bootstrap', 'grid', 'jquery-form', 'gallery-progress', 'imagelist']);
            echo $view->render();
        } else {
            $this->flash("Gallery not found: " . $name);
            return $this->invalid($f3);
        }
    }

    private function constructEdit($galid) {
        $image_set = [];
    }

    private function getSerieSelect() {
        $db = Server::db();
        $data = $db->exec('select * from series order by name');
        $select[0] = ' ';
        foreach ($data as $row) {
            $select[$row['id']] = $row['name'];
        }
        return $select;
    }

    private function constructView($galrec, $op = "edit", $isAjax = false) {

        $image_set = Gallery::getImages($galrec->id);
        $select['noop'] = ' ';
        $select['edit'] = 'Edit';
        $select['show'] = 'Show';
        $select['hide'] = 'Hide';
        $select['remove'] = 'Remove';

        $view = $this->view;

        $m = $view->model;
        $m->select = $select;
        $m->select_op = 'edit';
        $m->series = $this->getSerieSelect();
        $m->gallery = $galrec;
        $m->images = &$image_set;
        $m->post = $this->viewPost;
        $elist = [];
        if ($op == "edit" && count($this->editList) > 0) {
            $tindex = [];

            foreach ($image_set as $img) {
                $tindex[$img->id] = $img;
            }
            foreach ($this->editList as $imgid) {
                $elist[] = $tindex[$imgid];
            }
        }
        $m->elist = $elist;
        return $view;
    }

    /**
     * Process [ajax] post for Image List Operation
     * @param type $f3
     * @param type $args
     */
    protected function imageList($f3) {
        $post = &$f3->ref('POST');

        if ($post) {
            $galleryid = Valid::toInt($post, 'galleryid', null);
            $image_op = Valid::toStr($post, 'image_op', null);
            $chkct = Valid::toInt($post, 'chkct', 0);

            if ($chkct > 0) {
                $sql = "";
                $id = 0;
                switch ($image_op) {
                    case "hide":
                        $myop = new VisibleOp();
                        $myop->value = 0;
                        break;
                    case "show":
                        $myop = new VisibleOp();
                        $myop->value = 1;
                        break;
                    case "remove":
                        $myop = new DeleteOp();
                        break;
                    case "edit":
                        $myop = new EditOp($post);
                        break;
                }
                if (isset($myop)) {
                    $myop->init($this, $galleryid);
                    for ($ix = 1; $ix <= $chkct; $ix++) {
                        $chkname = "chk" . $ix;
                        $chkvalue = Valid::toInt($post, $chkname, 0);
                        if ($chkvalue > 0) {
                            $myop->imageid = $chkvalue;
                            $myop->rowid = $ix;
                            $myop->doThing();
                        }
                    }
                }
            }
            $view = $this->constructView(Gallery::byId($galleryid), "edit", true);
            $view->layout = null;
            $view->content = 'gallery_adm/file.phtml';
            echo $view->render();
        } else {
            $this->flash->error('No Ajax');
            $this->view->model->events = [];
        }
    }

    protected function upload($f3) {
//$response->setHeader("Content-Type", "text/plain");
        $postData = &$f3->ref('POST');
        $reply = [];
// get the gallery to upload to
        $galleryid = filter_var($postData['galleryid'], FILTER_VALIDATE_INT);
        $gal = Gallery::byId($galleryid);
        if ($gal === false) {
            $reply[] = 'failed_: ' . $galleryid;
        } else {
            $toThumbs = Valid::toInt($postData, 'thumbs', 0) === 1 ? true : false;
            $dest_dir = $this->getWebDir() . $gal->path . DIRECTORY_SEPARATOR;
            if ($toThumbs) {
                $dest_dir .= 'thumbs' . DIRECTORY_SEPARATOR;
            }
            $f3->set('UPLOADS', $dest_dir);
            $web = \Web::instance();
            $files = $web->receive(
                    function($file, $formFieldName) {
                if ($file['size'] > (3 * 1024 * 1024)) // if bigger than 3 MB
                    return false;
                return true;
            }, true, false);
            if ($toThumbs && (count($files) > 0)) {
                $reply = [' files were transferred'];
            } else if (count($files) > 0) {
                foreach ($files as $f => $val) {
                    $imgRec = $this->registerImage($gal, $f);
                    if ($imgRec !== false) {
                        $this->set_thumb($imgRec->name, $dest_dir, $dest_dir . "thumbs" . DIRECTORY_SEPARATOR);
                        $reply[] = 'file ' . $f;
                    }
                }
            } else {
                $reply = ['No files were transferred'];
            }
        }
        // reconstruct gallery file list render
        $view = $this->constructView(Gallery::byId($galleryid), $image_op, true);
        $view->content = 'gallery_adm/file.phtml';
        $view->model->reply = $reply;
        echo $view->render();
    }

    static private function crimp_thumb($square, $width_o, $height_o, &$width_t, &$height_t) {
//set dimensions
        if ($width_o > $height_o) {
            $width_t = $square;
//respect the ratio
            $height_t = round($height_o / $width_o * $square);
//set the offset
        } elseif ($height_o > $width_o) {
            $height_t = $square;
            $width_t = round($width_o / $height_o * $square);
        } else {
            $width_t = $height_t = $square;
        }
    }

/// photos_dir = ‘uploads/photos’
/// thumbs_dir = photos_dir . /thumbs
/// squire_size = 150
/// quality = 100 // percent
// from http://www.webxpert.ro/andrei/2009/01/08/thumbnail-generation-with-php-tutorial/
//
    static public function set_thumb($file, $photos_dir, $thumbs_dir, $square_size = 100, $quality = 100) {

        $thumbfile = $thumbs_dir . $file;
        $srcfile = $photos_dir . $file;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext === 'pdf') {
            $thumbfile .= ".jpg";
            $isPDF = true;
        } else {
            $isPDF = false;
        }
        if (!file_exists($thumbs_dir)) {
            mkdir($thumbs_dir);
        }
        if (!file_exists($thumbfile)) {
//get image info
            if (!$isPDF) {
                list($width, $height, $type, $attr) = getimagesize($srcfile);
                //set dimensions
                static::crimp_thumb($square_size, $width, $height, $width_t, $height_t);

                switch ($type) {
                    case IMAGETYPE_GIF: $thumb = imagecreatefromgif($srcfile);
                        break;
                    case IMAGETYPE_JPEG: $thumb = imagecreatefromjpeg($srcfile);
                        break;
                    case IMAGETYPE_PNG: $thumb = imagecreatefrompng($srcfile);
                        break;
                    default: $thumb = null;
                }
                if (!empty($thumb)) {
                    $thumb_p = imagecreatetruecolor($width_t, $height_t);
                    imagecopyresampled($thumb_p, $thumb, 0, 0, 0, 0, $width_t, $height_t, $width, $height);
                    imagejpeg($thumb_p, $thumbfile, $quality);
                }
            } else {
                /*
                  $im = new \Imagick();
                  $im->setResolution(300,300);
                  $im->readImage($srcfile);
                  $im->setCompressionQuality(100);
                  $width = $im->getImageWidth();
                  $height = $im->getImageHeight();

                  static::crimp_thumb($square_size*2, $width, $height, $width_t, $height_t);
                  $im->resizeImage($width_t, $height_t,\Imagick::FILTER_LANCZOS,1, TRUE);
                  $im->setImageFormat('jpg');
                  $im->writeImage($thumbfile);
                  $im->clear();
                  $im->destroy();
                 */
            }
        }
    }

    public function getImage($imgid) {
        $image = Image::findFirst(array(
                    "conditions" => "id=?0",
                    "bind" => array(0 => $imgid)
        ));
        return $image;
    }

    /*
      public function galleryEditAction() {
      $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
      if (!$this->request->isPost()) {
      $idstr = $this->request->getQuery('id', 'string');
      $galid = intval(substr($idstr, 3));
      $gal = Gallery::byId($galid);

      $this->view->gallery = $gal;
      } else {
      $f3 = $this->f3;
      $post = &$f3->ref('POST');
      $galid = Valid::toInt($post, 'id', 0);
      $name = Valid::toStr($post, 'name');

      $gal = Gallery::byId($galid);
      if ($gal) {
      $gal['name'] = Valid::toStr($post, 'name');
      $gal['description'] = Valid::toStr($post, 'description');
      $gal['leva_path'] = Valid::toStr($post, 'leva_path');
      $gal['prava_path'] = Valid::toStr($post, 'prava_path');
      $gal['last_upload'] = Valid::toDateTime($post, 'last_upload');
      $gal->update();
      $this->view->gallery = $gal;
      }
      }
      } */
}
