<?php

##eloom.licenca##

class Eloom_PayU_Block_Terminal_Order_Info extends Mage_Core_Block_Template {

  protected function _construct() {
    parent::_construct();
		$this->setTemplate('eloom/payu/terminal/order/info.phtml');
  }

  public function getOrder() {
  	return Mage::getSingleton('eloom_payu/session')->getOrder();
	}
}