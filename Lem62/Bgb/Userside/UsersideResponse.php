<?php

namespace Lem62\Bgb\Userside;

require __DIR__ . '/../Model/QueryResponse.php';

use Lem62\Bgb\QueryResponse;

class UsersideResponse extends QueryResponse
{
    protected $values = [];

    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        return $this->values[$key];
    }

    public function __set($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        } else {
            $this->values[$key] = $value;
        }
    }

    public function __toString()
    {
        return json_encode([
            "result" => $this->result,
            "message" => $this->message,
            "exception" => $this->exception,
            "exception_class" => $this->exception_class,
            "data" => $this->values,
        ]);
    }
}
