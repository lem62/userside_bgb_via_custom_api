<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: transfer_inventory
* See: https://wiki.userside.eu/API_inventory
*/

class TransferInventory extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=transfer_inventory";
        $this->requiredParams = ['inventory_id', 'dst_account'];
        $this->data = [
            'inventory_id' => null, // ID ТМЦ
            'dst_account' => null, // Счет-получатель
            'comment' => null, // заметки
            'employee_id' => null, // ID сотрудника автора операции
            'operator_id' => null, // ID оператора автора операции (до версии 3.16dev2)
        ];
    }
}
