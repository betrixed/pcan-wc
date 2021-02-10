<?php

namespace WC\Link;

use Masterminds\HTML5;

/**
 * @author michael
 */
class RssView
{

    public $text;

    public static function getProviderFilters(): array {
        return [
            "Guardian" => "The Guardian",
            "Fairfax" => "Fairfax Newspapers",
            "NewsLimited" => "The Australian",
            "ABC" => "Australian Broadcasting Commission",
            "SkepticalScience" => "Skeptical Science"
        ];
    }
    public static function pullContent($url): array
    {
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        // 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        if (empty($result)) {
            return [null, ""];
        } else {
            $mtype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        }
        return [$result, $mtype];
    }

    /** found a <figure with chidlren
     * 
     * @param type $node
     * @return string
     */
    static public function grabFigure($node)
    {
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
        } else {
            // skip other content inside figure
        }
        return $result;
    }

    static public function inlineLink($node)
    {
        $href = $node->getAttribute('href');
        $content = $node->textContent;
        return " <a href=\"$href\">$content</a> ";
    }

    static public function inlineImage($img)
    {
        $src = null;
        $result = null;
        $datasrc = $img->getAttribute('data-src');
        if (!empty($datasrc)) {
            $src = $datasrc;
        } else {
            $src = $img->getAttribute('src');
        }
        if (!empty($src)) {
            $uri = null;
            if (strpos($src, "data:image/") === 0) {
                $uri = $src;
            } else {
                list($content, $mstr) = static::pullContent($src);
                //$gd = imagecreatefromstring($image_data);
                if (str_starts_with($mstr, "image")) {
                    $got = preg_match("@(\w+/\w+).*@", $mstr, $match);
                    if ($got === 1 && count($match) > 1) {
                        $mtype = $match[1];
                        $msub = substr($mstr, strlen($mtype));
                    } else {
                        throw new \Exception("unknown mime: " . $mstr);
                    }
                    if (str_contains($mtype, "svg")) {
                        return $content . " </svg>";
                    } else if ($msub === ";base64") {
                        $uri = "data:" . $mtype . ";base64," . $content;
                    } else {
                        $uri = "data:" . $mtype . ";base64," . base64_encode($content);
                    }
                }
            }
            if (!empty($uri)) {
                return "<img src=\"$uri\">";
            } else {
                return "";
            }
        } else {
            
        }
        return $result;
    }

    static function inlineP($node)
    {
        $node = $node->firstChild;
        $s = "";
        while (!empty($node)) {
            if ($node->nodeType === XML_TEXT_NODE) {
                $s .= $node->textContent;
            } else if ($node->nodeType === XML_ELEMENT_NODE) {
                switch($node->nodeName) {
                    case 'a': 
                        $s .= self::inlineLink($node); 
                        break;
                    case 'img': 
                        $s .= self::inlineImage($node);
                        break;
                    default:
                        break;
                }
            }
            $node = $node->nextSibling;
        }
        return $s;
    }

    private function nodeHtml($node) {
        $tag = $node->nodeName;
        $this->text .= "<$tag>" . $node->nodeValue . "</$tag>";
    }
    private function guardian2($node)
    {
        while (!empty($node)) {
            switch ($node->nodeName) {
                case 'h1': {
                        if (!isset($this->hasH1)) {
                            $this->hasH1 = true;
                        } else {
                            $this->text .= "<h1>" . $node->nodeValue . "</h1>";
                        }
                    }
                    break;
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    $this->nodeHtml($node);
                    break;
                case 'p': {
                        $this->text .= '<p>' . self::inlineP($node) . '</p>';
                    }
                    break;
                case 'figure': {
                        $this->text .= self::grabFigure($node);
                    }
                    break;
                case 'img': {
                        $image_text = RssView::inlineImage($node);
                        if (!str_starts_with($image_text, "<svg")) {
                            $this->text .= $image_text;
                        }
                    }
                    break;
                DEFAULT:
                    if ($node->hasChildNodes()) {
                        $this->guardian2($node->firstChild);
                    }
                    break;
            }
            $node = $node->nextSibling;
        }
    }

    private function guardian1($node)
    {
        while (!empty($node)) {
            if ($node->nodeName === "img") {
                $image_text = RssView::inlineImage($node);
                if (!str_starts_with($image_text, "<svg")) {
                    $this->text .= $image_text;
                }
            } else if (in_array($node->nodeName, ['main', 'article'])) {
                $this->guardian2($node->firstChild);
            } else if ($node->hasChildNodes() && !in_array($node->nodeName, ['head', 'meta', 'script'])) {
                $this->guardian1($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }

    private function fairfax1($node)
    {
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

    private function newsImageCaption($node)
    {
        $img_list = $node->getElementsByTagName('img');
        $plist = $node->getElementsByTagName('p');
        if (!empty($img_list) && !empty($plist)) {
            $this->text .= PHP_EOL . "<figure>" . PHP_EOL;
            $this->text .= $this->inlineImage($img_list->item(0));
            $this->text .= "<figcaption>" . $plist->item(0)->nodeValue . "</figcaption>" . PHP_EOL;
            $this->text .= PHP_EOL . "</figure>" . PHP_EOL;
        }
    }

    private function newsLimited2($node)
    {
        while (!empty($node)) {
            if ($node->nodeName === "div") {
                $at_class = $node->getAttribute("class");
                $clist = explode(' ', $at_class);
                if ((in_array('image', $clist) && in_array('media', $clist)) || in_array('module-content', $clist)) {
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

    private function newsLimited1($node)
    {
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

    public function headerJSON($node): string
    {
        $head = $node->getElementsByTagName('head');
        if (count($head) < 1) {
            return '';
        }
        $nodelist = $head->item(0)->getElementsByTagName('script');

        foreach ($nodelist as $script) {
            $stype = $script->getAttribute('type');
            if ($stype === 'application/ld+json') {
                $this->text .= $script->nodeValue;
                break;
            }
        }
        return $this->text;
    }

    public function scanHeaderJSON(string $extract): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        return $this->headerJSON($node);
    }

    public function scanNewsLimited(string $extract): ?string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->newsLimited1($node);
        return $this->text;
    }
    
    private function skeptic1($node)
    {
        while (!empty($node)) {
            if ($node->nodeName === 'div') {
                $id = $node->getAttribute('id');
                if ($id === "mainbody") {
                    $this->guardian2($node->firstChild);
                    $node = $node->nextSibling;
                    continue;
                }
            }
            if ($node->hasChildNodes()) {
                $this->skeptic1($node->firstChild);
            }
            $node = $node->nextSibling;
        }
    }

    private function ABC3($node)
    {
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

    private function ABC2($node)
    {
        while (!empty($node)) {
            if ($node->nodeName === 'div') {
                $id = $node->getAttribute('id');
                if ($id === "body") {
                    $this->ABC3($node->firstChild);
                    $node = $node->nextSibling;
                    continue;
                }
            }
            if ($node->hasChildNodes()) {
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

    private function ABC1($node)
    {
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

    public function scanABC(string $extract): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->ABC1($node);
        return $this->text;
    }

    public function scanFairfax(string $extract): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->fairfax1($node);
        return $this->text;
    }

    public function scanGuardian(string $extract): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->guardian1($node);
        return $this->text;
    }
    public function scanSkepticalScience(string $extract): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($extract);
        $node = $dom->documentElement;
        $this->text = '';
        $this->skeptic1($node);
        return $this->text;
    }
}
