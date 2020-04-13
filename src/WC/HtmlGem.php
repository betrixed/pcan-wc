<?php

namespace WC;
use App\Link\MenuTree;

class HtmlGem {
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

    static function generateTag($tag, $pset) {
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

    static function mergeClass(&$pset, $default) {
        /* class is a multi value attribute */
        if (isset($default['class']) && isset($pset['class'])) {
            $tmp = $default['class'] . ' ' . $pset['class'];
            $default['class'] = $tmp;
            unset($pset['class']);
        }
        $pset = array_merge($default, $pset);
    }

    static function getTag(&$pset, $default, $tag = 'input') {
        static::mergeClass($pset, $default);
        $out = static::generateTag($tag, $pset);
        return $out;
    }

    /**
     * Return label tag with attributes
     * @param type $id
     * @param type $lclass
     * @return string
     */
    static function label_front($id, $lclass='')  {
        if (!empty($lclass)) {
            $class = " class=\"" . $lclass . "\"";
        }
        else {
            $class = '';
        }
        return "<label for=\"$id\"  $class>";
    }

    static function submit($node = []) {
        return static::getTag($node, ['type' => 'submit', 'value' => 'Submit']) . PHP_EOL;
    }

    static function checkbox(array $pset) {
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
    static function check_value($pset) {
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
    /**
     *  display datetime , optional label and forma
     * @param array $pset
     * @return string
     */
    static function datetime_value($pset) {
        $out = '';
        if (isset($pset['label'])) {
            $out .= '<label>' . $pset['label'] . '</label>';
        }
        $value = isset($pset['value']) ? $pset['value'] : false;
        $fmt = isset($pset['format']) ? $pset['format'] : null;
        if ($fmt) {
             if (!is_numeric($value))
                $time = strtotime($value); // convert string dates to unix timestamps
            $value = date($fmt, $value);
        }
        if ($value) {
            $out  .= "&nbsp;" . $value . "&nbsp;";
        }
        else {
            $out  .= "&nbsp;&#x2205;&nbsp;";
        }
        return $out;
    }
    // display text in simple span
    static function text_value($pset) {
        $out = '';
        if (isset($pset['label'])) {
            $out .= '<label class="label">' . $pset['label'] . '</label>';
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
    static function invisiCaptcha($pset) {
        $site = $pset['site'];
        $out = <<<EOD
   <input type="hidden" id="captcha-response" name="captcha-response" >
   <script>
        function startCaptcha() {
           
        }
        function storeToken(token){
            document.getElementById('captcha-response').value = token; 
        }
    </script>
   <div class="g-recaptcha" data-sitekey="$site" data-callback="storeToken" data-size="invisible" data-badge="inline"></div>
   <script src="https://google.com/recaptcha/api.js?onload=startCaptcha" async defer></script>
EOD;
        return $out;
    }
    static function recaptcha($pset)
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
    
    static function xcheck($node)
    {
        if (!isset($node['name'])) {
            $node['name'] = 'xcheck';
        }
        return static::getTag($node, ['type' => 'hidden']);
    }
    
    static function inputType(array $pset, $type = '') {
        static::ensureIdValue($pset);
        $id = $pset['id'];
        $out = '';
        $wrapdiv = isset($pset['div']) ? $pset['div'] : false;
        
        if ($wrapdiv) {
            $out .= '<div class="' . $wrapdiv . '">';
            unset($pset['div']);
        }
        
        if (isset($pset['label'])) {
             $label = '<label for="' . $id . '" class="label" > ' . $pset['label'] . ' ';
             $label_end = ' </label>';
             unset($pset['label']);
        }
        else {
            $label = '';
            $label_end = '';
        }

        
        $out .= $label . static::getTag($pset, ['type' => $type]) . $label_end . PHP_EOL;
        
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
    static function plainText(array $pset) {
        return static::inputType($pset, 'text');
    }
    
    static function number(array $pset) {
        return static::inputType($pset,'number');
    }
    
    static function email(array $pset)
    {
        if (!isset($pset['placeHolder']))
        {
            $pset['placeHolder'] = 'your@email.domain';
        }
        if (!isset($pset['aria-describedby'])) {
            $pset['aria-describedby'] = 'emailHelp';
        }
        return static::inputType($pset,'email');
    }
    static function hidden($node)
    {
        return static::inputType($node,  'hidden');
    }
    static function money(array $pset) {
        return static::inputType($pset,'money');
    }
    static function phone(array $pset) {
        return static::inputType($pset,'tel');
    }
    static function password(array $pset)
    {
        return static::inputType($pset,'password');
    }

    static function price($value) {
         setlocale(LC_MONETARY, 'en_AU');
         $v = floatval($value);
         return money_format('%!10.2n', $v);
    }
    static function addAttr($name, $pset) {
        if (isset($pset[$name])) {
            return ' ' . $name . '="' . $pset[$name] . '"';
        } else
            return "";
    }
    
    static function linkTo($pset)
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
    

    static function dropDown(array $pset) {
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
    static function select($pset) {
        $out = "";
        if (isset($pset['label'])) {
            $out .=  static::label_front($id, 'label') . ' ' . $pset['label']
                    . ' ';
            unset($pset['label']);
            $label_end = '</label>';
        }
        else {
            $label_end = '';
        }
       
        if (isset($pset['list'])) {
            // value contains a php variable between <?= 
            $val = $pset['list'];
            $curval = isset($pset['value']) ? $pset['value'] : null;
            $out .= '<div class="select"><select';

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
            $out .= '</select></div>' . $label_end . PHP_EOL;
            return $out;
        }
    }
    static function datetime($pset)
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
    
     static function multiline($pset)
     {
       static::ensureIdValue($pset);
        $id = $pset['id'];
        //$out = "<div class='" . static::FORMDIV . "'>" . PHP_EOL;
        if (isset($pset['label'])) {
            $out .= static::label_front($id, 'label' ) . ' ' . $pset['label'] . ' ';
            $label_end = '</label>';
        }
        else {
            $label_end = '';
        }
        if (isset($pset['value'])) {
            $value = $pset['value'];
            unset($pset['value']);
        } else {
            $value = '';
        }
        //$pset['class'] = 'form-control';
        $out .= static::generateTag('textarea', $pset) . $value . '</textarea>' . $label_end . PHP_EOL;
        //$out .= "</div>" . PHP_EOL;
        return $out;
    }
}


