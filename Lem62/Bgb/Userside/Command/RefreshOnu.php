<?php

namespace Lem62\Bgb\Userside\Command;

use Lem62\Bgb\Model\ApiRequest;
use Lem62\Bgb\Model\UsersideCommand;

/*
* Implementation: attach_gpon_serial
*/

class RefreshOnu extends UsersideCommand implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&command=refresh_onu";
        $this->requiredParams = ['customer_id', 'onu_serial'];
        $this->data = [
            'customer_id' => null, // id абонента в US
            'onu_serial' => null, // Серийный ОНУ
        ];
    }
}