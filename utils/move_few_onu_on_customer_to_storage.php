<?php

require_once __DIR__ . '/../Lem62/AnalizeFacade.php';
require_once __DIR__ . '/../Lem62/Traits/CustomDotEnv.php';
require_once __DIR__ . '/../Lem62/Model/Config.php';
require_once __DIR__ . '/../Lem62/Userside/Api/ApiUserside.php';
require_once __DIR__ . '/../Lem62/Userside/Api/Model/ApiRequest.php';
require_once __DIR__ . '/../Lem62/Userside/Api/Model/UsersideAction.php';
require_once __DIR__ . '/../Lem62/Userside/Api/Action/Inventory/TransferInventory.php';

use Lem62\Model\Config;
use Lem62\Userside\Api\ApiUserside;
use Lem62\Userside\Api\Action\Inventory\TransferInventory;

$analize = new AnalizeFacade();
$few = $analize->analizeOnu();
$few = $few['few'];
$analize = null;

print_r($few);

echo "Count: " . count($few);

if (count($few) == 0) {
    exit();
}

$config = new Config('sync_onu');
$apiUserside = new ApiUserside($config->us_api_url, $config->us_api_timeout);

foreach ($few as $k => $v) {
    moveOnuStorage($v[0]['id']);
    moveOnuStorage($v[1]['id']);
}

function moveOnuStorage($onuId) {
    
    global $apiUserside, $config;

    $request = new TransferInventory();
    $request->inventory_id = $onuId;
    $request->dst_account = "2040300000" . $config->storage_id;
    $url = $request->getUrl();
    if ($url == null) {
        return "Can no get URL " . $onuId;
    }
    $result = $apiUserside->get($url);
    return $result;
}