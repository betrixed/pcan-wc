<?php

namespace WC;

/**
 * What gets stored in session serialized data
 *
 * @author michael
 */
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response;
use WC\App;

class UserSession
{

    //put your code here
    public $userName;
    public $roles;
    public $email;
    public $id; // database record user id
    public $status;
    public $keys;
    public $doWrite = false;
    // frameworks session object
    static private $session;
    static private $instance;

    static public function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new UserSession();
        }
        return self::$instance;
    }

    static public function shutdown()
    {
        if (!isset(self::$instance))
            return;
        self::$instance->write();
    }

    public function delayWrite()
    {
        $this->doWrite = true;
    }

    public function __construct()
    {
        $this->keys['flash'] = [];
        static::session();
    }

    static public function flash($msg, $extra = null, $status = 'info')
    {
        if (!empty($extra)) {
            foreach ($extra as $line) {
                $msg .= '<br>' . PHP_EOL . $line;
            }
        }
        UserSession::instance()->addFlash($msg, $status);
    }

    /** implementation framework session object */
    static public function session()
    {
        if (empty(static::$session)) {
            $tmp = App::instance()->TEMP;
            static::$session = new Manager();
            static::$session->setAdapter(new Stream(['savePath' => $tmp]));
            static::$session->start();
        }
        return static::$session;
    }

    public function hasRole($role)
    {
        if (!is_array($this->roles)) {
            return false;
        }
        $key = array_search($role, $this->roles);
        return ($key !== false);
    }

    public function hasUser()
    {
        return !empty($this->id);
    }

    public function isGuest()
    {
        return ($this->id === 0) ? true : false;
    }

    private function setGuest()
    {
        $this->setValidUser('Guest', ['Guest']);
    }

    /** To be real is to be persisted
     * 
     * @param type $name
     * @param type $roles
     */
    public function setValidUser($name, $roles)
    {
        $this->id = 0;
        $this->status = 'OK';
        $this->email = '';
        $this->userName = $name;
        $this->roles = $roles;
        $this->delayWrite();
    }

    /**
     * Set from \WC
     * @param type $user, with get('') properties
     * id, status, email, name, and 
     * getRoleList()
     */
    public function setUser($user)
    {
        $this->id = $user->get('id');
        $this->status = $user->get('status');
        $this->email = $user->get('email');
        $this->userName = $user->get('name');
        $this->roles = $user->getRoleList();
        $this->delayWrite();
    }

    public function wipe()
    {
        $this->setGuest();
        $this->keys = [];
    }

    /** Write is performed by fat free hive persist */
    public function write()
    {
        if ($this->doWrite) {
            $this->doWrite = false;
            self::session()->userdata = $this;
        }
    }

    public function updated()
    {
        $sess = static::session();
        $sess->setData($this);
    }

    /**
     * dump all messages and clear them
     * @return array
     */
    public function getFlash()
    {
        $out = $this->keys['flash'];
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
        $this->keys['flash'] = [];
        $this->delayWrite();
    }

    /**
     * check if there messages in the stack
     * @return bool
     */
    public function hasValues()
    {
        return !empty($this->keys);
    }

    /** clean up the fat free session and user data */
    public function destroy()
    {
        if (empty(static::$session)) {
            return;
        }
        static::$session->remove('userdata');
        $adapter = static::$session->getAdapter();
        $adapter->gc(24 * 60 * 60);
        static::$session->destroy();
        static::$session = null;
    }

    public function addFlash($text, $status = 'info')
    {
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
    public function setKey($key, $val = TRUE)
    {
        $this->keys[$key] = $val;
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

    static public function hasInstance()
    {
        return isset(self::$instance);
    }

    /**
     * check if there's a  key existing
     * @param $key
     * @return bool
     */
    public function hasKey($key)
    {
        return ($this->keys && array_key_exists($key, $this->keys));
    }

    /**
     *  Static  read returns and sets instance
     * @return UserSession or null
     */
    static public function read()
    {
        //$s1 = session_status();
        //$id = session_id();
        //$name = session_name();
        // Get FatFree request session parameters
        //$id2 = session_id();
        // Get stored UserSession if any
        if (!is_null(static::$instance)) {
            return static::$instance;
        } else {
            $sess = static::session();
            static::$instance = $sess->userData;
            return static::$instance;
        }
    }

    /**
      @return UserSession
     */
    static public function guestSession()
    {
        $us = UserSession::instance();
        $us->setGuest();
        $us->addFlash('Browser session ID cookie made active for data entry ');
        $us->write();
        return $us;
    }

    /**
      @return UserSession
     */
    static public function activate()
    {
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

    /** remove Guest Session */
    static public function nullify()
    {
        $us = UserSession::$instance;
        if (!is_null($us) && $us->isGuest()) {
            $us->destroy();
            
        }
        if (isset(static::$session) && !is_null(static::$session)) {
                static::$session->destroy();
                static::$session = null;
        }
    }

    static public function getURL()
    {
        $server = $_SERVER;
        $scheme = $server['REQUEST_SCHEME'];
        $host = $server['HTTP_HOST'];
        return $scheme . '://' . $host . $server['REQUEST_URI'];
    }

    static public function https()
    {
        $server = $_SERVER;
        if ($server['REQUEST_SCHEME'] !== 'https') {
            $ssl_host = App::instance()->get('ssl_host', null);
            $host = $server['HTTP_HOST'];
            // This is because a ssl certificate required a www.NAME
            if (!empty($ssl_host)) {
                $ssl_host = $ssl_host . '.';
                if (strpos($host, $ssl_host) !== 0) {
                    $host = $ssl_host . $host;
                }
            }
            static::reroute('https://' . $host . $server['REQUEST_URI']);
            return false;
        }
        return true;
    }

    static public function auth($role)
    {
        $us = static::read();
        return (!empty($us) && $us->hasRole($role));
    }

    static public function sessionName()
    {
        $us = static::$instance;
        if (isset($us) && !empty($us->userName)) {
            return $us->userName;
        } else if (isset(static::$session) && !is_null(static::$session)) {
            return 'ANON';
        }
        return 'NULL';
    }

    static public function reroute($url)
    {
        static::save();
        $response = new Response();
        $response->redirect($url, true);
    }

    static public function save()
    {
        if (static::hasInstance()) {
            $us = UserSession::instance();
            $us->write(); // finalize session now
        }
    }

    static public function isLoggedIn($role)
    {
        $us = static::$instance;
        return (isset($us) && $us->hasRole($role)) ? true : false;
    }

}
