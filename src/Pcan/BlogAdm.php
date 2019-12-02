<?php
namespace Pcan;
/**
 * @author Michael.Rynn
 */
use WC\UserSession;
use WC\Valid;
use WC\DB\Server;
use Pcan\DB\Blog;
use Pcan\DB\PageInfo;
use Pcan\DB\Links;
use Pcan\DB\Event;


class BlogAdm extends Controller {

    public $url = '/admin/blog/';
    private $editAssets = ['bootstrap', 'SummerNote', 'DateTime', 'jquery-form', 'blog-edit'];
    
    public function beforeRoute() {
        if (!$this->auth()) {
            return false;
        }
    }

    public function edit($f3, $args) {
        $id = $args['bid'];
        $blog = new Blog();
        $active = $blog->load("id = " . $id);
        $view = $this->view;
        $view->blog = $active;
        $this->editForm();
    }

    private static function int_bool($pvar) {
        if (is_null($pvar))
            return 0;
        else
            return 1;
    }

    /**
     * Reference to POSTDATA and blog record. 
     */
    private function setBlogTitle(&$POSTDATA, $blog) {
        $newTitle = strip_tags($POSTDATA['title']);
        $oldTitle = $blog['title'];
        $titleChanged = ($oldTitle !== $newTitle);
        $blog['title'] = $newTitle;
        $newUrl = '';
        $lock_url = filter_var($POSTDATA['lock_url'], FILTER_VALIDATE_INT);

        $oldUrl = $blog['title_clean'];
        $autoUrl = (!empty($lock_url) || strlen($oldUrl) == 0) ? true : false;
        $blogid = $blog['id'];
        if ($autoUrl && !empty($blogid)) {
            $newUrl = strip_tags($POSTDATA['title_clean']);

            if ($newUrl !== $oldUrl) {
                $blog['title_clean'] = Blog::unique_url($blogid, $newUrl);
                $autoUrl = False;
            }
        }

        if ($titleChanged && $autoUrl) {
            $newUrl = Blog::url_slug($blog['title']);
            $blog['title_clean'] = Blog::unique_url($blogid, $newUrl);
        }
    }

    private function setBlogFromPost(&$POSTDATA, $blog) {
        $this->setBlogTitle($POSTDATA, $blog);

        $blog['article'] = $POSTDATA["article"];

        $blog['style'] = $POSTDATA['style'];
        $blog['issue'] = filter_var($POSTDATA['issue'], FILTER_VALIDATE_INT);

        $blog['featured'] = filter_var($POSTDATA['featured'], FILTER_VALIDATE_INT);
        $blog['enabled'] = filter_var($POSTDATA['enabled'], FILTER_VALIDATE_INT);
        $blog['comments'] = filter_var($POSTDATA['comments'], FILTER_VALIDATE_INT);
        $blog['date_published'] = Valid::toDateTime($POSTDATA, 'date_published');
        $blog['date_updated'] = Valid::now();
    }

    private function errorPDO($e, $blogid) {
        $err = $e->errorInfo;
        $this->flash($err[0] . ": " . $err[1]);
        UserSession::reroute('/admin/blog/edit/' . $blogid);
        return false;
    }

