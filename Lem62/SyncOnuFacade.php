<?php

// error_reporting(E_ALL); ini_set('display_errors', 1);

require_once __DIR__ . '/Log/LogFile.php';
require_once __DIR__ . '/Traits/CustomDotEnv.php';
require_once __DIR__ . '/Traits/OutputFormat.php';
require_once __DIR__ . '/Traits/FileOperation.php';
require_once __DIR__ . '/Model/Config.php';
require_once __DIR__ . '/Bgb/Db/MysqlDb.php';
require_once __DIR__ . '/Userside/Api/ApiUserside.php';
require_once __DIR__ . '/Userside/Api/Model/ApiRequest.php';
require_once __DIR__ . '/Userside/Api/Model/UsersideAction.php';
require_once __DIR__ . '/Userside/Api/Action/Module/GetUserList.php';
require_once __DIR__ . '/Userside/Api/Action/Customer/GetAbonId.php';
require_once __DIR__ . '/Userside/Api/Action/Customer/GetData.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/AddInventory.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/AddInventoryAssortment.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventory.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryId.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryAmount.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetInventoryCatalog.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/TransferInventory.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/DeleteInventory.php';
require_once __DIR__ . '/Userside/Api/Action/Inventory/GetOperation.php';

use Lem62\Log\LogFile;
use Lem62\Traits\OutputFormat;
use Lem62\Traits\FileOperation;
use Lem62\Model\Config;
use Lem62\Bgb\Db\MysqlDb;
use Lem62\Userside\Api\ApiUserside;
use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Action\Module\GetUserList;
use Lem62\Userside\Api\Action\Customer\GetAbonId;
use Lem62\Userside\Api\Action\Customer\GetData;
use Lem62\Userside\Api\Action\Inventory\AddInventory;
use Lem62\Userside\Api\Action\Inventory\AddInventoryAssortment;
use Lem62\Userside\Api\Action\Inventory\GetInventory;
use Lem62\Userside\Api\Action\Inventory\GetInventoryId;
use Lem62\Userside\Api\Action\Inventory\GetInventoryAmount;
use Lem62\Userside\Api\Action\Inventory\GetInventoryCatalog;
use Lem62\Userside\Api\Action\Inventory\TransferInventory;
use Lem62\Userside\Api\Action\Inventory\DeleteInventory;
use Lem62\Userside\Api\Action\Inventory\GetOperation;

class SyncOnuFacade 
{
    use OutputFormat, FileOperation;

    private $debug = true; // log both info and error
    private $log = null;
    private $apiUserside = null;
    /**
    * @var Lem62\Bgb\Db\MysqlDb $db
    */
    private $db = null;
    private $fullSync = false;
    private $forceFullSync = false;
    private $logPrefix = null;
    private $logPath = __DIR__ . "/../logs/sync/";
    private $isLock = false;
    private $lockFile = "sync_onu.lock";
    private $lockExpirePeriod = 3600; // seconds (1h)
    private $lastFullSyncFile = "last_full_sync_onu";
    private $fullSyncStep = 86400; // seconds (24h)
    private $lastMidnightSyncFile = "last_midnight_sync_onu";
    private $midnightStep = 10800; // seconds (3h)

