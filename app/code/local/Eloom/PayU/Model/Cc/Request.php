<?php

##eloom.licenca##

class Eloom_PayU_Model_Cc_Request extends Mage_Core_Model_Abstract {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  public function generatePaymentRequest(Mage_Sales_Model_Order $order) {
    $payment = $order->getPayment();
    $additionalData = json_decode($payment->getAdditionalData());

    $billingAddress = $order->getBillingAddress();
    $shippingAddress = null;
    if ($order->getIsVirtual()) {
      $shippingAddress = $order->getBillingAddress();
    } else {
      $shippingAddress = $order->getShippingAddress();
    }
    /* ------- Sender ------- */
    $senderName = trim($order->getCustomerFirstname()) . ' ' . ($order->getCustomerMiddlename() != null ? trim($order->getCustomerMiddlename()) . ' ' : '') . trim($order->getCustomerLastname());
    $telephone = preg_replace('/\D/', '', $billingAddress->getTelephone());

    $payerBirthday = null;
    $payerTaxVat = null;
    $payerFone = null;

    if (isset($additionalData->creditCardHolderAnother) && $additionalData->creditCardHolderAnother == 1) {
      $payerTaxVat = $additionalData->creditCardHolderCpf;
      $payerFone = $additionalData->creditCardHolderPhone;
      $payerBirthday = $additionalData->creditCardHolderBirthDate;
    } else {
      $payerTaxVat = $order->getCustomerTaxvat();
      $payerFone = $billingAddress->getTelephone();
      $payerBirthday = $order->getCustomerDob();
    }
    $creditCardNumber = Mage::helper('core')->decrypt($additionalData->creditCardNumber);
    $creditCardCvc = $additionalData->creditCardCvc;

    $payerBirthday = Mage::getModel('core/date')->date('Y-m-d', strtotime($payerBirthday));
    $payerTaxVat = preg_replace('/\D/', '', $payerTaxVat);
    $payerFone = preg_replace('/\D/', '', $payerFone);
    $payerName = null;

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
      $payerName = substr($additionalData->creditCardHolderName, 0, 50);
    } else {
      Eloom_PayU_Api_Environment::setPaymentsCustomUrl("https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi");
      $merchantId = Eloom_PayU_Api_Environment::MERCHANT_ID_TEST;
      $apiKey = Eloom_PayU_Api_Environment::API_KEY_TEST;
      $apiLogin = Eloom_PayU_Api_Environment::API_LOGIN_TEST;
      $accountId = Eloom_PayU_Api_Environment::ACCOUNT_ID_TEST;
      $payerName = 'APPROVED'; // APPROVED | REJECTED | PENDING
      $isTest = true;
    }

    Eloom_PayU_PayU::$isTest = $isTest;
    Eloom_PayU_PayU::$language = Eloom_PayU_Api_SupportedLanguages::PT;
    Eloom_PayU_PayU::$merchantId = $merchantId;
    Eloom_PayU_PayU::$apiKey = $apiKey;
    Eloom_PayU_PayU::$apiLogin = $apiLogin;

		$total = $order->getBaseGrandTotal();
		if($config->isReceiptByAntecipacao()) {
			if ($order->getPayuBaseInterestAmount()) {
				$total -= $order->getPayuBaseInterestAmount();
			}
		}

