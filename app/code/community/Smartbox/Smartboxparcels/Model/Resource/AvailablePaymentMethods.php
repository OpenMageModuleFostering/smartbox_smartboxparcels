<?php


class Smartbox_Smartboxparcels_Model_Resource_AvailablePaymentMethods
{
    public function toOptionArray()
    {
        $options =  array();

        foreach (Mage::app()->getStore()->getConfig('payment') as $code => $payment) {
        if($code != 'Smartbox_Smartboxcsod'){
            
            if(isset($payment['active'])){
                if ($payment['active'] && isset($payment['title'])) {
                    $options[] = array(
                        'value' => $code,
                        'label' => $payment['title']
                    );
                 }
                }
            }
        }
        $options[] = array('value' => '-1','label' => 'None');
        return $options;
    }
}