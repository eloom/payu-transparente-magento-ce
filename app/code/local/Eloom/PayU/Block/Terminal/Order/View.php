<?php

##eloom.licenca##

class Eloom_PayU_Block_Terminal_Order_View extends Mage_Core_Block_Template {

	public function getPaymentInfoHtml() {
		return $this->getChildHtml('payment_info');
	}

	protected function _construct() {
		parent::_construct();
		$this->setTemplate('eloom/payu/terminal/order/view.phtml');
	}

	protected function _prepareLayout() {
		if ($headBlock = $this->getLayout()->getBlock('head')) {
			$headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
		}
		$this->setChild(
			'payment_info',
			$this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
		);
	}

	/**
	 * Retrieve current order model instance
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder() {
		return Mage::getSingleton('eloom_payu/session')->getOrder();
	}

}
