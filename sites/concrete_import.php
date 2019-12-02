<?php

/** its from a obsolete version of concrete 5, import content */

use WC\DB\Server;
use WC\DB\Blog;

class importC5 {
    // import most recent version by its URL 'handle'
            
    public $src; // source db
    public $dest; // destination db

    public function setDBNames($src, $dest) {
        $this->src = Server::db($src);
        $this->dest = Server::db($dest); // database
    }
    public function import1($handle) {
$getsql = <<<EOD
     select CV.cvName as title, MCV.cvHandle as title_clean, MCV.cvDateCreated as date_published, 
            CL.content as article from CollectionVersions CV 
     join
 (select  cID, cvDateCreated, cvHandle, max(cvID) as  vID from  CollectionVersions 
 	where  cvHandle = ? LIMIT 0, 1 )
 MCV on CV.cID = MCV.cID and CV.cvID = MCV.vID
 join CollectionVersionBlocks VB on VB.cvID = CV.cvID and VB.cID = CV.cID
 join btContentLocal CL on CL.bID = VB.bID    
EOD;

    $results =  $this->src->exec($getsql, $handle);
    if (!empty($results)) {
        $rec = &$results[0];
        $blog = new Blog($this->dest);

        $blog['title'] = $rec['title'];
        $blog['title_clean'] = $rec['title_clean'];
        $blog['date_published'] = $rec['date_published'];
        $blog['date_updated'] = $rec['date_published'];
        $blog['article'] = $rec['article'];
        
        $blog['enabled'] = 1;
        $blog['comments'] = 0;
        $blog['featured'] = 0;
        
        $blog['author_id'] = 1;
        
        $blog->save();
        }
    }
    public function importBatch($prefix) {
        $this->setDBNames('concrete','database');
        $getsql = <<<EOD
select distinct SUBSTRING(cPath, :start ,LENGTH(cPath) - :len) as cvHandle 
    from PagePaths where cPath like :prefix
EOD;
        $pos = strlen($prefix);
        $results = $this->src->exec($getsql,[':start' => $pos+1, ':len' => $pos, ':prefix' => $prefix . '%']);
        if (!empty($results)) {
            foreach( $results as $handle) {
                $this->import1($handle['cvHandle']);
            }
        }   
    } 
}

(new importC5())->importBatch("/news/");
