<?php

namespace Lem62\Onec\Command;

use Lem62\Onec\Model\OnecApiRequest;
use Lem62\Onec\Model\OnecCommand;


class NewCustomerData extends OnecCommand implements OnecApiRequest
{
    public function __construct() {}
    
    public function prepare($data)
    {

    }
}