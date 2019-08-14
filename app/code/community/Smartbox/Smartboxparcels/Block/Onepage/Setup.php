<?php

class Smartbox_Smartboxparcels_Block_Onepage_Setup extends Mage_Core_Block_Template
{

    // Return the value of the shipping method radio button
    protected function getSmartboxShippingMethodValue()
    {
        return Mage::helper('smartbox_smartboxparcels')->getShippingMethodCode();
    }

    // Return the configuration Google API key
    protected function getGoogleApiKey()
    {
        return Mage::getStoreConfig('carriers/smartbox_smartboxparcels/google_api_key');
    }

}