<?php

/*
 *  Who cares?
 */

namespace Chimp;

use WC\Valid;
/**
 * Description of lists
 *
 * @author michael
 */


function gen_array(string &$outs, $prop, &$obj) {
    if ($prop === "_links") {
        return;
    }
    if (!empty($prop)) {
        $outs .= "<li>ARRAY $prop</li>";
    }
    $outs .= "<ul>" . PHP_EOL;
    foreach($obj as $key => $value) {
        if (is_string($value)) {
            $outs .= "<li>" . $key . ": " . $value . "</li>" . PHP_EOL;
        }
        else if (is_object($value)) {
            gen_object($outs, $key, $value);
        }
        else if (is_array($value)) {
            gen_array($outs, $key, $value);
        }
    }
    $outs .= "</ul>" . PHP_EOL;    
}
function gen_object(string &$outs, $prop, $obj) {
    $outs .= "<li><b>$prop</b></li>";
    $outs .= "<ul>" . PHP_EOL;
    foreach($obj as $key => $value) {
        if (is_string($value)) {
            $outs .= "<li>" . $key . ": " . $value . "</li>" . PHP_EOL;
        }
        else if (is_object($value)) {
            gen_object($outs, $key, $value);
        }
        else if (is_array($value)) {
            gen_array($outs, $key, $value);
        }
    }
    $outs .= "</ul>" . PHP_EOL;
}
class lists extends \WC\Controller {
    //put your code here
    public function index($f3, $args) {
        

        // get id of first list
        /*$first = $lists->lists[0];
        $id = $first->id;
        $stats = $first->stats;

        $total = $stats->member_count + $stats->unsubscribe_count + $stats->cleaned_count;*/

        $view = $this->view;
        $view->content = 'chimp/lists.phtml';
        
        $view->assets(['bootstrap']);
        
        $data = \Chimp\DB\ChimpLists::sync();

        

        $view->data = $data;
        echo $view->render();
    }
    
    
}
