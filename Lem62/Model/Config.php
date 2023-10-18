<?php

namespace Lem62\Model;

use Lem62\Traits\CustomDotEnv;

/*
* $configFile дожен возвращать массив - return ['key' => 'value']
* В $configFile допускается использование $this->dotEnvConfig($key, $defaultValue),
* для получения значений из Lem62/.env
*/

class Config
{
    use CustomDotEnv;

    private $configDir = __DIR__  . "/../config/";
    private $config = null;

    /**
     * @return object
     */
    public function __construct($configFile, $doException = true)
    {
        if (file_exists($this->configDir . $configFile . ".php")) {
            $this->config = include $this->configDir . $configFile . ".php";
        } else {
            if ($doException) {
                throw new \Exception('Can get app config ' . $configFile);
            }
        }
    }

    public function __get($key)
    {
        if ($this->config === null) {
            return null;
        }
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
    }

    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->config)) {
            $this->config[$key] = $value;
        }
    }
}