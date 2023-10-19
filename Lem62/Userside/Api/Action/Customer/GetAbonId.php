<?php

namespace Lem62\Userside\Api\Action\Customer;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_abon_id
* See: https://wiki.userside.eu/API_customer
*/

class GetAbonId extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=customer&action=get_abon_id";
        $this->requiredParams = ['data_typer', 'data_value'];
        $this->data = [
            'data_typer' => null, // тип данных, которые проверяем (возможные значения: account, billing_uid, codeti, dognumber, ip, login, mac, mail, phone)
            'data_value' => null, // значение
            'is_skip_old' => null // флаг - не выполнять поиск среди бывших абонентов
        ];
    }
}