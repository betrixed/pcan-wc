<?php
/**
 * This file, if it exists, is pulled in by require at the bottom
 * of the \WC\App::init function.
 * It therefore has a $this of the App object, 
 * and  $f3 - \Base object
 * Delete on Production Site
 */
if ($f3->get('FAKE_USER_SESSION')) {
    $us = \WC\UserSession::read();
    if (is_null($us)) {
        // Start a fake User
        $us = \WC\UserSession::instance();
        $us->setValidUser('FAKE USER', ['Guest','Editor','Admin','User']);
        $us->write();
    }
}

    
   /*
    * 

    if (($agent['name'] === 'Apple Safari') && ($agent['version'] === '5.0.6'))
    {
        $f3->set('navigate', 'simple_nav.phtml');
        $bundles = WC\Assets::instance();
        $bundles->add('simple');
        // nullify bootstrap
        WC\Assets::registerAssets([
            'bootstrap' => [],
        ]);
    }
    else {
        $bundles = WC\Assets::instance();
        $bundles->add('bootstrap');
    }
*/


