<?php

namespace Lem62\Traits;

trait UsEquipment
{
    public function validateOnuSerial($onuSerial)
    {
        return preg_match("/^(HWTC|HWFW|HWHW|GONT)([0-9A-Z]{8})$/", $onuSerial);
    }
}