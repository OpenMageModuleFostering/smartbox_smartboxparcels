<?php

class Smartbox_Smartboxparcels_IndexController extends Mage_Core_Controller_Front_Action
{

    public function getClosestLongLatAction()
    {
        // Grab the long and lat from the request
        $long = $this->getRequest()->getParam('long');
        $lat = $this->getRequest()->getParam('lat');

        // Verify they're both set and not false
        if(!$long || !$lat) {
            return $this->returnAsJson(array('error' => $this->__('You must specify both latitude and longitude to use this action.')));
        }

        // Load up the layout
        $this->loadLayout();

        // Return a formatted JSON response
        return $this->returnAsJson(array(
            'success' => 'true',
            'html' => $this->getLayout()->getOutput()
        ));
    }

    protected function returnAsJson($json)
    {
        // Set the response
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($json));
        return false;
    }

}