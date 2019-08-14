<?php

class Smartbox_Smartboxparcels_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    // Carrier Code , Found in parent Class
    protected $_code = 'smartbox_smartboxparcels';

    // Method Key
    const METHOD_KEY = 'collection';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');
        $result->append($this->_getStandardRate());
        return $result;
    }

    // Returns shipping methods
    public function getAllowedMethods()
    {
        return array(
            self::METHOD_KEY => $this->getConfigData('name')
        );
    }

    //Get rate object
    protected function _getStandardRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        $rate->setCarrier($this->_code);
        $rate->setMethod('collection');
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethodTitle($this->getConfigData('name').' (FREE)');
        $rate->setPrice($this->getConfigData('price'));
        $rate->setCost(0);
        return $rate;
    }

}