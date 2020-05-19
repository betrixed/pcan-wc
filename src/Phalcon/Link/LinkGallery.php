<?php
/*
 * Each line should be prefixed with  * 
 */
namespace App\Link;
use WC\Db\Server;
use WC\Db\DbQuery;
use Phalcon\Db\Column;
/**
 *
 * @author Michael Rynn
 */
class LinkGallery 
{

    static public function removeRef($linkid, $gallid)
    {
        $db = Server::db();
        return $db->execute(["delete from link_gallery where linkid = :lid and gallid = :gid",
                    ':lid' => $linkid, ':gid' => $gallid]);
    }

    static public function byLink ( $linkid ) {

            return (new DbQuery())->arraySet("select G.* from link_gallery G "
                    . " join linktogallery L on G.id = L.gallid "
                    . " where L.linkid = :lid",
                    ['lid' => $linkid], ['lid' => Column::BIND_PARAM_INT]
                    );
    }
    
    static public function getAllLinks($id)
    {
         
        $sql = <<<EOD
select i.* from links i
    join linktogallery k on i.id = k.linkid and k.gallid = :id    
EOD;
        return (new DbQuery())->arraySet($sql, [':id' => $id]);
    }


}
