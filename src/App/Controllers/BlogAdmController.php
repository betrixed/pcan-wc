<?php

namespace App\Controllers;

/**
 * @author Michael.Rynn
 */

use WC\Valid;
use WC\DB\Server;

use App\Models\{Blog, Links,Event,BlogRevision};

use Phalcon\Mvc\Controller;
use WC\Db\DbQuery;
use WC\WConfig;
use App\Link\{
    PageInfo,
    BlogView,
    LinksOps
};

class BlogAdmController extends Controller
{

    use \WC\Mixin\ViewPhalcon;
    use \WC\Mixin\Auth;
    use \WC\Mixin\HtmlPurify;
    use \App\Link\RevisionOp;

    public function getAllowRole()
    {
        return 'Admin';
    }

    public $url = '/admin/blog/';
    private $editAssets = ['bootstrap', 'SummerNote', 'DateTime', 'jquery-form', 'blog-edit'];

    /** routing actions  */
    public function h_get($f3, $args)
    {
        $action = $args['action'];
        switch ($action) {
            case 'new':
                $this->newRec($f3, $args);
                break;
            default:
                $this->$action($f3, $args);
                break;
        }
    }

    public function h_ajax($f3, $args)
    {
        $action = $args['action'];
        switch ($action) {
            case 'categorytick':
                $this->catTick($f3, $args);
                break;
            default:
                $this->$action($f3, $args);
                break;
        }
    }

    public function h_post($f3, $args)
    {
        $action = $args['action'];
        switch ($action) {
            default:
                $this->$action($f3, $args);
                break;
        }
    }

    public function editAction($id)
    {
        $blog = Blog::findFirstById($id);
        $view = $this->getView();
        $m = $view->m;
        $m->airmode = $this->request->getQuery('airmode', 'int', 0);
        $m->blog = $blog;
        return $this->editForm();
    }

    private static function int_bool($pvar)
    {
        if (is_null($pvar))
            return 0;
        else
            return 1;
    }

    /**
     * Reference to POSTDATA and blog record. 
     */
    private function setBlogTitle($post, $blog)
    {
        $newTitle = strip_tags(Valid::toStr($post, 'title'));
        $oldTitle = $blog->title;
        $titleChanged = ($oldTitle !== $newTitle);
        $blog->title = $newTitle;
        $newUrl = '';
        $lock_url = Valid::toBool($post, 'lock_url');

        $oldUrl = $blog->title_clean;
        $autoUrl = (!empty($lock_url) || strlen($oldUrl) == 0) ? true : false;
        $blogid = $blog->id;
        if ($autoUrl && !empty($blogid)) {
            $newUrl = strip_tags(Valid::toStr($post, 'title_clean'));

            if ($newUrl !== $oldUrl) {
                $blog->title_clean = $this->unique_url($blogid, $newUrl);
                $autoUrl = False;
            }
        }

        if ($titleChanged && $autoUrl) {
            $newUrl = Valid::url_slug($blog->title);
            $blog->title_clean = $this->unique_url($blogid, $newUrl);
        }
    }

    private function setBlogFromPost($post, $m)
    {
        $blog = $m->blog;
        $revision = $m->revision;

        $this->setBlogTitle($post, $blog);

        $action = Valid::toStr($post, 'rev_list');
        if ($action === 'Revision') {
            $blog->revision = $this->newRevisionId($blog);//RevisionOp
        }
        $revision->content = $this->purify($post, 'article');
        $revision->date_saved = Valid::now();

        $blog->style = $post['style'];

        $blog->featured = Valid::toBool($post, 'featured');
        $blog->enabled = Valid::toBool($post, 'enabled');
        $blog->comments = Valid::toBool($post, 'comments');
    }

    private function errorPDO($e, $blogid)
    {
        $err = implode(PHP_EOL, $e->errorInfo);
        $this->flash($err);
        $this->reroute('/admin/blog/edit/' . $blogid);
        return false;
    }

