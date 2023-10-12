<?php

namespace Lem62\Bgb\Model;

interface ApiRequest {
    public function getUrl();
    public function validate();
}

