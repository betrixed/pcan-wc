<?php

namespace App\Link;

use App\Models\Blog;
use App\Models\BlogRevision;
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

    static function getLinkedRevision($blog): ?object
    {
        return self::getBlogRevision($blog->id, $blog->revision);
    }

    static public function getBlogRevision($bid, $rid): ?object
    {
        $rev = BlogRevision::findFirst([
                    'conditions' => 'blog_id = :bid: and revision = :rev:',
                    'bind' => ['bid' => intval($bid), 'rev' => intval($rid)]
        ]);
        return $rev;
    }

}
