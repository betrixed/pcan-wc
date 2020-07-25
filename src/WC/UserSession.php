<?php

namespace WC;

/**
 * What gets stored in session serialized data
 *
 * @author michael
 */


class UserData {

    //put your code here
    public $userName;
    public $roles;
    public $email;
    public $id = 0; // database record user id
    public $status;
    public $keys = [];

    public function hasAnyRole(array $roles): bool {

        if (!is_array($this->roles)) {
            return false;
        }
        foreach ($roles as $name) {
            if (array_search($name, $this->roles) !== false) {
                return true;
            }
        }
        return false;
    }
    public function getFlash(): array {
        $out = $this->keys['flash'] ?? [];
        if (!empty($out)) {
            $this->clearFlash();
        }
        return $out;
    }
    public function hasRole($role): bool {
        if (!is_array($this->roles)) {
            return false;
        }
        return (array_search($role, $this->roles) !== false);
    }
    public function hasUser() {
        return !empty($this->id);
    }

    public function isGuest() {
        return ($this->id === 0) ? true : false;
    }

    public  function setGuest() {
        $this->setValidUser('Guest', ['Guest']);
    }

    public function wipe() {
        $this->keys = [];
    }
    /** To be real is to be persisted
     * 
     * @param type $name
     * @param type $roles
     */
    public function setValidUser($name, $roles) {
        $this->id = 0;
        $this->status = 'OK';
        $this->email = '';
        $this->userName = $name;
        $this->roles = $roles;
    }
    public function addFlash($text, $status = 'info') {
        if (!isset($this->keys['flash'])) {
            $this->keys['flash'] = [];
        }
        $this->keys['flash'][] = ['text' => $text, 'status' => $status];
    }
    public function auth($role) : bool
    {
        if (is_string($role)) {
            return $this->hasRole($role);
        }
        if (is_array($role)) {
            return $role->hasAnyRole($role);
        }
        return false;
    }
}

use WC\App;

class UserSession {

    protected $doWrite = false;
    protected $session;
    protected $data;
    protected $app;

    /*
      static private $instance;

      static public function instance()
      {
      if (!isset(self::$instance)) {
      self::$instance = new UserSession();
      }
      return self::$instance;
      }
     */

    public function shutdown() {
        $this->write();
    }

    public function delayWrite() {
        $this->doWrite = true;
    }

    public function __construct(App $app) {
        $this->app = $app;
        $data = new UserData();
        $this->data = $data;

        $data->keys['flash'] = [];
    }

    /**
     * Inject popup notices into HTML, or leave in session
     * @param string $msg
     * @param type $extra
     * @param type $status 
     */
    public function flash($msg, $extra = null, $status = 'info') {
        if (!empty($extra)) {
            foreach ($extra as $line) {
                $msg .= '<br>' . PHP_EOL . $line;
            }
        }
        $this->addFlash($msg, $status);
    }

    /** implementation framework session object */
    public function getSession() {
        if (empty($this->session)) {
            $this->session = $this->app->services->get('session');
        }
        return $this->session;
    }
    /**
     * Set from \WC
     * @param type $user, with get('') properties
     * id, status, email, name, and 
     * getRoleList()
     */
    public function setUser($user) {
        $this->data->setUser($user);
        $this->delayWrite();
    }
    
    public function setGuest() {
        $this->data->setGuest();
    }

    /*     * get comma separated list for display */

    public function roles(): string {
        $data = $this->data;
        $outs = '';
        foreach ($data->roles as $name) {
            if (!empty($outs)) {
                $outs .= ', ';
            }
            $outs .= $name;
        }
        return $outs;
    }

    public function wipe() {
        $data = $this->data;
        $data->wipe();
        $data->setGuest();
        $this->delayWrite();

    }

    /** Write is performed by fat free hive persist */
    public function write() {
        if ($this->doWrite) {
            $this->doWrite = false;
            $session = $this->getSession();
            $session->userData = $this->data;
        }
    }

    public function updated() {
       $this->getSession()->userData = $this->data;
        //$this->session->setData($this->data);
    }

    /**
     * dump all messages and clear them
     * @return ?array
     */
    public function getFlash(): ?array {
        $out = $this->keys['flash'] ?? null;
        if (!empty($out)) {
            $this->clearFlash();
        }
        return $out;
    }

    /**
     * reset message stack
     */
    public function clearFlash() {
        $this->keys['flash'] = [];
        $this->delayWrite();
    }

    /**
     * check if there messages in the stack
     * @return bool
     */
    public function hasValues() {
        return !empty($this->data->keys);
    }

    /** clean up the fat free session and user data */
    public function destroy() {
        if (empty(static::$session)) {
            return;
        }
        static::$session->remove('userData');
        $adapter = static::$session->getAdapter();
        $adapter->gc(24 * 60 * 60);
        static::$session->destroy();
    }

    public function addFlash($text, $status = 'info') {
        $this->data->addFlash($text,$status);
        $this->delayWrite();
    }

    /**
     * set a flash key
     * @param $key
     * @param bool $val
     */
    public function setKey($key, $val = TRUE) {
        $this->data->keys[$key] = $val;
        $this->delayWrite();
    }

    /**
     * get and clear any key, if it's existing
     * @param $key 
     * @param $clear boolean
     * @return mixed|null
     */
    public function getKey($key, $clear = false) {
        $data = $this->data;
        $value = $data->keys[$key] ?? null;
        
        if ($value !== null) {
            if ($clear) {
                unset($data->keys[$key]);
                $this->delayWrite();
            }
        }
        return $value;
    }
    /**
     * check if there's a  key existing
     * @param $key
     * @return bool
     */
    public function hasKey($key) {
        $data = $this->data;
        return isset($data->keys[$key]);
    }

    /**
     *  Static  read returns and sets instance
     * @return UserSession or null
     */
    public function read() : UserSession
    {
             $this->data =$this->getSession()->userData;
             if (!$this->data) {
                 $this->data = new UserData();
        }
            return $this;
    }

    /**
      @return UserSession
     */
    public function guestSession() : UserSession
    {
        $this->setGuest();
        $this->addFlash('Browser session ID cookie made active for data entry ');
        $this->write();
        return $this;
    }

    /**
      @return UserSession
     */
    public function activate() : UserSession {
        $this->doWrite = true;
        $this->write();

        return $this;
    }

    /** remove Guest Session */
    public function nullify() {
        $this->read();
        if ($this->isGuest()) {
            $this->destroy();
        }
        if ($this->session) {
            $this->session->destroy();
            $this->session = null;
        }
    }

    public function getUserName() {
        return $this->data->userName;
    }
    public function auth($role): bool {
        return $this->data->auth($role);

                }

   public function sessionName() {
       $data = $this->data;
        if (!empty($data->userName)) {
            return $data->userName;
        }
        return 'NULL';
    }

    public function save() {
            $this->write(); // finalize session now
    }
       
     public function isLoggedIn($role) {
        return ($this->data->hasRole($role)) ? true : false;
        }

        }
