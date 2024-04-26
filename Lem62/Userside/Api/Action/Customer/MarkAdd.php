<?php

namespace Lem62\Userside\Api\Action\Customer;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/**
 * Implementation: mark_add
 * See: https://wiki.userside.eu/API_customer
 */

class MarkAdd extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=customer&action=mark_add";
        $this->requiredParams = ['customer_id', 'mark_id'];
        $this->data = [
            'customer_id' => null, // id абонента
            'mark_id' => null, // id метки
        ];
    }
}