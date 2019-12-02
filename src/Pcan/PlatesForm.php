<?php

namespace Pcan;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

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

    static public function label($id, $text) {
        return "<label for='$id' class='label'>" . $text . "</label>" . PHP_EOL;
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
        $text = $pset['label'] ?? '';
        if (!empty($text)) {
            unset($pset['label']);
        } else {
            $text = $pset['text'] ?? '';
        }
        if (!empty($text)) {
            unset($pset['text']);
        }
        $out = '<label class=' . "\"checkbox\">";
        $out .= static::getTag($pset, ['type' => 'checkbox']);
        if (!empty($text)) {
            $out .= $text;
        }

        $out .= "</label>" . PHP_EOL;
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
        static::ensureIdValue($pset);
        $id = $pset['id'];
        $out = '';
        $wrapdiv = isset($pset['div']) ? $pset['div'] : false;
        
        if ($wrapdiv) {
            $out .= '<div class="' . $wrapdiv . '">';
            unset($pset['div']);
        }
        if (isset($pset['label'])) {
            $out .= static::label($id, $pset['label']);
        }

        
        $out .= static::getTag($pset, ['type' => 'text']) . PHP_EOL;
        
        if ($wrapdiv) {
            $out .= '</div>';
        }
        return $out;
    }

    static public function addAttr($name, $pset) {
        if (isset($pset[$name])) {
            return ' ' . $name . '="' . $pset[$name] . '"';
        } else
            return "";
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

