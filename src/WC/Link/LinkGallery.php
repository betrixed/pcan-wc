<?php
/*
 * Each line should be prefixed with  * 
 */
namespace WC\Link;
use WC\Db\Server;
use WC\Db\DbQuery;
/**
 *
 * @author Michael Rynn
 */
trait LinkGallery 
{

    public function removeRef($linkid, $gallid)
    {
        $db = $this->db;
        return $db->execute(["delete from link_gallery where linkid = :lid and gallid = :gid",
                    ':lid' => $linkid, ':gid' => $gallid]);
    }

    public function byLink ( $linkid ) {

            return (new DbQuery($this->db))->arraySet("select G.* from link_gallery G "
                    . " join linktogallery L on G.id = L.gallid "
                    . " where L.linkid = :lid",
                    ['lid' => $linkid], ['lid' => \PDO::PARAM_INT]
                    );
    }
    
    public function getAllLinks($id)
    {
         
        $sql = <<<EOD
select i.* from links i
    join linktogallery k on i.id = k.linkid and k.gallid = :id    
EOD;
        return (new DbQuery($this->db))->arraySet($sql, [':id' => $id]);
    }


}
