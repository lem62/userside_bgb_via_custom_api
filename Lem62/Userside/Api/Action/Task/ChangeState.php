<?php

namespace Lem62\Userside\Api\Action\Task;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: change_state
* See: https://wiki.userside.eu/API_task
*/

class ChangeState extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=task&action=change_state";
        $this->requiredParams = ['id', 'state_id'];
        $this->data = [
            'id' => null, // * id задания (можно через запятую)
            'state_id' => null, // * id состояния задания
            'employee_id' => null, // id сотрудника, от имени которого изменять состояние
            'operator_id' => null // id оператора, от имени которого изменять состояние (до версии 3.16dev2)
        ];
    }
}