    public function editPost($f3, $args) {
        $postData = &$f3->ref('POST');
// check match?
        $check_id = filter_var($postData['id'], FILTER_VALIDATE_INT);
        $blog_rec = new Blog();
        $blog = $blog_rec->load("id = " . $check_id);
        if (!$blog) {
            $this->flash("blog does not exist " . $check_id);
            UserSession::reroute('/admin/blog/index');
            return false;
        }
// set updatable things
        
        $old_tc = $blog['title_clean'];
        $this->setBlogFromPost($postData, $blog);
        $update_link = ($old_tc !== $blog['title_clean']);
        
        try {
            $blog->update();
            
        } catch (\PDOException $e) {
            return $this->errorPDO($e, $blogid);
        }

        $this->view->blog = $blog;
        $blogid = $blog['id'];
        if ($update_link) 
        {
            Links::setBlogURL($blogid, $blog['title_clean']);
        }
        $metatags = Blog::getMetaTags($id);
        $db = Server::db();
        $inTrans = false;
        foreach ($metatags as $mtag) {
// key = metatag-#id
            $tagid = $mtag['id'];
            $content = strip_tags($postData['metatag-' . $tagid]);
            if (is_null($content) || empty($content)) {
// link content record needs deleting ? 
                if (isset($mtag['blog_id'])) {
                    if (!$inTrans) {
                        $db->begin();
                        $inTrans = True;
                    }
                    $sql = "delete from blog_meta where blog_id = :blogid"
                            . " and meta_id = :metaid";
                    $db->exec($sql, [':blogid' => $blogid, ':metaid' => $tagid]);
                }
            } else {
                if (!$inTrans) {
                    $db->begin();
                    $inTrans = True;
                }
                $sql = "replace into blog_meta (blog_id, meta_id, content)"
                        . " values(:blogid, :metaid, :content)";
                $db->exec($sql, [':blogid' => $blogid,
                    ':metaid' => $tagid,
                    ':content' => $content]);
            }
        }
        if ($inTrans) {
            $db->commit();
        }
        UserSession::reroute($this->url . 'edit/' . $blog['id']);
        //$this->editForm();
    }

    public function newRec() {
        $view = $this->view;
        $view->url = $this->url;
        $view->content = 'blog\new.phtml';
        $view->assets($this->editAssets);
        echo $view->render();
    }

    public function postFlag($f3, $args) {
        $post = &$f3->ref('POST');
        $op = Valid::toStr($post, 'flagsel', null);
        $list = [];
        foreach ($post as $name => $value) {
            if (substr($name, 0, 2) === 'op') {
                $list[] = intval(substr($name, 2 ));
            }
        }
        if (count($list) > 0) {
            $db = Server::db();
            $val = 0;
           
            switch ($op) {
                case 'comment':
                    $val = 1;
                case 'noComment':
                    $update = 'comment';
                    break;
                case 'feature':
                    $val = 1;
                case 'noFeature':
                    $update = 'feature';
                    break;
                case 'enable':
                    $val = 1;
                case 'disable':
                    $update = 'enabled';
                    break;
            }
            $sql = "update blog set $update = :val where id = :id" ;
        }
        $params[ ':val'] = $val;
         $db->begin();
        foreach ($list as $id) {
            $params[ ':id' ] = $id;
            $db->exec( $sql,  $params );
        }
        $db->commit();
        $args = $post['args'];
        UserSession::reroute($this->url . 'index/?' .  $args);
    }

    public function postNew($f3, $args) {

        $blog = new Blog();
        $postData = &$f3->ref('POST');

        $us = UserSession::instance();

        $blog['author_id'] = $us->id;
        $blog['date_published'] = date('Y-m-d H:i:s');
        $blog['date_updated'] = date('Y-m-d H:i:s');
        $blog['featured'] = 0;
        $blog['enabled'] = 0;
        $blog['comments'] = 1;

        $this->setBlogTitle($postData, $blog);
        try {
            $blog->save();
            $this->flash("Blog created");
        } catch (\PDOException $e) {
            return $this->errorPDO($e, 'new');
        }
        UserSession::reroute($this->url . 'edit/' . $blog['id']);
    }

    /**
     * Make an edit form for blog $id
     * @param type $id
     * @return type null
     */
    private function editForm() {
        $view = $this->view;
        $view->content = 'blog/edit.phtml';
        $blog = $view->blog;
        $view->isApprover = true; // isApprover()
        
        /* if (!$this->canEdit) {
          $this->response->redirect("blog/comment/" . $id);
          return;
          } */
        //$fileset = $this->getBlogFiles($id);
        //$view->upfiles = $fileset;
        $db = Server::db();
        

        $styles = $db->exec('select style_class, style_name from blog_style');
        $stylelist = [];
        foreach ($styles as $row) {
            $stylelist[$row['style_class']] = $row['style_name'];
        }
        $id = $blog['id'];
        $view->title = '#' . $id;
        $view->stylelist = &$stylelist;
        $view->catset = Blog::getCategorySet($id);
        $view->events = Blog::getEvents($id);
        $view->metatags = Blog::getMetaTags($id);
        $view->url = $this->url;
        $view->assets($this->editAssets);
        echo $view->render();
    }
    
