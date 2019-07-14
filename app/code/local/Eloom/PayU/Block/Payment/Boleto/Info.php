<?php

##eloom.licenca##

class Eloom_PayU_Block_Payment_Boleto_Info extends Mage_Payment_Block_Info {

	protected function _construct() {
		parent::_construct();
		$this->setTemplate('eloom/payu/payment/boleto/info.phtml');
	}

	/**
	 * Prepare credit card related payment info
	 *
	 * @param Varien_Object|array $transport
	 * @return Varien_Object
	 */
	protected function _prepareSpecificInformation($transport = null) {
		if (null !== $this->_paymentSpecificInformation) {
			return $this->_paymentSpecificInformation;
		}
		$helper = Mage::helper('eloom_payu');
		$transport = parent::_prepareSpecificInformation($transport);
		$info = $this->getInfo();
		$data = array();

		$status = $info->getCcStatus();
		if (!empty($status)) {
			$data[$helper->__('Status')] = $helper->__('Transaction.State.' . $status);
		}

		if ($lastTransactionId = $info->getLastTransId()) {
			$data[$helper->__('PayU Transaction ID')] = $lastTransactionId;
		}

		$additionalData = json_decode($info->getAdditionalData());
		if (!empty($additionalData)) {
			if (isset($additionalData->payuOrderId)) {
				$data[$helper->__('PayU Order ID')] = $additionalData->payuOrderId;
			}
			if (isset($additionalData->barCode)) {
				$data[$helper->__('PayU Bar Code')] = $additionalData->barCode;
			}
		}

		// errors
		$errors = json_decode($info->getCcDebugResponseBody());
		if (!empty($errors)) {
			foreach($errors as $error) {
				$data[$helper->__('Error')] = $error;
			}
		}

		return $transport->setData(array_merge($data, $transport->getData()));
	}

	public function getBilletLink() {
		$info = $this->getInfo();
		$lastTransId = $info->getLastTransId();
		$config = Mage::helper('eloom_payu/config');

		$status = $info->getOrder()->getStatus();
		if (!empty($lastTransId) && ($status == $config->getNewOrderStatus())) {
			$additionalData = json_decode($info->getAdditionalData());
			return $additionalData->paymentLink;
		}

		return null;
	}

	public function getSynchronizeButton() {
		$button = Mage::getSingleton('core/layout')->createBlock('eloom_payu/adminhtml_system_config_form_sinc_button');

		return $button->toHtml();
	}

	public function getLoggerButton() {
		$button = Mage::getSingleton('core/layout')->createBlock('eloom_payu/adminhtml_system_config_form_logger_button');

		return $button->toHtml();
	}

	public function getLoggerContainer() {
		$button = Mage::getSingleton('core/layout')->createBlock('eloom_payu/adminhtml_system_config_form_logger_textarea');

		return $button->toHtml();
	}
}
