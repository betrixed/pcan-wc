<?php

namespace WC\Controllers;

/**
 * Show article page
 *
 * @author Michael Rynn
 */
use WC\Db\Server;
use WC\Models\Blog;
use WC\Models\Linkery;
use WC\Valid;
use Phalcon\Mvc\Controller;
use WC\Link\Article;

class ArticleController extends BaseController {

    use \WC\Mixin\ViewPhalcon;

    public function titleAction($title) {
        $m = $this->getViewModel();
        // If necessary creates a new blog record (not saved).
        if (Article::findArticleTitle($title, $m)) {

            $m->body_container = "container";
            $m->back = null;
            $req = $this->getRequest();

            // see if previous page hint exists
            $ly = Valid::toInt($req, 'lnky', 0);
            if (!empty($ly)) {
                $linkery = Linkery::findFirstById($ly);

                if (!empty($linkery)) {
                    $m->back = '/linkery/view/' . $linkery->name;
                    $m->backname = $linkery->name;
                }
            }
            if (isset($req['sub'])) {
                $this->noLayouts();
            }
            return $this->render('index', 'article');
        } else {
            return $this->error("Article not found: $title");
        }
    }
}