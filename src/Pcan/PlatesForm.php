<?php

namespace Pcan;
use Plates\Engine;
use Plates\Extension\ExtensionInterface;
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
        if (!empty($type)) {
            $class = " class=\"" . $type . "\"";
        }
        else {
            $class = '';
        }
        return "<label for=\"$id\"  $class>$text</label>" . PHP_EOL;
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
        if (isset($pset['checked'])) {
            $val = $pset['checked'];
            unset($pset['checked']);
            if ($val) {
                $pset[] = 'checked'; //back as integer index  
            }
        }
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
    // display bool in simple span
    public function check_value($pset) {
        $out = '';
        if (isset($pset['label'])) {
            $out .= '<label>' . $pset['label'] . '</label>';
        }
        $checked = isset($pset['checked']) ? $pset['checked'] : false;
        if ($checked) {
            $out  .= "&nbsp;&#x2713;&nbsp;";
        }
        else {
            $out  .= "&nbsp;&#x2205;&nbsp;";
        }
        return $out;
    }
    // display datetime in simple span
    public function datetime_value($pset) {
        $out = '';
        if (isset($pset['label'])) {
            $out .= '<label>' . $pset['label'] . '</label>';
        }
        $value = isset($pset['value']) ? $pset['value'] : false;
        if ($value) {
            $out  .= "&nbsp;" . $value . "&nbsp;";
        }
        else {
            $out  .= "&nbsp;&#x2205;&nbsp;";
        }
        return $out;
    }
    // display text in simple span
    public function text_value($pset) {
        $out = '';
        if (isset($pset['label'])) {
            $out .= '<label>' . $pset['label'] . '</label>';
        }
        $value = isset($pset['value']) ? $pset['value'] : false;
        if ($value) {
            $out  .= "&nbsp;" . $value . "&nbsp;";
        }
        else {
            $out  .= "&nbsp;&#x2205;&nbsp;";
        }
        return $out;
 
    }
    public function recaptcha($pset)
    {
        $text = $pset['text'];
        $id = $pset['id'];
        $site = $pset['site'];
        if (!empty($pset['class'])) {
            $class = $pset['class'];
        } else {
            $class = "btn btn-outline-primary";
        }
        $out = <<<EOD
    <script>
        function cformSubmit(token) {
            document.getElementById("$id").submit();
        }
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <button class ="g-recaptcha $class" data-sitekey="$site" data-callback="cformSubmit">
        $text
    </button>
EOD;
        return $out;
    }
    
    public function xcheck($node)
    {
        return static::getTag($node, ['name' => 'xcheck', 'type' => 'hidden']);
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
    
    public function number(array $pset) {
        return $this->inputType($pset,'number');
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
    public function hidden($node)
    {
        return $this->inputType($node,  'hidden');
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
    
    static public function linkTo($pset)
    {
        if (isset($pset['href'])):
            $href = $pset['href'];
            unset($pset['href']);
        else:
            $href = '#';
        endif;

        if (isset($pset['icon'])):
            $icon = $pset['icon'];
            unset($pset['icon']);
        else:
            $icon = null;
        endif;
        if (isset($pset['text'])):
            $text = $pset['text'];
            unset($pset['text']);
        else:
            $text = $href;
        endif;
        $out = '<a href="' . $href . '"';
        foreach ($pset as $arg => $val):
            $out .= ' ' . $arg . '="' . $val . '"';
        endforeach;
        $out .= '>';
        if (!empty($icon)) {
            $out .= "<i class='$icon' ></i> ";
        }
        $out .= $text . '</a>';
        return $out;
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
    public function datetime($pset)
    {
        static::ensureIdValue($pset);
        $id = $pset['id'];
        $dateid = 'pick' . $pset['id'];
        $out = "<div class='input-group date' id='$dateid' data-target='nearest'>" . PHP_EOL;

        if (isset($pset['label'])) {
            $label = $pset['label'];
            $out .= "<label for='$id'>" . $label . "</label>" . PHP_EOL;
        }
        $out .= static::getTag($pset, ['type' => 'text',
                    'class' => "datetimepicker-input",
                    'data-target' => '#' . $dateid,
                    'size' => "15",
                    'maxlength' => "15"
        ]);
        $out .= PHP_EOL . "<div class=\"input-group-append\" data-target=\"#$dateid\" data-toggle=\"datetimepicker\">" . PHP_EOL;
        $out .= '<div class="input-group-text"><img src="/font/glyphicons_free/glyphicons/png/glyphicons-46-calendar.png"></div>' . PHP_EOL;
        $out .= "</div>" . PHP_EOL;
        $out .= "</div>" . PHP_EOL;
        $javaid = $id;
        if (strpos($javaid,'<?=') === 0) {
            $javaid = substr($id,3,strlen($id)-5 );
        }
        return $out;
    }
    
     public function multiline($pset)
     {
       static::ensureIdValue($pset);
        $id = $pset['id'];
        //$out = "<div class='" . static::FORMDIV . "'>" . PHP_EOL;
        if (isset($pset['label'])) {
            $out .= static::label($id, $pset['label']);
        }
        if (isset($pset['value'])) {
            $value = $pset['value'];
            unset($pset['value']);
        } else {
            $value = '';
        }
        //$pset['class'] = 'form-control';
        $out .= static::generateTag('textarea', $pset) . $value . '</textarea>' . PHP_EOL;
        //$out .= "</div>" . PHP_EOL;
        return $out;
    }
}


