<?php

namespace App\Link;
use WC\Db\Server;
use WC\Db\DbQuery;
/**
 *
 * @author Michael Rynn
 */
class ImageView
{
    // Make an object,  id, galleryid,  im_path, im_file, im_width, im_caption using query
    static public function getData($id) {
        $db = new DbQuery();
$sql = <<<ESQL
SELECT M.id, G.id as galleryid, CONCAT( '/', G.path , '/') as im_path,
 M.name as im_file, M.width as im_width, 
 M.description as im_caption, M.thumb_ext
 FROM image M join gallery G on G.id = M.galleryid
 WHERE M.id = :id limit 1
ESQL;
        $result = $db->objectSet($sql, [':id'=>$id] );
        if (!empty($result)) {
            $img = $result[0];
            $fname  = pathinfo(  $img->im_file, PATHINFO_FILENAME);
            $img->thumb = empty($img->thumb_ext) ? $img->im_file : $fname . '.' . $img->thumb_ext;
            $img->thumb = "thumbs/" . $img->thumb;
            return $img;
        }
        return null;
    }
    static public function getGalleryCount($imageid) {
        $db = Server::db();
        $result = $db->exec("select count(*) as gct from img_gallery g where g.imageid = :id"
                , [':id' => $imageid]);
        return $result[0]['gct'];
    }
    static public function updateDescription($imageid, $new_description)
    {
        $db = Server::db();
        return $db->exec("update image set description = :ndesc where id = :id", [':ndesc' => $new_description, ':id' => $imageid]);
    }
    static public function updateThumbExt($img_id, $thumb_ext)
    {
        $db = Server::db();
        return $db->exec("update image set thumb_ext = :ext where id = :imgid", [':imgid' => $img_id, ':ext' => $thumb_ext]);
    }
}
