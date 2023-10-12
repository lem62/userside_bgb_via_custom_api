<?php

namespace Lem62\Userside\Api\Action\Customer;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: edit
* See: https://wiki.userside.eu/API_customer
*/

class Edit extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=customer&action=edit";
        $this->requiredParams = ['id'];
        $this->data = [
            'id' => null, // * id абонента
            'account_number' => null, // номер лицевого счёта
            'agreement_date' => null, // дата договора
            'agreement_number' => null, // номер договора
            'apartment_number' => null, // номер квартиры
            'comment' => null, // заметки
            'date_activity' => null, // дата активности в сети
            'date_activity_inet' => null, // дата активности в интернете
            'date_connect' => null, // дата подключения
            'email' => null, // адрес электронной почты
            'entrance' => null, // номер подъезда
            'flag_corporate' => null, // флаг' => null, // юридическое лицо
            'floor' => null, // этаж
            'group_id' => null, // id группы
            'house_id' => null, // id дома
            'is_potential' => null, // флаг' => null, // потенциальный абонент
            'login' => null, // логин
            'manager_id' => null, // id сотрудника-менеджера
            'name' => null, // наименование абонента
            'parent_id' => null, // id родительского абонента (для дочернего абонента)
            'phone0' => null, // номер мобильного телефона
            'phone1' => null // номер домашнего телефона
        ];
    }
}