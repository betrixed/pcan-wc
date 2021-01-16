<?php

namespace WC\Controllers;

/**
 * Extract RSS from varied sources, and extract matches to 
 * selected categories
 *
 * @author michael
 */
class RssController extends \Phalcon\Mvc\Controller {

    use \WC\Mixin\ViewPhalcon;

    const RSS_Sources = [
        'https://www.theguardian.com/science/rss',
        'https://www.smh.com.au/rss/environment.xml'
    ];
    const RSS_Topics = ['Climate change', 'Environment', 'Energy storage'];

    public function fetchAction() {
        $all = [];
        foreach (self::RSS_Sources as $url) {
            $items = $this->fetch_items($url);
            if (!empty($items)) {
                $all = array_merge($all, $items);
            }
        }
        $view = $this->getView();
        $m = $view->m;
        $m->items = $all;
        return $this->render('rss', 'guardian');
    }

    /**
     * RSS structure -- 
     * rss/channel
     *     -- title
     *     -- link
     *     -- description
     *     -- language
     *     -- copyright
     *     -- pubDate { long form }
     *     -- dc:date { standard date time }
     *     -- dc:language
     *     -- dc:rights
     *     -- image
     *     -- item
     *        -- title, link, description, category*, pubDate, guid
     *           media:content* ( media:credit }
     *           dc:date
     *           dc:creator
     */
    public function fetch_items($url): array {
        $rss = simplexml_load_file($url);
        $match = [];
        if (!empty($rss)) {
            $i = 0;
            foreach ($rss->channel as $chan) {
                foreach ($chan->item as $item) {
                    $rss_source = new \stdClass();
                    $rss_source->title = strval($chan->image->title);
                    $rss_source->url = $url;
                    $rss_source->image = strval($chan->image->url);
                    $catlist = [];
                    foreach ($item->category as $cat) {
                        $attr = $cat->attributes();
                        $attr_domain = strval($attr['domain']);
                        $catlist[$attr_domain] = strval($cat);
                    }
                    //foreach ($catlist as $ckey => $cvalue) {
                    //if (array_search($cvalue, self::RSS_Topics)) {
                    $ni = new \stdClass();
                    //d$ni->domains = $catlist;
                    $ni->catlist = array_unique(array_values($catlist));
                    //$ni->date = $item->
                    $ni->title = strval($item->title);
                    $ni->link = strval($item->link);
                    $ni->description = strval($item->description);

                    $ns = $item->getNamespaces(true);
                    $dc = $item->children($ns['dc']);
                    $ni->creator = strval($dc->creator);
                    $ni->date = strval($dc->date);

                    $images = [];
                    if (isset($ns['media'])) {
                        $media = $item->children($ns['media']);
                        $biggest = null;
                        foreach ($media->content as $m) {
                            $img = new \stdClass();
                            foreach ($m->attributes() as $a => $b) {
                                $img->$a = strval($b);
                            }
                            $img->credit = strval($m->credit);

                            if (empty($biggest)) {
                                $biggest = $img;
                            } else {
                                if ($img->width > $biggest->width) {
                                    $biggest = $img;
                                }
                            }
                        }
                        $ni->image = $biggest;
                        $ni->source = $rss_source;
                        $match[] = $ni;
                    }
                    else {
                        // SMH no media
                        $ni->source = $rss_source;
                        $match[] = $ni;
                    }
                }
            }
        }
        return $match;
    }

}
