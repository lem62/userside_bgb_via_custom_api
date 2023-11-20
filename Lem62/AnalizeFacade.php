<?php

// error_reporting(E_ALL); ini_set('display_errors', 1);

require_once __DIR__ . '/Log/LogFile.php';
require_once __DIR__ . '/Traits/CustomDotEnv.php';
require_once __DIR__ . '/Traits/OutputFormat.php';
require_once __DIR__ . '/Traits/FileOperation.php';
require_once __DIR__ . '/Traits/UsEquipment.php';
require_once __DIR__ . '/Model/Config.php';
require_once __DIR__ . '/Bgb/Db/MysqlDb.php';
require_once __DIR__ . '/Userside/Api/ApiUserside.php';
require_once __DIR__ . '/Userside/Api/Model/ApiRequest.php';
require_once __DIR__ . '/Userside/Api/Model/UsersideAction.php';
require_once __DIR__ . '/Userside/Api/Action/Module/GetUserList.php';
require_once __DIR__ . '/Userside/Api/Action/Customer/GetAbonId.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventory.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryId.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryAmount.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryCatalog.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetOperation.php';

use Lem62\Log\LogFile;
use Lem62\Traits\OutputFormat;
use Lem62\Traits\FileOperation;
use Lem62\Traits\UsEquipment;
use Lem62\Model\Config;
use Lem62\Bgb\Db\MysqlDb;
use Lem62\Userside\Api\ApiUserside;
use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Action\Module\GetUserList;
use Lem62\Userside\Api\Action\Customer\GetAbonId;
use Lem62\Userside\Api\Action\Inventory\GetInventory;
use Lem62\Userside\Api\Action\Inventory\GetInventoryId;
use Lem62\Userside\Api\Action\Inventory\GetInventoryAmount;
use Lem62\Userside\Api\Action\Inventory\GetInventoryCatalog;
use Lem62\Userside\Api\Action\Inventory\GetOperation;

class AnalizeFacade 
{
    use OutputFormat, FileOperation, UsEquipment;

    private $debug = false; // log both info and error
    private $log = null;
    private $logPrefix = null;
    private $logPath = __DIR__ . "/../logs/analize/";
    private $apiUserside = null;
    /**
    * @var Lem62\Bgb\Db\MysqlDb $db
    */
    private $db = null;
    /**
    * @var object $config
    */
    private $config = null;
    private $onuList = null;

    public function __construct()
    {
        date_default_timezone_set('Asia/Bishkek');
        set_error_handler(
            function($level, $error, $file, $line){
                if(0 === error_reporting()){
                    return false;
                }
                throw new ErrorException($error, $level, $level, $file, $line);
            },
            E_ALL
        );
        set_exception_handler([$this, 'exceptionHandler']);
        $this->log = new LogFile($this->logPath, "analize");
        $this->config = new Config('analize');
        $this->debug = $this->config->debug;
        // $this->db = new MysqlDb();
        $this->apiUserside = new ApiUserside(
            $this->config->us_api_url,
            $this->config->us_api_timeout
        );
    }

    public function __destruct()
    {
        $this->config = null;
        $this->db = null;
        $this->apiUserside = null;
        $this->log = null;
        $this->onuList = null;
    }

    /*
    * Выполняется 20 и более минут. Не нашел более подходящих действий в API
    */
    public function analizeAmountOnuOperation($countMore = 0)
    {
        $executeStart = hrtime(true);
        $this->logPrefix = "analizeOnu";
        $this->log->info("Start amount ONU operations analize");
        $onu = $this->getOnus('storage');
        if (!$onu) {
            $this->log("Can not get storage ONUs", false);
            return [];
        }
        $result['storage'] = $this->getOnuOperationAmountArray($onu['data'], $countMore);
        $onu = $this->getOnus('customer');
        if (!$onu) {
            $this->log("Can not get customer ONUs", false);
            return $result;
        }
        $result['customer'] = $this->getOnuOperationAmountArray($onu['data'], $countMore);
        $this->log->info($this->executeTime($executeStart));
        $this->log->info("Finish amount ONU operations analize");
        return $result;
    }

