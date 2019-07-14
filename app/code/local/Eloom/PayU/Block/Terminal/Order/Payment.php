<?php

##eloom.licenca##

class Eloom_PayU_Block_Terminal_Order_Payment extends Mage_Core_Block_Template {

	/**
	 * Getter
	 *
	 * @return float
	 */
	public function getBaseGrandTotal() {
		return (float)Mage::getSingleton('eloom_payu/session')->getOrder()->getBaseGrandTotal();
	}
}
