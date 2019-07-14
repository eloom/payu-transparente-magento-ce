<?php

##eloom.licenca##

class Eloom_PayU_Block_Adminhtml_System_Config_Form_Logger_Textarea extends Mage_Adminhtml_Block_System_Config_Form_Field {

	protected function _construct() {
		parent::_construct();
		$this->setTemplate('eloom/payu/payment/logger/textarea.phtml');
	}

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		return $this->_toHtml();
	}
}