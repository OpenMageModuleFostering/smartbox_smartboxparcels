<?php

class Smartbox_Smartboxparcels_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getShippingMethodCode()
    {
        $carrier = Mage::getSingleton('Smartbox_Smartboxparcels/carrier');
        return $carrier->getCarrierCode() . '_' . $carrier::METHOD_KEY;
    }
    public function getParcelStatus(){
        return array(
            'Created' => 'Created',
            'Prepared' => 'Prepared'
        );
    }

    public function getWarehouseId(){
        return Mage::getStoreConfig('carriers/Smartbox_Smartboxparcels/warehouse_id');
    }

}