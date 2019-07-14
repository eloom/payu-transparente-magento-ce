<?php

##eloom.licenca##

class Eloom_PayU_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action {

	public function loggerAction() {
		$orderId = $this->getRequest()->getParam('order_id');

		$result = array();
		if ($orderId) {
			try {
				$isTest = false;
				$merchantId = null;
				$apiKey = null;
				$apiLogin = null;
				$accountId = null;

				$config = Mage::helper('eloom_payu/config');
				if ($config->isInProduction()) {
					$merchantId = $config->getMerchantId();
					$apiKey = $config->getApiKey();
					$apiLogin = $config->getLoginApi();
					$accountId = $config->getAccountId();
				} else {
					Eloom_PayU_Api_Environment::setReportsCustomUrl("https://sandbox.api.payulatam.com/reports-api/4.0/service.cgi");
					$merchantId = Eloom_PayU_Api_Environment::MERCHANT_ID_TEST;
					$apiKey = Eloom_PayU_Api_Environment::API_KEY_TEST;
					$apiLogin = Eloom_PayU_Api_Environment::API_LOGIN_TEST;
					$accountId = Eloom_PayU_Api_Environment::ACCOUNT_ID_TEST;
					$isTest = true;
				}

				Eloom_PayU_PayU::$isTest = false;
				Eloom_PayU_PayU::$language = Eloom_PayU_Api_SupportedLanguages::PT;
				Eloom_PayU_PayU::$merchantId = $merchantId;
				Eloom_PayU_PayU::$apiKey = $apiKey;
				Eloom_PayU_PayU::$apiLogin = $apiLogin;

				$order = Mage::getModel('sales/order')->load($orderId);
				$parameters = array(
					Eloom_PayU_Util_PayUParameters::ACCOUNT_ID => $accountId,
					Eloom_PayU_Util_PayUParameters::REFERENCE_CODE => $order->getIncrementId()
				);
				$result = Eloom_PayU_PayUReports::getOrderDetailByReferenceCode($parameters);

			} catch(Exception $e) {
				$result = $e->getMessage();
			}

			$this->getResponse()->setHeader('Content-type', 'application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}

	/**
	 * Sincroniza o pedido com a PayU
	 */
	public function sincAction() {
		$orderId = $this->getRequest()->getParam('order_id');
		$result = null;

		if ($orderId) {
			try {
				$isTest = false;
				$merchantId = null;
				$apiKey = null;
				$apiLogin = null;
				$accountId = null;

				$config = Mage::helper('eloom_payu/config');
				if ($config->isInProduction()) {
					$merchantId = $config->getMerchantId();
					$apiKey = $config->getApiKey();
					$apiLogin = $config->getLoginApi();
					$accountId = $config->getAccountId();
				} else {
					Eloom_PayU_Api_Environment::setReportsCustomUrl("https://sandbox.api.payulatam.com/reports-api/4.0/service.cgi");
					$merchantId = Eloom_PayU_Api_Environment::MERCHANT_ID_TEST;
					$apiKey = Eloom_PayU_Api_Environment::API_KEY_TEST;
					$apiLogin = Eloom_PayU_Api_Environment::API_LOGIN_TEST;
					$accountId = Eloom_PayU_Api_Environment::ACCOUNT_ID_TEST;
					$isTest = true;
				}

				Eloom_PayU_PayU::$isTest = false;
				Eloom_PayU_PayU::$language = Eloom_PayU_Api_SupportedLanguages::PT;
				Eloom_PayU_PayU::$merchantId = $merchantId;
				Eloom_PayU_PayU::$apiKey = $apiKey;
				Eloom_PayU_PayU::$apiLogin = $apiLogin;

				$order = Mage::getModel('sales/order')->load($orderId);
				Mage::getModel('eloom_payu/transaction_code')->synchronizeTransaction($accountId, $order->getPayment(), $order);

				$result = $this->__('The Payment has been updated.');
			} catch(Exception $e) {
				$result = $this->__('The Payment has not been updated.');
			}

			$this->getResponse()->setHeader('Content-type', 'application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	}
}
