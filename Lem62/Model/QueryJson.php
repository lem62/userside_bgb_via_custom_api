<?php

namespace Lem62\Model;

class QueryJson
{
    protected $data = [];

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __toString()
    {
        return json_encode($this->data);
    }
}
