<?php

/*
See the "licence.txt" file at the root "private" folder of this site
*/
namespace WC\Chimp;

use \Phalcon\Http\Client\Provider\Curl;
use Mailchimp\MailchimpLists;
use WC\App;

class Api {
    
    private $mChimp;
    protected $LOCAL_TIMEZONE = null;
    
    public function cnvDateTime($chimpTime) {
        $d1 = new \DateTime($chimpTime);
        $d1->setTimezone($this->LOCAL_TIMEZONE);
        return $d1->format('Y-m-d H:i:s');
    }
    
    public function getDefaultListId () : int {
        return intval($this->mChimp['default-list']);
        
    }
    public function getOptions() : ?array
    {
        return $this->mChimp;
    }
    public function __construct(App $app, array $options) 
    {
        if (isset($options['autoload'])) {
            require_once $app->php_dir . $options['autoload'];
        }
        $this->mChimp = $options;
        $this->LOCAL_TIMEZONE = new \DateTimeZone($app->TZ);
    }
/**
    public function doCurl($opType, $op, $params = [])
    {
        $mChimp = $this->mChimp;
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
    */
    public function getMembers($list_id, $parameters = []) {
         $cfg = $this->mChimp;
        
        $lists = new MailchimpLists($cfg['key'], $cfg['user']);
        
        return $lists->getMembers($list_id, $parameters);
    }
    
    public function listApi() {
        $cfg = $this->mChimp;
        return new MailchimpLists($cfg['key'], $cfg['user']);
    }
    public function getLists($params = []) {
        return $this->listApi()->getLists($params);
    }
}