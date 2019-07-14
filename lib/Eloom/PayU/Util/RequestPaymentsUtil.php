<?php

##eloom.licenca##

class Eloom_PayU_Util_RequestPaymentsUtil extends Eloom_PayU_Util_CommonRequestUtil {

  /**
   * Build a ping request
   * @param string $lang language to be used 
   * @return the ping request built
   */
  static function buildPingRequest($lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::PING);

    return $request;
  }

  /**
   * Builds the payment method list request
   * @param string $lang language to be used
   * @return the request built
   */
  public static function buildPaymentMethodsListRequest($lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::GET_PAYMENT_METHODS);

    return $request;
  }

  /**
   * Builds a get bank list request
   * 
   * @param string $paymentCountry the payment country 
   * @param string $lang language to be used
   * 
   * @return The complete bank list response
   */
  public static function buildBankListRequest($paymentCountry, $lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::GET_BANKS_LIST);

    $request->bankListInformation = new stdClass();

    $request->bankListInformation->paymentMethod = Eloom_PayU_Api_PaymentMethods::PSE;
    $request->bankListInformation->paymentCountry = $paymentCountry;

    return $request;
  }

  /**
   * Builds a Payment Method Availability Request
   *
   * @param string $paymentMethodParameter the payment method
   * @param string $lang language to be used
   *
   * @return The complete payment method information from API
   */
  public static function buildPaymentMethodAvailabilityRequest($paymentMethodParameter, $lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::GET_PAYMENT_METHOD_AVAILABILITY);

    $request->paymentMethod = $paymentMethodParameter;

    return $request;
  }

  /**
   * Build a payment request
   * @param array $parameters the parameters to build a request
   * @param string $transactionType the transaction type
   * @param strng $lang to be used
   * @return the request built
   */
  static function buildPaymentRequest($parameters, $transactionType, $lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::SUBMIT_TRANSACTION);

    $transaction = null;

    if (Eloom_PayU_Api_TransactionType::AUTHORIZATION_AND_CAPTURE == $transactionType || Eloom_PayU_Api_TransactionType::AUTHORIZATION == $transactionType) {

      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::buildTransactionRequest($parameters, $lang);
    } else if (Eloom_PayU_Api_TransactionType::VOID == $transactionType || Eloom_PayU_Api_TransactionType::REFUND == $transactionType || Eloom_PayU_Api_TransactionType::CAPTURE == $transactionType) {

      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::buildTransactionRequestAfterAuthorization($parameters);
    }

    $transaction->type = $transactionType;
    $request->transaction = $transaction;
    return $request;
  }

  /**
   * Build a transaction object to be added to payment request
   * @param array $parameters the parameters to build a transaction
   * @param strng $lang to be used
   * @return the transaction built
   * @throws InvalidArgumentException if any paramter is invalid
   * 
   */
  private static function buildTransactionRequest($parameters, $lang) {
    $transaction = new stdClass();
    $order = null;

    $transaction->paymentCountry = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::COUNTRY);


    if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ORDER_ID) == null) {
      $signature = null;
      if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::SIGNATURE) != null) {
        $signature = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::SIGNATURE);
      }

      $merchantId = Eloom_PayU_PayU::$merchantId;
      $order = Eloom_PayU_Util_RequestPaymentsUtil::buildOrderRequest($parameters, $lang);

      if ($signature == null && $merchantId != null) {
        $signature = Eloom_PayU_Util_SignatureUtil::buildSignature($order, $merchantId, Eloom_PayU_PayU::$apiKey, Eloom_PayU_Util_SignatureUtil::MD5_ALGORITHM);
      }
      $order->signature = $signature;
      $transaction->order = $order;
    } else {
      $orderId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ORDER_ID);
      $order = new stdClass();
      $order->orderId($orderId);
      $transaction . setOrder($order);
    }

    $transaction->order->buyer = Eloom_PayU_Util_RequestPaymentsUtil::buildBuyer($parameters);

    if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::IP_ADDRESS) != null) {
      $transaction->ipAddress = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::IP_ADDRESS);
    }

    if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_COOKIE) != null) {
      $transaction->cookie = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_COOKIE);
    } else {
      $transaction->cookie = 'cookie_' . microtime();
    }

    $transaction->deviceSessionId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::DEVICE_SESSION_ID);



    if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::USER_AGENT) != null) {
      $transaction->userAgent = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::USER_AGENT);
    } else {
      $transaction->userAgent = sprintf("%s %s", Eloom_PayU_PayU::API_NAME, Eloom_PayU_PayU::API_VERSION);
    }

    $transaction->source = Eloom_PayU_PayU::API_CODE_NAME;

    if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER) != null) {
      $transaction->creditCard = Eloom_PayU_Util_RequestPaymentsUtil::buildCreditCardTransaction($transaction, $parameters);
    } else if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID) != null) {
      $transaction->creditCard = Eloom_PayU_Util_RequestPaymentsUtil::buildCreditCardForToken($parameters);
    }


    if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::INSTALLMENTS_NUMBER) != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::TRANSACTION_INSTALLMENTS_NUMBER, self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::INSTALLMENTS_NUMBER));
    }

    if (self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RESPONSE_URL) != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::RESPONSE_URL, self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RESPONSE_URL));
    }

    $expirationDate = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::EXPIRATION_DATE);
    if (isset($expirationDate) && self::isValidDate($expirationDate, Eloom_PayU_Api_PayUConfig::PAYU_DATE_FORMAT, Eloom_PayU_Util_PayUParameters::EXPIRATION_DATE)) {
      $transaction->expirationDate = $expirationDate;
    }

    $transaction->creditCardTokenId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);

    $paymentMethod = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD);

    // PSE extra parameters
    if ("PSE" == $paymentMethod) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addPSEExtraParameters($transaction, $parameters);
    }

    $transaction->paymentMethod = $paymentMethod;

    $transaction->payer = Eloom_PayU_Util_RequestPaymentsUtil::buildPayer($parameters);

    $transaction->order = $order;
    return $transaction;
  }

  /**
   * Build a transaction object to be added to payment request
   * this build a transaction request to after authorization o authorization and capture 
   * @param array $parameters the parameters to build a transaction
   * @return the transaction built
   */
  private static function buildTransactionRequestAfterAuthorization($parameters) {

    $transaction = new stdClass();
    $transaction->parentTransactionId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TRANSACTION_ID);
    $transaction->reason = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::REASON);

    $order = new stdClass();
    $order->id = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ORDER_ID);

    $transaction->order = $order;

    return $transaction;
  }

  /**
   * Build a order object to be added to payment request  
   * @param array $parameters the parameters to build a object
   * @param string $lang
   * @return the order built
   */
  private static function buildOrderRequest($parameters, $lang) {
    $order = new stdClass();
    $order->accountId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ACCOUNT_ID);
    $order = Eloom_PayU_Util_RequestPaymentsUtil::addOrderBasicData($order, $parameters, $lang);
    $order->notifyUrl = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::NOTIFY_URL);
    $order->partnerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PARTNER_ID);
    $order->additionalValues = Eloom_PayU_Util_RequestPaymentsUtil::buildOrderAdditionalValues(self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CURRENCY), self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::VALUE), self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TAX_VALUE), self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TAX_RETURN_BASE)
    );

    return $order;
  }

  /**
   * Adds to order object the basic data
   * @param object $order
   * @param array $parameters the parameters to build a object
   * @param string $lang to be used
   * @return the order with the basic information added
   * 
   */
  private static function addOrderBasicData($order, $parameters, $lang) {
    if (!isset($order)) {
      $order = new stdClass();
    }

    $order->referenceCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::REFERENCE_CODE);
    $order->description = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::DESCRIPTION);
    $order->language = $lang;

    return $order;
  }

  /**
   * Build order additional values
   * @param string $txCurrency
   * @param string $txValue
   * @param string $taxValue
   * @param string $taxReturnBase
   * @return the a map with the valid additional values
   * 
   */
  private static function buildOrderAdditionalValues($txCurrency, $txValue, $taxValue, $taxReturnBase) {
    $additionalValues = new stdClass();
    $additionalValues = Eloom_PayU_Util_RequestPaymentsUtil::addAdditionalValue($additionalValues, $txCurrency, Eloom_PayU_Api_PayUKeyMapName::TX_VALUE, $txValue);
    $additionalValues = Eloom_PayU_Util_RequestPaymentsUtil::addAdditionalValue($additionalValues, $txCurrency, Eloom_PayU_Api_PayUKeyMapName::TX_TAX, $taxValue);
    $additionalValues = Eloom_PayU_Util_RequestPaymentsUtil::addAdditionalValue($additionalValues, $txCurrency, Eloom_PayU_Api_PayUKeyMapName::TX_TAX_RETURN_BASE, $taxReturnBase);

    return $additionalValues;
  }

  /**
   * Build a additional value and add it to container object
   * @param Object $container
   * @param string $txCurrency the code of the transaction currency
   * @param string $txValueName the parameter name
   * @param string $value the parameter value
   * @return $container whith the valid additional values  added
   * 
   */
  private static function addAdditionalValue($container, $txCurrency, $txValueName, $value) {

    if ($value != null) {
      $additionalValue = new stdClass();
      $additionalValue->value = $value;
      $additionalValue->currency = $txCurrency;

      $container->$txValueName = $additionalValue;
    }

    return $container;
  }

  /**
   * Build a additional value and add it to container object
   * @param Object $container
   * @param string $name the name of parameter
   * @param string $value the parameter value
   * @return $container whith the valid extra parameters added
   *
   */
  private static function addExtraParameter($container, $name, $value) {
    if (!isset($container->extraParameters)) {
      $container->extraParameters = new stdClass();
    }

    if (isset($value) && isset($name)) {
      $extraParameter = new stdClass();
      $extraParameter->value = $value;
      $container->extraParameters->$name = $value;
    }

    return $container;
  }

  /**
   * Build a buyer object to be added to payment request  
   * @param array $parameters
   * @return return a buyer
   */
  private static function buildBuyer($parameters) {
    $buyer = new stdClass();
    $buyer->fullName = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_NAME);
    $buyer->emailAddress = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_EMAIL);
    $buyer->cnpj = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_CNPJ);
    $buyer->contactPhone = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_CONTACT_PHONE);
    $buyer->dniNumber = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_DNI);

    $buyer->shippingAddress = Eloom_PayU_Util_RequestPaymentsUtil::buildBuyerAddress($parameters);

    return $buyer;
  }

  /**
   * Build a payer object to be added to payment request
   * @param array $parameters
   * @return return a payer
   */
  private static function buildPayer($parameters) {
    $payer = new stdClass();
    $payer->fullName = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_NAME);
    $payer->emailAddress = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_EMAIL);
    $payer->cnpj = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_CNPJ);
    $payer->contactPhone = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_CONTACT_PHONE);
    $payer->dniNumber = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_DNI);
    $payer->dniType = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_DNI_TYPE);
    $payer->businessName = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_BUSINESS_NAME);

    $payerBirthDay = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_BIRTHDATE);
    if (isset($payerBirthDay) && self::isValidDate($payerBirthDay, Eloom_PayU_Api_PayUConfig::PAYU_DAY_FORMAT, Eloom_PayU_Util_PayUParameters::PAYER_BIRTHDATE)) {
      $payer->birthdate = $payerBirthDay;
    }

    $payer->billingAddress = Eloom_PayU_Util_RequestPaymentsUtil::buildAddress($parameters);

    return $payer;
  }

  /**
   * Build an address object to be added to payment request
   * @param array $parameters
   * @return return an address
   */
  private static function buildAddress($parameters) {

    $address = new stdClass();
    $address->city = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_CITY);
    $address->country = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_COUNTRY);
    $address->phone = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_PHONE);
    $address->postalCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE);
    $address->state = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STATE);
    $address->postalCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE);
    $address->street1 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STREET);
    $address->street2 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STREET_2);
    $address->street3 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STREET_3);

    return $address;
  }

  /**
   * Build an address object to be added to payment request
   * @param array $parameters
   * @return return an address
   */
  private static function buildBuyerAddress($parameters) {

    $address = new stdClass();
    $address->city = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_CITY);
    $address->country = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_COUNTRY);
    $address->phone = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_PHONE);
    $address->postalCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_POSTAL_CODE);
    $address->state = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_STATE);
    $address->postalCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_POSTAL_CODE);
    $address->street1 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_STREET);
    $address->street2 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_STREET_2);
    $address->street3 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BUYER_STREET_3);

    return $address;
  }

  /**
   * Build a credit card object to be added to payment request
   * @param object $transaction the transaction where the credit card will be added
   * @param object $parameters with the credit card info
   * @return the credit built
   */
  private static function buildCreditCardTransaction($transaction, $parameters) {
    return self::buildCreditCard($parameters);
  }

  /**
   * Build a credit card object to be added to payment request when is used token for payments
   * @param object $parameters with the credit card info
   * @return the credit card built 
   */
  private static function buildCreditCardForToken($parameters) {
    $creditCard = new stdClass();
    $creditCard->securityCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_SECURITY_CODE);
    return $creditCard;
  }

  /**
   * Adds the extra parameters required by the PSE payment method
   *
   * @param transaction
   * @param parameters
   * @throws InvalidParametersException
   */
  private static function addPSEExtraParameters($transaction, $parameters) {

    // PSE reference identification 1
    $pseReference1 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::IP_ADDRESS);

    // PSE reference identification 2
    $pseReference2 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_DOCUMENT_TYPE);

    // PSE reference identification 3
    $pseReference3 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_DNI);

    // PSE user type N-J (Natural or Legal)
    $pseUserType = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_PERSON_TYPE);

    // PSE financial institution code (Bank code)
    $pseFinancialInstitutionCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PSE_FINANCIAL_INSTITUTION_CODE);

    // PSE financial institution name (Bank Name)
    $pseFinancialInstitutionName = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PSE_FINANCIAL_INSTITUTION_NAME);

    if ($pseFinancialInstitutionCode != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::FINANCIAL_INSTITUTION_CODE, $pseFinancialInstitutionCode);
    }

    if ($pseFinancialInstitutionName != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::FINANCIAL_INSTITUTION_NAME, $pseFinancialInstitutionName);
    }

    if ($pseUserType != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::USER_TYPE, $pseUserType);
    }

    if ($pseReference1 != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::PSE_REFERENCE1, $pseReference1);
    }

    if ($pseReference2 != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::PSE_REFERENCE2, $pseReference2);
    }

    if ($pseReference3 != null) {
      $transaction = Eloom_PayU_Util_RequestPaymentsUtil::addExtraParameter($transaction, Eloom_PayU_Api_PayUKeyMapName::PSE_REFERENCE3, $pseReference3);
    }

    return $transaction;
  }

  /**
   * Build a bank account request
   * @param array $parameters
   * @return stdClass with the bank account request built
   */
  public static function buildBankAccountRequest($parameters) {
    $bankAccount = new stdClass();

    $bankAccount->id = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
    $bankAccount->accountId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ACCOUNT_ID);
    $bankAccount->customerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    $bankAccount->name = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_CUSTOMER_NAME);
    $bankAccount->documentNumber = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER);
    $bankAccount->documentNumberType = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER_TYPE);
    $bankAccount->bank = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_BANK_NAME);
    $bankAccount->type = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_TYPE);
    $bankAccount->accountNumber = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_NUMBER);
    $bankAccount->state = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_STATE);
    $bankAccount->country = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::COUNTRY);
    $bankAccount->agencyDigit = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_AGENCY_DIGIT);
    $bankAccount->agencyNumber = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_AGENCY_NUMBER);
    $bankAccount->accountDigit = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ACCOUNT_DIGIT);

    return $bankAccount;
  }

  /**
   * Builds a bank account list request
   *
   * @param parameters The parameters to be sent to the server
   * @return stdClass with the bank account list request built
   */
  public static function buildBankAccountListRequest($parameters) {
    $bankAccountListRequest = new stdClass();

    $bankAccountListRequest->customerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);

    return $bankAccountListRequest;
  }

}
