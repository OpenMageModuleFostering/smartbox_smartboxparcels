<?php

class Smartbox_Smartboxparcels_Model_Observer {
	/*** Step 1 Insert Ship to Smartbox Option **/
	public function insertShipToSmartbox(Varien_Event_Observer $observer){

		/*** Get the Block To be Inserted ***/
		$block = $observer->getBlock();

		/*** Confirm block type we are getting ***/
		if(Mage::getStoreConfigFlag('carriers/Smartbox_Smartboxparcels/active') && $block instanceof Mage_Checkout_Block_Onepage_Billing){

			$transport = $observer->getTransport();
			/*** Get clean html ***/
			$html = mb_convert_encoding($transport->getHtml(), 'HTML-ENTITIES', "UTF-8");
			$zendQueryObj = new Zend_Dom_Query($html);

			/*** Finding Ship to Diff. Address from html ***/
			$shipToDiff = $zendQueryObj->query('input[id="billing:use_for_shipping_no"]');
			/*** Check if we Found It! ***/
			if($shipToDiff->count()){
			/*** Get the Parent li Of Ship to Other Address ***/
			$li = $shipToDiff->current()->parentNode;
			
			/*** Get Partial html ***/
			$shipToSmartbox = Mage::app()->getLayout()->createBlock('Smartbox_Smartboxparcels/onepage_billing_option')->toHtml();
			

			 /*** Get Fragment ***/
            $fragment = $shipToDiff->getDocument()->createDocumentFragment();
            /*** Insert our Partial ***/
            $fragment->appendXML($shipToSmartbox);
			/*** Insert this fragment ***/
            $li->appendChild($fragment);
            /*** Set the HTML as our new content ***/
                $transport->setHtml($shipToDiff->getDocument()->saveHTML());

			}

		}
	}

