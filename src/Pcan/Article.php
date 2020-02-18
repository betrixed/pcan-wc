<?php

namespace Pcan;

/**
 * Show article page
 *
 * @author Michael Rynn
 */

use WC\DB\Server;
use Pcan\DB\Blog;
use Pcan\DB\Linkery;
use WC\Valid;

class Article  extends Controller {
use Mixin\ViewPlates;

    public function news($f3, $args) {
        $server = &$f3->ref('SERVER');
        $req = $args['*'];
       // $req = $server['REQUEST_URI'];
         $db = Server::db('concrete');
         $sql = <<<EOD
 select CV.cID, CV.cvName, CV.cvID, VB.bID, CL.content from CollectionVersions CV 
 join
 (select  cID, max(cvID) as  vID from  CollectionVersions where  cvHandle = ? group by cID)
 MCV on CV.cID = MCV.cID and CV.cvID = MCV.vID
 join CollectionVersionBlocks VB on VB.cvID = CV.cvID and VB.cID = CV.cID
 join btContentLocal CL on CL.bID = VB.bID          
EOD;
        $content = $db->exec($sql, $req);
        $view = $this->getView();
        $m = $view->model;
        if (!empty($content)) {
            //$view->article = "Record " . $content[0]['cID'];
           $m->article = $content[0]['content'];
        }
        else {
            $m->article = "Not found";
        }
        $view->content = 'blog/sbo.phtml';
        $view->assets(['bootstrap']);
        echo $view->render();
    }
    public function title($f3, $args) {
        $title = $args['title'];
        $db = Server::db();
        $blog = new Blog();
        $active = $blog->load(['title_clean = :tc', ':tc' => $title] );
        if (!$active) {
             $blog['title'] = "Not found { /$title }";
            $blog['article'] = "Missing link { /$title }";
            $style_class = 'noclass';
        }
        else {
            $style_class = $blog['style'];
        }
        $v = $this->getView();
        $m = $v->model;
        
        $briefTitle = $blog['title'];
        if (strlen($briefTitle) > 30) {
            $temp = explode("\n", wordwrap( $briefTitle, 30));
            $briefTitle = $temp[0] . "\u{2026}";
        }
        $m->title =  Valid::asDate($blog['date_published']);
        $m->blog = $blog;
        $m->analytics = true;
        $meta = [];
        // fill the array up with article meta tags.
        $m->metadata = Blog::getMetaTagHtml($blog->id,$meta);
        $m->metaloaf = $meta;
        
        $req = &$f3->ref('REQUEST');
        if (isset($req['lnky'])) {
            $linkery = Linkery::byId($req['lnky']);
            
            if ($linkery !== false) {
                $m->back = '/linkery/view/' . $linkery['name'];
                $m->backname = $linkery['name'];
            }
        }

        //$canonical = $this->request->isSecure() ? 'https://' : 'http://';
        // Facebook likes reference to be only one of https or http
        $m->canonical = 'http://' . $f3->get('domain') . "/article/" . $name;
        
        $req = &$f3->ref('REQUEST');
         $v->content = 'blog/article.phtml';
        if (isset($req['sub'])) {
            $v->layout = null;
        }
        $v->assets(['bootstrap', $style_class]);
        echo $v->render();
    }
}
