<?php

namespace App\Link;
use WC\Db\Server;
use WC\Db\DbQuery;
/**
 *
 * @author Michael Rynn
 */
trait ImageView
{
    // Make an object,  id, galleryid,  im_path, im_file, im_width, im_caption using query
    public function getData($id) {
        $qry = new DbQuery($this->db);
$sql = <<<ESQL
SELECT M.id, G.id as galleryid, CONCAT( '/', G.path , '/') as im_path,
 M.name as im_file, M.width as im_width, 
 M.description as im_caption, M.thumb_ext
 FROM image M join gallery G on G.id = M.galleryid
 WHERE M.id = :id limit 1
ESQL;
        $result = $qry->objectSet($sql, [':id'=>$id] );
        if (!empty($result)) {
            $img = $result[0];
            $fname  = pathinfo(  $img->im_file, PATHINFO_FILENAME);
            $img->thumb = empty($img->thumb_ext) ? $img->im_file : $fname . '.' . $img->thumb_ext;
            $img->thumb = "thumbs/" . $img->thumb;
            return $img;
        }
        return null;
    }
    public function getGalleryCount($imageid) {
        $db = $this->db;
        $result = $db->exec("select count(*) as gct from img_gallery g where g.imageid = :id"
                , [':id' => $imageid]);
        return $result[0]['gct'];
    }
    public function updateDescription($imageid, $new_description)
    {
        $db = $this->db;
        return $db->exec("update image set description = :ndesc where id = :id", [':ndesc' => $new_description, ':id' => $imageid]);
    }
    public function updateThumbExt($img_id, $thumb_ext)
    {
        $db = $this->db;
        return $db->exec("update image set thumb_ext = :ext where id = :imgid", [':imgid' => $img_id, ':ext' => $thumb_ext]);
    }
}
