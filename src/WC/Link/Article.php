<?php

namespace WC\Link;
use WC\Models\{ Blog, BlogRevision };
use WC\WConfig;
/** 
 * Helper class for Blog and BlogRevision
 */
class Article {
        /**
     * Gets the revision record only
     * @param type $bid
     * @param type $rid
     * @return object|null 
     */
    static public function getBlogRevision($bid, $rid): ?BlogRevision
    {
        $rev = BlogRevision::findFirst([
                    'conditions' => 'blog_id = :bid and revision = :rev',
                    'bind' => ['bid' => intval($bid), 'rev' => intval($rid)]
        ]);
        return $rev;
    }
    
    
    static function findArticleTitle(string $title_clean, WConfig $m) : bool
    {
        global $APP; 
        $blog = Blog::findFirstByTitleClean($title_clean);
        if (empty($blog)) {
            return false;
        }
        $m->title =  $blog->title; // for browser tab
        $m->blog = $blog;
        $m->revision = self::getBlogRevision($blog->id, $blog->revision);
        $m->analytics = true;
        $meta = [];
        
        // fill the array up with article meta tags.
        $hostUrl = 'http://www.' . $APP->domain;
        $m->metadata = self::getMetaTagHtml($blog->id,$meta, $hostUrl);
        $m->metaloaf = $meta;
        $m->canonical = $hostUrl . "/article/" . $title_clean;
        return true;
    }
    // return highest revision number plus 1
    /**
     * Return or create Blog object from $title_clean,
     * Set up $m model object for display
     * 
     */
    static function getArticleTitle(string $title_clean, WConfig $m) : Blog 
    {
        global $APP; // to get $app object
        
        if (findArticleTitle($title_clean,$m)) {
            return $m->blog;
        }
        
        $blog = new Blog();
        $blog->title_clean =  $title_clean;
        $blog->title = "New Document";
        $blog->style = 'noclass';

        $m->title =  $blog->title;
        $m->blog = $blog;
        
        $revobj = new \stdClass();
        $revobj->content = "<p>New Document<br></p>";
        $m->revision = $revobj;

        $m->analytics = true;
        $m->metaloaf = [];
        $m->metadata = [];
        
        $hostUrl = 'http://www.' . $APP->domain;
        
        $m->canonical = $hostUrl . "/article/" . $title_clean;
        
        return $blog;
    }
    
    
    static public function getMetaTagHtml(int $id, array &$meta, string $hostUrl): array
    {
        global $container;
        $qry = $container->get('dbq');
        // setup metatag info
        $sql = <<<EOD
select m.*, b.content
    from meta m
    join blog_meta b on b.meta_id = m.id
    and b.blog_id = :id
    order by meta_name
EOD;
        $results = $qry->arraySet($sql, ['id' => $id]);
        //$scheme = $server['REQUEST_SCHEME'];
        //$sitePrefix = 'http' . '://' . $_SERVER['HTTP_HOST'];

        if (!empty($results)) {
            if (is_array($meta)) {
                $meta_tags = [];
                foreach ($results as $row) {
                    $content = str_replace("'", "&apos;", $row['content']);
                    // replace ' with &apos; 

                    if ($row['prefix_site'] && !str_starts_with($content, "http")) {
                        if (!str_starts_with($content, '/')) {
                            $content = '/' . $content;
                        }
                        $content = $hostUrl . $content;
                    }
                    $meta_tags[] = str_replace("{}", $content, $row ['template']);
                }
                $meta = $meta_tags;
            }
        } else {
            $results = [];
        }
        return $results;
    }
}