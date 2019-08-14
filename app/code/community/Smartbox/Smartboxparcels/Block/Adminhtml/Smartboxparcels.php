<?php

class Smartbox_Smartboxparcels_Block_Adminhtml_Smartboxparcels extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
    	$this->_blockGroup = 'Smartbox_Smartboxparcels';
        $this->_controller = 'adminhtml_smartboxparcels';        
        $this->_headerText = Mage::helper('Smartbox_Smartboxparcels')->__('Parcels');
        parent::__construct();
        $this->_removeButton('add');
       
    }

}