    public function postAction()
    {
        $post = $_POST;
// check match?
        $check_id = Valid::toInt($post, 'id');

        $blog = Blog::findFirstById($check_id);

        if (empty($blog)) {
            $this->flash("blog does not exist " . $check_id);
            return $this->reroute('/admin/blog/index');
        }
// set updatable things

        $old_tc = $blog->title_clean;
        $update = new WConfig();
        $update->blog = $blog;
        $update->revision = $this->getLinkedRevision($blog);//RevisionOp
        $this->setBlogFromPost($post, $update);
        $update_link = ($old_tc !== $blog->title_clean);
        try {
            $revision = $update->revision;
            if ($revision->revision !== $blog->revision) {
                //  have new primary key
                $new_revision = new BlogRevision();
                $new_revision->setRevision($blog->getRevision());
                $new_revision->setBlogId($blog->getId());
                $new_revision->setContent($revision->getContent());
                $new_revision->setDateSaved($revision->getDateSaved());
                $new_revision->create(); // a create using old record object fails silently
            } else {
                // update existing revision
                $revision->update();
            }
            $blog->update();
        } catch (\PDOException $e) {
            return $this->errorPDO($e, $blogid);
        }
        $blogid = $blog->id;
        if ($update_link) {
            $this->links_setBlogURL($blogid, $blog->title_clean);
        }
        $metatags = BlogView::getMetaTags($blogid);
        $db = $this->db;
        $inTrans = false;
        foreach ($metatags as $mtag) {
// key = metatag-#id
            $tagid = $mtag['id'];
            $content = strip_tags($post['metatag-' . $tagid]);
            if (is_null($content) || empty($content)) {
// link content record needs deleting ? 
                if (isset($mtag['blog_id'])) {
                    if (!$inTrans) {
                        $db->begin();
                        $inTrans = True;
                    }
                    $sql = "delete from blog_meta where blog_id = :blogid"
                            . " and meta_id = :metaid";
                    $db->execute($sql, ['blogid' => $blogid, 'metaid' => $tagid]);
                }
            } else {
                if (!$inTrans) {
                    $db->begin();
                    $inTrans = True;
                }
                $sql = "replace into blog_meta (blog_id, meta_id, content)"
                        . " values(:blogid, :metaid, :content)";
                $db->execute($sql, ['blogid' => $blogid,
                    'metaid' => $tagid,
                    'content' => $content]);
            }
        }
        if ($inTrans) {
            $db->commit();
        }
        return $this->reroute($this->url . 'edit/' . $blog->id);
        //$this->editForm();
    }

    public function revisionsAction($bid)
    {
        $view = $this->getView();
        $m = $view->m;
        $blog = Blog::findFirstById($bid);
        $m->blog = $blog;
        $all = $blog->getRelated('BlogRevision');
        $list = [];
        foreach ($all as $revision) {
            $item = new WConfig();
            $item->id = $revision->revision;
            $item->title = strval($revision->revision) . ") " . $revision->date_saved;
            $list[] = $item;
        }
        $m->list = $list;
        return $this->render('blog', 'revisions');
    }

    public function revgetAction($bid, $rid)
    {
        $view = $this->getView();
        $this->noLayouts();
        $m = $view->m;
        $m->blog = Blog::findFirstById($bid);
        $m->revision = $this->getRevision($bid, $rid);//RevisionOp
        $m->asHTML = $this->request->getQuery('html') ?? 0;
        return $this->render('blog', 'revget');
    }

    public function newAction()
    {
        $view = $this->getView();
        $m = $view->m->url = $this->url;
        return $this->render('blog', 'new');
    }

    public function postflag()
    {
        $post = $_POST;
        $op = Valid::toStr($post, 'flagsel', null);
        $list = [];
        foreach ($post as $name => $value) {
            if (substr($name, 0, 2) === 'op') {
                $list[] = intval(substr($name, 2));
            }
        }
        if (count($list) > 0) {
            $db = $this->db;
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
            $sql = "update blog set $update = :val where id = :id";
        }
        $params['val'] = $val;
        $db->begin();
        foreach ($list as $id) {
            $params['id'] = $id;
            $db->execute($sql, $params);
        }
        $db->commit();
        $args = $post['args'];
        $this->reroute($this->url . 'index/?' . $args);
    }

    public function postnewAction()
    {

        $blog = new blog();
        $post = $_POST;

        $us = $this->user_session;

        $blog->featured = 0;
        $blog->enabled = 0;
        $blog->comments = 1;
        $blog->revision = 1;

        $this->setBlogTitle($post, $blog);
        try {
            $blog->create();
            $revision = new BlogRevision();
            $revision->blog_id = $blog->id;
            $revision->date_saved = Valid::now();
            $revision->revision = $blog->revision;
            $revision->content = "<p>Edit This Content<br></p>";
            $revision->create();
            $this->flash("Blog created");
        } catch (\PDOException $e) {
            return $this->errorPDO($e, 'new');
        }
        return $this->reroute($this->url . 'edit/' . $blog->id);
    }

