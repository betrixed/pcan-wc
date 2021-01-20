<?php

namespace WC;

/**
 * What gets stored in session serialized data
 *
 * @author michael
 */
class UserData
{

    //put your code here
    public $userName;
    public $roles;
    public $email;
    public $id = 0; // database record user id
    public $status;
    public $keys = [];

    public function hasAnyRole(array $roles): bool
    {

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

    public function getFlash(): array
    {
        $out = $this->keys['flash'] ?? [];
        if (!empty($out)) {
            $this->clearFlash();
        }
        return $out;
    }

    public function hasRole($role): bool
    {
        if (!is_array($this->roles)) {
            return false;
        }
        return (array_search($role, $this->roles) !== false);
    }

    public function hasUser()
    {
        return !empty($this->id);
    }

    public function isGuest()
    {
        return ($this->id === 0) ? true : false;
    }

    public function setGuest()
    {
        $this->setValidUser('Guest', ['Guest']);
    }

    public function wipe()
    {
        $this->keys = [];
    }

    /** To be real is to be persisted
     * 
     * @param type $name
     * @param type $roles
     */
    public function setValidUser($name, $roles)
    {
        $this->id = -1;
        $this->status = 'OK';
        $this->email = '';
        $this->userName = $name;
        $this->roles = $roles;
        
    }

    /** Set from database model 'Users'
     * 
     * @param type $user
     */
    public function addFlash($text, $status = 'info')
    {
        if (!isset($this->keys['flash'])) {
            $this->keys['flash'] = [];
        }
        $this->keys['flash'][] = ['text' => $text, 'status' => $status];
    }

    public function auth($role): bool
    {
        if (is_string($role)) {
            return $this->hasRole($role);
        }
        if (is_array($role)) {
            return $this->hasAnyRole($role);
        }
        return false;
    }

}

use WC\App;

class UserSession
{

    // sync to stored session
    protected $doWrite = false;
    protected $wasRead = false;
    protected $session;
    protected $data;
    protected $app;


    public function shutdown()
    {
        $this->write();
    }

    public function delayWrite()
    {
        $this->doWrite = true;
    }

    public function __construct(App $app)
    {
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
    public function flash($msg, $extra = null, $status = 'info')
    {
        if (!empty($extra)) {
            foreach ($extra as $line) {
                $msg .= '<br>' . PHP_EOL . $line;
            }
        }
        $this->addFlash($msg, $status);
    }

    /** implementation framework session object */
    public function getSession()
    {
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
    public function setUser($user, $roles)
    {
        $data = $this->data;

        $data->id = $user->id;
        $data->status = $user->status;
        $data->email = $user->email;
        $data->userName = $user->name;
        $data->roles = $roles;
        $this->wasRead = true; // pretend was read

        $this->delayWrite();
    }

    public function isEmpty()
    {
        return ($this->data->id === 0);
    }

    public function setAdmin() {
        $this->data->setValidUser('admin',['Admin','User','Editor']);
        $this->wasRead = true;
    }
    
    public function setGuest()
    {
        $this->data->setGuest();
        $this->wasRead = true;
    }

    /*     * get comma separated list for display */

    public function roles(): string
    {
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

    public function wipe()
    {
        $data = $this->data;
        $data->wipe();
        $data->setGuest();
        $this->delayWrite();
    }

    /** Persisted by framework session */
    public function write(bool $force = false)
    {
        if ($this->doWrite || $force) {
            $this->doWrite = false;
            $session = $this->getSession();
            $session->userData = $this->data;
        }
    }

    public function updated()
    {
        $this->getSession()->userData = $this->data;
        //$this->session->setData($this->data);
    }

    /**
     * dump all messages and clear them
     * @return ?array
     */
    public function getFlash(): ?array
    {
        $out = $this->data->keys['flash'] ?? null;
        if (!empty($out)) {
            $this->clearFlash();
        }
        return $out;
    }

    /**
     * reset message stack
     */
    public function clearFlash()
    {
        $this->data->keys['flash'] = [];
        $this->delayWrite();
    }

    /**
     * check if there messages in the stack
     * @return bool
     */
    public function hasValues()
    {
        return !empty($this->data->keys);
    }

    public function addFlash($text, $status = 'info')
    {
        $this->data->addFlash($text, $status);
        $this->delayWrite();
    }

    /**
     * set any key (flash is used already)
     * @param $key
     * @param mixed $val
     * Value type must be serialisble
     */
    public function setKey($key, $val = TRUE)
    {
        $this->data->keys[$key] = $val;
        $this->delayWrite();
    }

    /**
     * get and clear any key, if it's existing
     * @param $key 
     * @param $clear boolean
     * @return mixed|null
     */
    public function getKey($key, $clear = false)
    {
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
    public function hasKey($key)
    {
        $data = $this->data;
        return isset($data->keys[$key]);
    }

    /**
     *  Static  read returns and sets instance
     * @return UserSession or null
     */
    public function read(): UserSession
    {
        if (!$this->wasRead) {
            $this->data = $this->getSession()->userData;
            $this->wasRead = true;
            $this->doWrite = false;
            if (!$this->data) {
                $this->data = new UserData();
            }
        }
        return $this;
    }

    /**
      @return UserSession
     */
    public function guestSession(): UserSession
    {
        $this->setGuest();
        //$this->addFlash('Browser session ID cookie made active for data entry ');
        $this->doWrite = true;
        $this->write();
        return $this;
    }

    /**
      @return UserSession
     */
    public function activate(): UserSession
    {
        $this->doWrite = true;
        $this->write();

        return $this;
    }

    /** remove Guest Session */
    public function nullify()
    {
        $this->read();
        if (!$this->isEmpty()) {
            $this->wipe();
        }
        if ($this->session) {
            $this->session->destroy();
            $this->session = null;
        }
    }

    public function getUserRoles() {
        if (!$this->wasRead) {
            $this->read();
        }
        return $this->data->roles;
    }
    public function getUserEmail()
    {
        if (!$this->wasRead) {
            $this->read();
        }
        return $this->data->email;
    }

    public function getUserName()
    {
        if (!$this->wasRead) {
            $this->read();
        }
        return $this->data->userName;
    }

    public function getUserId()
    {
        if (!$this->wasRead) {
            $this->read();
        }
        return $this->data->id;
    }

    public function auth($role): bool
    {
        if (!$this->wasRead) {
            $this->read();
        }
        return $this->data->auth($role);
    }

    public function sessionName()
    {
        return $this->getUserName();
    }

    public function save()
    {
        $this->write(); // finalize session now
    }

    public function isLoggedIn($role)
    {
        $this->read();
        return ($this->data->hasRole($role)) ? true : false;
    }

}
