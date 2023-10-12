<?php

namespace Lem62\Bgb\Userside\Command;

use Lem62\Bgb\Userside\UsersideResponse;
use Lem62\Bgb\Model\ApiRequest;

/*
* Implementation: attach_gpon_serial
*/

class AttachGponSerial implements ApiRequest
{
    private $command = "attach_gpon_serial";
    private $customerId = null;
    private $gpon_serial = null;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if ($property === "command") {
            return;
        }
        if (property_exists($this, $property)) {
            $this->$property = urlencode($value);
        }
    }
    
    public function getUrl()
    {
        $url = 
            "&command=" . $this->command .
            "&customerId=" . $this->customerId .
            "&gpon_serial=" . $this->gpon_serial;
        return $url;
    }
    
    public function validate() : UsersideResponse
    {
        $result = new UsersideResponse();
        if ($this->customerId === null) {
            $result->message = "Null customerId";
        }
        if ($this->gpon_serial === null) {
            $result->message = "Null gpon_serial";
        }
        if ($result->message === null) {
            $result->result = true;
        }
        return $result;
    }
}