<?php

namespace App\Controllers;

/**
 * @author Michael Rynn
 */
use WC\Db\{Server, DbQuery};
use App\Link\{PageInfo,BlogView};
use App\Models\{Links, Image, Blog};
use WC\{WConfig, Valid, Text, UserSession};

class LinksAdmController extends BaseController {

    use \WC\Mixin\Auth;
    use \WC\Mixin\ViewPhalcon;
    use \App\Link\LinksView;
    use \App\Link\LinkGallery;
    use \App\Link\ImageView;
    use \App\Link\RevisionOp;
    use \WC\Mixin\HtmlPurify;
    
    private $syncUrl = 'http://parracan.org';
    private $editList = [];
    private $url = '/admin/link/';

    public function getAllowRole() {
        return 'Editor';
    }

    public function indexAction() {
        $m = $this->getViewModel();
        $request = $_GET;
        $m->numberPage = Valid::toInt($request, "page", 1);
        $m->orderby = Valid::toStr($request, 'orderby', null);

        $m->order_field = self::indexOrderBy($m, $m->orderby);

        $m->url = $this->url;
        $m->page = $this->listPageNum($m->numberPage, 12, $m->order_field);

        return $this->render('admin', 'link');
    }

    protected function listPageNum($numberPage, $pageRows, $orderby) {
        $start = ($numberPage - 1) * $pageRows;

        $sql = "select  b.* , count(*) over() as full_count"
                . " from links b"
                . " order by " . $orderby
                . " limit " . $pageRows . " offset " . $start;

        $qry = new DbQuery($this->db);
        $results = $qry->arraySet($sql);

        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;
        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }

    private function viewNewLink(WConfig $m) {
        $m->linkid = 0;
        return $this->viewCommon();
    }

    /**
     * action handler for new record
     */
    public function newAction() {
        return $this->newLink();
    }

    /**
     * Optional Blog record id, to extract first part
     * @param int $bid
     * @return type
     */
    private function newLink(int $bid = 0) {
        $m = $this->getViewModel();
        $link = new Links();
        $m->link = $link;
        $link->sitename = 'Here';
        $link->url = '/';
        $link->urltype = 'Front';
        $link->enabled = true;

        if ($bid > 0) {

            $link->refid = $bid;
            $link->urltype = 'Blog';
            // get the actual blog, extract title, url and intro text
            $blog = Blog::findFirstById($bid);
            if (!empty($blog)) {
                $revision = $this->getLinkedRevision($blog);
                $link->url = "/article/" . $blog->title_clean;
                $link->title = $blog->title;
                $link->summary = Text::IntroText($revision->content, 300);
            }
        } else {
            $link->urltype = 'Front';
        }

        $m->collections = [];
        return $this->viewNewLink($m);
    }

    /* Get link edit form */

    private function viewCommon() {
        $m = $this->getViewModel();
        //$view->assets(['bootstrap','DateTime','SummerNote','links-edit']);

        $m->post = '/admin/link/post';
        $m->url = $this->url;
        $m->display = self::display();
        $m->urltypes = self::getUrlTypes();
        $id = $m->link->id ?? 'new';
        $m->title = 'Link#' . $id;
        return $this->render('links', 'edit');
    }

    private function editLink($rec, $id) {
        $m = $this->getViewModel();
        $m->link = $rec;
        $m->linkid = $id;
        $m->collections = $this->byLink($id);
        $m->title = 'Edit link ' . $id;
        $m->image = empty($rec->imageid) ? null : $this->getData($rec->imageid);

        $us = $this->user_session;
        $m->im_session = $us->getKey('imageid');
        $m->im_post = "/admin/link/image";
        $m->linkery = is_null($us) ? null : $us->getKey('linkery');

        if (!empty($m->linkery) && !empty($m->collections)) {
            $galid = $m->linkery['id'];
            foreach ($m->collections as $lg) {
                if ($lg['id'] === $galid) {
                    $m->linkery = null;
                    break;
                }
            }
        }

        return $this->viewCommon();
    }

    public function editAction($id) {
        $link = Links::findFirstById($id);
        if (!empty($link)) {
            return $this->editLink($link, $id);
        } else {
            //TODO: Error - Link id not found
            $this->flash('Link record not found - new?');
            return $this->newLink();
        }
    }

    public function ableItemsAction() {
        $post = $_POST;
        $op = Valid::toInt($post, 'link_enable', 1);

        foreach ($post as $key => $item) {
            if (strpos($key, 'lid') === 0):
                $id = intval(substr($key, 3));
                if ($id > 0) {
                    if ($op !== intval($item)) {
                        $this->setEnableId($id, $op);
                    }
                }
            endif;
        }
        $page = Valid::toInt($post, 'page', 1);
        $orderby = Valid::toStr($post, 'orderby');
        UserSession::reroute($this->url . '?orderby=' . $orderbypage . '&page=' . $page);
    }

    public function deleteItem($f3, $args) {
        $post = $_POST;
        $id = Valid::toInt($post, "id", 0);
        if ($id > 0) {
            $this->deleteId($id);
        }
        $this->f3->reroute('admin/link');
    }

    public function postAction() {
        $post = $_POST;
        $id = Valid::toInt($post, 'id', null);

        if (!empty($id)) {
            $link = Links::findFirstById($id);
        } else {
            $link = new Links();
        }

        $this->assignFromPost($post, $link);

        try {
            $m = $this->getViewModel();
            $m->link = $link;
            if (!empty($link->id)) {
                $link->update();
                $op = "updated";
            } else {
                $link->create();
                 $op = "created";
            }
            $this->flash("Link " . $link->id . " $op");
            return $this->reroute($this->url . 'edit/' . $link->id);
        } catch (\Exception $e) {
            $this->flash($e->getMessage());
        }
        return $this->viewCommon();
    }

    private function assignFromPost($post, $link) {

        $link->url = Valid::toStr($post, 'url', "");

        $link->urltype = Valid::toStr($post, 'urltype', "Front");
        $link->sitename = Valid::toStr($post, 'sitename', 'Here');
        $link->date_created = Valid::toDateTime($post, 'date_created');
        $link->title = Valid::toStr($post, 'title', "");
        $link->summary = $this->purify($post,'summary');
        $link->enabled = Valid::toBool($post, 'enabled', 0);
        $link->imageid = Valid::toInt($post, 'imageid');

        if (!isset($post['date_created'])) {
            $link->date_created = Valid::now();
        } else {
            $link->date_created = Valid::toDateTime($post, 'date_created');
        }
        $test = Valid::toInt($post, 'refid', 0);
        if (!empty($test)) {
            $link->refid = $test;
        }
    }

    public function generateAction($bid) {

        $link = Links::findFirst("urltype='Blog' and refid = " . $bid);

        if (!empty($link)) {
            // go to edit 
            return $this->editLink($link, $link->id);
        } else {
            return $this->newLink($bid);
        }
    }

}
