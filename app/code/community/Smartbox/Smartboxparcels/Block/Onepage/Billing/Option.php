<?php

class Smartbox_Smartboxparcels_Block_Onepage_Billing_Option extends Mage_Core_Block_Template{
	
	protected function _construct()
    {
        parent::_construct();
        // Set our template
        $this->setTemplate('smartbox/smartboxparcels/onepage/billing/option.phtml');
        // Anykind of Chaning
        return $this;
    }

    // Returns About Infomation of Smartbox
    protected function getAboutInformation()
    {
        // Check that more information is set
        if($moreInformation = Mage::getStoreConfig('carriers/smartbox_smartboxparcels/more_information')) {

            // Strip out any nasty tags
            $filter = new Zend_Filter_StripTags(array(
                'allowTags' => array('a', 'p', 'br', 'hr', 'h2', 'h3', 'h4', 'strong', 'em')
            ));
            return $filter->filter($moreInformation);
        }

        return false;
    }
    

}