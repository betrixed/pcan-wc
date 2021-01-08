<?php

namespace WC\Controllers;

/**
 * @author Michael Rynn
 */
use WC\Models\Image;
use WC\Models\ImgGallery;
use WC\Models\Gallery;
use WC\Models\Series;
use WC\UserSession;
use WC\WConfig;
use WC\Valid;
use WC\Db\Server;
use WC\Db\DbQuery;
use WC\Mixin\ViewPhalcon;
use Phalcon\Db\Column;

use WC\App;

class ImageOp {

    const THUMBS_DIR = "thumbs";

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
        
        $new_description = $this->controller->purify($this->post, 'desc' . $this->rowid );
        $update = Valid::toDateTime($this->post, 'date' . $this->rowid);

        $image = Image::findFirstById($this->imageid);
        $image->description = $new_description;
        $image->date_upload = $update;
        $image->update();
    }

}

class VisibleOp extends ImageOp {

    public $value;

    public function doThing() {

        $this->setVisible($this->galleryid, $this->imageid, $this->value);
    }

}

class DeleteOp extends ImageOp {

    public function doThing() {
        $controller = $this->controller;
        $img_id = $this->imageid;
        $gallery_id = $this->galleryid;
        $galcount = $controller->countImgGallery($img_id);
        if ($galcount > 1) {
// remove reference
            $controller->removeRef($img_id, $gallery_id);
            $msg = "Removed reference to image: id = " . $img_id;
        } else {
// remove reference, image and file
            $controller->removeRef($img_id, $gallery_id);
            $myimg = Image::findFirstById($img_id);
            $mygal = Gallery::findFirstById($myimg->galleryid);
            $dpath = $controller->getWebDir() . $mygal->path . "/";
            $path = $dpath . $myimg->name;
            $basename = pathinfo($myimg->name, PATHINFO_BASENAME);
            $thumbfile = $basename . '.' . $myimg->thumb_ext;
            $thumbpath = $dpath . ImageOp::THUMBS_DIR . '/' . $thumbfile;
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
                $myimg->delete();
            } else {
                $msg = "Problem with delete of " . $myimg->name;
            }
        }
        if (isset($msg)) {
            $controller->flash($msg);
        }
    }

}

class GalleryAdmController extends BaseController {

    use \WC\Mixin\Auth;
    use \WC\Mixin\ViewPhalcon;
    use \WC\Link\GalleryView;
    use \WC\Mixin\HtmlPurify;
    
    private $syncUrl = 'http://parracan.org';
    private $editList = [];
    protected $url = '/admin/gallery/';
    private $webdir;
    private $galleryPath;

    public function getWebDir(): string {
        if (empty($this->webdir)) {
            $this->webdir = $this->app->web_dir . DIRECTORY_SEPARATOR;
        }
        return $this->webdir;
    }

