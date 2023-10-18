<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: add_inventory_assortment
* See: https://wiki.userside.eu/API_inventory
*/

class AddInventoryAssortment extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=add_inventory_assortment";
        $this->requiredParams = ['section_id', 'name'];
        $this->data = [
            'section_id' => null, // id секции каталога товаров
            'name' => null, // наименование
            'unit_name' => null, // единица измерения
            'is_require_serial_number' => null, // флаг - требовать ввода серийного номера при приходе ТМЦ
            'is_require_mac' => null // флаг - требовать ввода MAC-адреса при приходе ТМЦ
        ];
    }
}