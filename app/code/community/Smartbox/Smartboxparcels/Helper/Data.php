<?php

class Smartbox_Smartboxparcels_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getShippingMethodCode()
    {
        $carrier = Mage::getSingleton('smartbox_smartboxparcels/carrier');
        return $carrier->getCarrierCode() . '_' . $carrier::METHOD_KEY;
    }
    public function getParcelStatus(){
        return array(
            'Created' => 'Created',
            'Prepared' => 'Prepared'
        );
    }

    public function getWarehouseId(){
        return Mage::getStoreConfig('carriers/smartbox_smartboxparcels/warehouse_id');
    }

}