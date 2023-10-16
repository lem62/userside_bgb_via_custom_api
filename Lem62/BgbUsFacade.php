<?php

require __DIR__ . '/Log/LogFile.php';
require __DIR__ . '/Traits/CustomDotEnv.php';
require __DIR__ . '/Traits/OutputFormat.php';
require __DIR__ . '/Bgb/Userside/UsersideResponse.php';
require __DIR__ . '/Bgb/ApiBgb.php';
require __DIR__ . '/Bgb/Model/ApiRequest.php';
require __DIR__ . '/Bgb/Userside/UsersideFacade.php';
require __DIR__ . '/Bgb/Userside/Command/GetContractNumber.php';
require __DIR__ . '/Bgb/Userside/Command/AttachGponSerial.php';
require __DIR__ . '/Userside/Api/ApiUserside.php';
require __DIR__ . '/Userside/Api/Model/ApiRequest.php';
require __DIR__ . '/Userside/Api/Model/UsersideAction.php';
require __DIR__ . '/Userside/Api/Action/Customer/GetData.php';
require __DIR__ . '/Userside/Api/Action/Customer/Edit.php';
require __DIR__ . '/Userside/Api/Action/Customer/ChangeBilling.php';
require __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryAmount.php';
require __DIR__ . '/Userside/Api/Action/Inventory/TransferInventory.php';
require __DIR__ . '/Userside/Api/Action/Task/Show.php';
require __DIR__ . '/Userside/Api/Action/Task/ChangeState.php';

use Lem62\Log\LogFile;
use Lem62\Traits\CustomDotEnv;
use Lem62\Traits\OutputFormat;
use Lem62\Bgb\Userside\UsersideFacade;
use Lem62\Bgb\Userside\Command\GetContractNumber;
use Lem62\Bgb\Userside\Command\AttachGponSerial;
use Lem62\Userside\Api\ApiUserside;
use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Action\Customer\GetData;
use Lem62\Userside\Api\Action\Customer\Edit;
use Lem62\Userside\Api\Action\Customer\ChangeBilling;
use Lem62\Userside\Api\Action\Inventory\GetInventoryAmount;
use Lem62\Userside\Api\Action\Inventory\TransferInventory;
use Lem62\Userside\Api\Action\Task\Show;
use Lem62\Userside\Api\Action\Task\ChangeState;

class BgbUsFacade 
{
    use OutputFormat, CustomDotEnv;

    private $debug = true; // log both info and error
    private $command = null;
    private $data = null;
    private $log = null;
    private $api = null;
    private $logPrefix = null;
    private $onuSectionId = 9;
    private $tariffListId = 25;
    private $storageId = 16;
    private $redirectUrl = null;
    private $statusNewCustomer = [
        'get_contract' => 16,
        'add_equipment' => 17,
        'apply_equipmnet' => 19,
        'equipmnet_applied' => 18,
        'cancel' => 11,
        'finish' => 12,
    ];

    public function __construct()
    {
        $this->api = new ApiUserside($this->dotEnvConfig('USERSIDE_API_URL'));
        $this->log = new LogFile(__DIR__ . "/../logs/", "bgb_us_facade");
    }
    
    public function log($msg, $info = true)
    {
        if ($info && !$this->debug) {
            return;
        }
        if (is_array($msg)) {
            $msg = $this->arrayToString($msg);
        }
        if ($this->validateJson($msg)) { /* Userside API return some keys as numeric *facepalm* */
            $msg = json_encode($msg);
        }
        $msg = $this->logPrefix . " " . $msg;
        if ($info) {
            $this->log->info($msg);
        } else {
            $this->log->error($msg);
        }
    }

    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function setLogPrefix($logPrefix)
    {
        $this->logPrefix = $logPrefix;
    }

