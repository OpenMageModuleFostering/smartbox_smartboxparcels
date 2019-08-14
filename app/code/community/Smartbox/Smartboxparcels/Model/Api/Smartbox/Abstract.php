<?php

abstract class Smartbox_Smartboxparcels_Model_Api_Smartbox_Abstract extends Mage_Core_Model_Abstract
{

    const API_KEY_XML_PATH = 'carriers/smartbox_smartboxparcels/api_key';
    const API_USERNAME_XML_PATH = 'carriers/smartbox_smartboxparcels/username';
    const API_PASSWORD_XML_PATH = 'carriers/smartbox_smartboxparcels/password';
    const API_ENVIRONMENT_XML_PATH = 'carriers/smartbox_smartboxparcels/environment';
    const API_STAGING_XML_PATH = 'carriers/smartbox_smartboxparcels/staging_api';
    const API_PRODUCTION_XML_PATH = 'carriers/smartbox_smartboxparcels/production_api';

    
    // make requests the API
    protected function buildRequest($call, $method = Varien_Http_Client::GET, $postData = false, $headers = false)
    {

        // Grab a new instance of HTTP Client
        $http = new Varien_Http_Client();

            if(Mage::getStoreConfig(self::API_ENVIRONMENT_XML_PATH) == Smartbox_Smartboxparcels_Model_System_Config_Environment::SMARTBOX_PRODUCTION){
                $http->setUri(Mage::getStoreConfig(self::API_PRODUCTION_XML_PATH) . $call);
                $api_key = array(
                    'X-Api-Key' => Mage::getStoreConfig(self::API_KEY_XML_PATH),
                    'X-Api-User'=> Mage::getStoreConfig(self::API_USERNAME_XML_PATH),
                    'X-Api-Passwd'=> Mage::getStoreConfig(self::API_PASSWORD_XML_PATH)
                    );
                $http->setHeaders($api_key);
            }
            else{
                $http->setUri(Mage::getStoreConfig(self::API_STAGING_XML_PATH) . $call);
                $api_key = array(
                    'X-Api-Key' => Mage::getStoreConfig(self::API_KEY_XML_PATH),
                    'X-Api-User'=> Mage::getStoreConfig(self::API_USERNAME_XML_PATH),
                    'X-Api-Passwd'=> Mage::getStoreConfig(self::API_PASSWORD_XML_PATH)
                    );
                $http->setHeaders($api_key);
            }

        // Set the method in, defaults to GET
        $http->setMethod($method);

        // Do we need to add in any post data?
        if($method == Varien_Http_Client::POST) {
            if (is_array($postData) && !empty($postData)) {

                // Add in our post data
                $http->setParameterPost($postData);

            } else if (is_string($postData)) {

                // Try and decode the string
                try {

                    // Attempt to decode the JSON
                    $decode = Mage::helper('core')->jsonDecode($postData, Zend_Json::TYPE_ARRAY);

                    // Verify it decoded into an array
                    if ($decode && is_array($decode)) {

                        // Include the post data as the raw data body
                        $http->setRawData($postData, 'application/json')->request(Varien_Http_Client::POST);
                    }

                } catch (Zend_Json_Exception $e) {
                    $this->_log($e);
                    return false;
                }

            }
        }

        // attempting to add any headers into the request
        if($headers && is_array($headers) && !empty($headers)) {

            // Add in our headers
            $http->setHeaders($headers);
        }

        // Return the HTTP body
        return $http;
    }

    // Request Making
    protected function makeRequest($http)
    {
        
        
        try {
            // Make the request to the server
            $response = $http->request();
        } catch (Exception $e) {
            $this->_log($e);
            return false;
        }
		
		
		
        // Check the status of the request
        if($response->getStatus() == 200) {

            // Retrieve the raw body, which should be JSON
			
            $body = $response->getBody();

            // Catch any errors
            try {

                // Attempt to decode the response
                $decodedBody = Mage::helper('core')->jsonDecode($body, Zend_Json::TYPE_ARRAY);

                // Return the decoded response
                return $decodedBody;

            } catch (Zend_Json_Exception $e) {

                $this->_log($e);
            } catch (Exception $e) {
                $this->_log($e);
            }

        } else {

            // If the request is anything but a 200 response make a log of it
            $this->_log($response->getStatus() . "\n" . $response->getRawBody());
        }

        return false;

    }

    //Function to log anything we're wanting to log
    protected function _log($data)
    {
        if($data instanceof Exception) {
            $data = $data->getMessage() . "\n" . $data->getTraceAsString();
        }
        Mage::log($data, null, 'smartbox_smartboxparcels.log', true);
    }

    
}