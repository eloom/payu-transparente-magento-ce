<?php

##eloom.licenca##

class Eloom_PayU_NotificationsController extends Mage_Core_Controller_Front_Action {

	private $logger;

	/**
	 * Initialize resource model
	 */
	protected function _construct() {
		$this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
		parent::_construct();
	}

	public function indexAction() {
		$data = $this->getRequest()->getPost();
		if ($data && isset($data['reference_sale']) && isset($data['state_pol'])) {
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

				$this->logger->info(sprintf("Processando notificação. Pedido [%s] - Status [%s].", $data['reference_sale'], $data['state_pol']));

				$order = Mage::getModel('sales/order')->loadByIncrementId($data['reference_sale']);
				Mage::getModel('eloom_payu/transaction_code')->synchronizeTransaction($accountId, $order->getPayment(), $order);
				$this->getResponse()->setHttpResponseCode(200);
			} catch(Exception $e) {
				$this->logger->fatal($e->getCode() . ' - ' . $e->getMessage());
				$this->getResponse()->setHttpResponseCode(500);
			}
		}
	}
}
