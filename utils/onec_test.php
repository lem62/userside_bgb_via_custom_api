<?php

error_reporting(E_ALL); ini_set('display_errors', 1);

require __DIR__ . '/../Lem62/Log/LogFile.php';
require __DIR__ . '/../Lem62/Traits/CustomDotEnv.php';
require __DIR__ . '/../Lem62/Model/Config.php';
require __DIR__ . '/../Lem62/Model/QueryResponse.php';
require __DIR__ . '/../Lem62/Traits/OutputFormat.php';
require __DIR__ . '/../Lem62/Userside/Api/ApiUserside.php';
require __DIR__ . '/../Lem62/Model/QueryJson.php';
require __DIR__ . '/../Lem62/Onec/Model/OnecApiRequest.php';
require __DIR__ . '/../Lem62/Onec/Model/OnecCommand.php';
require __DIR__ . '/../Lem62/Onec/ApiOnec.php';
require __DIR__ . '/../Lem62/Onec/OnecFacade.php';
require __DIR__ . '/../Lem62/Onec/Command/NewCustomerData.php';

use Lem62\Userside\Api\ApiUserSide;
use Lem62\Onec\OnecFacade;
use Lem62\Onec\Command\NewCustomerData;



$api = new ApiUserside("http://127.0.0.1/api.php?skip_internal_api=1&key=&");
$equipmentId = 385420;
$e = $api->get("&cat=inventory&action=get_inventory&id=" . $equipmentId);
$equipment = $e['data'];
$eo = $api->get("&cat=inventory&action=get_operation&inventory_id=" . $equipmentId);
$equipment['operations'] = $eo['data'];
    



// print_r($equipmentOnCustomer);
// print_r($equipment);

if ($equipment) {
    $equipment['type'] = 'equipment';
}


// var_dump($customer);

$command = new NewCustomerData();
$facade = new OnecFacade($command);
$facade->prepare($equipment);
$facade->perform();