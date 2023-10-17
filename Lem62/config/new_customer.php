<?php

/**
 * @var Lem62\Traits\CustomDotEnv $this
*/

return [
    'status' => [
        'get_contract' => $this->dotEnvConfig('NC_STATUS_GET_CONTRACT', 16),
        'add_equipment' => $this->dotEnvConfig('NC_STATUS_ADD_EQUIPMENT',17),
        'apply_equipmnet' => $this->dotEnvConfig('NC_STATUS_APPLY_EQUIPMNET',19),
        'equipmnet_applied' => $this->dotEnvConfig('NC_STATUS_EQUIPMNET_APPLIED',18),
        'cancel' => $this->dotEnvConfig('NC_STATUS_CANCEL',11),
        'finish' => $this->dotEnvConfig('NC_STATUS_FINISH',12)
    ],
    'task_type' => [26, 28],
    'us_api_url' => $this->dotEnvConfig('USERSIDE_API_URL'),
    'bgb_api_url' => $this->dotEnvConfig('BGBILLING_API_URL'),
    'onu_section_id' => 9,
    'tariff_list_id' => 25,
    'storage_id' => 16,
];