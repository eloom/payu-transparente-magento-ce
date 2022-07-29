<?php

##eloom.licenca##

class Eloom_PayU_Model_Pix_Request extends Mage_Core_Model_Abstract {

  private $logger;

  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  public function generatePaymentRequest(Mage_Sales_Model_Order $order) {
    $billingAddress = $order->getBillingAddress();
    $shippingAddress = null;
    if ($order->getIsVirtual()) {
      $shippingAddress = $order->getBillingAddress();
    } else {
      $shippingAddress = $order->getShippingAddress();
    }

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
      Eloom_PayU_Api_Environment::setPaymentsCustomUrl("https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi");
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

    /* ------- Sender ------- */
    $senderName = trim($order->getCustomerFirstname()) . ' ' . ($order->getCustomerMiddlename() != null ? trim($order->getCustomerMiddlename()) . ' ' : '') . trim($order->getCustomerLastname());
    $taxVat = preg_replace('/\D/', '', $order->getCustomerTaxvat());
    $telephone = $billingAddress->getTelephone();
    $zipCode = $shippingAddress->getPostcode();
    
    /* ------- Expiração Boleto */
    $expiration = new DateTime('now +' . $config->getPixExpiration() . ' hour');

    $parameters = array(
        Eloom_PayU_Util_PayUParameters::PARTNER_ID => '668978',
        Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD => Eloom_PayU_Api_PaymentMethods::PIX,
        Eloom_PayU_Util_PayUParameters::EXPIRATION_DATE => $expiration->format('Y-m-d\TH:i:s'),
        Eloom_PayU_Util_PayUParameters::ACCOUNT_ID => $accountId,
        Eloom_PayU_Util_PayUParameters::USER_AGENT => Mage::helper('core/http')->getHttpUserAgent(),
        Eloom_PayU_Util_PayUParameters::REFERENCE_CODE => $order->getIncrementId(),
        Eloom_PayU_Util_PayUParameters::DESCRIPTION => sprintf("Referente ao pedido %s", $order->getIncrementId()),
        Eloom_PayU_Util_PayUParameters::COUNTRY => Eloom_PayU_Api_PayUCountries::BR,
        Eloom_PayU_Util_PayUParameters::IP_ADDRESS => $order->getRemoteIp(),
        // -- Valores --
        Eloom_PayU_Util_PayUParameters::VALUE => number_format($order->getBaseGrandTotal(), 2, '.', ''),
        //Coloque aqui a moeda.
        Eloom_PayU_Util_PayUParameters::CURRENCY => 'BRL',
        Eloom_PayU_Util_PayUParameters::NOTIFY_URL => Mage::getUrl('eloompayu/notifications', array('_secure' => true)),
        // -- Comprador 
        Eloom_PayU_Util_PayUParameters::BUYER_NAME => substr($senderName, 0, 150),
        Eloom_PayU_Util_PayUParameters::BUYER_EMAIL => $order->getCustomerEmail(),
        Eloom_PayU_Util_PayUParameters::BUYER_CONTACT_PHONE => $telephone,
        Eloom_PayU_Util_PayUParameters::BUYER_DNI_TYPE => 'CPF',
        Eloom_PayU_Util_PayUParameters::BUYER_DNI => $taxVat,
        Eloom_PayU_Util_PayUParameters::BUYER_STREET => substr($shippingAddress->getStreet(1), 0, 100),
        Eloom_PayU_Util_PayUParameters::BUYER_STREET_2 => substr($shippingAddress->getStreet(2), 0, 100),
        Eloom_PayU_Util_PayUParameters::BUYER_CITY => $shippingAddress->getCity(),
        Eloom_PayU_Util_PayUParameters::BUYER_STATE => $shippingAddress->getRegionCode(),
        Eloom_PayU_Util_PayUParameters::BUYER_COUNTRY => Eloom_PayU_Api_PayUCountries::BR,
        Eloom_PayU_Util_PayUParameters::BUYER_POSTAL_CODE => $zipCode,
        Eloom_PayU_Util_PayUParameters::BUYER_PHONE => $telephone,
        // -- Pagador --
        Eloom_PayU_Util_PayUParameters::PAYER_NAME => substr($senderName, 0, 150),
        Eloom_PayU_Util_PayUParameters::PAYER_EMAIL => $order->getCustomerEmail(),
        Eloom_PayU_Util_PayUParameters::PAYER_DNI_TYPE => 'CPF',
        Eloom_PayU_Util_PayUParameters::PAYER_DNI => $taxVat,
        Eloom_PayU_Util_PayUParameters::PAYER_STREET => substr($shippingAddress->getStreet(1), 0, 100),
        Eloom_PayU_Util_PayUParameters::PAYER_STREET_2 => substr($shippingAddress->getStreet(2), 0, 100),
        Eloom_PayU_Util_PayUParameters::PAYER_STREET_3 => substr($shippingAddress->getStreet(3), 0, 100),
        Eloom_PayU_Util_PayUParameters::PAYER_CITY => $shippingAddress->getCity(),
        Eloom_PayU_Util_PayUParameters::PAYER_STATE => $shippingAddress->getRegionCode(),
        Eloom_PayU_Util_PayUParameters::PAYER_COUNTRY => Eloom_PayU_Api_PayUCountries::BR,
        Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE => $zipCode,
        Eloom_PayU_Util_PayUParameters::PAYER_PHONE => $telephone,
        Eloom_PayU_Util_PayUParameters::PAYER_CONTACT_PHONE => $telephone,
    );
	  if (strlen($taxVat) == 14) {
		  $parameters[Eloom_PayU_Util_PayUParameters::BUYER_DNI_TYPE] = 'CNPJ';
		  $parameters[Eloom_PayU_Util_PayUParameters::BUYER_POSTAL_CODE] = $taxVat;
		  $parameters[Eloom_PayU_Util_PayUParameters::PAYER_DNI_TYPE] = 'CNPJ';
		  $parameters[Eloom_PayU_Util_PayUParameters::PAYER_CNPJ] = $taxVat;

		  unset($parameters[Eloom_PayU_Util_PayUParameters::BUYER_DNI]);
		  unset($parameters[Eloom_PayU_Util_PayUParameters::PAYER_DNI]);
	  }
    $parametersToLog = $parameters;
    unset($parametersToLog[Eloom_PayU_Util_PayUParameters::PARTNER_ID]);
    $this->logger->info(sprintf("PayU - Pedido [%s] - Request [%s]", $order->getIncrementId(), json_encode($parametersToLog)));

    $response = Eloom_PayU_PayUPayments::doAuthorizationAndCapture($parameters);
    //$this->logger->info(sprintf("PayU - Pedido [%s] - Response [%s]", $order->getIncrementId(), json_encode($response)));
    
    return $response;
  }

}
