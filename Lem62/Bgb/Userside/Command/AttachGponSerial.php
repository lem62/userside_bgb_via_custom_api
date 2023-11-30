<?php

namespace Lem62\Bgb\Userside\Command;

use Lem62\Bgb\Model\ApiRequest;
use Lem62\Bgb\Model\UsersideCommand;

/*
* Implementation: attach_gpon_serial
*/

class AttachGponSerial extends UsersideCommand implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&command=attach_gpon_serial";
        $this->requiredParams = ['customer_id', 'gpon_serial'];
        $this->data = [
            'customer_id' => null, // id абонента в US
            'gpon_serial' => null, // Серийный ОНУ
        ];
    }
}