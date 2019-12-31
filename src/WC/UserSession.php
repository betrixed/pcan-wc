<?php
namespace WC;
/**
 * What gets stored in session serialized data
 *
 * @author michael
 */
class UserSession extends \Prefab {

    //put your code here
    public $userName;
    public $roles;
    public $email;
    public $id; // database record user id
    public $status;
    public $keys;
    public $doWrite = false;
    
    static private $session;
    
    static public function shutdown() {
        if (!\Registry::exists(__CLASS__)) {
            return;
        }
        $us = UserSession::instance();
        if ($us && $us->doWrite) {
            $us->write();
        }
    }
    
    public function delayWrite() {
        if (!$this->doWrite) {
            $this->doWrite = true;
            //register_shutdown_function(__CLASS__ . "::shutdown");
        }
    }
    public function __construct() {
        $this->keys['flash'] = [];
        static::session();
    }

    static public function flash($msg, $extra = null, $status = 'info') {
        if (!empty($extra)) {
            foreach($extra as $line) {
                $msg .= '<br>' . PHP_EOL . $line;
            }
        }
        UserSession::instance()->addMessage($msg, $status);
    }
    
    static public function session() {
        if (empty(static::$session)) {
            static::$session = new \Session();
        }
        return static::$session;
    }

    public function hasRole($role) {
        if (!is_array($this->roles)) {
            return false;
        }
        $key = array_search($role, $this->roles);
        return ($key !== false);
    }
    public function hasUser() {
        return !empty($this->id);
    }
    public function isGuest() {
        return ($this->id === 0) ? true : false;
    }
    
    public function setGuest() {
        $this->userName = 'Guest';
        $this->id = 0;
        $this->status = 'OK';
        $this->email = '';
        $this->roles = ['Guest'];
        $this->delayWrite();
    }
    
    public function setUser($user) {
        $this->userName = $user->get('name');
        $this->id = $user->get('id');
        $this->status = $user->get('status');
        $this->email = $user->get('email');
        $this->roles = $user->getRoleList();
        $this->delayWrite();
    }

    public function wipe() {
        $this->setGuest();
        $this->keys = [];
    }

    /** Write is performed by fat free hive persist */
    public function write() {
        if ($this->doWrite) {
            $this->doWrite = false;
            $f3 = \Base::instance();
            $f3->set('SESSION.userdata', $this);
        }
    }

    public function updated() {
        $sess = static::session();
        $sess->setData($this);
    }

    /**
     * dump all messages and clear them
     * @return array
     */
    public function getMessages() {
        $out = $this->keys['flash'];
        if (!empty($out)) {
            $this->clearMessages();
        }
        return $out;
    }

    /**
     * reset message stack
     */
    public function clearMessages() {
        $this->keys['flash'] = [];
        $this->delayWrite();
    }

    /**
     * check if there messages in the stack
     * @return bool
     */
    public function hasMessages() {
        return !empty($this->msg);
    }

    public function addMessage($text, $status = 'info') {
        if (!isset($this->keys['flash'])) {
            $this->keys['flash'] = [];
        }
        $this->keys['flash'][] = ['text' => $text, 'status' => $status];
        $this->delayWrite();
    }

    /**
     * set a flash key
     * @param $key
     * @param bool $val
     */
    public function setKey($key, $val = TRUE) {
        $this->keys[$key] = $val;
        $this->delayWrite();
    }

    /**
     * get and clear any key, if it's existing
     * @param $key 
     * @param $clear boolean
     * @return mixed|null
     */
    public function getKey($key, $clear=false) {
        $out = NULL;
        if ($this->hasKey($key)) {
            $out = $this->keys[$key];
            if ($clear) {
                unset($this->keys[$key]);
                $this->delayWrite();
            }
        }
        return $out;
    }

    static public function hasInstance() {
        return \Registry::exists(__CLASS__);
    }
    /**
     * check if there's a  key existing
     * @param $key
     * @return bool
     */
    public function hasKey($key) {
        return ($this->keys && array_key_exists($key, $this->keys));
    }
     /**
      *  Static  read returns and sets instance
      * @return UserSession or null
      */
    static public function read()  {
        //$s1 = session_status();
        //$id = session_id();
        //$name = session_name();
        // Get FatFree request session parameters
        $sess = static::session();
        //$id2 = session_id();
        $f3 = \Base::instance();
        // Get stored UserSession if any
        $us = $f3->get('SESSION.userdata'); 
        if (!is_null($us)) {
            $f3->set('JAR.lifetime',30*60);
            \Registry::set( get_class($us), $us);
            //$cookie = session_get_cookie_params();
        }
        return $us;
    }
    /**
        @return UserSession
    */
    static public function guestSession()  {
            $us = UserSession::instance();
            $us->setGuest();
            $us->write();
            return $us;
    }
/**
        @return UserSession
    */
    static public function activate()  {
        //$s1 = session_status();
        //$sess = static::session();
        //if (! \Registry::exists(__CLASS__)) {
            $us = UserSession::instance();
            $us->doWrite = true;
            $us->write();
        //}
         //$s2 = session_status();
        return $us;
    }
    static public function getURL($f3) {
        $server = &$f3->ref('SERVER');
        $scheme = $server['REQUEST_SCHEME'];
        $host = $server['HTTP_HOST'];
        return $scheme . '://' . $host . $server['REQUEST_URI'];
    }
    
    static public function  https($f3) {
        $server = &$f3->ref('SERVER');
        if ($server['REQUEST_SCHEME'] !== 'https') {
            $ssl_host = $f3->get('ssl_host');
            $host = $server['HTTP_HOST'];
            // This is because a ssl certificate required a www.NAME
            if (!empty($ssl_host)) {
                $ssl_host = $ssl_host . '.';
                if (strpos($host, $ssl_host) !== 0) {
                    $host = $ssl_host . $host;
                }
            }    
            static::reroute('https://'  . $host . $server['REQUEST_URI']);
            return false;
        }
        return true;
    }
    static public function auth($role) {
        $us = static::read();
        return (!empty($us) && $us->hasRole($role));
    }

    static public function sessionName() {
        if (!\Registry::exists(__CLASS__)) {
            return 'NULL';
        }
        $us = static::instance();
        if (!empty($us->userName)) {
            return $us->userName;
        }
        return 'ANON';
    }
    
    static public function reroute($url) {
        static::save();
        \Base::instance()->reroute($url);
    }
    static public function save() {
         if (static::hasInstance()) {
            $us = UserSession::instance();
            $us->write(); // finalize session now
        }       
    }

    
    static public function isLoggedIn($role) {
        if (!\Registry::exists(__CLASS__)) {
            return false;
        }
        $us = static::instance();
        return (!empty($us) && $us->hasRole($role)) ? true : false;
    }
}
