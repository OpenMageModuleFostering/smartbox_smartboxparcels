<?php

class Smartbox_Smartboxparcels_Model_Terminal extends Varien_Object
{
    //magento formatted smartbox address
    public function getMagentoShippingAddress()
    {
        if($this->getData()['_id']) {

            // Grab the address from the stores data
            $addressData = $this->getData()['geocode']['formatted_address'];

            // Build up an array of address elements
            $address = array(
                'firstname' => 'Smartbox',
                'middlename' => '',
                'lastname' => $this->getData()['number'],
                'company' => '',
                'street' => $addressData,
                'city' => $this->getData()['city'],
                'region' => 'landmark:- '.$this->getData()['landmark'],
                'postcode' => $this->getData()['postalCode'],
                'country_id' => 'IN',
                'telephone' => 'info@smartbox.in',
                'same_as_billing' => 0,
                'save_in_address_book' => 0
            );

            // Some data seems to just have comma's set, so attempt to cleanse these
            foreach($address as $key => $addressItem) {
                if($addressItem == ',') {
                    $address[$key] = '';
                }
            }
            return $address;
        }

        return false;
    }
    
    //load terminal using terminalid
    public function load($id)
    {
        $terminalData = $this->getApi()->getTerminal($id, true);
        if($terminalData) {
            $this->addData($terminalData);
        }

        return $this;
    }

    //get object of terminals
    protected function getApi()
    {
        return Mage::getSingleton('smartbox_smartboxparcels/api_smartbox_terminals');
    }

}