<?php

namespace WC\Link;

use WC\Models\Blog;
use WC\Models\BlogRevision;
use WC\Valid;
use Phalcon\Db\Column;

/**
 *
 * @author michael
 */
trait RevisionOp
{

    public function newRevisionId(Blog $blog): int
    {
        $qry = $this->dbq;
        $result = $qry->arrayColumn('SELECT MAX(revision) as max_revision from blog_revision where blog_id = :bid',
                ['bid' => $blog->id], ['bid' => Column::BIND_PARAM_INT]);
        if (!empty($result)) {
            return intval($result[0]) + 1;
        }
        return 1;
    }
    /**
     * Get current revision record of blog
     * @param type $blog
     * @return object|null
     */
    static function getLinkedRevision($blog): ?object
    {
        return self::getBlogRevision($blog->id, $blog->revision);
    }

    /**
     * Gets the revision record only
     * @param type $bid
     * @param type $rid
     * @return object|null 
     */
    static public function getBlogRevision($bid, $rid): ?object
    {
        $rev = BlogRevision::findFirst([
                    'conditions' => 'blog_id = :bid: and revision = :rev:',
                    'bind' => ['bid' => intval($bid), 'rev' => intval($rid)]
        ]);
        return $rev;
    }

    /** return amalgam object of Blog and BlogRevision
     * 
     * @param type $bid
     * @param type $rid
     * @return object|null
     */
    public function getBlogAndRevision(int $bid, ?int $rid): ?object
    {
        $qry = $this->dbq;
        $sql = "select b.*, r.date_saved, r.content as article"
                . " from blog b join blog_revision r on r.blog_id = b.id";
        if (!empty($rid)) {
            $qry->whereCondition("r.revision=?", (int) $rid);
        }
        else {
            $sql .= ' and r.revision = b.revision';
        }
        $qry->whereCondition("b.id=?", (int) $bid);
        
        $result = $qry->queryOA($sql);
        return $result[0] ?? null;
    }
}
