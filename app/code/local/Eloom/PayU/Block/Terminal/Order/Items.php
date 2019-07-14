<?php

##eloom.licenca##

class Eloom_PayU_Block_Terminal_Order_Items extends Mage_Sales_Block_Items_Abstract {
	/**
	 * Retrieve current order model instance
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder() {
		return Mage::getSingleton('eloom_payu/session')->getOrder();
	}
}
