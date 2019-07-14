<?php

##eloom.licenca##

class Eloom_PayU_Block_Payment_Standard extends Mage_Payment_Block_Form {

  protected function _construct() {
    $this->setTemplate('eloom/payu/payment/standard.phtml');
    parent::_construct();
  }

  protected function _prepareLayout() {
    return parent::_prepareLayout();
  }

  public function listJsonErrors() {
    return Mage::helper('core')->jsonEncode(Eloom_PayU_Errors::listAll());
  }

  public function getPublicKey() {
    return Mage::helper('eloom_payu/config')->getPublicKey();
  }
  
  public function getAccountId() {
    return Mage::helper('eloom_payu/config')->getAccountId();
  }

}