    /**
     * Make an edit form for blog $id
     * @param type $id
     * @return type null
     */
    private function editForm()
    {
        $view = $this->getView();
        $model = $view->m;

        $blog = $model->blog;

        $model->isApprover = true; // isApprover()

        $stylelist = BlogView::getStyleList();

        $id = $blog->id;
        $all_revisions = $blog->getRelated('BlogRevision');


        foreach ($all_revisions as $rec) {
            if ($blog->revision === $rec->revision) {
                $model->revision = $rec;
            }
        }
        $model->all_revisions = $all_revisions;
        // submit choices
        $model->rev_list = [
            'Save' => 'Save',
            'Revision' => 'Save as new revision',
        ];

        $model->title = '#' . $id;
        $model->stylelist = $stylelist;
        $model->catset = BlogView::getCategorySet($id);
        $model->events = BlogView::getEvents($id);
        $model->metatags = BlogView::getMetaTags($id);
        $model->content = $model->url = $this->url;

        return $this->render('blog', 'edit');
    }

    public function eventpostAction()
    {
        $post = $_POST;

        $view = $this->getView();
        $m = $view->m;
        if (!empty($post)) {
            $blog_id = Valid::toInt($post, 'blogid', null);
            $event_op = Valid::toStr($post, 'event_op', '');
            $chkct = Valid::toInt($post, 'chkct', 'int');

            if ($chkct > 0) {
                $sql = "";
                switch ($event_op) {
                    case "enable":
                        $sql = "update event set enabled=1 where id = :id";
                        break;
                    case "disable":
                        $sql = "update event set enabled=0 where id = :id";
                        break;
                    case "remove":
                        $sql = "delete from event where id = :id";
                        break;
                }
                $db = $this->db;
                for ($ix = 1; $ix <= $chkct; $ix++) {
                    $chkname = "chk" . $ix;
                    $chkvalue = Valid::toInt($post, $chkname);
                    if (!empty($chkvalue)) {
                        $db->execute($sql, ['id' => $chkvalue]);
                    }
                }
            }

            $m->events = BlogView::getEvents($blog_id);
        }
        $m->url = $this->url;

        $this->noLayouts();
        return $this->render('partials', 'blog/event_dates');
    }

    public function addeventAction()
    {
        $post = $_POST;
        if (!empty($post)) {
            // make a new event from form, then return new list
            $bid = Valid::toInt($post, 'event_blogid', null);
            if (!empty($bid)) {
                try {
                    $event = new Event();
                    $event->blogid = $bid;
                    $event->fromtime = Valid::toDateTime($post, 'fromtime');
                    $event->totime = Valid::toDateTime($post, 'totime');
                    $event->slug = Valid::toStr($post, 'slug');
                    $event->enabled = 1;
                    $event->save();
                } catch (\Exception $e) {
                    $err = $e->errorInfo;
                    $this->flash('New event fail: ' . $err[0] . ' ' . $err[1]);
                }
                $view = $this->getView();
                $m = $view->m;
                $m->url = $this->url;
                $m->events = BlogView::getEvents($bid);
                $this->noLayouts();
                return $this->render('partials', 'blog/event_dates');
            }
        }
    }

    public function cattickAction()
    {

        $post = $_POST;
        $db = $this->db;
        $query = new DbQuery($db);

        $blog_id = Valid::toInt($post, 'blogid');
        $chkct = Valid::toInt($post, 'chkct');

        $db->begin();
        $id = 0;
        // get all the existing category ids
        $results = $query->arraySet("select category_id from blog_to_category where blog_id = :blogId", ['blogId' => $blog_id]);
        $hasCategory = [];
        foreach ($results as $row) {
            $hasCategory[$row['category_id']] = true;
        }
        $insertSql = "REPLACE INTO blog_to_category ( category_id, blog_id ) VALUES (:catId, :blogId)";
        // html form only returns the checked rows
        for ($ix = 1; $ix <= $chkct; $ix++) {
            $chkname = "cat" . $ix;
            $chkvalue = Valid::toInt($post, $chkname);
            if ($chkvalue > 0) {
                if (!array_key_exists($chkvalue, $hasCategory)) {
                    $params2 = ['blogId' => $blog_id, 'catId' => $chkvalue];
                    $db->execute($insertSql, $params2);
                } else {
                    $hasCategory[$chkvalue] = false;
                }
            }
        }
        // delete unconfirmed values
        if ($db->getType() === 'mysql') {
            $ignore = 'IGNORE';
        } else {
            $ignore = '';
        }
        $deleteSql = "DELETE $ignore from blog_to_category where category_id = :catId and blog_id = :blogId";
        foreach ($hasCategory as $key => $value) {
            if ($value) {
                $params2 = ['blogId' => $blog_id, 'catId' => $key];
                $db->execute($deleteSql, $params2);
            }
        }
        $db->commit();
        $view = $this->getView();
        $view->m->catset = $this->getCategorySet($blog_id);
        $this->noLayouts();
        return $this->render('partials', 'blog/category');
    }

    public function indexAction()
    {
        $view = $this->getView();
        $m = $view->m;
        BlogView::pageFromRequest($m);
        return $this->render('admin', 'blog');
    }

}