    public function eventUpdate($f3, $args) {
        $post = &$f3->ref('POST');
        $isAjax = $f3->get('AJAX');
        $view = $this->view;
        if (!empty($post) && $isAjax) {
            $blog_id = Valid::toInt($post, 'blogid', null);
            $event_op = Valid::toStr($post, 'event_op','');
            $chkct = Valid::toInt($post, 'chkct', 'int');

            if ($chkct > 0) {
                $sql = "";
                switch ($event_op) {
                    case "enable":
                        $sql = "update event set enabled=1 where id = ?";
                        break;
                    case "disable":
                        $sql = "update event set enabled=0 where id = ?";
                        break;
                    case "remove":
                        $sql = "delete from event where id = ?";
                        break;
                }
                $db = Server::db();
                for ($ix = 1; $ix <= $chkct; $ix++) {
                    $chkname = "chk" . $ix;
                    $chkvalue = Valid::toInt($post, $chkname, 0);
                    if (!empty($chkvalue)) {
                        $db->exec($sql, $chkvalue);
                    }
                }
            }
            $view->events = Blog::getEvents($blog_id);
        } else {
            $this->flash('No Ajax');
            $view->events = [];
        }
        $view->url = $this->url;
        echo TagViewHelper::render('blog/event_dates.phtml');
    }
    
    public function addEvent($f3, $args) {
        $post = &$f3->ref('POST');
        $isAjax = $f3->get('AJAX');
        if (!empty($post) && $isAjax) {
            // make a new event from form, then return new list
            $bid = Valid::toInt($post, 'event_blogid', null);
            if (!empty($bid)) {
                try {
                $event = new Event();
                $event['blogId'] = $bid;
                $event['fromTime'] = Valid::toDateTime($post,'fromDate');
                $event['toTime'] = Valid::toDateTime($post,'toDate');
                $event['enabled'] = 1;
                $event->save();
                }
                catch (\PDOException $e) {
                    $err = $e->errorInfo;
                    $this->flash('New event fail: ' . $err[0] . ' ' . $err[1]);
                }
                $view = $this->view;
                $view->url = $this->url;
                $view->events = Blog::getEvents($bid);
                echo TagViewHelper::render('blog/event_dates.phtml');
            }  
        }
        
        
    }
    public function catTick($f3, $args) {

        $postData = &$f3->ref('POST');
        $isAjax = $f3->get('AJAX');
        if ($postData && $isAjax) {
            $blog_id = filter_var($postData['blogid'], FILTER_VALIDATE_INT);
            $chkct = filter_var($postData['chkct'], FILTER_VALIDATE_INT);
            $db = Server::db();
            $db->begin();
            $id = 0;
            // get all the existing category ids
            $results = $db->exec("select category_id from blog_to_category where blog_id = :blogId", ['blogId' => $blog_id]);
            $hasCategory = [];
            foreach ($results as $row) {
                $hasCategory[$row['category_id']] = true;
            }
            $insertSql = "REPLACE INTO blog_to_category ( category_id, blog_id ) VALUES (:catId, :blogId)";
            // html form only returns the checked rows
            for ($ix = 1; $ix <= $chkct; $ix++) {

                $chkname = "cat" . $ix;
                $chkvalue = filter_var($postData[$chkname], FILTER_VALIDATE_INT);
                if ($chkvalue > 0) {
                    if (!array_key_exists($chkvalue, $hasCategory)) {
                        $params2 = [':blogId' => $blog_id, ':catId' => $chkvalue];
                        $db->exec($insertSql, $params2);
                    } else {
                        $hasCategory[$chkvalue] = false;
                    }
                }
            }
            // delete unconfirmed values
            $deleteSql = "DELETE IGNORE from blog_to_category where category_id = :catId and blog_id = :blogId";
            foreach ($hasCategory as $key => $value) {
                if ($value) {
                    $params2 = [':blogId' => $blog_id, ':catId' => $key];
                    $db->exec($deleteSql, $params2);
                }
            }
            $db->commit();
            $view = $this->view;
            $view->content = 'blog/category.phtml';
            $view->catset = Blog::getCategorySet($blog_id);
        }
        echo $view->render();
    }

