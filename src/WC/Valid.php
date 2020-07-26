<?php

namespace WC;
/*
 * Each line should be prefixed with  * 
 */

/**
 * Simple validations, usually first parameter is the POST array
 * @author Michael Rynn
 */


        
class Valid {
    
    const REG_URL = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    const REG_BITCOIN = "/bitcoin/i";
    const DATE_FMT = 'Y-m-d';
    const DATE_TIME_FMT = 'Y-m-d H:i:s';
    const TIME_FMT = 'H:i:s';
    /**
     * Has greater than or equal to #n digits
     * @param string $value
     * @param int $ct
     * @return boolean
     */
    static function has_GEnDigits($value, $ct) {
        $phone = preg_replace( '/[^0-9]/', '', $value);
        return (strlen($phone) >= $ct) ? true : false;
    }
    /**
     * 
     * @param type $secret Google domain secret key
     * @param type $response Google verify user 'g-recaptcha-response'
     * @param type $remoteip end-user's ip address
     * @return type array['success', 'error-codes']
     */
    static public function recaptcha($secret, $response, $remoteip = null) {
        $args['secret'] =  $secret;
        $args['response'] = $response;
        
        if (!is_null($remoteip)) {
            $args['remoteip'] = $remoteip;
        }
        
        $url = 'https://www.google.com/recaptcha/api/siteverify' . '?'
                . http_build_query($args);
        $gdata = @file_get_contents($url);
        $result = json_decode($gdata, true);
        
        $apiresponse['success']=$result['success'];
        if (!$result['success'])  {
            if (isset($result['error-codes'])) {
                $apiresponse['error-codes']=$result['error-codes'];
            }
        }
        else {
            $apiresponse['error-codes']=0;
        }
        return $apiresponse;
    }
    /**
     * 
     * @param string $s
     * @param string $p
     * @return bool
     */
    static function startsWith(string $s, string $p) : bool
    {
        $n = strlen($p);
        return ($n <= strlen($s) && ($n > 0) && substr($s, 0, $n) === $n);
    }
    
    static public function url_slug($str)
    {
        #convert case to lower
        $str = strtolower($str);
        #remove special characters
        $str = preg_replace('/[^a-zA-Z0-9]/i', ' ', $str);
        #remove white space characters from both side
        $str = trim($str);
        #remove double or more space repeats between words chunk
        $str = preg_replace('/\s+/', ' ', $str);
        #fill spaces with hyphens
        $str = preg_replace('/\s+/', '-', $str);
        return $str;
    }
    
    
    /**
     * 
     * @param type $req
     * @param type $ix
     * @param type $default
     * @return type integer
     */
    static public function toInt($req,  $ix, $default=0) {
        if (!isset($req[$ix])) {
            return $default;
        }
        $sval =  filter_var( $req[$ix],  FILTER_VALIDATE_INT);
        return is_null($sval) ? $default : intval($sval);
    }
    
    /** return TRUE if string contains URL 
     * 
     * @param type $req
     * @param type $ix
     * @return bool 
     */
    static public function hasURL($hay, &$msg) {
        if (preg_match(self::REG_URL,$hay, $match)) {
            $msg = "URL not allowed here";
            return true;
        }
        return false;
    }
    
    // Front slash removed from file path
    static public function noFrontSlash($s) {
        if (substr($s,0,1) === '/') {
            return substr($s,1);
        }
        return $s;
    }
    // back slash added to file path
    static public function endSlash($s) {
        $slen = strlen($s);
        if (substr($s,$slen-1,1) !== '/') {
            return $s . '/';
        }
        return $s;
    }    
    static public function hasBitcoin($hay, &$msg) {
        if ( preg_match(self::REG_BITCOIN, $hay, $match) )
        {
            $msg = "Bitcoin not allowed here";
            return true;
        }
        return false;
    }
    static public function toStr($req, $ix, $default='') {
        if (!isset($req[$ix])) {
            return $default;
        }
        $sval = $req[$ix];
         if (is_null($sval)) {
            return $default;
        }
        return filter_var( $sval, FILTER_SANITIZE_STRING);
    }
    /**
     * Boolean as integer, 0 or 1
     * @param type $req
     * @param type $ix
     * @param type $default
     * @return boolean
     */
    static public function toBool($req, $ix, $default=0) {
        if (!isset($req[$ix])) {
            return $default;
        }
        return 1;
    }
    static public function toMoney($req, $ix, $default=false) {
        if (!isset($req[$ix])) {
            return $default;
        }
        $sval = $req[$ix];
         if (is_null($sval)) {
            return $default;
        }
        return filter_var( $sval, FILTER_SANITIZE_STRING);
    }
    static public function timeNow() {
        return date(self::TIME_FMT);
    }
    static public function now() {
        return date(self::DATE_TIME_FMT);
    }
     static public function today() {
        return date(self::DATE_FMT);
    }   
    static public function toPhone($req, $ix, $default='') {
        $text = static::toStr($req, $ix, null);
        if (!empty($text)) {
            // strip out whitespace and check all are numbers
            $phone = preg_replace( '/[^0-9]/', '', $text);
            if (strlen($phone) >= 8) {
                return $phone;
            }
        }
        return $default;
    }
    static public function toEmail($req, $ix, $default='') {
        $email = static::toStr($req, $ix, null);
        if (!empty($email)) {
            if (filter_var( $email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }
        return $default;
    }
    
    static public function toTime($req, $ix) {
        if (!isset($req[$ix])) {
            return static::timeNow();
        }
        $sval = $req[$ix];
         if (is_null($sval)) {
             return static::timeNow();
        }
        return filter_var( $sval, FILTER_SANITIZE_STRING);
    }
    
    static public function asDate($time, $format = 'Y-m-d')
    {
        if (!is_numeric($time))
            $time = strtotime($time); // convert string dates to unix timestamps
        return date($format, $time);
    }
    
    static private  function defaultDate($default) {
         if ($default === "today") {
             return static::today();
         }
         else { 
             return $default;
         }
    }
     static public function toDate($req, $ix, $default ="today") {
        if (!isset($req[$ix])) {
             return static::defaultDate($default);
        }
        $sval = $req[$ix];
         if (empty($sval)) {
            return static::defaultDate($default);
        }
        $temp = filter_var( $sval, FILTER_SANITIZE_STRING);
        $date = strtotime($temp); 
        return date(self::DATE_FMT, $date);
    }
    static public function toDateTime($req, $ix) {
        if (!isset($req[$ix])) {
            return static::now();
        }
        $sval = $req[$ix];
         if (is_null($sval)) {
             return static::now();
        }
        $temp = filter_var( $sval, FILTER_SANITIZE_STRING);
        $date = strtotime($temp); 
        return date(self::DATE_TIME_FMT, $date);
    }
    
    static public function randomStr()
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', base64_encode(openssl_random_pseudo_bytes(24)));
    }
}
