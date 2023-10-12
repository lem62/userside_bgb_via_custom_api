<?php

namespace Lem62\Traits;

trait OutputFormat {

    public function arrayToString($array)
    {
        if (!is_array($array)) {
            return $array;
        }
        $str = var_export($array, true);
        $str = str_replace(PHP_EOL, "", $str);
        return $str;
    }

    public function stringToJson($str)
    {
        if ($str === null) {
            return null;
        }
        if (is_array($str)) {
            $str = json_encode($str);
        }
        $json = json_decode($str);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return $json;
    }

    public function validateJson($json)
    {
        if (is_array($json)) {
            return false;
        }
        if (!is_scalar($json) && !is_null($json) && !method_exists($json, '__toString')) {
            return false;
        }
        if ($json === null) {
            return false;
        }
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
}