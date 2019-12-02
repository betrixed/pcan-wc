<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SBO;

class CItem
{
    public $id;
    public $name;
    public $qty;
    public $cost;
    public $lineTotal;

    public function __construct($id, $qty, $cost)
    {
        $this->id = $id;
        $this->qty = $qty;
        $this->cost = floatval($cost);
    }

}

class Cart extends \stdClass
{

    public $list;
    public $postage;
    public $totalItems;
    public $totalCost;
    
    public $email;
    public $phone;
    public $address;
    public $first_name;
    public $last_name;
    public $city;
    public $region;
    public $country;
    public $postcode;
    public $total;
    
    static public function instance()
    {
        if (\Registry::exists(__CLASS__)) {
            return \Registry::get(__CLASS__);
        } else {
            $f3 = \Base::instance();
            $cart = $f3->get('SESSION.cart');
            
            if (is_null($cart)) {
                $cart = new Cart();
                \Registry::set(__CLASS__, $cart);
            }
            return $cart;
        }
    }

    public function itemsStr() {
        if ($this->totalItems > 1) {
            return "items";
        }
        else {
            return "item";
        }
    }
    public function calculate() {
        $sum = 0.0;
        $ct = 0.0;
        foreach($this->list as $item) {
            $ct += $item->qty;
            $item->lineTotal =  $item->cost * $item->qty;
            $sum += $item->lineTotal;
        }
        $this->totalCost = $sum;
        $this->totalItems = $ct;
        $this->total = $this->postage + $this->totalCost;
        
    }
    
    public function clear() {
        $this->list = [];
    }
    public function __construct()
    {
        $this->list = [];
        $this->totalItems = 0;
        $this->totalCost = 0;
    }

    public function get($id) {
        return $this->list[$id];
    }
    public function qty($id) 
    {
        if ( isset($this->list[$id]) ) {
            return $this->list[$id]->qty;
        }
        return 0;
    }    
    public function hasId($id) 
    {
        return isset($this->list[$id]);
    }
    public function addItem($id, $qty, $cost)
    {
        $key = $id;
        if (isset($this->list[$key])) {
            $items = $this->list[$key];
            $items->qty += $qty;
        } else {
            $this->list[$key] = new CItem($id, $qty, $cost);
        }
        $this->calculate();
    }

    public function save()
    {
        $f3 = \Base::instance();
        $f3->set('SESSION.cart',$this);
    }

}
