<?php
namespace WC;
/**
 * Compatibility extensions for Plates
 * This is required for phalcon .phtml views.
 * 
 * @author Michael Rynn
 */
class ViewEngine extends \Phiz\Mvc\View\Engine\Php
{
    public function layout($vpath, $data) {
        // negation, do nothing
    }
}
