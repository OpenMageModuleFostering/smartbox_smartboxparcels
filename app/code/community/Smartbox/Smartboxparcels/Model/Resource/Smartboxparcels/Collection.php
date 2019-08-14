<?php

class Smartbox_Smartboxparcels_Model_Resource_Smartboxparcels_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartbox_smartboxparcels/smartboxparcels');
    }
}