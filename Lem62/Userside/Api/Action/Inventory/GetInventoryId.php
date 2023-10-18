<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_inventory_id
* See: https://wiki.userside.eu/API_inventory
*/

class GetInventoryId extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=get_inventory_id";
        $this->requiredParams = ['data_typer', 'data_value'];
        $this->data = [
            'data_typer' => null, // тип данных, которые проверяем (возможные значения: barcode, inventory_number, serial_number, mac, ip)
            'data_value' => null, // значение
            'is_all_data' => null // флаг - возвращать все найденные ТМЦ, а не только одно
        ];
    }
}