    $parameters = array(
        Eloom_PayU_Util_PayUParameters::PARTNER_ID => '668978',
        Eloom_PayU_Util_PayUParameters::ACCOUNT_ID => $accountId,
        Eloom_PayU_Util_PayUParameters::USER_AGENT => Mage::helper('core/http')->getHttpUserAgent(),
        Eloom_PayU_Util_PayUParameters::REFERENCE_CODE => $order->getIncrementId(),
        Eloom_PayU_Util_PayUParameters::DESCRIPTION => sprintf("Referente ao pedido %s", $order->getIncrementId()),
        Eloom_PayU_Util_PayUParameters::COUNTRY => Eloom_PayU_Api_PayUCountries::BR,
        Eloom_PayU_Util_PayUParameters::IP_ADDRESS => $order->getRemoteIp(),
        // -- Valores --
        Eloom_PayU_Util_PayUParameters::VALUE => number_format($total, 2, '.', ''),
        //Coloque aqui a moeda.
        Eloom_PayU_Util_PayUParameters::CURRENCY => 'BRL',
        Eloom_PayU_Util_PayUParameters::NOTIFY_URL => Mage::getUrl('eloompayu/notifications', array('_secure' => true)),
        // -- Comprador
        Eloom_PayU_Util_PayUParameters::BUYER_NAME => substr($senderName, 0, 150),
        Eloom_PayU_Util_PayUParameters::BUYER_EMAIL => $order->getCustomerEmail(),
        Eloom_PayU_Util_PayUParameters::BUYER_CONTACT_PHONE => $telephone,
        Eloom_PayU_Util_PayUParameters::BUYER_DNI => preg_replace('/\D/', '', $order->getCustomerTaxvat()),
        Eloom_PayU_Util_PayUParameters::BUYER_STREET => substr($shippingAddress->getStreet(1), 0, 100),
        Eloom_PayU_Util_PayUParameters::BUYER_STREET_2 => substr($shippingAddress->getStreet(2), 0, 100),
        Eloom_PayU_Util_PayUParameters::BUYER_CITY => $shippingAddress->getCity(),
        Eloom_PayU_Util_PayUParameters::BUYER_STATE => $shippingAddress->getRegionCode(),
        Eloom_PayU_Util_PayUParameters::BUYER_COUNTRY => Eloom_PayU_Api_PayUCountries::BR,
        Eloom_PayU_Util_PayUParameters::BUYER_POSTAL_CODE => preg_replace('/\D/', '', $shippingAddress->getPostcode()),
        Eloom_PayU_Util_PayUParameters::BUYER_PHONE => $telephone,
        // -- Pagador --
        Eloom_PayU_Util_PayUParameters::PAYER_NAME => $payerName,
        Eloom_PayU_Util_PayUParameters::PAYER_EMAIL => $order->getCustomerEmail(),
        Eloom_PayU_Util_PayUParameters::PAYER_DNI => $payerTaxVat,
        Eloom_PayU_Util_PayUParameters::PAYER_STREET => substr($billingAddress->getStreet(1), 0, 100),
        Eloom_PayU_Util_PayUParameters::PAYER_STREET_2 => substr($billingAddress->getStreet(2), 0, 100),
        //Eloom_PayU_Util_PayUParameters::PAYER_STREET_3 => substr($billingAddress->getStreet(3), 0, 100),
        Eloom_PayU_Util_PayUParameters::PAYER_CITY => $billingAddress->getCity(),
        Eloom_PayU_Util_PayUParameters::PAYER_STATE => $billingAddress->getRegionCode(),
        Eloom_PayU_Util_PayUParameters::PAYER_COUNTRY => Eloom_PayU_Api_PayUCountries::BR,
        Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE => preg_replace('/\D/', '', $billingAddress->getPostcode()),
        Eloom_PayU_Util_PayUParameters::PAYER_PHONE => $payerFone,
        Eloom_PayU_Util_PayUParameters::PAYER_CONTACT_PHONE => $payerFone,
        //Eloom_PayU_Util_PayUParameters::PAYER_BIRTHDATE => $payerBirthday,
        // cartao
        Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER => $creditCardNumber,
        Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $additionalData->creditCardExpiry,
        Eloom_PayU_Util_PayUParameters::CREDIT_CARD_SECURITY_CODE => $creditCardCvc,
        Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD => $payment->getCcType(),
        Eloom_PayU_Util_PayUParameters::INSTALLMENTS_NUMBER => $additionalData->installments,
    );

    $parametersToLog = $parameters;
    unset($parametersToLog[Eloom_PayU_Util_PayUParameters::PARTNER_ID]);
    unset($parametersToLog[Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER]);
    $this->logger->info(sprintf("PayU - Pedido [%s] - Request [%s]", $order->getIncrementId(), json_encode($parametersToLog)));

		$response = Eloom_PayU_PayUPayments::doAuthorizationAndCapture($parameters);
    return $response;
  }

}
