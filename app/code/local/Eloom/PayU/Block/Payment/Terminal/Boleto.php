<?php

##eloom.licenca##

class Eloom_PayU_Block_Payment_Terminal_Boleto extends Mage_Core_Block_Template {

	/**
	 * Instructions text
	 *
	 * @var string
	 */
	protected $_instructions;

  protected function _construct() {
    parent::_construct();
		$this->setTemplate('eloom/payu/payment/terminal/boleto.phtml');
  }

	public function getGrandTotal() {
		return Mage::getSingleton('eloom_payu/session')->getOrder()->getBaseGrandTotal();
	}

	/**
	 * Get instructions text from config
	 *
	 * @return string
	 */
	public function getInstructions() {
		if (is_null($this->_instructions)) {
			$this->_instructions = Mage::helper('eloom_payu/config')->getBilletInstructions();
		}
		return $this->_instructions;
	}
}