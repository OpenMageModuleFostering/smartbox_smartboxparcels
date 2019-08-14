<?php

class Smartbox_Smartboxparcels_Model_Resource_Smartboxparcels extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {    
        $this->_init('Smartbox_Smartboxparcels/smartboxparcels', 'id');
        
    }
}