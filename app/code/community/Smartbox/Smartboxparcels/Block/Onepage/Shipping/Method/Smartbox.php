<?php

class Smartbox_Smartboxparcels_Block_Onepage_Shipping_Method_Smartbox extends Mage_Core_Block_template{

	protected function _construct(){
		parent::_construct();
		$this->setTemplate('Smartbox/Smartboxparcels/onepage/shipping/method/smartbox.phtml');
		return $this;
	}
	// Return Parent Block
	public function getParentBlock(){
		return $this->_parentBlock;
	}
	// Return If Smartbox Pickup
	public function getSmartboxPickup(){
		return Mage::registry('smartbox_pickup');
	}
	// Render HTML
	protected function _toHtml(){
		return parent::_toHtml();
	}
	// Returns About Infomation of Smartbox
	protected function getAboutInformation()
    {
        // Check that more information is set
        if($moreInformation = Mage::getStoreConfig('carriers/Smartbox_Smartboxparcels/more_information')) {

            // Strip out any nasty tags
            $filter = new Zend_Filter_StripTags(array(
                'allowTags' => array('a', 'p', 'br', 'hr', 'h2', 'h3', 'h4', 'strong', 'em')
            ));
            return $filter->filter($moreInformation);
        }

        return false;
    }
}