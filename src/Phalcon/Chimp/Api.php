<?php

/*
See the "licence.txt" file at the root "private" folder of this site
*/
namespace App\Chimp;

use \Phalcon\Http\Client\Provider\Curl;
use Mailchimp\MailchimpLists;
use WC\App;

class Api {
    
    private $mChimp;
    
    static private $instance;
    
    static function instance() {
        if (empty(static::$instance)) {
            static::$instance = new Api();
        }
        return static::$instance;
    }
    public function getConfig()
    {
        if (is_null($this->mChimp)) {
            $sec = App::instance()->get_secrets();
            if (isset($sec['chimp']))
            {
                $this->mChimp = &$sec['chimp'];
            }
        }
        return $this->mChimp;
    }
    public function doCurl($opType, $op, $params = [])
    {
        $mChimp = $this->getConfig();
        $prefix = $mChimp['uri'];
        $key = $mChimp['key'];
        $curl = new Curl();
        $curl->setOption(CURLOPT_USERNAME, "pcan" );
        $curl->setOption(CURLOPT_PASSWORD,  $key);
        $curl->setOption(CURLOPT_TIMEOUT, 400); // seconds
        
        $uri = $prefix . $op;
        switch($opType)
        {
            case "DELETE":
                $response = $curl->delete($uri,$params);
                if (is_object($response))
                {
                    $code = $response->header->statusCode;
                    if ($code == 200 || $code == 202 || $code == 204)
                    {
                        return $response;
                    }
                }
                break;
            case "GET" : 
                $response = $curl->get($uri,$params);
                if (is_object($response))
                {
                    $code = $response->header->statusCode;
                    if ($code == 200 || code == 204)
                    {
                        return $response;
                    }
                }
                break;
            default:
                $response = null;
                break;
        }
        if (is_object($response))
        {
            $this->flash($response->body);
            throw new \Exception('MailChimp-API: '. $opType / ":" .$response->header->status);
        }
        else {
            throw new \Exception('MailChimp-API: '. $opType / ": unhandled type");
        }
        return null;
    }    
    
    public function getMembers($list_id, $parameters = []) {
         $cfg = $this->getConfig();
        
        $lists = new MailchimpLists($cfg['key'], $cfg['user']);
        
        return $lists->getMembers($list_id, $parameters);
    }
    
    public function listApi() {
        $cfg = $this->getConfig();
        return new MailchimpLists($cfg['key'], $cfg['user']);
    }
    public function getLists($params = []) {
        return $this->listApi()->getLists($params);
    }
}