    public function index($f3, $args) {

        $view = $this->view;
        $view->content = 'blog/index.phtml';
        $request = $f3->get('REQUEST');

        $numberPage = Valid::toInt($request, 'page', 1);
        $category = Valid::toInt($request, 'catId', 0);
        $orderby = Valid::toStr($request, 'orderby', null);
        $order_field = Blog::viewOrderBy($this->view, $orderby);
        $grabsize = 12;
        $start = ($numberPage - 1) * $grabsize;

        $sql = "select b.*, u.name as author_name,"
                . " count(*) over() as full_count from blog b" .
                " left outer join users u on u.id = b.author_id ";
        $binders = [];
        if ($category > 0) {
            $sql .= " inner join blog_to_category bc on bc.blog_id = b.id and bc.category_id = :catId";
            $binders[':catId'] = $category;
        }
        $sql .= " order by " . $order_field
                . " limit " . $grabsize . " offset " . $start ;
        $db = Server::db();
        $results = $db->exec($sql, $binders);
        
        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;
        $paginator = new PageInfo($numberPage, $grabsize, $results, $maxrows);
        $view->page = $paginator;
        $view->args = $f3->get('SERVER.QUERY_STRING');
        $cat_items = $db->exec("select id, name from blog_category");
        $view->catItems = $cat_items;
        $view->catId = $category;
        $view->isEditor = true;
        $view->assets('bootstrap');
        echo $view->render();
    }
     public function verify($f3, $args) {
        $post = &$f3->ref('POST');
        $data =  $post['article'];
        try {
            $images = Blog::imageFiles($data);
            echo  json_encode($images);
        } catch (Exception $ex) {
            echo  json_encode($ex);
        }
     }
    public function exportPost($f3, $args) {
        $post = &$f3->ref('POST');
        $op = Valid::toStr($post, 'bksel', null);
        $list = [];
        foreach ($post as $name => $value) {
            if (substr($name, 0, 2) === 'op') {
                $list[] = intval(substr($name, 2 ));
            }
        }
        switch ($op) {
            case 'newbackup':
                $val = 1;
                break;
            case 'delbackup':
                $val = 2;
                break;
            case 'backupdel':
                $val = 3;
        }
        if (!empty($list)) {
             $db = Server::db();
             $bup = &$f3->ref('secrets.backups');
             $path = $f3->get('php') . "/" . $bup['path'] . DIRECTORY_SEPARATOR;
             if (!file_exists($path)) {
                mkdir($path,0777, true);
             }
             if ($op === 'newbackup') {
                foreach ($list as $id) {
                    $pack = Blog::export($id, $path);
                }
            }
            else if ($op === 'delbackup') {
                // replace existing backup
            }
            else if ($op === 'backupdel') {
                foreach ($list as $id) {
                    $pack = Blog::export($id, $path);
                    $pack = Blog::fullDelete($id);
                }
            }
        }
        $args = $post['args'];
        UserSession::reroute($this->url . 'export/?' .  $args);
    }
    