    public function getGalleryPath() {
        if (empty($this->galleryPath)) {
            $app = $this->app;
            $galpath = Valid::noFrontSlash($app->gallery);
            $this->galleryPath = Valid::endSlash($galpath);
        }
        return $this->galleryPath;
    }
    public function getAllowRole(): string {
        return 'Editor';
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
                            $this->invalid();
                        }
                        return;
                    case 'new':
                        $this->newRec($f3, $args);
                        return;
                }
            }
        }
        $this->invalid();
    }

    /*     * * set the imageid
     * store in UserSession
     */

    public function setidAction($id) {
        if (!empty($id)) {
            $imageid = $id;
            $us = $this->user_session;
            if (!empty($us)) {
                $us->setKey('imageid', $imageid);
                echo $imageid;
            } else {
                echo '0';
            }
        }
    }

    /** Handle actions for POST [ajax] from routes.php for '/admin/gallery/*'    */
    public function h_ajax($f3, $args) {
        if (!$f3->get('AJAX')) {
            return $this->invalid();
        }
        $url = $args['*'];
        $parts = explode('?', $url);
        if (!empty($parts)) {
            $action = explode('/', $parts[0]); //Before the ?
            if (!empty($action)) {
                $fn = $action[0];
                switch ($fn) {
                    case 'setid':
                        $this->$fn($f3, $action);
                        return;
                    case 'imageList' :
                    case 'upload' :
                        $this->$fn($f3);
                        return;
                }
            }
        }
        $this->invalid();
    }

    public function indexAction() {
        $view = $this->getView();
        $m = $view->m;
        $this->pageList($m, Valid::toInt($_GET, 'page', 1));

        $m->title = 'Gallerys List';
        $m->url = $this->url . 'edit/';
        return $this->render('gallery_adm', 'index');
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

    static private function dirFileList($imgdir): array {
        if (!file_exists($imgdir)) {
            return [];
        }
        $dh = opendir($imgdir);
        $imglist = [];
        while (true) {
// create a list of entries if image type
            $entry = readdir($dh);
            if ($entry === false) {
                break;
            }
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if ($ext == 'jpg' || $ext == 'png' || $ext == 'pdf' || $ext == 'gif') {
                $imglist[] = $entry;
            }
        }
        closedir($dh);
        // make newest first
        return array_reverse($imglist);
    }

    /**
     * 
     * all images as a file list attached to view
     *  
     */
    private function scanImages($imgdir) {
        $imglist = static::dirFileList($imgdir);
        $imgdir1 = Valid::endSlash($imgdir);
        $thumbsdir = $imgdir1 . ImageOp::THUMBS_DIR . '/';
        foreach ($imglist as $path) {
            $this->set_thumb($path, $imgdir1, $thumbsdir, 200, 100);
        }
        return $imglist;
    }

    
    protected function checkSizeInfo($dpath, $img, $gal) {
           if (intval($img->getHeight()) === 0 && ($gal->getViewThumbs())) {
                   $thumb_file =   $dpath . self::get_thumb_path($img, $gal);
                   if (file_exists($thumb_file)) {
                       $this->get_sizeinfo($img,$thumb_file);
                       $img->update();
                   }
                }               
      
    }
    private function scanMissing($gal) {
        $dpath = $this->getDirPath($gal);
        $imglist = $this->getImages($gal->id);
        foreach ($imglist as $img) {
            $ipath = $dpath . DIRECTORY_SEPARATOR . $img['name'];
            if (!file_exists($ipath)) {
                $url = $this->syncUrl . "/" . $gal->path . "/" . $img['name'];
                file_put_contents($ipath, fopen($url, 'r'));

            } else {
                    $this->checkSizeInfo($dpath, $img, $gal);
                }
        }
    }

    static function findThumbExt($dpath, $file) {
        $filename = pathinfo($file, PATHINFO_FILENAME);

        $fpath = $dpath . '/' . $file;
        $thumb = $dpath . '/' . ImageOp::THUMBS_DIR . '/' . $filename;

        if (file_exists($thumb . ".png")) {
            $thumb_ext = "png";
        } elseif (file_exists($thumb . ".jpg")) {
            $thumb_ext = "jpg";
        } else {
            $thumb_ext = "";
        }
        return $thumb_ext;
    }

    static function get_thumb_path($img, $gal) {
         $basename = pathinfo($img->name,PATHINFO_FILENAME);
        $ext =  $img->getThumbExt();
        if (empty($ext)) {
                 $ext = 'jpg';
               
        }
        return  "/thumbs/" . $basename. "." . $ext;
    }
    private function scanUnregistered($gal) {
        $dpath = $this->getDirPath($gal);
        if (file_exists($dpath) && is_dir($dpath)) {
            //$fileset = $this->scanImages($dpath);

            $fileset = static::dirFileList($dpath);
            $imglist = $gal->getRelated('Image');
            

            $lookup = [];
            // setup association lookup by file name
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
                $this->checkSizeInfo($dpath, $r,$gal);
                $thumb_ext = static::findThumbExt($dpath, $r->name);
                if ($r->thumb_ext !== $thumb_ext) {
                    $r->thumb_ext = $thumb_ext;
                    $r->update();
                }
            }
        }
    }

    public function postnewAction() {
        $post = $_POST;
        $id = Valid::toInt($post, 'id', null);
        $isnew = empty($id);
        $ok = true;
        try {
            if ($isnew) {
                $gal = new Gallery();
            } else {
                $gal = Gallery::findFirstById($id);
            }
            $this->assignFromPost($post, $gal, $isnew);
            if ($isnew) {
                $gal->create();
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
            return $this->reroute($this->url . "edit/" . $gal->name);
        } else {
            if ($isnew) {
                $view = $this->getView();
                $m = $view->m;
                $m->gallery = $gal;
                $m->url = $this->url;

                return $this->render('gallery_adm', 'new');
            }
        }
    }

    /*
     * Create Image Record for the image file name, found in gallery
     * $gal existing record. Requires full path for $imgfile.
     * 
     */

    private function registerImage($gal, $imgfile, $thumb_ext = "jpg") {
        if (!file_exists($imgfile)) {
            return false;
        }
        $info = pathinfo($imgfile);
        $bname = $info['basename'];
        $extension = strtolower($info['extension']);
        $active = Image::findFirst([
                    'conditions' => "galleryid = :id: AND name = :name:",
                    'bind' => ['id' => $gal->id, 'name' => $bname],
                    'bindTypes' => [Column::BIND_PARAM_INT, Column::BIND_PARAM_STR]
        ]);
        if (!empty($active)) {
            $image_id = $active->id;
        } else {
            $img = new Image();
            $img->galleryid = $gal->id;
            $img->name = $bname;
            // construct real path, get $file_size, $mimi_type, $date_upload, width , height

            if (in_array($extension, ['jpg', 'png', 'gif'])) {
                self::get_sizeinfo($img, $imgfile);
            } else if ($extension == 'pdf') {
                $img->mime_type = 'application/pdf';
            }
            $img->file_size = filesize($imgfile);
            $img->date_upload = date('Y-m-d H:i:s', filemtime($imgfile));
            $img->thumb_ext = $thumb_ext;
            
            $active = $img->create();
            $image_id = $img->id;
            $active = $img; //for return
        }

        $gid = $gal->id;
        $link = ImgGallery::findFirst([
                    'conditions' => 'imageid = :mid: AND galleryid = :gid:',
                    'bind' => ['mid' => $image_id, 'gid' => $gid],
                    'bindTypes' => [Column::BIND_PARAM_INT, Column::BIND_PARAM_INT]
                ]);
        if (empty($link)) {
            $link = new ImgGallery();
            $link->imageid = $image_id;
            $link->galleryid = $gid;
            $link->visible = 1;
            $link->create();
        }
       
        $this->user_session->setKey('imageid', $image_id);
        return $active;
    }

    private function assignFromPost(&$post, $gal, $isnew) {
        if ($isnew) {
            $name = Valid::toStr($post, 'name', "");
            $gal->name = $name;
            $gal->path = $this->getGalleryPath() . $name;
        }
        // ensure paths exist
        $this->makeGalleryDir($gal->path);
        $series = Valid::toInt($post, 'seriesid', null);
        if ($series === 0) {
            $series = null;
        }
        $gal->seriesid = $series;
        $gal->last_upload = Valid::toDateTime($post, 'last_upload');
        $gal->leva_path = Valid::toStr($post, 'leva_path', null);
        $gal->prava_path = Valid::toStr($post, 'prava_path', null);
        $gal->description = Valid::toStr($post, 'description', null);
        $gal->view_thumbs = Valid::toBool($post, 'view_thumbs');
    }

    /**
     * Make new gallery record
     * @param type $f3
     * @param type $args
     */
    public function newAction() {
        $view = $this->getView();
        $m = $view->m;
        $m->url = $this->url;
        $m->gallery = new Gallery();
        echo $this->render('gallery_adm', 'new');
    }

    /** make image and thumbs directory
     *    return bool
     */
    private function makeGalleryDir($galpath): bool {
        $imgdir = $this->getWebDir() . $galpath . "/thumbs";
        if (!file_exists($imgdir)) {
            if (!mkdir($imgdir, 0775, true)) {
                $this->flash("Cannot make path : " . $imgdir);
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @param type $id
     * id is a path, subdirectory of /image/gallery/
     */
    private function getGalleryFiles(WConfig $m, string $name) {
// see if path exists, is registered, if not, make it

        $gal = Gallery::findFirstByName($name);
        if ($gal === false) {
            $this->flash("Gallery not registered : " . $name);
        } else {
            if (!$this->makeGalleryDir($gal->path)) {
                $fileset = [];
            } else {
                $fileset = $this->scanImages($this->getWebDir() . $gal->path);
            }
            $m->gallery = $gal;
            $m->fileset = $fileset;
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
    public function syncAction($name) {
        $m = new WConfig();
        $gal = $this->getGalleryFiles($m, $name);
        if ($gal) {
            $this->scanMissing($gal);
        }
        return $this->reroute($this->url . 'images/' . $name);
    }

    public function scanAction($name) {
        $m = new WConfig();
        $gal = $this->getGalleryFiles($m, $name);
        if ($gal) {
            $this->scanUnregistered($gal);
            return $this->reroute($this->url . 'images/' . $name);
        } else {
            $this->flash("Gallery not found: " . $name);
            return $this->invalid();
        }
    }

    /**
     * Edit just the gallery record
     */
    public function editAction($name) {
        $m = $this->getViewModel();
        $gal = $this->getGalleryFiles($m, $name);
        if ($gal) {
            $prevlink = $gal->leva_path;
            $nextlink = $gal->prava_path;
            $m->prevlink = empty($prevlink) ? null : $this->getGalleryName($prevlink);
            $m->nextlink = empty($nextlink) ? null : $this->getGalleryName($nextlink);
            if (!empty($gal->seriesid)) {
                $series = Series::findFirstById($gal->seriesid);
                $m->indexlink = '/series/' . $series->tinytag;
            }
            $m->series = $this->getSeriesSelect();
            //$view->assets(['bootstrap', 'grid', 'jquery-form', 'gallery-progress', 'imagelist']);
            return $this->render('gallery_adm', 'editgal');
        } else {
            $this->flash("Gallery not found: " . $name);
            return $this->invalid();
        }
    }

    public function invalid() {
        $this->reroute('/error/block');
        return null;
    }
    /** Edit the gallery image list */
    public function imagesAction($name) {
        $m = $this->getViewModel();
        $gal = $this->getGalleryFiles($m, $name);
        if ($gal) {
            $this->constructModel($gal);
            return $this->render('gallery_adm', 'edit');
        } else {
            $this->flash("Gallery not found: " . $name);
            return $this->invalid();
        }
    }

    private function constructEdit($galid) {
        $image_set = [];
    }

    private function getSeriesSelect() {

        $data = (new DbQuery($this->db))->arraySet('select * from series order by name');
        $select[0] = ' ';
        foreach ($data as $row) {
            $select[$row['id']] = $row['name'];
        }
        return $select;
    }

    private function constructModel($galrec, $op = "edit"): WConfig {
        $image_set = $this->getImages($galrec->id);
        $select['noop'] = ' ';
        $select['edit'] = 'Edit';
        $select['show'] = 'Show';
        $select['hide'] = 'Hide';
        $select['remove'] = 'Remove';

        $view = $this->view;

        $m = $view->m;
        $m->select = $select;
        $m->select_op = 'edit';
        $m->series = $this->getSeriesSelect();
        $m->gallery = $galrec;
        $m->images = $image_set;
        $m->post = $this->url;
        $m->isAjax = false;
        $us = $this->user_session;
        $us->read();           
                    
        $m->sessImageId = (!empty($us)) ? $us->getKey('imageid') : 0;
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
        return $m;
    }

    /**
     * Process [ajax] post for Image List Operation
     * @param type $f3
     * @param type $args
     */
    public function imageopAction() {
        $post = $_POST;

        if ($post) {
            $galleryid = Valid::toInt($post, 'galleryid', null);
            $image_op = Valid::toStr($post, 'image_op', null);
            $chkct = Valid::toInt($post, 'chkct', 0);
            $myop = null;
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
                if (!empty($myop)) {
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
            $gal = Gallery::findFirstById($galleryid);
            $m = $this->constructModel($gal, "edit");
            $m->isAjax = true;
            $this->noLayouts();
            return $this->render('partials', 'gallery_adm/file');
        } else {
            $this->flash->error('No Ajax');
            $this->view->model->events = [];
        }
    }

    public function uploadAction() {
//$response->setHeader("Content-Type", "text/plain");
        $post = $_POST;
        $reply = [];
// get the gallery to upload to
        $galleryid = Valid::toInt($post, 'galleryid');
        $gal = Gallery::findFirstById($galleryid);
        $req = $this->request;
        if (empty($gal)) {
            $reply[] = 'No record gallery ' . $galleryid;
        } else if (!$req->hasFiles()) {
            $reply[] = 'No files received';
        } else {
            $toThumbs = Valid::toBool($post, 'thumbs');
            $dest_dir = $this->getWebDir() . $gal->path . '/';
            $thumbs_dir = $dest_dir . ImageOp::THUMBS_DIR . '/';
            if ($toThumbs) {
                $dest_dir = $thumbs_dir;
            }
            $files = $req->getUploadedFiles();
            $upcount = count($files);

            foreach ($files as $file) {
                $fname = $file->getName();
                $fsize = $file->getSize();
                $dest_file = $dest_dir . $fname;
                $file->moveTo($dest_file);
                $reply[] = $fname . ' ' . $fsize;
                if (!$toThumbs) {
                    $imgRec = $this->registerImage($gal, $dest_file);
                    if ($imgRec !== false) {
                        $this->set_thumb($imgRec->name, $dest_dir, $thumbs_dir);
                        if ($gal->getViewThumbs()) {
                            $this->update_sizeinfo($imgRec,$dest_dir, $thumbs_dir);
                        }
                    }
                }
            }
        }
        // reconstruct gallery file list render
        $m = $this->constructModel($gal);
        $m->isAjax = true;
        $m->reply = $reply;
        $this->noLayouts();
        return $this->render('partials', 'gallery_adm/file');
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

static public function get_sizeinfo($img, $filepath)
{
        $info = getimagesize($filepath);
        $img->height = $info[1];
        $img->width =  $info[0];
        $img->mime_type = $info['mime'];
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
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $thumbfile .= ".jpg";
            $isPDF = true;
        } else {
            $isPDF = false;
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
      $gal->name = Valid::toStr($post, 'name');
      $gal->description = Valid::toStr($post, 'description');
      $gal->leva_path = Valid::toStr($post, 'leva_path');
      $gal->prava_path = Valid::toStr($post, 'prava_path');
      $gal->last_upload = Valid::toDateTime($post, 'last_upload');
      $gal->update();
      $this->view->gallery = $gal;
      }
      }
      } */
}
