<?php

// error_reporting(E_ALL); ini_set('display_errors', 1);

require_once __DIR__ . '/Log/LogFile.php';
require_once __DIR__ . '/Traits/CustomDotEnv.php';
require_once __DIR__ . '/Model/Config.php';
require_once __DIR__ . '/Model/QueryResponse.php';
require_once __DIR__ . '/Traits/OutputFormat.php';
require_once __DIR__ . '/Userside/Api/ApiUserside.php';
require_once __DIR__ . '/Model/QueryJson.php';
require_once __DIR__ . '/Onec/Model/OnecApiRequest.php';
require_once __DIR__ . '/Onec/Model/OnecCommand.php';
require_once __DIR__ . '/Onec/ApiOnec.php';
require_once __DIR__ . '/Onec/OnecFacade.php';
require_once __DIR__ . '/Onec/Command/NewCustomerData.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventory.php';
require_once __DIR__ . '/Userside/Api/Model/ApiRequest.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetOperation.php';


use Lem62\Userside\Api\ApiUserSide;
use Lem62\Onec\OnecFacade;
use Lem62\Onec\Command\NewCustomerData;
use Lem62\Traits\OutputFormat;
use Lem62\Model\QueryResponse;
use Lem62\Log\LogFile;
use Lem62\Model\Config;
use Lem62\Userside\Api\Action\Inventory\GetInventory;
use Lem62\Userside\Api\Action\Inventory\GetOperation;
use Lem62\Userside\Api\Model\ApiRequest;

class EquipmentOnecFacade 
{
    use OutputFormat;

    private $debug = true; // log both info and error
    private $facade = null;
    /**
     * @var Lem62\Log\LogFile $log
     */
    private $log = null;
    private $api = null;
    private $logPrefix = null;
    /**
      * @var object $config
      */
    private $config = null;
    private $response = null;

    public function __construct()
    {
        $this->config = new Config('new_customer');
        $this->api = new ApiUserside($this->config->us_api_url);
        $this->log = new LogFile(__DIR__ . "/../logs/", "equipment_onec_facade");
        $this->response = new QueryResponse();
    }

    public function sendEquipment($eventArray)
    {
        if (!isset($eventArray['id'])) {
            $this->response->message = "Not set equipment id in event";
            return;
        }
        $equipment = $this->getEquipment($eventArray['id']);
        if (!$equipment) {
            $this->response->message = "Can not get equipment by id";
            return;
        }
        $equipment = $equipment['data'];
        $equipment['type'] = 'equipment';
        $operations = $this->getOperations($eventArray['id']);
        if ($operations) {
            $equipment['operations'] = $operations['data'];
        }
        $command = new NewCustomerData();
        $facade = new OnecFacade($command, $this->log);
        $facade->prepare($equipment);
        $facade->perform();
    }

    private function getEquipment($equipmentId) 
    {
        $request = new GetInventory();
        $request->id = $equipmentId;
        return $this->command($request);
    }

    private function getOperations($equipmentId) 
    {
        $request = new GetOperation();
        $request->inventory_id = $equipmentId;
        return $this->command($request);
    }
    
    private function log($msg, $info = true)
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

    public function response(QueryResponse $response, $also = null)
    {
        $this->log("Response - " . $response->result . ", msg: " . $response->message);
        if (isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'nogi=bogi') !== false) {
            return $this->jsonResponse($response);
        }
    }

    private function jsonResponse(QueryResponse $response)
    {
        if (!$response) {
            return true;
        }
        if ($response->result === null) {
            return true;
        }
        $this->log("JSON Response - " . json_encode($response));
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit();

    }
}