	/*** Step 2 Allow User to skip Shipping Information ***/
	public function skipShippingInformation(Varien_Event_Observer $observer){

		$controllerAction = $observer->getControllerAction();
		/*** data from request ***/
	
        $billing = $controllerAction->getRequest()->getPost('billing', array());

        /*** If user has selected Smartbox Option ***/        
        if(isset($billing['use_for_shipping']) && $billing['use_for_shipping'] == 'smartbox') {

        /*** Do similar as in use for Shipping ***/
        $billing['use_for_shipping'] = 1;

         /*** Replace the Post parameter use_for_shipping from Smartbox to 1 ***/
            $controllerAction->getRequest()->setPost('billing', $billing);

        /*** Set value for Further Evaluation ***/
            Mage::register('smartbox_pickup', true);

        
        }

	}
	/*** Step 3 When user saves the Billing / Shipping Information To Move Towards Shipping Method
	Note:: This step will be called recursively so be aware of that,
	we need to use only once when we are in shipping Method section
	 ***/
	public function insertSmartboxMarkup(Varien_Event_Observer $observer){

		
		$block = $observer->getBlock();

        /*** Only Deal with shipping method available section ***/
        if($block instanceof Mage_Checkout_Block_Onepage_Shipping_Method_Available) {

            $transport = $observer->getTransport();

            /***  Add Our partial HTML ***/
            $html = (!Mage::registry('smartbox_pickup') ? $transport->getHtml() : '') . Mage::app()
                    ->getLayout()
                    ->createBlock('Smartbox_Smartboxparcels/onepage_shipping_method_smartbox')
                    ->setParentBlock($block)
                    ->toHtml();

            // Set the HTML back into the transport object
            $transport->setHtml($html);

        }

        return $this;

	}
	/*** Step 4 Event When the user saved the shipping method capture Smartbox Terminal ***/
	public function captureTerminalFromShippingMethod(Varien_Event_Observer $observer){
 
		$ShippingMethod = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
		
		 /* @var $request Zend_Controller_Request_Abstract */
        $request = $observer->getRequest();

        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getQuote();
        $quote_id = $quote->getId();
		$shippingAddress = $quote->getShippingAddress();
		$billingAddress = $quote->getBillingAddress();
        $grandTotal = $quote->getGrandTotal();


        // Has the use selected to use Smartbox ?
        if($quote->getShippingAddress()->getShippingMethod() == Mage::helper('Smartbox_Smartboxparcels')->getShippingMethodCode()) {

        	// Grab the Terminal ID
            $terminalId = $request->getParam('smartbox-terminal');
             // If it's not empty attempt to load the terminal
            if(!empty($terminalId)) {
              // Attempt to load the terminal
			  $terminal = Mage::getModel('Smartbox_Smartboxparcels/terminal')->load($terminalId);
            }

            if(empty($terminalId) || isset($terminal) && !(count($terminal->getData())>0) ) {

                // Build our result array
                $result = array(
                    'error' => -1,
                    'message' => Mage::helper('Smartbox_Smartboxparcels')->__('The Smartbox terminal you\'ve selected is no longer available, please try and locate your nearest Terminal again.')
                );

                // Send it over
                Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                Mage::app()->getResponse()->sendResponse();
                exit;
            }
            
            $receiver = array(
                              'first_name' => $billingAddress->getData('firstname'),
                              'middle_name' => $billingAddress->getData('middlename'),
                              'last_name' => $billingAddress->getData('lastname'),
                              'email' => $billingAddress->getData('email'),
            				  'phone' => $billingAddress->getData('telephone'));
            $parcel_detail  = array('EShop_trackingNumber' => '',
            						'receiver' => $receiver,
            						'size' => '',
                                    'warehouseId' => Mage::helper('Smartbox_Smartboxparcels')->getWarehouseId(),
            						'target_machine' => $terminalId,
                                    'grand_total' => $grandTotal );
            $terminal_data = $terminal->getData();

            $parcel_target_machine_details = array('id' => $terminalId,
                                                    'address' => array(
                                                            'building_number' => $terminal_data['address'],
                                                            'city' => $terminal_data['city']
                                                            )
                                                        );
            										

            $smartbox_order_details = array('parcel_target_machine_id' => $terminalId,
        									'receiver_phone' =>  $billingAddress->getData('telephone'),
        									'parcel_detail' => $parcel_detail,
        									'parcel_target_machine' => $terminalId,
        									'parcel_target_machine_detail' =>  $parcel_target_machine_details,
                                            'receiver_name' => $billingAddress->getData('email'));
            $data = array($quote_id => $smartbox_order_details);

          

            // Set the terminal ID within the session
            Mage::getSingleton('checkout/session')->setSmartboxTerminalId($terminalId)->setSmartboxTerminalName($terminal_data['address'])->setSmartboxTerminalData($data);

        }
        else {

            // remove data from the session
            Mage::getSingleton('checkout/session')->unsSmartboxTerminalId()->unsSmartboxTerminalName();
        }

        return $this;

	}
	public function changeToSmartboxTerminalAddress(Varien_Event_Observer $observer){
		$order = $observer->getOrder();

        $quote = $observer->getQuote();

        if($order->getShippingMethod() == Mage::helper('Smartbox_Smartboxparcels')->getShippingMethodCode()) {

            // Retrieve the terminal ID from the session
            $terminalId = Mage::getSingleton('checkout/session')->getSmartboxTerminalId();
           
            // Verify we've got a terminal ID
            if(!$terminalId) {
                Mage::throwException(Mage::helper('Smartbox_Smartboxparcels')->__('No terminal has been selected for collection from Smartbox, please try again.'));
            }

            // Load up the terminal
            /* @var $terminal Smartbox_Smartboxparcels_Model_Terminal */
            $terminal = Mage::getModel('Smartbox_Smartboxparcels/terminal')->load($terminalId);
      

            // Check the terminal can load
            if(!$terminal) {
                //Mage::throwException(Mage::helper('Smartbox_Smartboxparcels')->__('The Smartbox Terminal you\'ve selected is no longer available, please try and locate your nearest Terminal again.'));
                Mage::throwException(Mage::helper('Smartbox_Smartboxparcels')->__('Something went wrong, please try again later to choose Smartbox as a delivery option.'));

            }

            // Change the address shipping address
            $order->getShippingAddress()->addData($terminal->getMagentoShippingAddress())->save();           

            // This order can no longer be shipped partially as Smartbox has to receive it all at once
            $order->setCanShipPartially(0)->setCanShipPartiallyItem(0);

            // Update the quote
            $quote->getShippingAddress()->addData($terminal->getMagentoShippingAddress())->save();

        }
       
        return $this;
	}
	/**** Step 6 Insert Order Details in Smartbox Order Table ***/
	public function insertSmartboxOrdersInDb(Varien_Event_Observer $observer){

        $order = $observer->getOrder();
		$quote = $observer->getQuote();
		$quote_id = $quote->getId();

		if($order->getShippingMethod() != Mage::helper('Smartbox_Smartboxparcels')->getShippingMethodCode()) {
            return;
         }

        $smartboxparcels = Mage::getSingleton('checkout/session')->getSmartboxTerminalData();
        if(isset($smartboxparcels[$quote_id])){
			$data = $smartboxparcels[$quote_id];
			$data['order_id'] = $order->getId();
            $data['parcel_detail']['EShop_trackingNumber'] = $order->getIncrementId();
            $smartboxparcelsModel = Mage::getModel('Smartbox_Smartboxparcels/smartboxparcels');

             $smartboxparcelsModel->setOrderId($data['order_id']);
             $smartboxparcelsModel->setParcelDetail(json_encode($data['parcel_detail']));
 			 $smartboxparcelsModel->setParcelTargetMachineId($data['parcel_target_machine_id']);
             $smartboxparcelsModel->setParcelTargetMachineDetail(json_encode($data['parcel_target_machine_detail']));
             if(Mage::getStoreConfig('carriers/Smartbox_Smartboxparcels/environment') == 'staging'){
                $smartboxparcelsModel->setApiSource(Mage::getStoreConfig('carriers/Smartbox_Smartboxparcels/staging_api'));
             }
             else{
                $smartboxparcelsModel->setApiSource(Mage::getStoreConfig('carriers/Smartbox_Smartboxparcels/production_api'));
             }
             $smartboxparcelsModel->setApiKey(substr(Mage::getStoreConfig('carriers/Smartbox_Smartboxparcels/api_key') , 0, 5));
             $smartboxparcelsModel->save();
		}
		
	}
    /*** Step after the user has selected the payment Method ***/
    public function checkPaymentMethod(Varien_Event_Observer $observer){

        

         if($paymentMethod = Mage::app()->getRequest()->getParam('payment')['method'] ){

            $session  = Mage::getSingleton('checkout/session');
            $quote_id = $session->getQuoteId();
            $quote = Mage::getModel('sales/quote')->load($quote_id );
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

            $smartboxparcels = Mage::getSingleton('checkout/session')->getSmartboxTerminalData();
            if($shippingMethod == 'Smartbox_Smartboxparcels_collection'){

                if($paymentMethod == 'Smartbox_Smartboxcsod'){
                    if(isset($smartboxparcels[$quote_id])){
                        $smartboxparcels[$quote_id]['parcel_detail']['paymentType'] = 'COD';
                        $smartboxparcels[$quote_id]['parcel_detail']['paymentMethod'] = $paymentMethod;
                    }
                }
                else{
                    $smartboxparcels[$quote_id]['parcel_detail']['paymentType'] = 'NonCOD';
                    $smartboxparcels[$quote_id]['parcel_detail']['paymentMethod'] = $paymentMethod;
                }
            }
            
            Mage::getSingleton('checkout/session')->setSmartboxTerminalData($smartboxparcels);
         };
    }
    /*** Filter out any payment other method ****/
    public function filterpaymentmethod(Varien_Event_Observer $observer){
        //get event


        $event      = $observer->getEvent();
        if($quote = $event->getQuote()){

        if($shipping_method = $quote->getShippingAddress()->getShippingMethod()){
            if($shipping_method == 'Smartbox_Smartboxparcels_collection'){

            if(Mage::getStoreConfig('payment/Smartbox_Smartboxcsod/disallowspecificpaymentmethods')){
                //get result
                $result  = $event->getResult();
                //get payment method 
                $method = $observer->getEvent()->getMethodInstance();
                $shippingMethodCode = $method->getCode();

                    $disallowedShippingMethods = Mage::getStoreConfig('payment/Smartbox_Smartboxcsod/disallowedpaymentmethods');

                    if (in_array($shippingMethodCode, explode(',', $disallowedShippingMethods))) {
                         $result->isAvailable = false;
                    }
                }

        }else{
            //get result
                $result  = $event->getResult();
                 //get payment method
                $method = $observer->getEvent()->getMethodInstance();
                $shippingMethodCode = $method->getCode();

            if($shippingMethodCode == 'Smartbox_Smartboxcsod'){
                $result->isAvailable = false;
                }
            }

        }

        }

        }
}