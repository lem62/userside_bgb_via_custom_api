<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_inventory_catalog
* See: https://wiki.userside.eu/API_inventory
*/

class GetInventoryCatalog extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=get_inventory_catalog";
        $this->requiredParams = [];
        $this->data = [
            'id' => null, // ID наименования ТМЦ (можно через запятую)
            'section_id' => null // ID типа ТМЦ (можно через запятую)
        ];
    }
}
