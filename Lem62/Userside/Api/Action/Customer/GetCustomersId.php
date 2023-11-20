<?php

namespace Lem62\Userside\Api\Action\Customer;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: get_customers_id
* See: https://wiki.userside.eu/API_customer
*/

class GetCustomersId extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=customer&action=get_customers_id";
        $this->requiredParams = [];
        $this->data = [
            'address_unit_id' => null, // id адресной единицы
            'appartment' => null, // номер квартиры
            'balance_from' => null, // баланс (с)
            'balance_to' => null, // баланс (до)
            'billing_id' => null, // id номера биллинга
            'date_connect_from' => null, // дата подключения (с)
            'date_connect_to' => null, // дата подключения (до)
            'dependence_device_id' => null, // id устройства, от которого зависят абоненты
            'house_id' => null, // id дома
            'is_corporate' => null, // флаг' => null, // юридическое лицо
            'is_ex' => null, // флаг' => null, // бывшие абоненты
            'mark_id' => null, // id метки
            'name' => null, // ФИО/название абонента
            'state_id' => null, // id статуса
            'tariff_id' => null, // id тарифа
            'limit' => null, // максимальное количество записей, что вернуть в ответе
            'is_like' => null, // флаг' => null, // использовать сравнение подстроки там где это возможно (а не полное совпадение)
        ];
    }
}