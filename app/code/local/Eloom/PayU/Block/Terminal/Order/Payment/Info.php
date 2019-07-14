<?php

##eloom.licenca##

class Eloom_PayU_Block_Terminal_Order_Payment_Info extends Mage_Payment_Block_Info_Container {
	/**
	 * Retrieve payment info model
	 *
	 * @return Mage_Payment_Model_Info
	 */
	public function getPaymentInfo() {
		$info = Mage::getSingleton('eloom_payu/session')->getOrder()->getPayment();
		if ($info->getMethod()) {
			return $info;
		}
		return false;
	}

	protected function _toHtml() {
		$html = '';
		if ($block = $this->getChild($this->_getInfoBlockName())) {
			$html = $block->toHtml();
		}
		return $html;
	}
}
