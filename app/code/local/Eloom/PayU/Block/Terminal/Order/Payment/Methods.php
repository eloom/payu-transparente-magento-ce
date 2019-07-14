<?php

##eloom.licenca##

class Eloom_PayU_Block_Terminal_Order_Payment_Methods extends Mage_Core_Block_Template {

	/**
	 * Prepare children blocks
	 */
	protected function _prepareLayout() {
		/**
		 * Create child blocks for payment methods forms
		 */
		foreach($this->getMethods() as $method) {
			$this->setChild(
				'payment.method.' . $method->getCode(),
				$this->helper('payment')->getMethodFormBlock($method)
			);
		}

		return parent::_prepareLayout();
	}

	public function getOrder() {
		return Mage::getSingleton('eloom_payu/session')->getOrder();
	}

	/**
	 * Retrieve method model object
	 *
	 * @param   string $code
	 * @return  Mage_Payment_Model_Method_Abstract|false
	 */
	public function getMethodInstance($code) {
		$key = Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS . '/' . $code . '/model';
		$class = Mage::getStoreConfig($key);
		return Mage::getModel($class);
	}

	/**
	 * Retrieve code of current payment method
	 *
	 * @return mixed
	 */
	public function getSelectedMethodCode() {
		if ($method = $this->getOrder()->getPayment()->getMethod()) {
			return $method;
		}
		return false;
	}

	/**
	 * Payment method form html getter
	 * @param Mage_Payment_Model_Method_Abstract $method
	 */
	public function getPaymentMethodFormHtml(Mage_Payment_Model_Method_Abstract $method) {
		return $this->getChildHtml('payment.method.' . $method->getCode());
	}

	/**
	 * Return method title for payment selection page
	 *
	 * @param Mage_Payment_Model_Method_Abstract $method
	 */
	public function getMethodTitle(Mage_Payment_Model_Method_Abstract $method) {
		$form = $this->getChild('payment.method.' . $method->getCode());
		if ($form && $form->hasMethodTitle()) {
			return $form->getMethodTitle();
		}
		return $method->getTitle();
	}

	/**
	 * Payment method additional label part getter
	 * @param Mage_Payment_Model_Method_Abstract $method
	 */
	public function getMethodLabelAfterHtml(Mage_Payment_Model_Method_Abstract $method) {
		if ($form = $this->getChild('payment.method.' . $method->getCode())) {
			return $form->getMethodLabelAfterHtml();
		}
	}

	/**
	 * Retrieve available payment methods
	 *
	 * @return array
	 */
	public function getMethods() {
		$methods = $this->getData('methods');
		if ($methods === null) {
			$quote = null;
			$store = $this->getOrder()->getStoreId();

			$methods = array();
			foreach($this->helper('payment')->getStoreMethods($store, $quote) as $method) {
				$this->_assignMethod($method);
				$methods[] = $method;
			}
			$this->setData('methods', $methods);
		}
		return $methods;
	}

	/**
	 * Check and prepare payment method model
	 *
	 * Redeclare this method in child classes for declaring method info instance
	 *
	 * @param Mage_Payment_Model_Method_Abstract $method
	 * @return bool
	 */
	protected function _assignMethod($method) {
		$method->setInfoInstance($this->getOrder()->getPayment());
		return $this;
	}

	/**
	 * Get and sort available payment methods for specified or current store
	 *
	 * array structure:
	 *  $index => Varien_Simplexml_Element
	 *
	 * @param mixed $store
	 * @param Mage_Sales_Model_Quote $quote
	 * @return array
	 */
	public function getStoreMethods($store = null, $quote = null) {
		$res = array();
		$_storeMethods = array(Eloom_PayU_Model_Method_Terminal::PAYMENT_METHOD_TERMINAL_CODE);

		foreach($_storeMethods as $code) {
			$prefix = Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS . '/' . $code . '/';
			if (!$model = Mage::getStoreConfig($prefix . 'model', $store)) {
				continue;
			}
			$methodInstance = Mage::getModel($model);
			if (!$methodInstance) {
				continue;
			}
			$methodInstance->setStore($store);
			if (!$methodInstance->isAvailable($quote)) {
				/* if the payment method cannot be used at this time */
				continue;
			}
			$sortOrder = (int)$methodInstance->getConfigData('sort_order', $store);
			$methodInstance->setSortOrder($sortOrder);
			$res[] = $methodInstance;
		}

		usort($res, array($this, '_sortMethods'));
		return $res;
	}

	protected function _sortMethods($a, $b) {
		if (is_object($a)) {
			return (int)$a->sort_order < (int)$b->sort_order ? -1 : ((int)$a->sort_order > (int)$b->sort_order ? 1 : 0);
		}
		return 0;
	}

	public function isBoletoEnabled() {
		$isEnabled = false;
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
		foreach ($payments as $paymentCode => $paymentModel) {
			if($paymentCode == Eloom_PayU_Model_Method_Boleto::PAYMENT_METHOD_BOLETO_CODE) {
				$isEnabled = true;
				break;
			}
		}

		return $isEnabled;
	}

	public function isCartaoEnabled() {
		$isEnabled = false;
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
		foreach ($payments as $paymentCode => $paymentModel) {
			if($paymentCode == Eloom_PayU_Model_Method_Cc::PAYMENT_METHOD_CC_CODE) {
				$isEnabled = true;
				break;
			}
		}

		return $isEnabled;
	}
}
