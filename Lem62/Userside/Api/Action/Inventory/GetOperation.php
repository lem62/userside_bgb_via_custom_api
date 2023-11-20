<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_operation
* See: https://wiki.userside.eu/API_inventory
*/

class GetOperation extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=get_operation";
        $this->requiredParams = [];
        $this->data = [
            'id' => null, // ID операции (можно несколько значений через запятую)
            'src_account' => null, // счёт-кредита (откуда)
            'dst_account' => null, // счёт-дебита (куда)
            'date_start' => null, // дата начала периода
            'date_finish' => null, // дата окончания периода
            'inventory_id' => null, // id ТМЦ
            'employee_id' => null, // id сотрудника, инициатора операции
            'inventory_assortment_id' => null, // id наименования ТМЦ
        ];
    }
}
