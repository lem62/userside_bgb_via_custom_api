<?php

namespace Lem62\Onec;

use Lem62\Onec\ApiOnec;
use Lem62\Onec\Model\OnecApiRequest;
use Lem62\Model\QueryResponse;
use Lem62\Log\LogFile;
use Lem62\Traits\OutputFormat;
use Lem62\Traits\CustomDotEnv;

class OnecFacade
{
    use OutputFormat, CustomDotEnv;

    private $api = null;
    private $log = null;
    private $command = null;
    private $response = null;
    private $debug = true;
    private $logPrefix = "OnecFacade";
    
    public function __construct(OnecApiRequest|Null $command, LogFile $log = null)
    {
        $this->command = $command;
        $this->log = $log;
        $this->api = new ApiOnec($this->dotEnvConfig('ONEC_API_URL'));
        $this->response = new QueryResponse();
    }
    
    public function prepare(array $data)
    {
        if ($this->command === null) {
            $this->log("prepare: Null command", false);
            return;
        }
        $this->command->prepare($data);
    }
    
    public function perform() : QueryResponse
    {
        if ($this->command === null) {
            $this->log("perform: Null command", false);
            $this->response->message = "Null command";
            return $this->response;
        }
        $this->log("perform: Request - " . $this->arrayToString($this->command, true), true);
        $result = $this->api->postJson($this->command->getJsonString());
        $this->log("perform: Response (onec) - " . $result, true);
        if (!$result) {
            $this->log("perform: Can not get response", false);
            $this->response->message = "Can not get response";
            return $this->response;
        }
        $result = $this->stringToJson($result);
        $this->response->result = property_exists($result, "success")
            ? $result->success
            : false;
        $this->response->message = property_exists($result, "response")
            ? $result->response
            : null;
        return $this->response;
    }

    private function log($msg, $info = true)
    {
        if ($this->log === null) {
            echo $msg . "\n\n";
            return;
        }
        if ($info && !$this->debug) {
            return;
        }
        if (is_array($msg)) {
            $msg = $this->arrayToString($msg);
        }
        if ($this->validateJson($msg)) { /* Userside API return some keys as numeric *facepalm* */
            $msg = json_encode($msg);
        }
        $msg = $this->logPrefix . " " . $msg;
        if ($info) {
            $this->log->info($msg);
        } else {
            $this->log->error($msg);
        }
    }
}
