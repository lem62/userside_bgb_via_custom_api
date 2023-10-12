<?php

namespace Lem62\Bgb;

class ApiBgb
{
    private $bgbApiUrl = null;

    public function __construct($url)
    {
        $this->bgbApiUrl = $url;
    }

    public function get($url)
    {
        if ($this->bgbApiUrl === null) {
            return null;
        }
        if (!function_exists("curl_init")) {
            return null;
        }
        $url = $this->bgbApiUrl . $url;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curl);
        if (!$result) {
            $result = curl_error($curl);
            curl_close($curl);
            return $result;
        }
        curl_close($curl);
        return $result;
    }
}