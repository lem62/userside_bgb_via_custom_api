<?php

namespace Lem62\Userside\Api\Action\Customer;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/**
 * Implementation: get_data
 * See: https://wiki.userside.eu/API_customer
 */

class GetData extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=customer&action=get_data";
        $this->requiredParams = ['customer_id'];
        $this->data = [
            'customer_id' => null, // * ID абонента
            'account_number' => null, // номер лицевого счета абонента
            'billing_id' => null // ID биллинга
        ];
    }
}