<?php

namespace Lem62\Traits;

trait CustomDotEnv
{
    private $dotEnvFile = __DIR__  . "/../.env";
    private $separator = "=";
    private $config = null;

    public function dotEnvConfig($key, $default = null)
    {
        if ($this->config === null) {
            $_env = file($this->dotEnvFile);
            foreach ($_env as $value) {
                preg_match("/^(.*?)" . $this->separator . "(.*)/", $value, $value);
                if (count($value) == 3) {
                    $value[1] = preg_replace("/(^[\'\"]|[\'\"]$)/", "", trim($value[1]));
                    $value[2] = preg_replace("/(^[\'\"]|[\'\"]$)/", "", trim($value[2]));
                    $this->config[$value[1]] = $value[2];
                }
            }
        }
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
}