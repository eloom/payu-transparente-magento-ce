<?php

##eloom.licenca##

class Eloom_PayU_Model_Cron extends Mage_Core_Model_Abstract {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
    parent::_construct();
  }

  public function waitingPaymentTransaction() {
    $this->logger->info('Pedidos com Status [pending] - início');

    $sondas = Mage::getResourceModel('sales/order_payment_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('method', array('in' => array(Eloom_PayU_Model_Method_Cc::PAYMENT_METHOD_CC_CODE,
																												     Eloom_PayU_Model_Method_Boleto::PAYMENT_METHOD_BOLETO_CODE,
																														 Eloom_PayU_Model_Method_Terminal::PAYMENT_METHOD_TERMINAL_CODE)))
            ->addFieldToFilter('cc_status', Eloom_PayU_Enum_Transaction_State::PENDING);

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

    foreach ($sondas as $payment) {
      try {
        $order = Mage::getModel('sales/order')->load($payment->getParentId());
        Mage::getModel('eloom_payu/transaction_code')->synchronizeTransaction($accountId, $payment, $order);
      } catch (Exception $exc) {
        $this->logger->error(sprintf("Erro ao verificar Pagamento [%s] [%s]", $payment->getId(), $exc->getMessage()));
      }
    }
    $this->logger->info('Pedidos com Status [pending] - fim');
  }

  public function cancelOrderWithPaymentExpired() {
    $this->logger->info('Pagamento Expirado - Inicio');
    $config = Mage::helper('eloom_payu/config');

    if ($config->isBoletoCancel()) {
      $collection = Mage::getModel('sales/order')->getCollection();
      $collection->getSelect()->join(array('p' => $collection->getResource()->getTable('sales/order_payment')), 'p.parent_id = main_table.entity_id', array());

      $collection->addFieldToSelect('increment_id');
      $collection->addFieldToFilter('status', array('eq' => $config->getNewOrderStatus()));
      $collection->addFieldToFilter('method', Eloom_PayU_Model_Method_Boleto::PAYMENT_METHOD_BOLETO_CODE);
      $collection->addAttributeToFilter('p.boleto_cancellation', array('lt' => date('Y-m-d H:i:s', strtotime('now'))));

			if($this->logger->isDebugEnabled()) {
				$this->logger->debug('SQL: ' . $collection->getSelect());
			}

      if ($collection->getSize()) {
        foreach ($collection as $order) {
          try {
            $order = Mage::getModel('sales/order')->loadByIncrementId($order->getIncrementId());
            Mage::getModel('eloom_payu/order')->cancel($order, 'Prazo de pagamento expirado.');
          } catch (Exception $e) {
            $this->logger->error($e->getMessage());
          }
        }
      }
    }

    $this->logger->info('Pagamento Expirado - Fim');
  }

}
