<?php

namespace Lem62\Log;

class LogFile
{

    private $logDt = null;
    private $logPath = './';
    private $logFile = 'log.txt';

    public function __construct($logPath, $logFile)
    {
        if ($logPath) {
            $this->logPath = $logPath;
        }
        if ($logFile) {
            $this->logDt = date('_Y_m');
            $this->logFile = $logFile . $this->logDt . '.txt';
        }
    }

    public function __destruct()
    {
        $this->logPath = null;
        $this->logFile = null;
        $this->logDt = null;
    }

    /* TRACE, DEBUG, INFO, WARN, ERROR, FATAL */
    
    public function info($msg)
    {
        $this->write($msg, "INFO");
    }

    public function error($msg)
    {
        $this->write($msg, "ERROR");
    }

    public function warn($msg)
    {
        $this->write($msg, "WARN");
    }

    public function exception($msg)
    {
        $this->write($msg, "EXCEPTION");
    }

    private function write($msg, $pref)
    {
        if (empty($msg)) {
            return;
        }
        $log = $this->logPath . $this->logFile;
        $pref = " " . $pref . " ";
        if (!file_exists($log)) {
            file_put_contents($log, date('Y-m-d H:i:s') . $pref . $msg . "\n");
        } else {
            file_put_contents($log, date('Y-m-d H:i:s') . $pref . $msg . "\n", FILE_APPEND);
        }
    }
}