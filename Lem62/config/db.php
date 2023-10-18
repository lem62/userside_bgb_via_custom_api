<?php

/**
 * @var Lem62\Traits\CustomDotEnv $this
*/

return [
    'db_name' => $this->dotEnvConfig('DB_NAME'),
    'db_host' => $this->dotEnvConfig('DB_HOST'),
    'db_user' => $this->dotEnvConfig('DB_USER'),
    'db_pass' => $this->dotEnvConfig('DB_PASS')
];