<?php

namespace Pcan\DB;
use WC\DB\Server;
/**
 *
 * @author Michael Rynn
 */
class Event extends \DB\SQL\Mapper {
    public function __construct() {
        $db = Server::db();
        parent::__construct($db, 'event', NULL, 1.0e8); // 100 second
    }
    
    static  public function getEventBlog($eid) {
        $db = Server::db();
$sql=<<<EOD
select e.fromTime, e.toTime, e.enabled, e.id as eventid, b.* from event e join blog b on b.id = e.blogid
where e.id = ?
EOD;
        return $db->exec($sql, [$eid ]);
    }
    static  public function byId($id) {
        $result = new Event();
        return $result->load([ 'id = ?', $id ]);
    }
    static  public function byBlogId($bid) {
        $db = Server::db();
        return $db->exec('select * from event where blogId = ? order by fromTime', [$bid ]);
    }    
    static public function getRego($eid) {
         $db = Server::db();
         return $db->exec('select * from register where eventid = ?', [$eid]);
    }
    static  public function getSlugId($slug) {
        $db = Server::db();
        return $db->exec('select id from event where slug = ? and NOW() < toTime and enabled=1 order by fromTime', [$slug]);
    }
    
}

