<?php
namespace WC\Controllers;
use WC\Valid;
use stdClass;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException as FBExcept;
use WC\Models\ {Users, FbookUser};

class FacebookController extends BaseController {
    use \WC\Link\UserAdm;
    use \WC\Mixin\ViewPhalcon;
    
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
        // have cached facebook user login>
        $fcache = $us->getKey('fbook_data',null);
        
        $status = Valid::toStr($post, "status");
        $isConnected = ($status === "connected");

        if ($isConnected) {
            $fb_userid = Valid::toInt($post,"user_id");
            // compare facebook ids
            if ($fcache === null || $fcache->user_id !== $fb_userid) {
                $fbdata = $this->validFBLogin($fb_userid, $post);
                
                $response = $this->getFBid($fbdata->access_token);
                
                $fbid = $response->getGraphUser();
                $name = $fbid->getName();
                $email = $fbid->getEmail(); 
                $fbdata->name = $name;
                $fbdata->email = $email;
                
                $us->setKey('fbook_data', $fbdata); // keep in current session
                $sess_id = $us->getUserId();
                if ($sess_id === 0) {
                    $rec = Users::findFirstByEmail($email);
                    if (empty($rec)) {
                      /* setup a user record to store associated events */
                        $rec = $this->newPlainUser($name,$email);  
                      /* make a FbookUser for counting purposes */
                        $fbuser = $this->createFbookUser($fbdata, $rec->id);
                    }
                    else {
                        // could be existing site user, but no previous facebook login
                        $fbuser = FbookUser::findFirstByid($fbdata->user_id);
                        if (!empty($fbuser)) {
                            $fbuser->update_count = $fbuser->update_count + 1;
                            $fbuser->modified_at =Valid::now();
                            $fbuser->update();
                        }
                        else {
                            $fbuser = $this->createFbookUser($fbdata, $rec->id);
                        }
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
    
    /**
     * Handle Facebook data deletion request
     * Content-Type: application/json 
     */
    public function deleteAction()
    {
        $post = $this->getPost();
        $signed_request = Valid::toStr($post,"signed_request");
        $data = UserAdm::parse_deletion_request($signed_request);
        $app = $this->app;
        $response = new Response();
        $response->setContentType('application/json','UTF-8');
        if (!empty($data)) {
            $user_id = $data['user_id'];
            $delcode = $this->fbUserDelete($user_id);
            $deldata = [
                'url' => "https://www." . $app->domain . "/fbdelete?id=" . $delcode,
                'confirmation_code' => $delcode
            ];
            $response->setContent(json_encode($deldata));
        }
        return $response;
    }
    
    /*
     * Return a report of previous deletion
     */
    public function delstatusAction() {
        $req = $this->getRequest();
        $delcode = Valid::toInt($req, "id");
        if ($delcode === 0) {
           $content = "No data was found for deletion request (0)";
        }
        else {
            $rec = FbookDeluser::findFirstByid($delcode);
            if (!empty($rec)) {
                $content = "Facebook data found and deleted on " . $rec->created_at;
            }
            else {
                 $content = "No deletion request found for code = " . $delcode;
            }
        }
        $m = $this->getViewModel();
        $view = $this->view;
        $view->setMainView('controllers/simple');
        
        $m->content = $content;
        return $this->render('fbook','delstatus');
    }
}
