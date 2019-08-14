<?php

class Smartbox_Smartboxparcels_Block_Adminhtml_Smartboxparcels_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function ____render(Varien_Object $row)
	{
	}

	public function render(Varien_Object $row)
	{
        
            if($row->getParcelId() == ''){
               $link_text = Mage::helper('smartbox_smartboxparcels')->__('Edit parcel');
            
            $url = $this->getUrl('*/*/edit/', array(
                    '_current'=>true,
                    'id' => $row->getId(),
                    Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->helper('core/url')->getEncodedUrl())
            );

            return "<a href='".$url."'>".$link_text."</a>";
            }
        
    }

}