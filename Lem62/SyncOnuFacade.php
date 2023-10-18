<?php

// error_reporting(E_ALL); ini_set('display_errors', 1);

require __DIR__ . '/Log/LogFile.php';
require __DIR__ . '/Traits/CustomDotEnv.php';
require __DIR__ . '/Traits/OutputFormat.php';
require __DIR__ . '/Model/Config.php';
require __DIR__ . '/Bgb/Db/MysqlDb.php';
require __DIR__ . '/Userside/Api/ApiUserside.php';
require __DIR__ . '/Userside/Api/Model/ApiRequest.php';
require __DIR__ . '/Userside/Api/Model/UsersideAction.php';
require __DIR__ . '/Userside/Api/Action/Customer/GetData.php';
require __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryAmount.php';
require __DIR__ . '/Userside/Api/Action/Inventory/TransferInventory.php';
require __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryCatalog.php';
require __DIR__ . '/Userside/Api/Action/Inventory/AddInventoryAssortment.php';
require __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryId.php';

use Lem62\Log\LogFile;
use Lem62\Traits\OutputFormat;
use Lem62\Model\Config;
use Lem62\Bgb\Db\MysqlDb;
use Lem62\Userside\Api\ApiUserside;
use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Action\Customer\GetData;
use Lem62\Userside\Api\Action\Inventory\GetInventoryAmount;
use Lem62\Userside\Api\Action\Inventory\TransferInventory;
use Lem62\Userside\Api\Action\Inventory\GetInventoryCatalog;
use Lem62\Userside\Api\Action\Inventory\AddInventoryAssortment;
use Lem62\Userside\Api\Action\Inventory\GetInventoryId;

class SyncOnuFacade 
{
    use OutputFormat;

    private $debug = true; // log both info and error
    private $log = null;
    private $apiUserside = null;
    /**
    * @var Lem62\Bgb\Db\MysqlDb $db
    */
    private $db = null;
    private $fullSync = false;
    private $logPrefix = null;
    /**
    * @var object $config
    */
    private $config = null;

    public function __construct(bool $fullSync)
    {
        $this->config = new Config('sync_onu');
        $this->db = new MysqlDb();
        $this->apiUserside = new ApiUserside($this->config->us_api_url, 120);
        $this->log = new LogFile(__DIR__ . "/../logs/sync/", "sync_onu");
        $this->fullSync = $fullSync;
        date_default_timezone_set('Asia/Bishkek');
    }

    public function __destruct()
    {
        $this->config = null;
        $this->db = null;
        $this->apiUserside = null;
        $this->log = null;
    }
    
    public function sync() 
    {
        $executeStart = hrtime(true);
        // $this->syncOnuModels();
        $this->logPrefix = "sync";
        // $bgbOnu = $this->getBgbOnu();
        // print_r($bgbOnu);
        // $storageOnu = $this->getOnuArray('storage');
        // $customerOnu = $this->getOnuArray('customer');
        // print_r($this->findOnu("HWTC4FE2003D"));

        echo "\nExecute time: " . ((hrtime(true) - $executeStart)/1e+6) . " ms.\n";
    }

    private function syncOnuModels()
    {
        $this->logPrefix = "syncOnuModels";        
        /* Get models from Userside */
        $usersideOnuModels = $this->getOnuModels();
        if (!$usersideOnuModels) {
            $this->log("Can not get ONU models (Userside)", false);
            return false;
        }
        /* Get models from Bgb */
        $bgbOnuModels = $this->getBgbOnuModels();
        if (!$bgbOnuModels) {
            $this->log("Can not get ONU models (BGB)", false);
            return false;
        }
        /* Compare lists */
        foreach ($usersideOnuModels as $k => $v) {
            foreach ($bgbOnuModels as $bgbKey => $bgb) {
                if ($k == $bgb['model']) {
                    unset($bgbOnuModels[$bgbKey]);
                }
            }
        }
        if (count($bgbOnuModels) == 0) {
            return true;
        }
        foreach ($bgbOnuModels as $bgbKey => $bgb) {
            $addOnuResult = $this->addOnuModel($bgb['model']);
            if (!$addOnuResult) {
                $this->log("Can not add ONU model (Userside)", false);
            }
        }
        $this->log("ONU models sync success");
        return true;
    }

