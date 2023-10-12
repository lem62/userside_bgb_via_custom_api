<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_inventory_amount
* See: https://wiki.userside.eu/API_inventory
*/

class GetInventoryAmount extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=get_inventory_amount";
        $this->requiredParams = ['location'];
        $this->data = [
            'location' => null, // * категория учёта [storage|employee|customer|node|task]
            'object_id' => null, // id объекта учёта (можно через запятую)
            'inventory_type_id' => null, // id наименования ТМЦ (можно через запятую)
            'section_id' => null // id секции каталога товаров (можно через запятую)
        ];
    }
}
