<?php

namespace SBO;

use WC\UserSession;
use WC\Valid;
use WC\DB\Server;
use WC\DB\Blog;
use WC\UserSession;

use SBO\CDItems;

class ShopAdm extends \WC\Controller {
    public function shoplist($f3, $args) {
        $view = $this->view;
        $view->cditems = Server::db()->exec("select * from cditems");
        $view->content = "shopadm/list.phtml";
        $view->assets(['bootstrap']);
        echo $view->render();
    }
    
    public function beforeRoute() {
        if (!$this->auth()) {
            return false;
        }
    }
    
    public function item($f3, $args) {
        $view = $this->view;
        $id = $args['id'];
        $view->title = "CD #" . $id;
        $view->rec = CDItems::byId($id);
        $view->content = "shopadm/item.phtml";
        $view->assets(['bootstrap']);
        echo $view->render();
    }
    
    private function setFromPost($rec, &$post) {
        $rec['stock'] = Valid::toInt($post,'stock',0);
        $rec['cost'] = Valid::toMoney($post,'cost',"0.0000");
        $rec['title'] = Valid::toStr($post,'title',null);
        $rec['article_id'] = Valid::toInt($post,'article_id',null);
    }
    
    public function article($f3, $args) {
        $post = &$f3->ref('POST');
        
        $id = Valid::toInt($post,'id',0);
        if ($id !== 0) {
            $rec = CDItems::byId($id);
            $pack = [
                "version" => "1.0",
                "blog" => [
                    "title" => $rec['title'],
                    "title_clean" => Blog::url_slug($rec['title']),
                    "article" => "<p>Product Information Here</p>",
                    'date_published' => Valid::timeNow(),
                    'date_updated' => Valid::timeNow(),
                    'style' => 'novosti',
                    'issue' => 0
                ]
            ];
            $newblog = new Blog();
            $newblog->insertPackage($pack,"new");
            $blogid = $newblog['id'];
            if ($blogid > 0) {
                $rec['article_id'] = $blogid;
                $rec->update();
                UserSession::reroute('/admin/blog/edit/' . $blogid);
            }
        }
    }
    
    public function post($f3, $args) {
        $post = &$f3->ref('POST');
        
        $id = Valid::toInt($post,'id',0);
        if ($id !== 0) {
            $rec = CDItems::byId($id);
            $this->setFromPost($rec,$post);
            $rec->update();
            
        }
        else {
            $rec = new CDItems();
             $this->setFromPost($rec,$post);
            $rec->save();
        }
        UserSession::reroute("/shop/admin/list");
    }
};
