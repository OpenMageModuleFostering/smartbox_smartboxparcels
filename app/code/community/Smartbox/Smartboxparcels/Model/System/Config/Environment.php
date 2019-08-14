<?php

class Smartbox_Smartboxparcels_Model_System_Config_Environment
{

    const SMARTBOX_STAGING = 'staging';
    const SMARTBOX_PRODUCTION = 'production';

    //Return the environment options as an array
    public function toOptionArray()
    {
        return array(
            array('value'=> self::SMARTBOX_STAGING, 'label'=> Mage::helper('smartbox_smartboxparcels')->__('Staging')),
            array('value'=> self::SMARTBOX_PRODUCTION, 'label'=>Mage::helper('smartbox_smartboxparcels')->__('Production')),
        );
    }
}
