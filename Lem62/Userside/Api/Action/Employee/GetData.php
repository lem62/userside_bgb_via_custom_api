<?php

namespace Lem62\Userside\Api\Action\Employee;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_data
* See: https://wiki.userside.eu/API_employee
*/

class GetData extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=employee&action=get_data";
        $this->requiredParams = ['id'];
        $this->data = [
            'id' => null, // id сотрудника для выборки (можно через запятую)
        ];
    }
}