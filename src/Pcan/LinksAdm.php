<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */

use WC\DB\Server;
use Pcan\DB\PageInfo;
use Pcan\DB\Links;
use Pcan\DB\Blog;
use Pcan\DB\Linkery;
use WC\Valid;
use WC\Text;
use WC\UserSession;

class LinksAdm extends Controller {
    private $syncUrl = 'http://parracan.org';
    private $editList = [];
    private $url = '/admin/link/';

    public function beforeRoute() {
         if (!$this->auth()) {
            return false;
         }
    }

    public function index($f3, $args) {
        $request = &$f3->ref('REQUEST');
        $numberPage = Valid::toInt($request, "page", 1);
        $orderby = Valid::toStr($request, 'orderby', null);

        $order_field = Links::indexOrderBy($this->view, $orderby);

        $view = $this->view;
        $view->content = 'links/index.phtml';
        $view->url = $this->url;
        $view->orderby = $orderby;
        $view->page = $this->listPageNum($numberPage, 12, $order_field);
        $view->assets(['bootstrap']);
        echo $view->render();
    }

    protected function listPageNum($numberPage, $pageRows, $orderby) {
        $start = ($numberPage - 1) * $pageRows;

        $sql = "select  b.* , count(*) over() as full_count"
                . " from links b"
                . " order by " . $orderby
                . " limit " . $pageRows . " offset " . $start;

        $db = Server::db();
        $results = $db->exec($sql);

        $maxrows = !empty($results) ? $results[0]['fullcount'] : 0;
        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }

    private function viewNewLink() {
        $view = $this->view;
        $view->linkid = 0;
        $this->viewCommon();
        echo $view->render();
        return null;
    }

    public function newLink($f3, $args) {
        $view = $this->view;
        $link = new Links();
        $link['sitename'] = 'Here';
        $link['url'] = '/';
        $link['urltype'] = 'Front';
        $link['enabled'] = true;
        if (isset($args['bid'])) {
            $bid = $args['bid'];
            $link['refid'] = $bid;
            $link['urltype'] = 'Blog';
            // get the actual blog, extract title, url and intro text
            $blog = Blog::findFirstById($bid);
            if ($blog !== false) {
                $link['url'] = "/article/" . $blog['title_clean'];
                $link['title'] = $blog['title'];
                $link['summary'] = Text::IntroText($blog['article'], 300);
            }
        }
        else {
             $link['urltype'] = 'Front';
        }
        $view->link = $link;
       
        $view->collections = [];
        return $this->viewNewLink();
    }

    /* Get link edit form */

    private function viewCommon() {
        $view = $this->view;
        $view->content = 'links/edit.phtml';
        $view->post = '/admin/link/post';
        $view->url = $this->url;
        $view->assets(['bootstrap','DateTime','SummerNote','links-edit']);
    }
    private function editLink($rec, $id) {
        $view = $this->view;

        $view->link = $rec;
        $view->linkid = $id;
        $view->title = 'Edit link ' . $id;

        $view->collections = Linkery::byLink($id);
        $us = $this->us;
        $view->linkery = is_null($us) ? null : $us->getKey('linkery');

        $this->viewCommon();
        echo $view->render();
    }
    
    public function edit($f3, $args) {
        $id = $args['lid'];
        $rec = new Links();
        
        $link = $rec->load("id = " . $id);
        if ($link !== false) {
            $this->editLink($link, $id);
        }
        else {
            //TODO: Error - Link id not found
            $this->flash('Link record not found - new?');
            $this->newLink($f3,$args);
        }
    }

    public function linkPost($f3, $args) {
        $link = new Links();
        $post = &$f3->ref('POST');
        $this->assignFromPost($post, $link);

        try {
            $this->view->link = $link;
            $id = $link['id'];
            if (isset($id) && $id !== 0) {
                $link->update();
            } else {
                $link->save();
                $this->flash("Link was added successfully");
            }
            UserSession::reroute($this->url . 'edit/' . $link['id']);
            return null;
        } catch (\PDOException $e) {
            $err = $e->errorInfo;
            $this->flash($err[0] . ": " . $err[1]);
        }
        return $this->viewNewLink();
    }

    private function assignFromPost(&$post, $link) {
        $id = Valid::toInt($post, 'id', null);
        if (isset($id)) {
            $link->load("id = " . $id);
        }
        $link['url'] = Valid::toStr($post, 'url', "");

        $link['urltype'] = Valid::toStr($post, 'urltype', "Front");
        $link['sitename'] = Valid::toStr($post, 'sitename', 'Here');
        $link['date_created'] = Valid::toDateTime($post, 'date_created');
        $link['title'] = Valid::toStr($post, 'title', "");
        $link['summary'] = $post['summary'];
        $link['enabled'] = Valid::toInt($post, 'enabled', 0);

        if (!isset($post['date_created'])) {
            $link->date_created = Valid::now();
        } else {
            $link->date_created = Valid::toDateTime($post, 'date_created');
        }
        $test = Valid::toInt($post, 'refid', 0);
        if (!empty($test)) {
            $link['refid'] = $test;
        }
    }
    
    public function generate($f3, $args) {
        $bid = $args['bid'];
        
        // Does a link record exist with blog_id as refid?
        
        $rec = new Links();
        $link = $rec->load("urltype='Blog' and refid = " . $bid);
        
        if ($link !== false) {
            // go to edit 
            $this->editLink($link, $link['id']);
        }
        else {
            $this->newLink($f3,$args);
        }
    }
}
