<?php

namespace Lem62\Userside\Api\Action\Module;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_user_list
* See: https://wiki.userside.eu/API_module
* See: https://wiki.userside.eu/API_-_usm_billing_-_get_user_list
* Note: Документация не полная. Реализовал, что нужно. Включаем критмыш, ибо...
*/

class GetUserList extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=module&request=get_user_list";
        $this->requiredParams = ['billing_id', 'is_id_billing_user_id', 'is_with_potential'];
        $this->data = [
            'billing_id' => null, // id биллинга
            'is_id_billing_user_id' => null, // подставлять в ключи id биллинга
            'is_with_potential' => null, // включать в список потенциальных абонентов
            'customer_id' => null, // id абонента
            'is_parent_id' => null, // перенос всех зависимых ключей (id абонента) в основной ключ (parent_id > account > id абонента) 
            'is_load_commutation' => null, // видимо какие-то комуникации
            'is_load_password' => null, // видимо пароли
            'timestamp_ready' => null, // 
        ];
    }
}