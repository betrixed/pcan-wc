<?php

namespace App\Controllers;

/**
 * Show article page
 *
 * @author Michael Rynn
 */

use WC\Db\Server;
use App\Models\Blog;
use App\Models\Linkery;
use WC\Valid;
use Phalcon\Mvc\Controller;
use App\Link\BlogView;

class ArticleController  extends Controller {
use \WC\Mixin\ViewPhalcon;

    public function titleAction($title) {
        $blog = Blog::findFirstByTitleClean($title);

        if (empty($blog)) {
            $blog = new Blog();
            $blog->title = "Not found { /$title }";
            $style_class = 'noclass';
        }
        else {
            $style_class = $blog->style;
        }
        $v = $this->getView();
        $m = $v->m;
        
        $briefTitle = $blog->title;
        if (strlen($briefTitle) > 30) {
            $temp = explode("\n", wordwrap( $briefTitle, 30));
            $briefTitle = $temp[0] . "\u{2026}";
        }
        $m->title =  $blog->title;
        $m->blog = $blog;
        $m->revision = BlogView::linkedRevision($blog);

        $m->analytics = true;
        $meta = [];
        // fill the array up with article meta tags.
        $m->metadata = BlogView::getMetaTagHtml($blog->id,$meta);
        $m->metaloaf = $meta;
        $m->back = null;
        $req = $_REQUEST;
        $ly = Valid::toInt($req,'lnky',0);
        if (!empty($ly)) {
            $linkery = Linkery::findFirstById($ly);
            
            if (!empty($linkery)) {
                $m->back = '/linkery/view/' . $linkery->name;
                $m->backname = $linkery->name;
            }
        }

        //$canonical = $this->request->isSecure() ? 'https://' : 'http://';
        // Facebook likes reference to be only one of https or http
        $m->canonical = 'http://' . $_SERVER['HTTP_HOST'] . "/article/" . $title;
        

        if (isset($req['sub'])) {
           $this->noLayouts();
        }
        

        
        return $this->render('index','article');
    }
}
