<?php

namespace Lem62\Userside\Api\Action\Task;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: show
* See: https://wiki.userside.eu/API_task
*/

class Show extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=task&action=show";
        $this->requiredParams = ['id'];
        $this->data = [
            'id' => null, // * id задания (можно через запятую)
            'employee_id' => null, // id сотрудника, который просматривает это задание (для фиксации в историю по заданию)
            'is_without_comments' => null, // флаг' => null, //не выводить комментарии в информации по заданию
            'operator_id' => null // id оператора, который просматривает это задание (для фиксации в историю по заданию) 
        ];
    }
}
