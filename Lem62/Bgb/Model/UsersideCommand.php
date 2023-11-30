<?php

namespace Lem62\Bgb\Model;

use Lem62\Bgb\Userside\UsersideResponse;

class UsersideCommand
{
    protected $url = null;
    protected $data = null;
    protected $requiredParams = null;

    public function getUrl()
    {
        if (!$this->validate()) {
            return null;
        }
        $this->url .= $this->getDataUrl();
        return $this->url;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
    }

    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        }
    }

    protected function getDataUrl() : string 
    {
        if (!$this->validate()) {
            return null;
        }
        $result = "";
        foreach ($this->data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result .= "&" . $key . "=" . urlencode($value);
        }
        return $result;
    }

    public function validate() : UsersideResponse 
    {
        $result = new UsersideResponse();
        if ($this->data === null || $this->requiredParams === null) {
            $result->message = "Empty data (or required params)";
            return $result;
        }
        if (!is_array($this->data) || !is_array($this->requiredParams)) {
            $result->message = "Data is not array (or required params)";
            return $result;
        }
        foreach ($this->requiredParams as $param) {
            if (!isset($this->data[$param]) || $this->data[$param] === null) {
                $result->message = "Not set " . $param;
            }
        }
        if ($result->message === null) {
            $result->result = true;
        }
        return $result;
    }
}
