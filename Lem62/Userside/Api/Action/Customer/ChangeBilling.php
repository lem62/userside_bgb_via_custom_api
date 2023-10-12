<?php

namespace Lem62\Userside\Api\Action\Customer;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: edit
* See: https://wiki.userside.eu/API_customer
*/

class ChangeBilling extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=customer&action=change_billing";
        $this->requiredParams = ['customer_id', 'billing_id'];
        $this->data = [
            'customer_id' => null, // * id абонента
            'billing_id' => null, // * id биллинга
            'billing_user_id' => null // id абонента в биллинге
        ];
    }
}