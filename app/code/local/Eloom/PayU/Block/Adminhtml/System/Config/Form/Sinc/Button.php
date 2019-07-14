<?php

##eloom.licenca##

class Eloom_PayU_Block_Adminhtml_System_Config_Form_Sinc_Button extends Mage_Adminhtml_Block_System_Config_Form_Field {

	private $order = null;

	protected function _construct() {
		parent::_construct();
		$this->order = Mage::registry('current_order');
		$this->setTemplate('eloom/payu/payment/sinc/button.phtml');
	}

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		return $this->_toHtml();
	}

	public function getSynchronizeUrl() {
		if($this->order) {
			return Mage::helper('adminhtml')->getUrl('eloom_payu/adminhtml_index/sinc', array('order_id' => $this->order->getId()));
		}
	}

	public function getButtonHtml() {
		if(!$this->order) {
			return '';
		}
		$button = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setData(array(
				'id' => 'payu_sinc',
				'label' => Mage::helper('eloom_payu')->__('Synchronize'),
				'onclick' => 'javascript:payuSinc(); return false;'
			));
		return $button->toHtml();
	}
}