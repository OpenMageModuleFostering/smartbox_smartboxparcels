<?php

class Smartbox_Smartboxparcels_Adminhtml_SmartboxparcelsController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('sales/smartboxparcels')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('Smartbox_Smartboxparcels')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {

        $this->_initAction()
            ->renderLayout();
    }
    

    public function massCreateMultipleParcelsAction(){
       
        $parcelsIds = $this->getRequest()->getPost('parcels_ids', array());

        $countParcel = 0;
        $countNonParcel = 0;

        $parcels = array();
        
        foreach ($parcelsIds as $id) {
            $parcelCollection = Mage::getModel('Smartbox_Smartboxparcels/smartboxparcels')->load($id);
            $orderCollection = Mage::getResourceModel('sales/order_grid_collection')
                ->addFieldToFilter('entity_id', $parcelCollection->getOrderId())
                ->getFirstItem();

            $parcelDetailDb = json_decode($parcelCollection->getParcelDetail());

            if($parcelDetailDb){

            $terminal = Mage::getModel('Smartbox_Smartboxparcels/store')->load($parcelDetailDb->target_machine);
            $terminal_id = $terminal->getData()[0]['_id'];
            
            $receiver_name = $parcelDetailDb->receiver->first_name;
            if($parcelDetailDb->receiver->middle_name){
                $receiver_name .= ' '.$parcelDetailDb->receiver->middle_name;
            }
            if($parcelDetailDb->receiver->last_name){
                $receiver_name .= ' '.$parcelDetailDb->receiver->last_name;
            }
            

            $body = array('destination_terminal_id' => $terminal_id,
                          'receiver_name' => $receiver_name,
                          'receiver_phone' => $parcelDetailDb->receiver->phone,
                          'receiverEmail' => $parcelDetailDb->receiver->email,
                          'size' => $parcelDetailDb->size,
                          'warehouseId' => $parcelDetailDb->warehouseId,
                          'price' => $parcelDetailDb->grand_total,
                          'paymentType' => $parcelDetailDb->paymentType,
                          'paymentMethod' => $parcelDetailDb->paymentMethod,
                          'EShop_trackingNumber' => $parcelDetailDb->EShop_trackingNumber);
            
            $stores = Mage::getSingleton('Smartbox_Smartboxparcels/api_smartbox_terminals');
            $result = $stores->createParcel($body);
            if(!empty($result) && is_array($result)){
                //get tracking number from smartbox
                $parcel_details = $stores->getParcelDetails($parcelDetailDb->EShop_trackingNumber);
                
                if($parcel_details && is_array($parcel_details)){
                    $tracking_number = $parcel_details['trackingNumber'];
                }
                else{
                    $tracking_number = 'failed to fetch';
                }
                $fields = array(
                    'parcel_id' => $result['_id'],
                    'parcel_status' => 'Created',
                    'tracking_number' => $tracking_number,
                    'parcel_detail' => $result['message'],
                    'parcel_target_machine_id' => $terminal_id,
                    'parcel_target_machine_detail' => ''
                );
                $parcelCollection->setParcelId($fields['parcel_id']);
                $parcelCollection->setParcelStatus($fields['parcel_status']);
                $parcelCollection->setTrackingNumber($fields['tracking_number']);
                $parcelCollection->setParcelDetail($fields['parcel_detail']);
                $parcelCollection->setParcelTargetMachineId($fields['parcel_target_machine_id']);
                $parcelCollection->setParcelTargetMachineDetail($fields['parcel_target_machine_detail']);
                $parcelCollection->save();

                $countParcel++;
            }
            else{
                $countNonParcel++;
            }

            }
            else{
                $countNonParcel++;
            }

        }

        if ($countNonParcel) {
            if ($countNonParcel) {
                $this->_getSession()->addError($this->__('%s parcel(s) cannot be created', $countNonParcel));
            } else {
                $this->_getSession()->addError($this->__('The parcel(s) cannot be created'));
            }
        }
        if ($countParcel) {
            $this->_getSession()->addSuccess($this->__('%s parcel(s) have been created.', $countParcel));
        }

        $this->_redirect('*/*/');

    }

    public function editAction(){
        $id = $this->getRequest()->getParam('id');
        $parcel = Mage::getModel('Smartbox_Smartboxparcels/smartboxparcels')->load($id);
        

        if ($parcel->getId() || $id == 0) {
            $parcelTargetMachineDetailDb = json_decode($parcel->getParcelTargetMachineDetail());
            $parcelDetailDb = json_decode($parcel->getParcelDetail());

        
            
            $smartboxparcelsData = array(
                'id' => $parcel->getId(),
                'parcel_id' => $parcel->getId(),
                'parcel_cod_amount' => @$parcelDetailDb->cod_amount,
                'parcel_EShop_trackingNumber' => @$parcelDetailDb->EShop_trackingNumber,
                'parcel_receiver_first_name' => @$parcelDetailDb->receiver->first_name,
                'parcel_receiver_middle_name' => @$parcelDetailDb->receiver->middle_name,
                'parcel_receiver_last_name' => @$parcelDetailDb->receiver->last_name,
                'parcel_receiver_email' => @$parcelDetailDb->receiver->email,
                'parcel_receiver_phone' => @$parcelDetailDb->receiver->phone,
                'parcel_size' => @$parcelDetailDb->size,
                'parcel_tmp_id' => @$parcelDetailDb->tmp_id,
                'parcel_target_machine_id' => @$parcelDetailDb->target_machine,
                'parcel_grand_total' => @$parcelDetailDb->grand_total,
                'parcel_warehouseId' => @$parcelDetailDb->warehouseId,
                'parcel_paymentType' => @$parcelDetailDb->paymentType,
                'parcel_paymentMethod' => @$parcelDetailDb->paymentMethod,
                'parcel_status' => $parcel->getParcelStatus(),
                
                
            );
            Mage::register('smartboxparcelsData', $smartboxparcelsData);

            $this->_initAction()
                ->renderLayout();

        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('<module>')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $postData = $this->getRequest()->getPost();
                $id = $postData['id'];

                $parcel = Mage::getModel('Smartbox_Smartboxparcels/smartboxparcels')->load($postData['id']);
                
                $parcelTargetMachineDetailDb = json_decode($parcel->getParcelTargetMachineDetail());
                $parcelDetailDb = json_decode($parcel->getParcelDetail());

                 if($parcel->getParcelId() == ''){
                   
                   $parcelDetail = $parcelDetailDb;
                    $parcelDetail = array(
                        'EShop_trackingNumber' => $postData['parcel_EShop_trackingNumber'],
                        'receiver' => array(
                            'first_name' => $postData['parcel_receiver_first_name'],
                            'middle_name' => $postData['parcel_receiver_middle_name'],
                            'last_name' => $postData['parcel_receiver_last_name'],
                            'email' => $postData['parcel_receiver_email'],
                            'phone' => $postData['parcel_receiver_phone']
                        ),
                        'size' => $postData['parcel_size'],
                        'tmp_id' => $postData['parcel_tmp_id'],
                        'target_machine' => $postData['parcel_target_machine_id'],
                        'grand_total' => $postData['parcel_grand_total'],
                        'paymentType' => $postData['parcel_paymentType'],
                        'paymentMethod' => $postData['parcel_paymentMethod'],
                        'warehouseId' => $postData['parcel_warehouseId']
                    );
                   
                        $parcel->setParcelDetail(json_encode($parcelDetail));
                        $parcel->setParcelTargetMachineId($postData['parcel_target_machine_id']);
                        $parcel->setParcelTargetMachineDetail(json_encode($parcelTargetMachineDetailDb));
                        $parcel->save();
                 }
                 else{
                     throw new Exception("Parcel already created!");
                     }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Parcel Updated successfully !'));
                Mage::getSingleton('adminhtml/session')->setSmartboxparcelsData(false);
                
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setSmartboxparcelsData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function gridAction()
    {

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('inpostparcels/adminhtml_inpostparcels_grid')->toHtml()
        );
    }
}