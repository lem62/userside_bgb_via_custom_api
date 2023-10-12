<?php

namespace Lem62\Bgb\Userside\Command;

use Lem62\Bgb\Userside\UsersideResponse;
use Lem62\Bgb\Model\ApiRequest;

/*
* Implementation: get_contract_number
*/

class GetContractNumber implements ApiRequest
{
    private $command = "get_contract_number";
    private $customerId = null;
    private $group = null;
    private $name = null;
    private $tariff = null;

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
            "&group=" . $this->group .
            "&name=" . $this->name .
            "&tariff=" . $this->tariff;
        return $url;
    }
    
    public function validate() : UsersideResponse
    {
        $result = new UsersideResponse();
        if ($this->customerId === null) {
            $result->message = "Null customerId";
        }
        if ($this->group === null) {
            $result->message = "Null group";
        }
        if ($this->name === null) {
            $result->message = "Null name";
        }
        if ($this->tariff === null) {
            $result->message = "Null tariff";
        }
        if ($result->message === null) {
            $result->result = true;
        }
        return $result;
    }
}