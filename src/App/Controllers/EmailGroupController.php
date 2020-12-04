<?php
namespace App\Controllers;
/**
 * @author Michael Rynn
 */

use App\Models\{EmailGroup,EmailTpl,Register};

use WC\{Valid, WConfig, UserSession};
use Soundasleep\Html2Text;

class EmailGroupController extends BaseController
{
use \WC\Mixin\Auth;
use \WC\Mixin\ViewPhalcon;
use \App\Link\EmailData;

    private $editList = [];
    protected $url = '/admin/email_group/';
    protected $groupPath;
    
    public function getTemplates($id) : array {
        $qry = $this->dbq;
        $qry->bindParam('id', intval($id));
        $list = $qry->queryOA('select * from email_tpl where groupid = :id');
        return $list;
    }
    
    public function indexAction()
    {
        $view = $this->getView();
        $m = $view->m;
        $this->indexPage($m);
        $m->url = $this->url;
        return $this->render('email_group', 'index');
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


    /**
     * 
     * all images as a file list attached to view
     *  
     */
    private function scanTemplates($imgdir)
    {
        $dh = opendir($imgdir);
        $imglist = [];
        while (true) {
// create a list of entries if image type
            $entry = readdir($dh);
            if ($entry === false) {
                break;
            }
            $ext = pathinfo($entry, PATHINFO_EXTENSION);
            if ($ext == 'phtml') {
                $imglist[] = $entry;
            }
        }
        closedir($dh);
        return $imglist;
    }


    public function postAction()
    {
        $post = $_POST;
        $id = Valid::toInt($post, 'id', null);
        try {
            $ok = true;
            if (!empty($id)) {
                $gal = EmailGroup::findFirstById($id);
                $this->assignFromPost($post, $gal, false);
                $gal->update();
            } else {
                $gal = new EmailGroup();
                $this->assignFromPost($post, $gal, true);
                $gal->create();
            }
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
            $ok = false;
        }

        if ($ok) {
            $this->reroute($this->url . "edit/" . $gal->id);
            return true;
        }

        $m = $this->getViewModel();
        $m->group = $gal;
        return $this->render('email_group', 'edit_group');
    }

    

    private function assignFromPost($post, $gal, $isnew)
    {
        if ($isnew) {
            $name = Valid::toStr($post, 'name', "");
            $gal->name = $name;
            $gal->path = $this->getWebDir() . $name;
            $this->makeSetsDir($gal->path);
        }
        
        $gal->description = Valid::toStr($post, 'description', null);
       // $gal->last_send = Valid::toDateTime($post, 'last_send');
    }

    public function newAction()
    {
        $m = $this->getViewModel();
        $m->group = new EmailGroup();
        $m->url = $this->url;
        return $this->render('email_group', 'new_group');
    }
    

    public function posttplAction() {
        $post = $_POST;
        $id = Valid::toInt($post, 'id', null);
        $tp = EmailTpl::findFirstById($id);
        $tp->subject = Valid::toStr($post,'subject');
        $tp->description = Valid::toStr($post,'description');
        
        $content = $post['article'];
        $gal = EmailGroup::findFirstById($tp->groupid);
        
        $path = $this->getDirPath($gal) . $this->getLeafPath($tp);
        
        $tp->modified_at = Valid::now();
        $tp->html = $content;
        $ok = $tp->update();
        
        if (!$ok) {
            $this->flash('Update failed');
        }
        file_put_contents($path, $content);
        
        $action = Valid::toStr($post,'preview');
        
        if (!empty($action)) {
            return $this->reroute($this->url . "preview/$id");
        }
        $this->reroute($this->url . "edittpl/$id");
    }

    
    public function previewAction($id) {
        $tp = EmailTpl::findFirstById($id);
        $group = EmailGroup::findFirstById($tp->groupid);
        
        
        $app = $this->app;
        
        $data = new  WConfig();
        $data->fname = 'TEST_FNAME';
        $data->lname = 'TEST_LNAME';
        $prefix = "https://" . $app->domain . "/emailid/";
        $code = $this->link_encode('259',$tp->id);
        $data->link = $prefix . $code;
        $data->date = Valid::today();
        
        
        $params = [ 'm' => $data, 'app' => $app];
        $plates = $this->setupPlates($tp, $group);

        $m = $this->getViewModel();
        $m->group = $group;
        $m->template = $tp;
        
        $m->htmlMsg = static::simpleView($plates->view, $params, $plates);
        $m->textMsg =  Html2Text::convert($m->htmlMsg);
        $qry = $this->dbq;
        
        $sql = <<<EOD
 select B.*, R.mail from register B 
 left outer join reg_mail R  on R.reg_id = B.id
                and R.email_tpl_id = :tid 
EOD;
        $qry->bindParam('tid', $id);
        $targets = $qry->queryOA($sql);
        $m->rego = $targets;
        $m->url = $this->url;
        $m->actions = [
            "No" => "",
            "Mail" => "Add to mail job",
            "Remove" => "Remove from mail job",
            "Delete" => "Unregister"
        ];
        return $this->render('email_group', 'preview');
    }
    public function previewregAction($id, $regid) {
        $tp = EmailTpl::findFirstById($id);
        $group = EmailGroup::findFirstById($tp->groupid);
        $reg = Register::findFirstById($regid);
        
        
        $app = $this->app;
        
        $data = new  WConfig();
        $data->fname = $reg->fname;
        $data->lname = $reg->lname;
        
        $prefix = "https://" . $app->domain . "/emailid/";
        $code = $this->link_encode($regid,$id);
        $data->link = $prefix . $code;
        $data->date = Valid::today();
        
        $params = [ 'm' => $data, 'app' => $app];
        $plates = $this->setupPlates($tp, $group);

        $m = $this->getViewModel();
        $m->group = $group;
        $m->template = $tp;
        
        $m->htmlMsg = static::simpleView($plates->view, $params, $plates);
        $m->textMsg =  Html2Text::convert($m->htmlMsg);
        $qry = $this->dbq;
        
        $sql = <<<EOD
 select B.*, R.mail from register B 
 left outer join reg_mail R  on R.reg_id = B.id
                and R.email_tpl_id = :tid 
EOD;
        $qry->bindParam('tid', $id);
        $targets = $qry->queryOA($sql);
        $m->rego = $targets;
        $m->url = $this->url;
        $m->actions = [
            "No" => "",
            "Mail" => "Add to mail job",
            "Remove" => "Remove from mail job",
            "Delete" => "Unregister"
        ];
        return $this->render('email_group', 'preview');
    }
    
    public function edittplAction($id) {
        $tp = EmailTpl::findFirstById($id);
        if (empty($tp)) {
            $this->flash("template id $id not found");
            return $this->notAuthorized();
        }
        $m = $this->getViewModel();
        $m->template = $tp;
        $m->group = EmailGroup::findFirstById($tp->groupid);
        $m->url = $this->url;
        $path = $this->getDirPath($m->group) . $this->getLeafPath($tp);
        if (file_exists($path) && (empty($tp->html))) {
            $m->content = file_get_contents($path);
        }
        else if (!empty($tp->html)) {
            $m->content = $tp->html;
        }
        else {
            $m->content = "<p>New</p>";
        }

        return $this->render('email_group','edit_tpl');
    }
    

    public function newtplAction() {
        $post = $_POST;
        $groupid = Valid::toInt($post, 'groupid', null);
        $tpl = new EmailTpl();

        $tpl->groupid = $groupid;
         $tpl->name = Valid::toStr($post,'name');
        $tpl->description = Valid::toStr($post,'description');
        $tpl->subject = Valid::toStr($post, 'subject');
        $tpl->modified_at = Valid::now();
        
        $ok = $tpl->create();
        if (!$ok) {
            $this->flash('Failed to create template record');
        }
        return $this->reroute($this->url . 'edittpl/' . $tpl->id );
        
    }
    private function getModel($id)
    {
        $gal = EmailGroup::findFirstById($id);
        if (!$gal) {
            $this->flash->error("Gallery was not found");

            return $this->dispatcher->forward(array(
                        "controller" => "email_group",
                        "action" => "index"
            ));
        }
        $this->setTagFromGallery($gal);
        $this->view->gal = $gal;
    }

    
    public function editAction($id)
    {
        if (is_numeric($id)) {
            $gal = EmailGroup::findFirstById($id);
        }
        else {
            $gal = EmailGroup::findFirstByName($id);
        }
        if (!empty($gal)) {
              $this->constructView($gal);

            return $this->render('email_group','edit_group');
        } else {
            $this->flash("Email_Group not found: " . $id);
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
            $link->save();
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
        }
        UserSession::reroute("/admin/link/edit/" . $linkid);
    }

    private function constructView($gal, $op = "edit", $isAjax = false)
    {
        $m = $this->getViewModel();
        $m->url = $this->url;
        $m->group = $gal;
        $id = $gal->id;
        $m->templates = $this->getTemplates($id);

        $us = $this->user_session;
        
        $us->setKey('email_group', ['id' => $id, 'name' => $gal->name]);
        $m->post = $this->url;

        $select = [];
        $select['edit'] = 'Edit';
        $select['remove'] = 'Remove';

        $m->select = $select;
        $m->select_val = 'edit';
        
    }
    public function queueAction()
    {
        $post = $_POST;
        $id = Valid::toInt($post, 'id');
        if ($id > 0) {
            $tp = EmailTpl::findFirstById($id);
            $db = $this->db;
            foreach ($post as $key => $value) {
                if (substr($key, 0, 3) === 'chk') {
                    $regid = (int) substr($key, 3);
                    // ensure mail  job record exists
                    $sql = "insert into reg_mail(reg_id, email_tpl_id, mail) values ($regid,$id,1)"
                            . " on duplicate key update mail=1";
                    $db->execute($sql);
                }
            }
            
        }
        $this->reroute($this->url . "preview/$id");
    }

}
