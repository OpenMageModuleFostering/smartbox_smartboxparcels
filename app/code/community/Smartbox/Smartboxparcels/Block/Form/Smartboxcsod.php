<?php
class Smartbox_Smartboxparcels_Block_Form_Smartboxcsod extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('smartbox/smartboxparcels/form/smartboxcsod.phtml');
  }
}