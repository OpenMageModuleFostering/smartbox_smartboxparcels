<?php

class Smartbox_Smartboxparcels_Model_Smartboxcsod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'smartbox_smartboxcsod';
  protected $_formBlockType = 'smartbox_smartboxparcels/form_smartboxcsod';
  protected $_infoBlockType = 'smartbox_smartboxparcels/info_smartboxcsod';
 
  public function assignData($data)
  {
    $info = $this->getInfoInstance();
    return $this;
  }
 
  public function validate()
  {
    parent::validate();
    $info = $this->getInfoInstance();
    return $this;
  }

}