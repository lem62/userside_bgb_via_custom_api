<?php

/**
 * @var Lem62\Traits\CustomDotEnv $this
*/
return [
    'debug' => $this->dotEnvConfig('DEBUG', false),
    'us_api_url' => $this->dotEnvConfig('USERSIDE_API_URL'),
    'us_api_timeout' => 420, // 7 minutes
    'billing_id' => 1,
    'onu_section_id' => 9,
    'tariff_list_id' => 25,
    'storage_id' => 16,
    'return_storage_id' => 59,
    'trader_id' => 1,
    'default_onu_model_id' => 22,
    'pref_onu_on_customer' => '20503',
    'bgb_onu_sn_id' => 39,
    'bgb_onu_model_id' => 51,
    'sync_modified_in' => 60, // minutes
    'location' => [
        '204' => 'storage',
        '205' => 'customer',
    ],
];