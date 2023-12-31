<?php

namespace Lem62\Userside\Api;

class ApiUserside
{
    private $usersideApiUrl = null;
    private $connetionTimeout = 3; // seconds
    private $timeout = 5; // seconds

    public function __construct($url, $timeout = 5)
    {
        $this->usersideApiUrl = $url;
        $this->timeout = $timeout;
    }

    public function __destruct()
    {
        $this->usersideApiUrl = null;
    }

    function get($url)
    {
        $result = null;
        if (!function_exists("curl_init")) {
            return $result;
        }
        $url = $this->usersideApiUrl . $url;
        // echo $url;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connetionTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curl);
        if (!$result) {
            $result = curl_error($curl);
            curl_close($curl);
            return $result;
        }
        curl_close($curl);
        return json_decode($result, true);
    }
}