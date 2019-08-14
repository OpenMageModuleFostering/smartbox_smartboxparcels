<?php

class Smartbox_Smartboxparcels_Model_Smartboxcsod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'Smartbox_Smartboxcsod';
  protected $_formBlockType = 'Smartbox_Smartboxparcels/form_smartboxcsod';
  protected $_infoBlockType = 'Smartbox_Smartboxparcels/info_smartboxcsod';
 
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