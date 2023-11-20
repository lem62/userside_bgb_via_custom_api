<?php

require_once __DIR__ . '/../Lem62/AnalizeFacade.php';
require_once __DIR__ . '/../Lem62/Traits/CustomDotEnv.php';
require_once __DIR__ . '/../Lem62/Model/Config.php';
require_once __DIR__ . '/../Lem62/Userside/Api/ApiUserside.php';
require_once __DIR__ . '/../Lem62/Userside/Api/Model/ApiRequest.php';
require_once __DIR__ . '/../Lem62/Userside/Api/Model/UsersideAction.php';
require_once __DIR__ . '/../Lem62/Userside/Api/Action/Inventory/TransferInventory.php';
require_once __DIR__ . '/../Lem62/Userside/Api/Action/Inventory/DeleteInventory.php';

use Lem62\Model\Config;
use Lem62\Userside\Api\ApiUserside;
use Lem62\Userside\Api\Action\Inventory\TransferInventory;
use Lem62\Userside\Api\Action\Inventory\DeleteInventory;

$analize = new AnalizeFacade();
$wrongFormat = $analize->analizeOnu();
$wrongFormat = isset($wrongFormat['format']) ? $wrongFormat['format'] : [];
$analize = null;

print_r($wrongFormat);

echo "Count: " . count($wrongFormat) . "\n";

if (count($wrongFormat) == 0) {
    exit();
}


foreach ($wrongFormat as $k => $v) {
    if (strlen($v['sn']) != 12) {
        echo "Length over 12: " . $v['id'] . " / " . $v['sn'] . "\n";
        unset($wrongFormat[$k]);
    }
}

echo "Count: " . count($wrongFormat) . "\n";

$config = new Config('sync_onu');
$apiUserside = new ApiUserside($config->us_api_url, $config->us_api_timeout);

foreach ($wrongFormat as $k => $v) {
    removeOnu($v['id']);
    echo date('Y-m-d H:i:s') . " Remove - " . $v['id'] . " / " . $v['sn'] . "\n";
}

function removeOnu($onuId) {
    
    global $apiUserside, $config;

    $request = new TransferInventory();
    $request->inventory_id = $onuId;
    $request->dst_account = "900030000" . $config->storage_id;
    $url = $request->getUrl();
    if ($url == null) {
        return "Can no get URL " . $onuId;
    }
    $response = $apiUserside->get($url);
    if (!$response) {
        return false;
    }
    $request = new DeleteInventory();
    $request->id = $onuId;
    $url = $request->getUrl();
    if ($url == null) {
        return "Can no get URL " . $onuId;
    }
    $response = $apiUserside->get($url);
    if (!$response) {
        return false;
    }
    return true;
}