    /**
    * @var object $config
    */
    private $config = null;
    private $onuModels = null;

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
        $this->log = new LogFile($this->logPath, "sync_onu");
        $this->checkLock();
        $this->logPrefix = "construct";
        if ($this->isLock) {
            $this->log("Lock file found", false);
            return;
        }
        $this->config = new Config('sync_onu');
        $this->debug = $this->config->debug;
        $this->db = new MysqlDb();
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
    }

    public function forceFullSync(bool $forceFullSync)
    {
        $this->forceFullSync = $forceFullSync;
    }

    public function syncStorages() 
    {
        $this->logPrefix = "syncStorages";
        $this->log->info("Start sync");
        if ($this->isLock) {
            $this->log("Lock file found", false);
            return;
        }
        $executeStart = hrtime(true);
        $this->filePutContent($this->logPath . $this->lockFile, time(), false);
        $this->setFullSync();
        if ($this->fullSync) {
            $this->syncFull();
        } else {
            $this->syncPartial();
        }
        $this->fileRemove($this->logPath . $this->lockFile);
        $this->log->info($this->executeTime($executeStart));
        $this->log->info("Finish sync");
    }

    public function sync() 
    {
        $this->logPrefix = "sync";
        $this->log->info("Start sync");
        if ($this->isLock) {
            $this->log("Lock file found", false);
            return;
        }
        $executeStart = hrtime(true);
        $this->filePutContent($this->logPath . $this->lockFile, time(), false);
        $this->setFullSync();
        if ($this->fullSync) {
            $this->syncFull();
        } else {
            $this->syncPartial();
        }
        $this->fileRemove($this->logPath . $this->lockFile);
        $this->log->info($this->executeTime($executeStart));
        $this->log->info("Finish sync");
    }

    private function syncFull() 
    {
        $this->logPrefix = "syncFull";

        /* Get onu model list */
        $this->log("## Get onu model list");
        $this->onuModels = $this->getOnuModels();
        $this->log($this->onuModels);
        if (!$this->onuModels) {
            $this->log("Can no get model list", false);
            return;
        }
        
        /* Get Userside user IDs */ 
        $this->log("## Get Userside user IDs");
        $usUserIds = $this->getUsUserIds();
        if (!$usUserIds) {
            $this->log("Can no get all User ids (us)", false);
            $this->fileRemove($this->logPath . $this->lastFullSyncFile);
            return;
        }
 
        /* Get ONU from billing */
        $this->log("## Get all ONU from billing");
        $bgbOnu = $this->getBgbOnu(0);
        if (!$bgbOnu) {
            $this->log("Can no get all ONU (bgb)", false);
            $this->fileRemove($this->logPath . $this->lastFullSyncFile);
            return;
        }

        /* Get all storage ONU */
        $this->log("## Get all storage ONU");
        $storageOnu = $this->getOnuArray('storage');
        if (!$storageOnu) {
            $this->log("Can no get all storage ONU (us)", false);
            $this->fileRemove($this->logPath . $this->lastFullSyncFile);
            return;
        }

        /* Get all customer ONU */
        $this->log("## Get all customer ONU");
        $customerOnu = $this->getOnuArray('customer');
        if (!$customerOnu) {
            $this->log("Can no get all customer ONU (us)", false);
            $this->fileRemove($this->logPath . $this->lastFullSyncFile);
            return;
        }

        /* Add US customer ID and ONU */
        $this->log("## Add US customer ID and ONU");
        foreach ($bgbOnu as $k => $v) {
            $bgbOnu[$k]['customer_id'] = isset($usUserIds[$v['cid']]) ? $usUserIds[$v['cid']] : null;
            if (!$bgbOnu[$k]['customer_id']) { /* No customer, no action */
                continue;
            }
            $onu = isset($customerOnu[$v['sn']]) ? $customerOnu[$v['sn']] : null;
            if (!$onu) {
                $onu = isset($storageOnu[$v['sn']]) ? $storageOnu[$v['sn']] : null;
            }
            $bgbOnu[$k]['onu'] = $onu;
        }

        /* Perform sync */
        $this->performSync($bgbOnu);
    }

    private function syncPartial() 
    {
        $this->logPrefix = "syncPartial";

        /* Get ONU from billing */
        $this->log("## Get ONU from billing");
        $bgbOnu = $this->getBgbOnu(
            ($this->modifiedFromMidnight()
            ? 0
            : $this->config->sync_modified_in)
        );
        $this->log("ONU array (next line):");
        $this->log($bgbOnu);
        if (!$bgbOnu) {
            $this->log("There are no modified ONUs (bgb)");
            return;
        }

        /* Sync model list */
        $this->log("## Sync model list");
        $this->syncOnuModels();
        $this->logPrefix = "syncPartial";
        
        /* Get onu model list */
        $this->log("## Get onu model list");
        $this->onuModels = $this->getOnuModels();
        if (!$this->onuModels) {
            $this->log("Can no get model list", false);
            return;
        }

        /* Add US customer ID and ONU */
        $this->log("## Add US customer ID and ONU");
        foreach ($bgbOnu as $k => $v) {
            $bgbOnu[$k]['customer_id'] = $this->findCustomer($v['cid']);
            if (!$bgbOnu[$k]['customer_id']) { /* No customer, no action */
                $this->log("No customer in US cid: " . $v['cid']);
                continue;
            }
            $bgbOnu[$k]['onu'] = $this->findOnu($v['sn']);
        }

        /* Perform sync */
        $this->performSync($bgbOnu);
    }

    private function performSync(array $bgbOnu)  /* [cid, sn, model, onu => [id, customer_id, model, model_id]] */
    {
        /* Main actions (add, skip, transfer) */
        $this->log("## Main actions (add, skip, transfer)");
        $this->log("Count billing ONU: " . count($bgbOnu));
        foreach ($bgbOnu as $k => $v) {
            if (!$v['customer_id']) { /* No customer, no action */
                $this->log("No customer in US cid: " . $v['cid']);
                continue;
            }
            $this->log("cid: " . $v['cid'] . ", customer_id: " . $v['customer_id'] . ", sn: " . $v['sn'] . " / " . $v['model']);
            $this->log($v);
            if (!$v['onu']) {
                $existOnu = $this->existOnuCustomer($v['customer_id']);
                if ($existOnu) {
                    foreach ($existOnu as $key => $value) {
                        $this->moveOnuStorage($value, $v['customer_id']);
                    }
                }
                $modelId = isset($this->onuModels[$v['model']]) 
                    ? $this->onuModels[$v['model']]
                    : $this->config->default_onu_model_id;
                $newOnuId = $this->addOnu($v['sn'], $modelId);
                $this->log("New ONU ID: " . $newOnuId);
                if ($newOnuId === 0) {
                    $this->log("New ONU not created", false);
                    $this->log($v, false);
                    continue;
                }
                if (!$this->moveOnuCustomer($v['customer_id'], $newOnuId)) {
                    $this->log("Can no transfer ONU to Customer", false);
                    $this->log($v, false);
                    continue;
                }
                $this->log("New ONU created and transferred");
                continue;
            }
            if ($v['model'] != $v['onu']['model']) {
                $this->log("Not equal ONU models", false);
                $this->log($v, false);
            }
            if ($v['customer_id'] == $v['onu']['customer_id']) {
                $this->log("ONU already on customer");
                continue;
            }
            $existOnu = $this->existOnuCustomer($v['customer_id']);
            if ($existOnu) {
                foreach ($existOnu as $key => $value) {
                    $this->moveOnuStorage($value, $v['customer_id']);
                }
            }
            if (!$this->moveOnuCustomer($v['customer_id'], $v['onu']['id'])) {
                $this->log("Can no transfer ONU to Customer", false);
                $this->log($v, false);
                continue;
            }
            $this->log("ONU transferred");
        }
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

    private function modifiedFromMidnight()
    {
        $lastMidnightSyncFile = $this->fileGetContent($this->logPath . $this->lastMidnightSyncFile);
        if (!$lastMidnightSyncFile) {
            $this->filePutContent($this->logPath . $this->lastMidnightSyncFile, time(), false);
            $this->log->info("Choose from midnight");
            return true;
        }
        $timeDiff = time() - (int)$lastMidnightSyncFile;
        if ($timeDiff > $this->midnightStep) {
            $this->filePutContent($this->logPath . $this->lastMidnightSyncFile, time(), false);
            $this->log->info("Choose from midnight");
            return true;
        }
        return false;
    }

    private function setFullSync()
    {
        if ($this->forceFullSync) {
            $this->log->info("Force full sync");
            $this->fullSync = true;
        } else {
            $currentHour = (int)date('H');
            if ($currentHour > 4 && $currentHour <= 23) {
                $this->fullSync = false;
                return;
            }
        }
        $lastFullSyncFile = $this->fileGetContent($this->logPath . $this->lastFullSyncFile);
        if (!$lastFullSyncFile) {
            $this->filePutContent($this->logPath . $this->lastFullSyncFile, time(), false);
            $this->log->info("It is full sync");
            $this->fullSync = true;
            return;
        }
        $timeDiff = time() - (int)$lastFullSyncFile;
        if ($timeDiff > $this->fullSyncStep) {
            $this->filePutContent($this->logPath . $this->lastFullSyncFile, time(), false);
            $this->log->info("It is full sync");
            $this->fullSync = true;
            return;
        }
        $this->fullSync = false;
    }

    /*
    * BGB
    */
    private function getBgbOnu($minute = 0) 
    {
        $q =
        "select cp.cid cid, cp.val sn, model.val model " .
        "from contract_parameter_type_1 cp " .
        "left join contract_parameter_type_1 model on cp.cid = model.cid and model.pid = " . $this->config->bgb_onu_model_id .
        " where cp.pid = " . $this->config->bgb_onu_sn_id;
        if (!$this->fullSync) {
            $cids = $this->getModifiedOnu($minute);
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

    /*
    * Userside
    */
    private function addOnu($onuSerial, $modelId) 
    {
        $request = new AddInventory();
        $request->sn = $onuSerial;
        $request->inventory_catalog_id = $modelId;
        $request->trader_id = $this->config->trader_id;
        $request->storage_id = $this->config->storage_id;
        $request->comment = "bgb";
        $response = $this->command($request);
        if (!$response) {
            return 0;
        }
        return $response['id'];
    }

    private function addOnuModel($model) 
    {
        $request = new AddInventoryAssortment();
        $request->section_id = $this->config->onu_section_id;
        $request->name = trim($model);
        $request->unit_name = "шт.";
        return $this->command($request);
    }

    public function getOnuModels(bool $modelInKey = true) 
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
            if ($modelInKey) {
                $result[$v['name']] = $k;
            } else {
                $result[$k] = $v['name'];
            }
        }
        return $result;
    }

    private function findOnu($sn) 
    {
        $request = new GetInventoryId();
        $request->data_typer = "serial_number";
        $request->data_value = trim($sn);
        $response = $this->command($request);
        if (!$response) {
            return null;
        }
        if ($response['id'] === 0) {
            return null;
        }
        $request = new GetInventory();
        $request->id = $response['id'];
        $response = $this->command($request);
        if (!$response) {
            return null;
        }
        $response = $response['data'];
        $result = [
            'id' => $response['id'],
            'customer_id' => $response['location_object_id'],
            'model' => $response['name'],
            'model_id' => $response['catalog_id'],
        ];
        return $result;
    }

    private function findCustomer($billingId) 
    {
        $request = new GetAbonId();
        $request->data_typer = 'billing_uid';
        $request->data_value = $billingId;
        $response = $this->command($request);
        return isset($response['Id']) ? $response['Id'] : 0;
    }

    private function getUsUserIds() 
    {
        $request = new GetUserList();
        $request->billing_id = $this->config->billing_id;
        $request->is_id_billing_user_id = 1;
        $request->is_with_potential = 1;
        $url = $request->getUrl();
        if ($url == null) {
            $this->log("getUsUserIds Implementation: " . $request::class, false);
            $this->log("getUsUserIds Url is null", false);
            return null;
        }
        $response = $this->apiUserside->get($url);
        if (!is_array($response)) {
            $this->log("getUsUserIds Response is not array", false);
            return null;
        }
        foreach ($response as $key => $value) {
            $result[$key] = $value['userside_id'];
        }
        return $result;
    }

    private function getOnuArray($location)
    {
        $serials = $this->getOnus($location);
        if (!$serials) {
            return null;
        }
        $onuModels = $this->getOnuModels(false);
        if (!$onuModels) {
            return null;
        }
        foreach ($serials['data'] as $k => $v) {
            $result[$v['serial_number']] = [
                'id' => $k, 
                'model' => $onuModels[$v['inventory_type_id']],
                'model_id' => $v['inventory_type_id'],
                'customer_id' => $v['object_id']
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

    private function moveOnuCustomer($customerId, $onuId)
    {
        $request = new TransferInventory();
        $request->inventory_id = $onuId;
        $request->dst_account = $this->getCidForTransfer($customerId);
        return $this->command($request);
    }

    private function moveOnuStorage($onuId, $customerId = 0)
    {
        $storageId = $this->getRegionStorageId($customerId);
        $request = new TransferInventory();
        $request->inventory_id = $onuId;
        $request->dst_account = "2040300000" . $storageId;
/*
        $storageId = $this->getLastStorageId($onuId);
        $storageId = ($storageId === 0) 
            ? $this->config->return_storage_id
            : $storageId;
        $request->dst_account = "2040300000" . $storageId;
*/
        return $this->command($request);
    }

    private function getRegionStorageId($customerId)
    {
        $storageId = $this->config->return_south_storage_id;
        $request = new GetData();
        $request->customer_id = $customerId;
        $response = $this->command($request);
        if (!$response || !isset($response['data'])) {
            return $storageId;
        }
        if (!isset($response['data']['group']) || count($response['data']['group']) == 0) {
            return $storageId;
        }
        $response= $response['data']['group'];
        $groups = [
            "3" => $this->config->return_south_storage_id, // Gpon Юг частный Дом
            "4" => $this->config->return_south_storage_id, // Gpon Юг многоэтажный дом
            "5" => $this->config->return_south_storage_id, // А.Равшан
            "6" => $this->config->return_north_storage_id, // Gpon север частный Дом
            "7" => $this->config->return_north_storage_id, // Gpon север многоэтажный дом
            "8" => $this->config->return_north_storage_id, // Gpon Токмок многоэтажный дом
            "9" => $this->config->return_north_storage_id, // Gpon Токмок частный Дом
            "10" => $this->config->return_south_storage_id, // Gpon Узген многоэтажный дом
            "11" => $this->config->return_south_storage_id // Gpon Узген частный Дом
        ];
        foreach ($response as $v) {
            $storageId = $groups[$v['id']];
        }
        return $storageId;
    }

    private function removeOnu($onuId)
    {
        $request = new TransferInventory();
        $request->inventory_id = $onuId;
        $request->dst_account = "900030000" . $this->config->storage_id;
        $response = $this->command($request);
        if (!$response) {
            return false;
        }
        $request = new DeleteInventory();
        $request->id = $onuId;
        $response = $this->command($request);
        if (!$response) {
            return false;
        }
        return true;
    }

    private function existOnuCustomer($customerId)
    {
        $request = new GetInventoryAmount();
        $request->location = "customer";
        $request->section_id = $this->config->onu_section_id;
        $request->object_id = $customerId;
        $response = $this->command($request);
        if (!$response) {
            return null;
        }
        if (!isset($response['data'])) {
            return null;
        }
        if (count($response['data']) == 0) {
            return null;
        }
        foreach ($response['data'] as $k => $v) {
            $result[] = $v['id'];
        }
        return $result;
    }

    private function getOperations($onuId) 
    {
        $request = new GetOperation();
        $request->inventory_id = $onuId;
        $response = $this->command($request);
        if (!$response) {
            return null;
        }
        if (!isset($response['data'])) {
            return null;
        }
        return $response['data'];
    }

    private function getLastStorageId($onuId)
    {
        $result = 0;
        $data = $this->getOperations($onuId);
        if (!$data) {
            return $result;
        }
        $data = array_reverse($data);
        foreach ($data as $k => $v) {
            if ($v['dst_account_type'] != 204) {
                continue;
            }
            $result = $v['dst_account_object_id'];
            break;
        }
        return $result;
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

    private function getCidForTransfer($cid)
    {
        if (preg_match("/^(" . $this->config->pref_onu_on_customer . "\d+)$/", $cid)) {
            return $cid;
        }
        if (!is_int($cid)) {
            return null;
        }
        if ($cid < 0) {
            return null;
        }
        /* https://wiki.userside.eu/API_inventory */
        $zeros = 7 - strlen($cid);
        $zeros = ($zeros < 0) ? 0 : $zeros;
        $zeros = str_repeat("0", $zeros);
        return $this->config->pref_onu_on_customer . $zeros . $cid;
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

    private function checkLock()
    {
        $lockFile = $this->fileGetContent($this->logPath . $this->lockFile);
        if (!$lockFile) {
            return;
        }
        $timeDiff = time() - (int)$lockFile;
        if ($timeDiff > $this->lockExpirePeriod) {
            $this->fileRemove($this->logPath . $this->lockFile);
            $this->log->info("Lock file expired. Try to remove it");
            return;
        }
        $this->isLock = true;
    }

    public function executeTime($executeStart)
    {
        return "Execute time: " . ((hrtime(true) - $executeStart)/1e+6) . " ms.";
    }

    public function exceptionHandler(Throwable $e)
    {
        /* Lock on 3 minutes */
        $this->filePutContent($this->logPath . $this->lockFile, (time()-($this->lockExpirePeriod-180)));
        if (!$this->log) {
            throw $e;
        }
        $this->log->exception("Message - " . $e->getMessage());
        $this->log->exception("Trace -\n" . $e->getTraceAsString());
        throw $e;
    }
}
