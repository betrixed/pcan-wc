<?php

namespace SBO;

use WC\DB\Server;
use WC\Valid;
use WC\UserSession;

class Shop extends \WC\Controller {
    
    public $title = "SBO CD Shop";
    public function view($f3, $args) {
        $db = Server::db();
        $view = $this->view;
        $view->title = $this->title;
        UserSession::activate();
        $cart =  Cart::instance();
        $view->cart = $cart;
        $view->items = $db->exec("select * from cditems order by position");
        $view->content = 'shop/view.phtml';
        $view->assets(['bootstrap']);
        echo $view->render();
    }
    
    public function item($f3, $args) {
        $db = Server::db();
        $view = $this->view;
        $cid = $args['id'];
$sql =<<< EOD
select c.id, c.cost, c.title, b.style, b.article, b.title as title2 from 
 cditems c left outer join blog b on b.id = c.article_id 
 where c.id = :cid
EOD;
        $result = $db->exec($sql,[':cid' => $cid]);
        $view->item = !empty($result) ? $result[0] : null;
        $view->title = $this->title;
        
        $view->content = 'shop/item.phtml';
        $view->assets(['bootstrap']);
        echo $view->render();
    }
    
    /** Add to basket 
     */
    public function buy($f3, $args) {
        $db = Server::db();
        $view = $this->view;
        $cid = $args['id'];
        $req = &$f3->ref('REQUEST');
        $qty = Valid::toInt($req,'qty', 0);
        
        if ($qty > 0) {
$sql =<<< EOD
select c.id, c.cost from cditems c
 where c.id = :cid
EOD;
            $result = $db->exec($sql,[':cid' => $cid]);

            $add = !empty($result) ? $result[0] : null;
            if ($add) {
                $cart = Cart::instance();
                $cart->addItem($cid, $qty, $add['cost']);
                $cart->save();
            }
            UserSession::reroute('/shop/view');
        }
        else {
            UserSession::reroute('/shop/item/' . $cid);
        }
    }
    
    public function update($f3, $args) {
        $view = $this->view;
        $post = &$f3->ref('POST');
        $isAjax = $f3->get('AJAX');
        
        $cart = Cart::instance();
        $newlist = [];
        foreach($cart->list as $item) {
            $id = "cd" . $item->id;
            $q = Valid::toInt($post, $id , 0);
            if ($q !== 0) {
                $item->qty = $q;
                $newlist[$item->id] = $item;
                unset($post[$id]);
            }
        }
        $cart->list = $newlist;
        $cart->calculate();
        $this->getCartNames();
        $cart->save();
        
        $view->cart = $cart;
        $view->layout = 'shop/cart_sub.phtml';
        echo $view->render();
    }
    
    public function fromsub($f3, $args) {
        $view = $this->view;
        $post = &$f3->ref('POST');
        $isAjax = $f3->get('AJAX');
        
        $cart = Cart::instance();
        
       
        $cart->address = Valid::toStr($post,'address',null);
        $cart->city = Valid::toStr($post,'city',null);
        $cart->region = Valid::toStr($post,'region',null);   
        $cart->postcode = Valid::toStr($post,'postcode',null); 
        $cart->country = Valid::toStr($post,'country',null); 
        $cart->email = Valid::toEmail($post,'email',null); 
        $cart->phone = Valid::toStr($post,'phone',null); 
        $cart->first_name = Valid::toStr($post,'first_name',null); 
        $cart->last_name = Valid::toStr($post,'last_name',null); 
        
        $cart->save();
        $view->cart = $cart;
        $view->layout = 'shop/from_sub.phtml';
        echo $view->render();
        //$f3->reroute('/shop/cart/edit');
    }
    
    private function getCartNames() {
        $cart = Cart::instance();
        $ilist = "";
        foreach($cart->list as $item) {
            if (empty($item->name))
            {
                if (!empty($ilist)) {
                    $ilist .= ",";
                }
                $ilist .= $item->id; 
            }
        }
        if (!empty($ilist)) {
            $sql = "select c.* from cditems c where c.id in ($ilist)";
            $db = Server::db();
            $result = $db->exec($sql);
            if (count($result) > 0) {
                foreach($result as $rec) {
                    $item = $cart->list[$rec['id']];
                    if (!empty($item)) {
                        $item->name = $rec['title'];
                    }
                }
            }
        }
    }
    public function edit($f3, $args) {
        $view = $this->view;
        $cart = Cart::instance();
        
        if (empty($cart->postage)) {
            $cart->postage = "7.50";
        }
        $cart->calculate();
        $view->cart = $cart;

        $view->content = 'shop/cart.phtml';
        $view->assets(['bootstrap', 'jquery-form','cartjs']);

        $view->title = $this->title;    
        $this->getCartNames();
        
        
        echo $view->render();
    }
}