    public function registrationNewCustomer($eventArray)
    {
        $this->setLogPrefix("registrationNewCustomer");
        if (!is_array($eventArray)) {
            return $this->response(true, "Не получен массив из события");
        }
        if (!isset($eventArray['customerId'])) {
            return $this->response(true, "Не определен id абонента в массиве из события");
        }
        if (!isset($eventArray['taskId'])) {
            return $this->response(true, "Не определен id задания в массиве из события");
        }
        if (!isset($eventArray['stateId'])) {
            return $this->response(true, "Не определен id статуса в массиве из события");
        }
        if (!in_array($eventArray['stateId'], $this->statusNewCustomer)) {
            return $this->response(true, "Не подходящий id статуса в массиве из события");
        }
        $this->log("Event array - " . $this->arrayToString($eventArray, true));
        $this->setRedirectUrl("/oper/?core_section=task&action=show&id=" . $eventArray['taskId']);
        switch ($eventArray['stateId']) {
            case $this->statusNewCustomer['get_contract']: // Получение номера договора
                return $this->getContractNumber($eventArray['taskId'], $eventArray['customerId']);
            case $this->statusNewCustomer['apply_equipmnet']: // Регистрация Ону
                return $this->attachGponSerial($eventArray['taskId'], $eventArray['customerId']);
            case $this->statusNewCustomer['cancel']: // Отмена предыдущего статуса
                if (!isset($eventArray['stateCurrendId'])) {
                    break;
                }
                if ($eventArray['stateCurrendId'] != $this->statusNewCustomer['add_equipment']) { // ТМЦ
                    break;
                }
                return $this->removeEquipmentInTask($eventArray['taskId']);
            case $this->statusNewCustomer['finish']: // Выполнено
                if (!isset($eventArray['stateCurrendId'])) {
                    return $this->response(false, "Завершить задачу можно только после регистрации ONU");
                }
                if ($eventArray['stateCurrendId'] != $this->statusNewCustomer['equipmnet_applied']) { // Ону зарегистрирована
                    return $this->response(false, "Завершить задачу можно только после регистрации ONU");
                }
                $this->switchToRegular($eventArray['customerId']);
                break;
        }
        return $this->response(true, "Обработано");
    }

    public function registrationNewCustomerAfter($eventArray)
    {
        $this->setLogPrefix("registrationNewCustomerAfter");
        if (!is_array($eventArray)) {
            return $this->response(true, "Не получен массив из события");
        }
        if (!isset($eventArray['customerId'])) {
            return $this->response(true, "Не определен id абонента в массиве из события");
        }
        if (!isset($eventArray['taskId'])) {
            return $this->response(true, "Не определен id задания в массиве из события");
        }
        if (!isset($eventArray['stateId'])) {
            return $this->response(true, "Не определен id статуса в массиве из события");
        }
        if (!in_array($eventArray['stateId'], $this->statusNewCustomer)) {
            return $this->response(true, "Не подходящий id статуса в массиве из события");
        }
        $this->log("Event array - " . $this->arrayToString($eventArray, true));
        $this->setRedirectUrl("/oper/?core_section=task&action=show&id=" . $eventArray['taskId']);
        switch ($eventArray['stateId']) {
            case $this->statusNewCustomer['get_contract']: // Получение номера договора
                return $this->response(false, "Номер договора выделен");
            case $this->statusNewCustomer['apply_equipmnet']: // Регистрация Ону
                return $this->response(false, "ONU зарегистрирована");
        }
        return $this->response(true, "Обработано");
    }

    private function getContractNumber($taskId, $customerId) 
    {
        $this->setLogPrefix("getContractNumber");
        $customer = $this->getCustomerData($customerId);
        if (!$customer) {
            $this->log("Can not get customer by id: " . $customerId, false);
            return $this->response(false, "Не удалось найти абонента");
        }
        if (!property_exists($customer, "data")) {
            $this->log("No data property", false);
            return $this->response(false, "Нет данных");
        }
        if ($customer->data->is_potential !== 1) {
            $this->log("Customer is not potential", false);
            return $this->response(false, "Абонент не потенциальный");
        }
        if (!property_exists($customer->data, "full_name")) {
            $this->log("Can not get full name", false);
            return $this->response(false, "Не удалось получить ФИО");
        }
        if (!property_exists($customer->data, "group")) {
            $this->log("Can not get group", false);
            return $this->response(false, "Не удалось получить группу");
        }
        if (property_exists($customer->data, "agreement")) {
            if (count($customer->data->agreement) == 0) {
                $this->log("Count zero (???)", false);
                return $this->response(false, "Каунт зеро (???)");
            }
            $contractNumber = $this->getFirstInObject($customer->data->agreement, "number");
            if ($contractNumber) {
                $this->log("Customer has already had contruct number", false);
                return $this->response(false, "Номер договора уже присвоин");
            }
        }
        $customerData["customerId"] = $customerId;
        $customerData["name"] = $customer->data->full_name;
        $customerData["group"] = $this->getBgbGroup($customer);
        $customerData["tariff"] = $this->getTariffForBgb($taskId);
        $this->setCommand("get_contract_number");
        $this->setData($customerData);
        $json = $this->execute();
        if (!$json) {
            $this->log("Can no execute request to bgb", false);
            return $this->response(false, "Не удалось выполнить запрос в БГБ");
        }
        if (!$json->result) {
            $msg = $json->message;
            if (property_exists($json, "exception") && $json->exception !== null) {
                $msg = $json->exception . " (" . $msg . ")";
            }
            $this->log($msg, false);
            return $this->response(false, $msg);
        }
        if (!$this->setContractNumber($customerId, $json->data->title)) {
            $this->log("Can no set contract number", false);
            return $this->response(false, "Не удалось прикрепить номер договора");
        }
        if (!$this->setBillingId($customerId, $json->data->cid)) {
            $this->log("Can no set bgb Id contract number", false);
            return $this->response(false, "Не удалось привязать Id бгб");
        }
        return $this->response(true, "Номер договора получен");
    }

