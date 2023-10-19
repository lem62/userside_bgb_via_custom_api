<?php

namespace Lem62\Userside\Api\Action\Inventory;

use Lem62\Userside\Api\Model\ApiRequest;
use Lem62\Userside\Api\Model\UsersideAction;

/*
* Implementation: add_inventory
* See: https://wiki.userside.eu/API_inventory
*/

class AddInventory extends UsersideAction implements ApiRequest
{
    public function __construct()
    {
        $this->url = "&cat=inventory&action=add_inventory";
        $this->requiredParams = ['inventory_catalog_id', 'trader_id'];
        $this->data = [
            'inventory_catalog_id' => null, // ID наименования ТМЦ
            'trader_id' => null, // ID поставщика
            'amount' => null, // количество (по-умолчанию: 1)
            'cost' => null, // стоимость (по-умолчанию: 0)
            'storage_id' => null, // ID склада, на который выполнить приход (по-умолчанию: 1)
            'comment' => null, // заметки
            'sn' => null, // серийный номер
            'barcode' => null, // штрихкод
            'inventory_number' => null, // инвентарный номер
            'document_number' => null, // номер документа прихода
            'document_date' => null, // дата документа прихода
            'additional_data_ip' => null, // IP-адрес (для ТМЦ-оборудования)
            'additional_data_mac' => null, // MAC-адрес (для ТМЦ-оборудования)
        ];
    }
}