    public function analizeOnu()
    {
        $executeStart = hrtime(true);
        $this->logPrefix = "analizeOnu";
        $this->log->info("Start ONU analize");
        $serials = $this->getOnus('storage');
        if (!$serials) {
            $this->log("Can not get storage ONUs", false);
            return null;
        }
        foreach ($serials['data'] as $k => $v) {
            if (!$v['serial_number']) {
                $result['empty'][] = $v['id'];
                continue;
            }
            if (empty(trim($v['serial_number']))) {
                $result['empty'][] = $v['id'];
                continue;
            }
            if (!$this->validateOnuSerial($v['serial_number'])) {
                $result['format'][] = ['id' => $v['id'], 'sn' => $v['serial_number']];
                continue;
            }
            $duplicate[] = $v['serial_number'];
        }
        $serials = $this->getOnus('customer');
        if (!$serials) {
            $this->log("Can not get customer ONU", false);
            return null;
        }
        foreach ($serials['data'] as $k => $v) {
            if (!$v['serial_number']) {
                $result['empty'][] = $v['id'];
                continue;
            }
            if (!$this->validateOnuSerial($v['serial_number'])) {
                $result['format'][] = ['id' => $v['id'], 'sn' => $v['serial_number']];
                continue;
            }
            $duplicate[] = $v['serial_number'];
            $fewOnu[$v['object_id']][] = ['id' => $v['id'], 'sn' => $v['serial_number']];
        }
        $duplicate = array_count_values($duplicate);
        foreach ($duplicate as $k => $v) {
            if ($v > 1) {
                $result['duplicate'][] = ['sn' => $k, 'count' => $v];
            }
        }
        foreach ($fewOnu as $k => $v) {
            if (count($v) == 1) {
                unset($fewOnu[$k]);
            }
        }
        $result['few'] = $fewOnu;
        $this->log->info($this->executeTime($executeStart));
        $this->log->info("Finish ONU analize");
        return $result;
    }

    public function analizeDuplicateBillingUid() 
    {
        $executeStart = hrtime(true);
        $this->logPrefix = "analizeOnu";
        $this->log->info("Start duplicate billing ID analize");
        $request = new GetUserList();
        $request->billing_id = $this->config->billing_id;
        $request->is_id_billing_user_id = 0;
        $request->is_with_potential = 1;
        $url = $request->getUrl();
        if ($url == null) {
            $this->log("Implementation: " . $request::class, false);
            $this->log("Url is null", false);
            return null;
        }
        $response = $this->apiUserside->get($url);
        if (!is_array($response)) {
            $this->log("Response is not array", false);
            return null;
        }
        // $this->log($response, false); // жирные данные осторожно
        foreach ($response as $k => $v) {
            $duplicate[$v['billing_id']][] = $k;
        }
        foreach ($duplicate as $k => $v) {
            if (count($v) > 1) {
                $result[$k] = $v;
            }
        }
        $this->log->info($this->executeTime($executeStart));
        $this->log->info("Finish duplicate billing ID analize");
        return $result;
    }

    private function getOnuOperationAmountArray($onuArray, $countMore)
    {
        $request = new GetOperation();
        $result = [];
        foreach ($onuArray as $k => $v) {
            $request->inventory_id = $v['id'];
            $response = $this->apiUserside->get($request->getUrl());
            if (!$response) {
                continue;
            }
            if (!isset($response['data'])) {
                continue;
            }
            $count = count($response['data']);
            if ($count >= $countMore) {
                $result[$v['id']] = $count;
            }
        }
        return $result;
    }

    /*
    * Userside
    */

    private function getOnus($location) 
    {
        if ($location !== 'customer' && $location !== 'storage') {
            $this->log("Location must be customer or storage", false);
            return null;
        }
        if ($this->onuList !== null && isset($this->onuList[$location])) {
            $this->log("Get onu list from \$this->onuList\[$location\]");
            return $this->onuList[$location];
        }
        $request = new GetInventoryAmount();
        $request->location = $location;
        $request->section_id = $this->config->onu_section_id;
        $this->onuList[$location] = $this->command($request);
        return $this->onuList[$location];
    }

    private function command(ApiRequest $request)
    {
        $url = $request->getUrl();
        if ($url == null) {
            $this->log("Implementation: " . $request::class, false);
            $this->log("Url is null", false);
            return null;
        }
        $result = $this->apiUserside->get($url);
        $this->log("Url: " . $url);
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
        if ($response === null) {
            $this->log("Null response", false);
            return false;
        }
        if (!is_array($response)) {
            $this->log("Response not array", false);
            return false;
        }
        if (count($response) == 0) {
            $this->log("Response zero count", false);
            return false;
        }
        if (!isset($response['result'])) {
            $this->log("Result index not exist", false);
            return false;
        }
        if ($response['result'] != 'OK') {
            $this->log("Result index not OK", false);
            return false;
        }
        return true;
    }

    /* 
    * Service 
    */
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

    public function exceptionHandler(Throwable $e)
    {
        if (!$this->log) {
            throw $e;
        }
        $this->log->exception("Message - " . $e->getMessage() . ", line " . $e->getLine());
        $this->log->exception("Trace -\n" . $e->getTraceAsString());
        throw $e;
    }

    public function executeTime($executeStart)
    {
        return "Execute time: " . ((hrtime(true) - $executeStart)/1e+6) . " ms.";
    }
}