    private function removeEquipmentInTask($taskId)
    {
        $this->setLogPrefix("removeEquipmentInTask");
        $onus = $this->getEquipmentFromTask($taskId);
        if (!$onus) {
            $this->log("There are no equipments in task or can no get list (anyway return success)");
            return $this->response(true, "В задаче нет ТМЦ");
        }
        foreach ($onus->data as $key => $value) {
            $this->log("Try to remove " . $value->serial_number);
            $removeResult = $this->removeEquipmentCommand($value->id);
            if (!$removeResult) {
                $this->log("Remove equipment command is not passed id:" . $value->id, false);
                return $this->response(false, "Не удалось убрать ТМЦ в задаче " . $value->serial_number);
            }
        }
        $this->log("Equipment removed in task");
        return $this->response(true, "ТМЦ удалены");
    }

    private function attachGponSerial($taskId, $customerId) 
    {
        $this->setLogPrefix("attachGponSerial");
        $serials = $this->getEquipmentFromTask($taskId);
        if (!$serials) {
            $this->log("Can not find Onu in task", false);
            return $this->response(false, "Не удалось найти ONU в задаче");
        }
        $gponSerial = $this->getFirstInObject($serials->data, "serial_number");
        if (!$gponSerial) {
            $this->log("Can not find Onu in task (2)", false);
            return $this->response(false, "Не удалось найти ONU в задаче");
        }
        if (!preg_match("/^(HWTC|HWFW|HWHW|GONT)([0-9A-Z]{8})$/", $gponSerial)) {
            $this->log("Serial has wrong format", false);
            return $this->response(false, "Серийный имеет неверный формат");
        }
        $serial["gpon_serial"] = $gponSerial;
        $serial["customerId"] = $customerId;
        $this->setCommand("attach_gpon_serial");
        $this->setData($serial);
        $json = $this->execute();
        if (!$json) {
            $this->log("Can no execute request to bgb", false);
            return $this->response(false, "Не удалось выполнить запрос в БГБ");
        }
        if (!$json->result) {
            $msg = $json->message;
            if (property_exists($json, "exception") && $json->exception !== null) {
                $msg = $json->exception . " (" . $msg . ")";
            }
            $this->log($msg, false);
            return $this->response(false, $msg);
        }
        if (!$this->changeTaskStatus($taskId, $this->statusNewCustomer['equipmnet_applied'])) { // ONU зарегистрирована
            $this->log("Can no set pre complate status", false);
            return $this->response(false, "Не удалось перевести задачу в статус - ONU зарегистрирована");
        } else {
            return $this->redirectToTask();
        }
    }

    private function switchToRegular($customerId) 
    {
        $this->setLogPrefix("switchToRegular");
        $regular = $this->switchToRegularCustomer($customerId);
        if (!$regular) {
            $this->log("Can not to switch to regular id:" . $customerId, false);
        }
    }

    public function response($succes, $msg)
    {
        $this->log("Response - succes: " . $succes . ", msg: " . $msg);
        $result['result'] = ($succes) ? "0" : "1";
        $result['msg'] = $msg;
        return $this->redirectIfError($result);
    }

    private function redirectIfError($msg)
    {
        if (!is_array($msg)) {
            return true;
        }
        if (!isset($msg['result'])) {
            return true;
        }
        if ($msg['result'] != '1') {
            return true;
        }
        $msg = "&msg=" . urldecode($msg['msg']);
        header('Location: ' . $this->redirectUrl . $msg);
        exit();
    }

    private function redirectToTask()
    {
        header('Location: ' . $this->redirectUrl);
        exit();
    }
    
    private function getFirstInObject($object, $property)
    {
        foreach ($object as $key => $value) {
            if (!property_exists($value, $property)) {
                continue;
            }
            return $value->$property;
        }
    }

    private function getTariffForBgb($taskId) 
    {
        $result = null;
        $task = $this->getTask($taskId);
        if (!$task) {
            $this->log("Can not get task", false);
            return $result;
        }
        if (!property_exists($task->data, "additional_data")) {
            $this->log("Task does not have tariff list", false);
            return $result;
        }
        foreach ($task->data->additional_data as $key => $value) {
            if (!property_exists($value, "value")) {
                continue;
            }
            if ($value->id != $this->tariffListId) {
                continue;
            }
            $result = $value->value;
            break;
        }
        if (!$result) {
            return null;
        }
        return $result;
    }

