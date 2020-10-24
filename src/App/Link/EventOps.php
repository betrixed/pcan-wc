<?php

namespace App\Link;
use WC\Db\Server;
use WC\Db\DbQuery;
use Phalcon\Db\Column;
/**
 * @author michael
 */
trait EventOps
{
    protected function getRego($eid) {
         $qry = new DbQuery($this->db);
         return $qry->objectSet(
                 'select * from register where eventid = :eid', 
                 ['eid' => $eid], ['eid' => Column::BIND_PARAM_INT]);
    }
    protected function getRegoMail($eid) {
         $qry = new DbQuery($this->db);
         return $qry->objectSet(
                 'select R.*, RM.mail from register R '
                     . ' left outer join reg_mail RM on RM.reg_id = R.id'
                 . ' where R.eventid = :eid', 
                 ['eid' => $eid], ['eid' => Column::BIND_PARAM_INT]);
    }
    protected function getPending() {
        $db = $this->db;
        if ($db->getType() === 'sqlite') {
            $nowfn1 = "datetime( B.fromtime) > datetime('now')";
            $nowfn2 = "datetime( B.totime) > datetime('now')";
        } else {
            $nowfn1 = "B.fromtime > NOW()";
            $nowfn2 = "B.totime > NOW()";
        }
        $sql = <<<EOQ
      SELECT A.id, A.title, B.fromtime as  date1, B.totime as date2,
      R.content as article,  A.style, A.title_clean, C.content
      from blog A 
      join event B on A.id = B.blogid and A.enabled = 1
      and (
      ((B.fromtime is NOT NULL) AND ( $nowfn1 ))
      OR ((B.totime  is NOT NULL) AND ( $nowfn2 ))
      )
      join blog_revision R on R.blog_id = A.id and R.revision = A.revision
      join
      (select MC.blog_id, MC.content from blog_meta MC join meta M on MC.meta_id = M.id
      where M.meta_name = 'og:description') C on C.blog_id = A.id
      order by B.fromtime
EOQ;

        return (new DbQuery($db))->arraySet($sql);
    }
}
