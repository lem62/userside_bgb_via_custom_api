<?php

namespace Lem62\Bgb\Userside;

use Lem62\Bgb\ApiBgb;
use Lem62\Bgb\Model\ApiRequest;
use Lem62\Bgb\Userside\UsersideResponse;
use Lem62\Log\LogFile;
use Lem62\Traits\OutputFormat;
use Lem62\Traits\CustomDotEnv;

class UsersideFacade
{
    use OutputFormat, CustomDotEnv;

    private $api = null;
    private $log = null;
    private $command = null;
    private $response = null;
    
    public function __construct(ApiRequest|Null $command, LogFile $log)
    {
        $this->command = $command;
        $this->log = $log;
        $this->api = new ApiBgb($this->dotEnvConfig('BGBILLING_API_URL'));
        $this->response = new UsersideResponse();
    }
    
    public function prepare($data)
    {
        if (!is_array($data)) {
            $this->log->error("Request is not array");
            return;
        }
        if ($this->command === null) {
            $this->log->error("Null command");
            return;
        }
        foreach ($data as $key => $value) {
            $this->command->$key = $value;
        }
    }
    
    public function validate() : UsersideResponse
    {
        $result = $this->command->validate();
        $this->log->info("Validate - " . $result);
        return $result;
    }
    
    public function perform() : UsersideResponse
    {
        $this->log->info("Request - " . $this->command->getUrl());
        $result = $this->api->get($this->command->getUrl());
        $this->log->info("Response (bgb) - " . $this->arrayToString($result));
        /*
        * Не получили ответ от биллинга
        */
        if (!$result) {
            $this->response->message = "Empty response";
            return $this->response;
        }
        $xml = $this->getXml($result);
        /*
        * Не удалось получить объект SimpleXML
        */
        if ($xml instanceof UsersideResponse) {
            return $this->response;
        }
        /*
        * Не удалось получить объект SimpleXML
        */
        if (!$xml) {
            $this->response->message = "Can not parse response";
            return $this->response;
        }
        /*
        * Смотрим не свалилися ли биллинг во время обработки запроса
        */
        if ($xml->attributes()->status == "error") {
            $this->response->message = "Uncontrolled exception";
            return $this->response;
        }

        /*
        * Смотрим получен ли результат обработки запроса
        */
        if (!$xml->response) {
            $this->response->message = "Unset response";
            return $this->response;
        }
        $this->prepareResponse($xml->response);
        return $this->response;
    }

    private function getXml($response)
    {
        try {
            $xml = simplexml_load_string($response);
        } catch (\Exception $e) {
            $this->response->message = "See exception (getXml)";
            $this->response->exception = $e->getMessage();
            return $this->response;
        }
        return $xml;
    }

    private function prepareResponse($xml)
    {
        foreach ((array)$xml as $key => $value) {
            /*
            * БГБ при успехе возвращает 1, и 0 при неудаче
            */
            if ($key == "result") {
                $value = ($value === "1") ? true : false;
            }
            $this->response->$key = $value;
        }
    }
}
