<?php

namespace App\Link;
use Masterminds\HTML5;
/**
 * @author michael
 */
class RssView {

    public $text;

    public static function pullContent($url) {
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        if (empty($result)) {
            $result = "";
        }
        return $result;
    }

    static public function grabFigure($node) {
        $result = '';
        $list = $node->getElementsByTagName('img');
        $ct = 0;
        foreach ($list as $img) {
            $image_text = static::inlineImage($img);
            if (!empty($image_text)) {
                $result .= PHP_EOL . "<figure>" . PHP_EOL;
                $result .= PHP_EOL . $image_text;
                $ct += 1;
                break;
            }
        }
        if ($ct > 0) {
            $list = $node->getElementsByTagName('figcaption');
            foreach ($list as $caption) {
                $result .= "<figcaption>$caption->textContent</figcaption>";
                break;
            }
            $result .= "</figure>";
        }
        return $result;
    }

    static public function inlineImage($img) {
        $src = null;
        $datasrc = $img->getAttribute('data-src');
        if (!empty($datasrc)) {
            $src = $datasrc;
        }
        else {
            $src = $img->getAttribute('src');
        }
        if (!empty($src)) {
            $uri = null;
            if (strpos($src,"data:image/" ) === 0) {
                $uri = $src;
            }
            else {
                $image_data = static::pullContent($src);
            //$gd = imagecreatefromstring($image_data);
                 if (!empty($image_data)) {
                    $uri = "data:image/png;base64," . base64_encode($image_data);
                 }
            }
            if (!empty($uri)) {
                return "<img src=\"$uri\">";
            }
            else {
                return "";
            }
        }
        else {
            
        }
        return $result;
    }

    

    private function fairfax1($node) {
        while (!empty($node)) {
            if ($node->nodeName === "p") {
                $this->text .= "<p>" . $node->textContent . "</p>";
            } else if ($node->nodeName == "figure" && $node->hasChildNodes()) {
                $this->text .= RssView::grabFigure($node);
            } else if ($node->nodeName == "img") {
                $image_text = RssView::inlineImage($node);
                $this->text .= $image_text;
            } else if ($node->hasChildNodes()) {
                $this->fairfax1($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }
    
 private function newsImageCaption($node) {
    $img_list = $node->getElementsByTagName('img');
    $plist = $node->getElementsByTagName('p');
    if (!empty($img_list) && !empty($plist)) {
        $this->text .= PHP_EOL . "<figure>" . PHP_EOL;
        $this->text .= $this->inlineImage($img_list->item(0));
        $this->text .= "<figcaption>" . $plist->item(0)->nodeValue . "</figcaption>" . PHP_EOL;
        $this->text .= PHP_EOL . "</figure>" . PHP_EOL;
    }     
 }
private function newsLimited2($node) {
        while (!empty($node)) {
            if ($node->nodeName === "div") {
                $at_class = $node->getAttribute("class");
                $clist = explode(' ', $at_class);
                if ( (in_array('image',$clist) && in_array('media',$clist)) || in_array('module-content', $clist)) {
                    // expect img and caption
                    $this->newsImageCaption($node);
                    $node = $node->nextSibling;
                    continue;
                }
            }
            if ($node->nodeName === "p") {
                $this->text .= "<p>" . $node->textContent . "</p>";
            } else if ($node->nodeName == "figure" && $node->hasChildNodes()) {
                $this->text .= RssView::grabFigure($node);
            } else if ($node->nodeName == "img") {
                $image_text = RssView::inlineImage($node);
                $this->text .= $image_text;
            } else if ($node->hasChildNodes()) {
                $this->newsLimited2($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }
    private function newsLimited1($node) {
        while (!empty($node)) {
            if ($node->nodeName === 'article') {
                $id = $node->getAttribute('id');
                if ($id === "story") {
                    $this->newsLimited2($node->firstChild);
                     $node = $node->nextSibling;
                     continue;
                }
            }
            if ($node->hasChildNodes()) {
                $this->newsLimited1($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }

    public function headerJSON($node) : string {
        $head = $node->getElementsByTagName('head');
        if (count($head) < 1) {
           return '';
        }
        $nodelist = $head->item(0)->getElementsByTagName('script');
        
        foreach($nodelist as $script) {
                $stype = $script->getAttribute('type');
                if ($stype === 'application/ld+json' )
                {
                    $this->text .= $script->nodeValue;
                    break;
                }
            }
        return $this->text;
    }
    public function scanHeaderJSON(string $extract) : string {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        return $this->headerJSON($node);
    }

    public function scanNewsLimited(string $extract): ?string {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->newsLimited1($node);
        return $this->text;
    }
    
    
 
    private function ABC3($node) {
        while (!empty($node)) {
            if ($node->nodeName === "aside") {
                $node = $node->nextSibling;
                continue;
            }
            if ($node->nodeName === "p") {
                $this->text .= "<p>" . $node->textContent . "</p>";
            } else 
            if ($node->nodeName == "figure" && $node->hasChildNodes()) {
                $this->text .= RssView::grabFigure($node);
            } else
            if ($node->nodeName == "img") {
                $image_text = RssView::inlineImage($node);
                $this->text .= $image_text;
            } else if ($node->hasChildNodes()) {
                $this->ABC3($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }
    
    private function ABC2($node) {
        while (!empty($node)) {
            if ($node->nodeName === 'div') {
                   $id = $node->getAttribute('id');
                   if ($id === "body") {
                            $this->ABC3($node->firstChild);
                              $node = $node->nextSibling; 
                              continue;
                   }
             }
            if ($node->hasChildNodes()) 
            {
                if ($node->nodeName == "figure") {
                    $this->text .= RssView::grabFigure($node);
                     $node = $node->nextSibling;
                     continue;
                }
                $this->ABC2($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }
    private function ABC1($node) {
        while (!empty($node)) {
            if ($node->nodeName === 'article') {
                    $this->ABC2($node->firstChild);
                     $node = $node->nextSibling;
                     continue;
             }
            
            if ($node->hasChildNodes()) {
                $this->ABC1($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }
    
 public function scanABC(string $extract): string {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->ABC1($node);
        return $this->text;
    }
    public function scanFairfax(string $extract): string {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->fairfax1($node);
        return $this->text;
    }

}
