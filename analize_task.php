<?php

require_once __DIR__ . '/Lem62/AnalizeFacade.php';

$analize = new AnalizeFacade();

$report = '';

/* ONU analize */
$onus = $analize->analizeOnu();
if (isset($onus['empty'])) {
    $report .= "## ONU with empty serial number\n\n";
    $ids = '';
    foreach ($onus['empty'] as $key => $value) {
        $ids .= $value . ", ";
    }
    $report .= "*ids: " . preg_replace("/, $/", "", $ids) . "*\n\n";
}
if (isset($onus['format'])) {
    $report .= "## Wrong ONU format\n\n";
    $ids = '';
    foreach ($onus['format'] as $key => $value) {
        $report .= "- " . $value['sn'] . "\n";
        $ids .= $value['id'] . ", ";
    }
    $report .= "\n*ids: " . preg_replace("/, $/", "", $ids) . "*";
    $report .= "\n\n";
}
if (isset($onus['duplicate'])) {
    $report .= "## Duplicates ONU (a count in brackets)\n\n";
    foreach ($onus['duplicate'] as $key => $value) {
        $report .= "- " . $value['sn']  . " ("  . $value['count'] . ")\n";
    }
    $report .= "\n\n";
}
if (isset($onus['few'])) {
    print_r($onus['few']);
}
/* if (isset($onus['few'])) {
    $report .= "## Few ONU on customer\n\n";
    foreach ($onus['few'] as $key => $value) {
        $report .= "\t" . $value['sn']  . " ("  . $value['count'] . ")";
    }
    $report .= "\n\n";
} */
// print_r($analize->analizeOnu());

/* Duplicates billing ids */
$billingIds = $analize->analizeDuplicateBillingUid();
if ($billingIds && count($billingIds) > 0) {
    $report .= "## Duplicates billing ids (customer ids in brackets)\n\n";
    foreach ($billingIds as $key => $value) {
        $report .= "- " . @$key . " ("  . implode(", ", $value) . ")\n";
    }
    $report .= "\n\n";
}
// print_r($analize->analizeDuplicateBillingUid());

/* Large ONU operations amount */
$operationsAmount = $analize->analizeAmountOnuOperation(30);
if (isset($operationsAmount['storage']) && count($operationsAmount['storage']) > 0) {
    $report .= "## Large amount ONU operations on STORAGE (a count in bracket)\n\n";
    foreach ($operationsAmount['storage'] as $key => $value) {
        $report .= "- " . $key . " ("  . $value . ")\n";
    }
    $report .= "\n\n";
}
if (isset($operationsAmount['customer']) && count($operationsAmount['customer']) > 0) {
    $report .= "## Large amount ONU operations on CUSTOMER (a count in bracket)\n\n";
    foreach ($operationsAmount['customer'] as $key => $value) {
        $report .= "- " . $key . " ("  . $value . ")\n";
    }
    $report .= "\n\n";
}


print_r($report);
return $report;
// print_r($analize->analizeAmountOnuOperation(30));