    public function importPost($f3, $args) {
        $post = &$f3->ref('POST');
        $op = Valid::toStr($post, 'bksel', null);

        foreach ($post as $name => $value) {
            if (substr($name, 0, 3) === 'op-') {
                $list[] = $value;
            }
        }
        $imp = &$f3->ref('secrets.imports');
        $php = $f3->get('php') . "/";
        $imp_path = $php . $imp['path'];
        $bup = &$f3->ref('secrets.backups');
        $bup_path = $php . $bup['path'];
        $op = Valid::toStr($post, 'bksel', null);
        foreach($list as $fname) {
            $import = $imp_path . DIRECTORY_SEPARATOR . $fname;
            $archive = $bup_path . DIRECTORY_SEPARATOR . $fname;
            if ($op === "new") {
                $blog = json_decode(file_get_contents($import), true);
                $n = new Blog();
                $n->insertPackage($blog, $op);
                $n->save();
                rename($import, $archive);
            }
            else if ($op === "archive") {
                $blog = json_decode(file_get_contents($import), true);
                $n = Blog::byTitleClean($blog['title_clean']);
                if ($n !== false) {
                    Blog::export($n['id']);
                    $dbop = "update";
                }
                else {
                    $n = new Blog();
                    $dbop = "save";
                }
                $n->insertPackage($blog, $op);
                $n->$dbop();
            }
            else if ($op === "replace") {
                $blog = json_decode(file_get_contents($import), true);
                $n = Blog::byTitleClean($blog['title_clean']);
                if ($n !== false) {
                    $dbop = "update";
                }
                else {
                    $n = new Blog();
                    $dbop = "save";
                }
                $n->insertPackage($blog, $op);
            }
             else if ($op === "move") {
                rename($import, $archive);
            }
        }
        UserSession::reroute($this->url . 'import' );
    }
    public function import($f3, $args) {
        $bup = &$f3->ref('secrets.imports');
            
        $path = $f3->get('php') . "/" . $bup['path'];
        $dh = opendir($path);
        $packs = [];
        $log = [];
        
        while (true) {
// create a list of entries if image type
            $entry = readdir($dh);
            if ($entry === false) {
                break;
            }
            $ext = pathinfo($entry, PATHINFO_EXTENSION);
            if ($ext == 'json') {
                $pack = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . $entry), true);
                $pack['file'] = $entry;
                $blog = $pack['blog'];
                $version = $pack['version'];
                if (empty($version)) {
                    $compare = "Empty Version";
                }
                else if (floatval($version) < 0.2) {
                    $compare = "Version $version";
                }
                else {
                    $compare = "New";
                }
                // check if matching title clean exists, get its id, date_updated & published
                $title_clean = $blog['title_clean'];
                $match = Blog::byTitleClean($title_clean);
                if ($match !== false) {
                    $pack['match'] = $match;
                    $current = $match['date_updated'];
                    $import = $blog['date_updated'];
                    if ($import > $current) {
                        $compare = "Newer";
                    }
                    else if ($import === $current) {
                        $compare = "Same";
                    }
                    else if ($import < $current) {
                        $compare = "Older";
                    }
                }
                else {
                    $pack['match'] = null;
                }
                $pack['compare'] = $compare;
                $packs[] = $pack;
            }
        }
        closedir($dh);
        $view = $this->view;
        $view->packs = $packs;
        $view->args = "";
        $view->title = "Import";
        $view->path = $path;
        $view->assets('bootstrap');
        $view->content = 'blog/import.phtml';
        echo $view->render();     
        
    }

    
    public function export($f3, $args) {
        $view = $this->view;
        $view->content = 'blog/export.phtml';
        $request = $f3->get('REQUEST');

        $numberPage = Valid::toInt($request, 'page', 1);
        $category = Valid::toInt($request, 'catId', 0);
        $orderby = Valid::toStr($request, 'orderby', null);
        $order_field = Blog::viewOrderBy($this->view, $orderby);
        $grabsize = 12;
        $start = ($numberPage - 1) * $grabsize;

        $sql = "select b.*, u.name as author_name,  count(*) over() as full_count from blog b" .
                " left outer join users u on u.id = b.author_id ";
        $binders = [];
        if ($category > 0) {
            $sql .= " inner join blog_to_category bc on bc.blog_id = b.id and bc.category_id = :catId";
            $binders[':catId'] = $category;
        }
        $sql .= " order by " . $order_field
                . " limit " . $grabsize . " offset " . $start ;
        $db = Server::db();
        $results = $db->exec($sql, $binders);
        $maxrows = !empty($results) ? $results[0]['fullcount'] : 0;
        $paginator = new PageInfo($numberPage, $grabsize, $results, $maxrows);
        $view->page = $paginator;
        $view->args = $f3->get('SERVER.QUERY_STRING');
        $cat_items = $db->exec("select id, name from blog_category");
        $view->catItems = $cat_items;
        $view->catId = $category;
        $view->isEditor = true;
        $view->assets('bootstrap');
        echo $view->render();
    }
}
