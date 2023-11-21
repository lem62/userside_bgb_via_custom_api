<?php

namespace Lem62\Onec;

class ApiOnec
{
    private $apiUrl = null;
    private $connetionTimeout = 3; // seconds
    private $timeout = 5; // seconds

    public function __construct($url, $timeout = 5)
    {
        $this->apiUrl = $url;
        $this->timeout = $timeout;
    }
    
    public function __destruct()
    {
        $this->apiUrl = null;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function postJson($json)
    {
        if ($this->apiUrl === null) {
            return null;
        }
        if (!function_exists("curl_init")) {
            return null;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->apiUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // curl_setopt($curl, CURLOPT_USERPWD, "us:JrnbuHh7B4D0");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connetionTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
/* 
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_STDERR, $verbose);

 */        $result = curl_exec($curl);
        if (!$result) {
            $result = curl_error($curl);
            curl_close($curl);
            return $result;
        }
        curl_close($curl);
        return $result;
    }
}