    private function getBgbGroup($customer) {
        $groups = [
            "3" => 13, // Gpon Юг частный Дом
            "4" => 13, // Gpon Юг многоэтажный дом
            "5" => 14, // А.Равшан
            "6" => 16, // Gpon север частный Дом
            "7" => 16, // Gpon север многоэтажный дом
            "8" => 23, // Gpon Токмок многоэтажный дом
            "9" => 23, // Gpon Токмок частный Дом
            "10" => 24, // Gpon Узген многоэтажный дом
            "11" => 24 // Gpon Узген частный Дом
        ];
        $group = $this->getFirstInObject($customer->data->group, "id");
        return isset($groups[$group]) ? $groups[$group] : null;
    }

    /*
    * Bgb
    */
    private function execute()
    {
        $usersideFacade = new UsersideFacade($this->command, $this->log);
        $usersideFacade->prepare($this->data);
        $validated = $usersideFacade->validate();
        if (!$validated->result) {
            return $this->stringToJson($validated);
        }
        $response = $usersideFacade->perform();
        $this->log->info("Response - " . $response);
        return $this->stringToJson($response);
    }

    private function setCommand($command)
    {
        switch ($command) {
            case "get_contract_number":
                $this->command = new GetContractNumber();
                break;
            case "attach_gpon_serial":
                $this->command = new AttachGponSerial();
                break;
            default:
                $this->command = null;
        }
    }

    private function setData($data)
    {
        $this->data = $data;
    }

    /*
    * Userside
    */
    private function getSerialFromCustomer($customerId) 
    {
        $request = new GetInventoryAmount();
        $request->location = "customer";
        $request->section_id = $this->onuSectionId;
        $request->object_id = $customerId;
        return $this->stringToJson($this->command($request));
    }

    private function getEquipmentFromTask($taskId) 
    {
        $request = new GetInventoryAmount();
        $request->location = "task";
        $request->object_id = $taskId;
        return $this->stringToJson($this->command($request));
    }

    private function getCustomerData($customerId) 
    {
        $request = new GetData();
        $request->customer_id = $customerId;
        return $this->stringToJson($this->command($request));
    }

    private function getTask($taskId) 
    {
        $request = new Show();
        $request->id = $taskId;
        return $this->stringToJson($this->command($request));
    }

    private function setContractNumber($customerId, $title) 
    {
        $request = new Edit();
        $request->id = $customerId;
        $request->agreement_number = $title;
        return $this->stringToJson($this->command($request));
    }

    private function switchToRegularCustomer($customerId)
    {
        $request = new Edit();
        $request->id = $customerId;
        $request->is_potential = 0;
        return $this->stringToJson($this->command($request));
    }

    private function setBillingId($customerId, $billingUserId) 
    {
        $request = new ChangeBilling();
        $request->customer_id = $customerId;
        $request->billing_id = 1;
        $request->billing_user_id = $billingUserId;
        return $this->stringToJson($this->command($request));
    }

    private function removeEquipmentCommand($invId)
    {
        /*
        * Мы не реализуем удаление серийного, они возобновляемые.
        * Возвращаем их на склад ($this->storageId)
        */
        $request = new TransferInventory();
        $request->inventory_id = $invId;
        $request->dst_account = "2040300000" . $this->storageId;
        return $this->stringToJson($this->command($request));
    }

    private function changeTaskStatus($taskId, $statusId) 
    {
        $request = new ChangeState();
        $request->id = $taskId;
        $request->state_id = $statusId;
        return $this->stringToJson($this->command($request));
    }

    private function command(ApiRequest $request)
    {
        $result = null;
        $url = $request->getUrl();
        if ($url == null) {
            $this->log("Implementation: " . $request::class, false);
            $this->log("Url is null", false);
            return $result;
        }
        $this->log("Url: " . $url);
        $result = $this->api->get($url);
        $this->log("Response (on the next line): ");
        $this->log($result);
        if (!$this->checkResponse($result)) {
            $this->log("Url: " . $url, false);
            $this->log("Response (on the next line): ", false);
            $this->log($result, false);
            $result = null;
        }
        return $result;
    }

    private function checkResponse($response)
    {
        $result = false;
        if ($response === null) {
            $this->log("Null response", false);
            return $result;
        }
        if (!is_array($response)) {
            $this->log("Response not array", false);
            return $result;
        }
        if (count($response) == 0) {
            $this->log("Response zero count", false);
            return $result;
        }
        if (!isset($response['result'])) {
            $this->log("Result index not exist", false);
            return $result;
        }
        if ($response['result'] == 'OK') {
            $result = true;
        }
        return $result;
    }
}
