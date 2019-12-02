<?php

/*
See the "licence.txt" file at the root "private" folder of this site
*/
namespace Chimp;

use \Phalcon\Http\Client\Provider\Curl;

class Api extends \Prefab {
    
    private $mChimp;
    
    public function &getConfig()
    {
        if (is_null($this->mChimp)) {
            $f3 = \Base::instance();
            $sec = &$f3->ref('secrets');
        
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
}