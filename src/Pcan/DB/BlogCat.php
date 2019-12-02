<?php
namespace Pcan\DB;

/**
 * @author Michael Rynn
 */
use WC\DB\Server;

class BlogCat  extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'blog_category', NULL, 1.0e8); // 100 second
    }
    
    static public function byId($id) {
        $db = Server::db();
        $cat = new BlogCat();
        $cat = $cat->load(['id = ?', intval($id) ]);
        return $cat;
    }
    static public function bySlug($slug) {
         $db = Server::db();
         $cat = new BlogCat();
         $cat = $cat->load(['name_clean = ?', $slug]);
         return $cat;
    }
}
