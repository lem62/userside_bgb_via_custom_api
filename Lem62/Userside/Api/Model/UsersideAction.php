<?php

namespace Lem62\Userside\Api\Model;

class UsersideAction
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
        $result = "";
        if ($this->data === null) {
            return $result;
        }
        if (!is_array($this->data)) {
            return $result;
        }
        foreach ($this->data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result .= "&" . $key . "=" . urlencode($value);
        }
        return $result;
    }

    protected function validate() : bool 
    {
        if ($this->data === null || $this->requiredParams === null) {
            return false;
        }
        if (!is_array($this->data) || !is_array($this->requiredParams)) {
            return false;
        }
        $result = true;
        foreach ($this->requiredParams as $param) {
            if (!isset($this->data[$param]) || $this->data[$param] === null) {
                $result &= false;
            }
        }
        return $result;
    }
}
