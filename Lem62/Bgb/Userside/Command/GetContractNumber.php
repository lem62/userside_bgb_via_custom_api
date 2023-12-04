<?php

namespace Lem62\Bgb\Userside\Command;

use Lem62\Bgb\Model\ApiRequest;
use Lem62\Bgb\Model\UsersideCommand;

/*
* Implementation: get_contract_number
*/

class GetContractNumber extends UsersideCommand implements ApiRequest
{
    public function __construct()
    {
        $this->url = "command=get_contract_number";
        $this->requiredParams = ['customer_id', 'group', 'name', 'tariff', 'curator'];
        $this->data = [
            'customer_id' => null, // id абонента в US
            'group' => null, // Группа
            'name' => null, // Наименование / ФИО
            'tariff' => null, // Тариф
            'curator' => null, // Куратор
            'phone' => null, // Телефоны (сепаратор запятая)
            'address' => null, // Адрес
            'extra_catv' => null, // Доп. точки catv (int)
        ];
    }
}