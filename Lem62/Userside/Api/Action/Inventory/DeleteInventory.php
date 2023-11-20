<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: delete_inventory
* See: https://wiki.userside.eu/API_inventory
*/

class DeleteInventory extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=delete_inventory";
        $this->requiredParams = ['id'];
        $this->data = [
            'id' => null // id ТМЦ (ТМЦ обязательно должно быть списанным)
        ];
    }
}
