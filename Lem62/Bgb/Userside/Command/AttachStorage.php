<?php

namespace Lem62\Bgb\Userside\Command;

use Lem62\Bgb\Model\ApiRequest;
use Lem62\Bgb\Model\UsersideCommand;

/*
* Implementation: attach_gpon_serial
*/

class AttachStorage extends UsersideCommand implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&command=attach_storage";
        $this->requiredParams = ['contract_number', 'storage_name'];
        $this->data = [
            'contract_number' => null,
            'storage_name' => null,
        ];
    }
}