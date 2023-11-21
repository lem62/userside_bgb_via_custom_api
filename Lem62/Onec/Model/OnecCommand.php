<?php

namespace Lem62\Onec\Model;

use Lem62\Model\QueryJson;

class OnecCommand extends QueryJson
{

    public function getJsonString() : string 
    {
        return json_encode($this->data);
    }

    protected function returnKey($value) 
    {
        if (!isset($value)) {
            return null;
        }
        return $value;
    }
}
