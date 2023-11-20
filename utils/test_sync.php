<?php

error_reporting(E_ALL); ini_set('display_errors', 1);

require __DIR__ . '/../Lem62/SyncOnuFacade.php';

$syncOnu = new SyncOnuFacade(false);
$syncOnu->sync();