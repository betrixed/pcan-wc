<?php

/*
 * @author Michael Rynn
 */

namespace Pcan;

/**
 * The Zend Opcache extension can halve response times.
 * The single page file called from here,
 * was distributed from GitHub and Packagist
 * as rlerdorf/opcache-status
 * 
 */
class Opcache extends \Pcan\Controller {
use Mixin\Auth;
    
    public function index($f3, $args) {
        $path = $f3->get('vendor_path') . 'rlerdorf/opcache-status/';
        return include $path . 'opcache.php';
    }
    
    public function getAllowRole() {
        return 'Admin';
    }
}