    /*
    * BGB
    */
    private function getBgbOnuModels() 
    {
        $response = $this->db->fetchAll(
            "select val model " .
            "from contract_parameter_type_1 " .
            "where pid = " . $this->config->bgb_onu_model_id . 
            " group by val"
        );
        return $response;
    }
    
    private function getBgbOnu() 
    {
        $q =
        "select cp.cid cid, cp.val sn, model.val model " .
        "from contract_parameter_type_1 cp " .
        "left join contract_parameter_type_1 model on cp.cid = model.cid and model.pid = " . $this->config->bgb_onu_model_id .
        " where cp.pid = " . $this->config->bgb_onu_sn_id;
        if (!$this->fullSync) {
            $cids = $this->getModifiedOnu($this->config->sync_modified_in);
            if (!$cids) {
                return null;
            }
            $q .= " and cp.cid in (" . $cids . ")";
        }
        return $this->db->fetchAll($q);
    }

    private function getModifiedOnu($minute = 0) 
    {
        if ($minute == 0) {
            $dt = strtotime("midnight", time());
        } else {
            if (!is_int($minute)) {
                return null;
            }
            $dt = strtotime("-" . $minute . " minutes", time());
        }
        $dt = date('Y-m-d H:i:s', $dt);
        $q = 
        "select group_concat(cid) " .
        "from contract_parameter_type_1_log " .
        "where pid = ? " .
        "and dt_change >= ? " .
        "and val is not null";
        $stmt = $this->db->get()->prepare($q);
        $stmt->execute(
            [$this->config->bgb_onu_sn_id,
            $dt]
        );
        $cids = $stmt->fetch(PDO::FETCH_NUM);
        $cids = $cids[0];
        return $cids;
    }

    /*
    * Userside
    */
    public function getOnuModels($refill = false) 
    {
        $request = new GetInventoryCatalog();
        $request->section_id = $this->config->onu_section_id;
        $response = $this->command($request);
        if (!$response) {
            $this->log("Can not get ONU models in Userside", false);
            return null;
        }
        $result = [];
        foreach ($response['data'] as $k => $v) {
            $result[$v['name']] = $k;
        }
        return $result;
    }

    private function findOnu($serial) 
    {
        $request = new GetInventoryId();
        $request->data_typer = "serial_number";
        $request->data_value = trim($serial);
        return $this->command($request);
    }

    private function addOnuModel($model) 
    {
        $request = new AddInventoryAssortment();
        $request->section_id = $this->config->onu_section_id;
        $request->name = trim($model);
        $request->unit_name = "шт.";
        return $this->command($request);
    }

    public function getOnuArray($location)
    {
        $serials = $this->getOnus($location);
        if (!$serials) {
            return null;
        }
        foreach ($serials['data'] as $k => $v) {
            $result[$v['serial_number']] = [
                'id' => $k, 
                'serial' => $v['serial_number'],
                'model_id' => $v['inventory_type_id'],
                'customerId' => $v['object_id']
            ];
        }
        return $result;
    }

    private function getOnus($location) 
    {
        if ($location !== 'customer' && $location !== 'storage') {
            $this->log("Location must be customer or storage", false);
            return null;
        }
        $request = new GetInventoryAmount();
        $request->location = $location;
        $request->section_id = $this->config->onu_section_id;
        return $this->command($request);
    }

    private function getSerialFromCustomer($customerId) 
    {
        $request = new GetInventoryAmount();
        $request->location = "customer";
        $request->section_id = $this->config->onu_section_id;
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

    private function removeEquipmentCommand($invId)
    {
        /*
        * Мы не реализуем удаление серийного, они возобновляемые.
        * Возвращаем их на склад ($this->config->storage_id)
        */
        $request = new TransferInventory();
        $request->inventory_id = $invId;
        $request->dst_account = "2040300000" . $this->config->storage_id;
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
        $result = $this->apiUserside->get($url);
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
}
