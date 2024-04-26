<?php

namespace Lem62\Bgb\Userside\Command;

use Lem62\Bgb\Model\ApiRequest;
use Lem62\Bgb\Model\UsersideCommand;

/*
* Implementation: attach_gpon_serial
*/

class AttachOnu extends UsersideCommand implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&command=attach_onu";
        $this->requiredParams = ['contract_number', 'onu_serial'];
        $this->data = [
            'contract_number' => null, // id абонента в US
            'onu_serial' => null, // Серийный ОНУ
        ];
    }
}