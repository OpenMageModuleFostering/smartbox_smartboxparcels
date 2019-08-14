<?php

class Smartbox_Smartboxparcels_Block_Onepage_Shipping_Method_Smartbox_Table extends Mage_Core_Block_Template
{
    //Return the terminal API
    private function _getTerminalApi()
    {
        return Mage::getSingleton('Smartbox_Smartboxparcels/api_smartbox_terminals');
    }

    //Return the terminals
    protected function getTerminals()
    {
        return $this->_getTerminalApi()->getClosestTerminals($this->getRequest()->getParam('lat'), $this->getRequest()->getParam('long'));
    }


}