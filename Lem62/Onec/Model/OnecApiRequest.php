<?php

namespace Lem62\Onec\Model;

interface OnecApiRequest
{
    public function prepare($data);
    public function getJsonString();
}