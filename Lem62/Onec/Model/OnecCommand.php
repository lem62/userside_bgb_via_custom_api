<?php

namespace Lem62\Onec\Model;

use Lem62\Model\QueryJson;

class OnecCommand extends QueryJson
{

    protected function getJsonString() : string 
    {
        $result = "";
        if ($this->data === null) {
            return $result;
        }
        if (!is_array($this->data)) {
            return $result;
        }
        foreach ($this->data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result .= "&" . $key . "=" . urlencode($value);
        }
        return $result;
    }
}
