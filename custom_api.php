<?php

// error_reporting(E_ALL); ini_set('display_errors', 1);

require_once '/home/ldb/php/userside_bgb_via_custom_api/Lem62/BgbUsFacade.php';
require_once '/home/ldb/php/userside_bgb_via_custom_api/Lem62/EquipmentOnecFacade.php';

function api_function($apiFunction, $arg1 = 0, $arg2 = 0, $arg3 = 0, $arg4 = 0, $arg5 = 0) {
    // pl($apiFunction . " " . argToString($arg1, $arg2, $arg3, $arg4, $arg5) . "\n");
    switch ($apiFunction) {
        
        case "task_state_change_before":
            $bgbUsFacade = new BgbUsFacade();
            return $bgbUsFacade->registrationNewCustomer($arg1);

        case "task_state_change":
            $bgbUsFacade = new BgbUsFacade();
            return $bgbUsFacade->registrationNewCustomerAfter($arg1);

        case "inventory_transfer":
            $equipmentOnecFacade = new EquipmentOnecFacade();
            $equipmentOnecFacade->sendEquipment($arg1);
            break;

        case "user_mark_add":
            $bgbUsFacade = new BgbUsFacade();
            return $bgbUsFacade->hiddenApi($arg1);
    }
    return true;
}

function argToString($arg1 = 0, $arg2 = 0, $arg3 = 0, $arg4 = 0, $arg5 = 0) {
    $result = 
        "arg1: " . var_export($arg1, true) . "\n" . 
        "arg2: " . var_export($arg2, true) . "\n" .
        "arg3: " . var_export($arg3, true) . "\n" .
        "arg4: " . var_export($arg4, true) . "\n" .
        "arg5: " . var_export($arg5, true);
    return $result;
}

function pl($message) {
    if(empty($message)) return 0;
    $logFile = "/home/ldb/php/userside_bgb_via_custom_api/logs/custom_internal_api.txt";
    if (!file_exists($logFile)) {
        file_put_contents($logFile, date('[Y-m-d H:i:s]: ').$message."\r\n");
    } else {
        file_put_contents($logFile, date('[Y-m-d H:i:s]: ').$message."\r\n", FILE_APPEND);
    }
}