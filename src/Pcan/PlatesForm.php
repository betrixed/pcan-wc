<?php

namespace Pcan;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Pcan\Models\MenuTree;

class PlatesForm implements ExtensionInterface {

    protected $engine;
    public $template;

    public function getObject() {
        return $this;
    }

    public function register(Engine $engine) {
        $this->engine = $engine;

        $engine->registerFunction('form', [$this, 'getObject']);
    }

    static private function ensureIdValue(&$pset, $value = null) {
        if (isset($pset['name']) && !isset($pset['id'])):
            $pset['id'] = $pset['name'];
            if (!isset($pset['value'])):
                if (!empty($value)) {
                    $pset['value'] = $value;
                }
            endif;
        endif;
    }

    static public function generateTag($tag, $pset) {
        $out = '<' . $tag;
        foreach ($pset as $arg => $val):

            if (is_int($arg)) {
                $out .= ' ' . $val;
            } else {
                $out .= ' ' . $arg . '="' . $val . '"';
            }
        endforeach;
        $out .= '>';
        return $out;
    }

    static public function mergeClass(&$pset, $default) {
        /* class is a multi value attribute */
        if (isset($default['class']) && isset($pset['class'])) {
            $tmp = $default['class'] . ' ' . $pset['class'];
            $default['class'] = $tmp;
            unset($pset['class']);
        }
        $pset = array_merge($default, $pset);
    }

    static public function getTag(&$pset, $default, $tag = 'input') {
        static::mergeClass($pset, $default);
        $out = static::generateTag($tag, $pset);
        return $out;
    }

    static public function label($id, $text, $type='') {
        return "<label for=\"$id\" class=\"$type\">$text</label>" . PHP_EOL;
    }

    static public function submit($node = []) {
        return static::getTag($node, ['type' => 'submit', 'value' => 'Submit']) . PHP_EOL;
    }

    public function checkbox(array $pset) {
        static::ensureIdValue($pset);
        $id = $pset['id'];
        $out = '';
        $wrapdiv = isset($pset['div']) ? $pset['div'] : false;
        if ($wrapdiv) {
            $out .= '<div class="' . $wrapdiv . '">';
            unset($pset['div']);
        }
        $text = isset($pset['label']) ?  $pset['label'] : '';
        if (!empty($text)) {
            unset($pset['label']);
        } else {
            $text = isset($pset['text']) ?  $pset['text'] : '';
        }
        if (!empty($text)) {
            unset($pset['text']);
        }
        $out = '<label class=' . "\"checkbox\">";
        $out .= static::getTag($pset, ['type' => 'checkbox']);
        if (!empty($text)) {
            $out .= ' ' . $text; // auto space one character
        }

        $out .= "</label>" . PHP_EOL;
        if ($wrapdiv) {
            $out .= '</div>';
        }
        return $out;
    }


    public function inputType(array $pset, $type = '') {
        static::ensureIdValue($pset);
        $id = $pset['id'];
        $out = '';
        $wrapdiv = isset($pset['div']) ? $pset['div'] : false;
        
        if ($wrapdiv) {
            $out .= '<div class="' . $wrapdiv . '">';
            unset($pset['div']);
        }
        if (isset($pset['label'])) {
            $out .= static::label($id, $pset['label'], $type);
        }

        
        $out .= static::getTag($pset, ['type' => $type]) . PHP_EOL;
        
        if ($wrapdiv) {
            $out .= '</div>';
        }
        return $out;
    }
    /**
     * 
     * @param array $pset
     * @return string
     */
    public function plainText(array $pset) {
        return $this->inputType($pset,'text');
    }
    public function email(array $pset)
    {
        if (!isset($pset['placeHolder']))
        {
            $pset['placeHolder'] = 'your@email.domain';
        }
        if (!isset($pset['aria-describedby'])) {
            $pset['aria-describedby'] = 'emailHelp';
        }
        return $this->inputType($pset,'email');
    }
    public function phone(array $pset) {
        return $this->inputType($pset,'tel');
    }
    public function password(array $pset)
    {
        return $this->inputType($pset,'password');
    }

    static public function addAttr($name, $pset) {
        if (isset($pset[$name])) {
            return ' ' . $name . '="' . $pset[$name] . '"';
        } else
            return "";
    }
    static public function dropDown(array $pset) {
       static::ensureIdValue($pset);

        $out = "<li class=\"nav-item dropdown\">" . PHP_EOL;
        $menuName = isset($pset['root']) ? $pset['root'] : -1;
        $mid = "dd_m" . $menuName;
        
        $out .= "<a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"$mid\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">";
        $title = isset($pset['title']) ? $pset['title'] : null;
        $out .= PHP_EOL . $title . PHP_EOL;
        $out .= "</a>" . PHP_EOL;
        $out .= "<div class=\"dropdown-menu\" aria-labelledby=\"$mid\">" . PHP_EOL;

        $tree = MenuTree::getMenuSet($menuName);
        $out .= MenuTree::generateSubMenu($pset, $tree);
        
        $out .= PHP_EOL . "</div>" . PHP_EOL;
        $out .= "</li>" . PHP_EOL;
        return $out;   
    }
    static public function select($pset) {
        $out = "";
        if (isset($pset['label'])) {
            $out .= static::label($id, $pset['label']);
        }
        if (isset($pset['list'])) {
            // value contains a php variable between <?= 
            $val = $pset['list'];
            $curval = isset($pset['value']) ? $pset['value'] : null;
            $out .= '<select ';

            $out .= self::addAttr('id', $pset);
            $out .= self::addAttr('class', $pset);
            $out .= self::addAttr('name', $pset);

            $out .= '>' . PHP_EOL;

            foreach ($val as $skey => $sval):
                if (is_null($curval)):
                    $curval = $skey;
                endif;
                $selected = ($skey === $curval) ? " selected" : "";
                $out .= '<option value="' . $skey . '" ' . $selected . '>' . $sval . '</option>' . PHP_EOL;
            endforeach;
            $out .= '</select>' . PHP_EOL;
            return $out;
        }
    }

}

;

