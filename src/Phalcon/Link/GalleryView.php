<?php


namespace App\Link;
use WC\Db\DbQuery;
use WC\Db\Server;
use App\Link\PageInfo;
use WC\App;
use Phalcon\Db\Column;
/**
 * Gallery operations
 *
 * @author michael
 */
class GalleryView
{
    static public function  countImgGallery($imageid) {
        $result = (new DbQuery())->arraySet("select count(*) as gct from img_gallery g where g.imageid = :id"
                , ['id' => $imageid] , ['id' => Column::BIND_PARAM_INT]);
        return $result[0]['gct'];
    }
    
     static public function removeRef($img_id, $gallery_id)
    {
        $db = Server::db();
        return $db->execute("delete from img_gallery where imageid = :imgid and galleryid = :gid",
                    ['imgid' => $img_id, 'gid' => $gallery_id], 
                    ['imgid' => Column::BIND_PARAM_INT, 'gid' => Column::BIND_PARAM_INT]);
    }
    
    static public function setVisible($galleryid, $imageid, $value) {
        $db = Server::db();
        return $db->execute("update img_gallery set visible = :val "
                . "where imageid = :imgid and galleryid = :galid", 
                ['val' => $value, 'imgid' => $imageid, 'galid' => $galleryid],
                ['val' => Column::BIND_PARAM_INT, 'imgid' => Column::BIND_PARAM_INT,
                        'galid' =>Column::BIND_PARAM_INT]);
    }
    static public function getImages($id)
    {
        $sql = <<<EOD
select i.*, g.path from image i, gallery g, img_gallery a
where a.galleryid = :id and a.visible <> 0
and i.id = a.imageid
and g.id = i.galleryid  
order by i.date_upload asc
EOD;
        $results = (new DbQuery())->arraySet($sql, ['id' => $id]);
        return $results;
    }
    
    static function pageList(\WC\WConfig $m, int $pageNum)
    {
        $m->numberPage = $pageNum;
        $m->orderby = 'path';
        $m->order_field = 'b.last_upload desc'; 
        $m->page = static::listPageNum($m->numberPage, 12, $m->order_field);

    }
    static function listPageNum($numberPage, $pageRows, $orderby) {
        $start = ($numberPage - 1) * $pageRows;
        $sql =  <<<EOD
select b.*,
    count(*) over() as full_count             
    from gallery b
    order by  $orderby
    limit $pageRows offset $start
EOD;
        $db = new DbQuery();
        $results = $db->arraySet($sql);

        $maxrows = !empty($results) ? $results[0]['full_count'] : 0;

        return new PageInfo($numberPage, $pageRows, $results, $maxrows);
    }
}
