<?php

require_once __DIR__ . '/../Lem62/SyncOnuFacade.php';

$syncOnu = new SyncOnuFacade();
$syncOnu->forceFullSync(true);
$syncOnu->sync();

exit();
