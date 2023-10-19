<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_inventory
* See: https://wiki.userside.eu/API_inventory
*/

class GetInventory extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=get_inventory";
        $this->requiredParams = ['id'];
        $this->data = [
            'id' => null, // id - ID ТМЦ
        ];
    }
}
