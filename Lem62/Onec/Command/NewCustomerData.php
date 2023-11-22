<?php

namespace Lem62\Onec\Command;

use Lem62\Onec\Model\OnecApiRequest;
use Lem62\Onec\Model\OnecCommand;


class NewCustomerData extends OnecCommand implements OnecApiRequest
{
    public function __construct() {}
    
    public function prepare($data)
    {
        if (!isset($data['type'])) {
            return;
        }
        $result = null;
        switch ($data['type']) {
            case 'customer':
                if (!isset($data['data'])) {
                    break;
                }
                $result['id'] = $this->returnKey($data['data']['id']);
                $result['name'] = $this->returnKey($data['data']['full_name']);
                $result['manager'] = $this->returnKey($data['data']['manager_id']);
                if (isset($data['data']['agreement']) && count($data['data']['agreement']) > 0) {
                    $result['contract_number'] = $this->returnKey($data['data']['agreement'][0]['number']);                    
                }
                if (isset($data['data']['phone']) && count($data['data']['phone']) > 0) {
                    $result['phone'] = "";
                    foreach ($data['data']['phone'] as $phone) {
                        $result['phone'] .= $phone['number'] . ",";
                    }
                }
                break;
            case 'equipment':
                $result = $data;
                break;
        }
        $this->data[$data['type']] = $result;
    }
}