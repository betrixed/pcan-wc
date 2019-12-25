<?php
namespace Pcan;
/**  
 * User rendering object as controller->view
 * Fat Free built in View render
 */
use WC\UserSession;
use WC\Assets;


class Html {
    public $layout; // name of initial render file
    public $nav;    // name of navigation layout file
    public $usrSess; // instance of User Session
    public $flash;  // list of flash messages
    public $url;    // used as base URL 
    public $content;  // name of included content file
    public $f3;
    
    static public $browser;
    
    //! Front-end processorg
    static function getBrowser($u_agent) { 
      $bname = 'Unknown';
      $platform = 'Unknown';
      $version= "";

      //First get the platform?
      if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
      }elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
      }elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
      }

      // Next get the name of the useragent yes seperately and for good reason
      if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
        $bname = 'Internet Explorer';
        $ub = "MSIE";
      }elseif(preg_match('/Firefox/i',$u_agent)){
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
      }elseif(preg_match('/OPR/i',$u_agent)){
        $bname = 'Opera';
        $ub = "Opera";
      }elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
        $bname = 'Google Chrome';
        $ub = "Chrome";
      }elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
        $bname = 'Apple Safari';
        $ub = "Safari";
      }elseif(preg_match('/Netscape/i',$u_agent)){
        $bname = 'Netscape';
        $ub = "Netscape";
      }elseif(preg_match('/Edge/i',$u_agent)){
        $bname = 'Edge';
        $ub = "Edge";
      }elseif(preg_match('/Trident/i',$u_agent)){
        $bname = 'Internet Explorer';
        $ub = "MSIE";
      }
      else {
          $ub = "other";
      }

      // finally get the correct version number
      $known = array('Version', $ub);
      $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
      if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
      }
      // see how many we have
      $i = count($matches['browser']);
      if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }else {
            $version= $matches['version'][1];
        }
      }else {
        $version= $matches['version'][0];
      }

      // check if we have a number
      if ($version==null || $version=="") {$version="?";}

      return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
      );
    }

    public function __construct($f3,  $path = null, $ext = null) {
        $this->layout = 'layout.phtml';
        $this->nav = $f3->get('navigate');
        $this->f3 = $f3;
        if (is_null($path)) {
            $path = $f3->get('sitepath') . 'views';
        }
        //$f3->set('UI', $path . '/|' . $f3->get('pkg') . 'views/');
        
        //$agent = $f3->get('AGENT');
       
    }
    /**
     * @return string  rendered browser HTML content
     */
    public function render() {
        // see if UserSession exists, and has flash messages
        if (UserSession::hasInstance()) {
            $us = UserSession::instance();
            $this->usrSess = $us;
            $this->flash = $us->getMessages(); // clears messages
            $us->write(); // finalize session now
        }
        $plate = \Template::instance();
        TagViewHelper::register($plate);
        return $plate->render($this->layout);
    }
    public function assets($items) {
        $bundles = Assets::instance();
        $bundles->add($items);
    }

}
