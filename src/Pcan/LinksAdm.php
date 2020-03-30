<?php
namespace Pcan;
/**
 * @author Michael Rynn
 */

use WC\DB\Server;
use Pcan\DB\PageInfo;
use Pcan\DB\Links;
use Pcan\DB\Image;
use Pcan\DB\Blog;
use Pcan\DB\Linkery;
use WC\Valid;
use WC\Text;
use WC\UserSession;

class LinksAdm extends Controller {
    use Mixin\Auth;
    use Mixin\ViewPlates;
    
    private $syncUrl = 'http://parracan.org';
    private $editList = [];
    private $url = '/admin/link/';

    public function index($f3, $args) {
        $request = &$f3->ref('REQUEST');
        $numberPage = Valid::toInt($request, "page", 1);
        $orderby = Valid::toStr($request, 'orderby', null);
        $view = $this->getView();
        $order_field = Links::indexOrderBy($view->model, $orderby);

        
        $view->content = 'links/index';
        $view->assets(['bootstrap']);
                
        $m = $view->model;
        
        $m->url = $this->url;
        $m->orderby = $orderby;
        $m->page = $this->listPageNum($numberPage, 12, $order_field);

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

        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;
        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }

    private function viewNewLink($view) {
        $m = $view->model;
        $m->linkid = 0;
        $this->viewCommon();
        echo $view->render();
        return null;
    }

    public function newLink($f3, $args) {
        $view = $this->getView();
        $m = $view->model;
        $link = new Links();
        $m->link = $link;
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
       
        $view->collections = [];
        return $this->viewNewLink($view);
    }

    /* Get link edit form */

    private function viewCommon() {
        $view = $this->getView();
        $view->assets(['bootstrap','DateTime','SummerNote','links-edit']);
        $view->content = 'links/edit';
        $m = $view->model;
        
        $m->post = '/admin/link/post';
        

        $m->url = $this->url;
        
    }
    private function editLink($rec, $id) {
        $view = $this->getView();
        $m = $view->model;
        $m->link = $rec;
        $m->linkid = $id;
        $m->collections = Linkery::byLink($id);
        $m->title = 'Edit link ' . $id;
        $m->image = empty($rec['imageid']) ? null : Image::getData($rec['imageid']);
        
        $us = UserSession::instance();
        $m->im_session = $us->getKey('imageid');
        $m->im_post = "/admin/link/image";
        $m->linkery = is_null($us) ? null : $us->getKey('linkery');

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

    public function ableItems($f3, $args) {
           $post = &$f3->ref('POST');
           $op = Valid::toInt($post, 'link_enable',1);
           
           foreach($post as $key => $item) {
               if (strpos( $key, 'lid') === 0):
                   $id =  intval(substr($key,3));
                  if ($id > 0) {
                       if ($op !== intval($item)) {
                           Links::setEnableId($id, $op);
                       }
                    }
               endif;
           }
           $page = Valid::toInt($post,'page',1);
           $orderby = Valid::toStr($post,'orderby');
           $f3->reroute($this->url  . '?orderby='.$orderbypage.'&page=' .$page );
    }
    public function deleteItem($f3, $args) {
           $post = &$f3->ref('POST');
           $id = Valid::toInt($post, "id", 0);
           if ($id > 0) {
               Links::deleteId($id);
           }
           $this->f3->reroute('admin/link');
    }
    public function linkPost($f3, $args) {
        $link = new Links();
        $post = &$f3->ref('POST');
        $this->assignFromPost($post, $link);
        
        try {
            $view = $this->getView();
            $view->link = $link;
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
        return $this->viewNewLink($view);
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
        $link['enabled'] = Valid::toBool($post, 'enabled', 0);
        $link['imageid'] = Valid::toInt($post, 'imageid');
        
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
