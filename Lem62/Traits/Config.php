<?php

namespace Lem62\Traits;

use Lem62\Traits\CustomDotEnv;

trait Config
{
    use CustomDotEnv;

    private $configDir = __DIR__  . "/../config/";
    private $config = null;

    /**
     * @return void
     */
    public function setConfig($configFile)
    {
        if (!file_exists($this->configDir . $configFile . ".php")) {
            return;
        }
        $this->config = include $this->configDir . $configFile . ".php";
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
/*
    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->config)) {
            $this->config[$key] = $value;
        }
    }
*/
}