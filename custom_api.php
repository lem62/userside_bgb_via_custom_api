<?php

error_reporting(E_ALL); ini_set('display_errors', 1);

require '/home/ldb/php/userside_bgb_via_custom_api/Lem62/BgbUsFacade.php';

function api_function($apiFunction, $arg1 = 0, $arg2 = 0, $arg3 = 0, $arg4 = 0, $arg5 = 0) {
    switch ($apiFunction) {
        case "task_state_change_before":
            // pl($apiFunction . " " . argToString($arg1, $arg2, $arg3, $arg4, $arg5) . "\n");
            $bgbUsFacade = new BgbUsFacade();
            return $bgbUsFacade->registrationNewCustomer($arg1);
            break;
        case "task_state_change":
            // pl($apiFunction . " " . argToString($arg1, $arg2, $arg3, $arg4, $arg5) . "\n");
            if (is_array($arg1) && isset($arg1['stateId']) && isset($arg1['customerId'])) {
                $bgbUsFacade = new BgbUsFacade();
                $bgbUsFacade->setRedirectUrl("/oper/?core_section=task&action=show&id=" . $arg1['taskId']);
                switch ($arg1['stateId']) {
                    case 16: // Получение номера договора
                        return $bgbUsFacade->response(false, "Номер договора выделен");
                    case 19: // Регистрация Ону
                        return $bgbUsFacade->response(false, "ONU зарегистрирована");
                }
                $bgbUsFacade = null;
            }
            break;
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