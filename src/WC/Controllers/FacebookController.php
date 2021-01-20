<?php
namespace WC\Controllers;
use WC\Valid;
use stdClass;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException as FBExcept;
use WC\Models\Users;

class FacebookController extends BaseController {
    use \WC\Link\UserAdm;
    
    protected function getApp() : ?Facebook {
         $app = $this->app;
         $fbdata = $app->getSecrets('facebook');
         
        return new Facebook([
                'app_id' => $fbdata->app_id,
                'app_secret' => $fbdata->app_secret,
                'graph_api_version' => $fbdata->api_version]
        );
    }
    
    protected function getFBid($token) : ?object {
       $fb = $this->getApp();
       try {
            $response = $fb->get('/me?fields=name,email', $token);
            return $response;
       }
       catch( FBExcept $e) {
           $this->flash($e->getMessage);
           return null;
       }
       return null;
    }
    /* Facebook doesn't notify logout from facebook! 
     * so
     * Allow to keep session!
     */
    
    public function statusAction() {
        $post = $this->getPost();
        $us = $this->user_session;
        $us->read();
        $fcache = $us->getKey('fbook_data',null);
        $status = Valid::toStr($post, "status");
        $isConnected = ($status === "connected");

        if ($isConnected) {
            $fb_userid = Valid::toInt($post,"user_id");
            if ($fcache === null || $fcache->user_id !== $fb_userid) {
                $fbdata = new stdClass();
                $fbdata->user_id = $fb_userid;
                $fbdata->connected = $isConnected;
                $fbdata->svr_token = Valid::toInt($post,"svr_token");
                $fbdata->signed_req = Valid::toStr($post,"signed_req");
                $fbdata->graph_domain = Valid::toStr($post, "graph_domain");
                $fbdata->exp_time = Valid::toInt($post,"exp_time");
                $fbdata->access_exp = Valid::toInt($post,"access_exp");
                $fbdata->access_token = Valid::toStr($post,"access_token");  
                $response = $this->getFBid($fbdata->access_token);
                $fbid = $response->getGraphUser();
                $name = $fbid->getName();
                $email = $fbid->getEmail(); 
                $fbdata->name = $name;
                $fbdata->email = $email;
                $us->setKey('fbook_data', $fbdata);
                $sess_id = $us->getUserId();
                if ($sess_id === 0) {
                    $rec = Users::findFirstByEmail($email);
                    if (empty($rec)) {
                      /* setup a user record to store associated events */
                        $rec = $this->newPlainUser($name,$email);  
                    }
                    if (!empty($rec)) {
                        /* try for a new user */
                        $roles = $this->getRoleList($rec);
                        $us->setUser($rec, $roles);
                    }
                }
                $fcache = $fbdata;
                $us->write(); 
            }
            if ($fcache !== null) {
                return $fcache->name;
            }
        }
        else {
            // not connected but may have session still
            if ($fcache !== null) {
                $fcache->connected = false;
                $us->write(true);
            }

        }
        return $us->getUserName();
    }
}
