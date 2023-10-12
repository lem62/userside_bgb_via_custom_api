<?php
namespace Lem62\Bgb;

class QueryResponse
{
    protected $result = false;
    protected $message = null;
    protected $exception = null;
    protected $exception_class = null;
    
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}
