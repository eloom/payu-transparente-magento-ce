<?php

class Eloom_PayU_PayUPayments {

  /**
   * Makes a ping request
   * @param string $lang language of request see SupportedLanguages class
   * @throws PayUException 
   * @return The response to the ping request sent
   */
  static function doPing($lang = null) {
    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(Eloom_PayU_Util_RequestPaymentsUtil::buildPingRequest($lang), $payUHttpRequestInfo);
  }

  /**
   * Makes a get payment methods request
   * @param string $lang language of request see SupportedLanguages class
   * @return The payment method list
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function getPaymentMethods($lang = null) {
    $request = Eloom_PayU_Util_RequestPaymentsUtil::buildPaymentMethodsListRequest($lang);
    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Evaluate if a payment method is available in Payments API
   * @param string $paymentMethodParameter the payment method to evaluate
   * @param string $lang language of request see SupportedLanguages class
   * @return The payment method information 
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function getPaymentMethodAvailability($paymentMethodParameter, $lang = null) {
    $request = Eloom_PayU_Util_RequestPaymentsUtil::buildPaymentMethodAvailabilityRequest($paymentMethodParameter, $lang);
    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * list PSE Banks 
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * 
   * @return The bank list information
   * @throws PayUException
   * @throws InvalidArgumentException
   * 
   */
  public static function getPSEBanks($parameters, $lang = null) {
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, array(Eloom_PayU_Util_PayUParameters::COUNTRY));
    $paymentCountry = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::COUNTRY);
    $request = Eloom_PayU_Util_RequestPaymentsUtil::buildBankListRequest($paymentCountry);
    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Do an authorization and capture transaction 
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   * 
   */
  public static function doAuthorizationAndCapture($parameters, $lang = null) {
    return Eloom_PayU_PayUPayments::doPayment($parameters, Eloom_PayU_Api_TransactionType::AUTHORIZATION_AND_CAPTURE, $lang);
  }

  /**
   * Makes payment petition
   *
   * @param parameters The parameters to be sent to the server
   * @param transactionType
   *            The type of the payment transaction
   * @param string $lang language of request see SupportedLanguages class            
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function doPayment($parameters, $transactionType, $lang) {

    $requiredAll = array(
        Eloom_PayU_Util_PayUParameters::REFERENCE_CODE,
        Eloom_PayU_Util_PayUParameters::DESCRIPTION,
        Eloom_PayU_Util_PayUParameters::CURRENCY,
        Eloom_PayU_Util_PayUParameters::VALUE,
    );

    $paymentMethodParameter = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD);

    if ($paymentMethodParameter != null) {

      $responseAvailability = Eloom_PayU_PayUPayments::getPaymentMethodAvailability($paymentMethodParameter, $lang);
      $paymentMethod = $responseAvailability->paymentMethod;

      if (array_key_exists(Eloom_PayU_Util_PayUParameters::TOKEN_ID, $parameters)) {

        $requiredTokenId = array(
            Eloom_PayU_Util_PayUParameters::INSTALLMENTS_NUMBER,
            Eloom_PayU_Util_PayUParameters::TOKEN_ID);

        $required = array_merge($requiredAll, $requiredTokenId);
      } else if (array_key_exists(Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER, $parameters)) {

        $requiredCreditCard = array(
            Eloom_PayU_Util_PayUParameters::INSTALLMENTS_NUMBER,
            Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER,
            Eloom_PayU_Util_PayUParameters::PAYER_NAME,
            Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE,
            Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD);


        $processWithoutCvv2 = Eloom_PayU_PayUPayments::isProcessWithoutCvv2Param($parameters);
        if (!$processWithoutCvv2) {
          $requiredCreditCard[] = Eloom_PayU_Util_PayUParameters::CREDIT_CARD_SECURITY_CODE;
        }
        $required = array_merge($requiredAll, $requiredCreditCard);
      } else if ($paymentMethod != null && (Eloom_PayU_Api_PayUPaymentMethodType::CASH == $paymentMethod->type )) {
        $requiredCash = array(
            Eloom_PayU_Util_PayUParameters::PAYER_NAME,
            Eloom_PayU_Util_PayUParameters::PAYER_DNI,
            Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD);

        $required = array_merge($requiredAll, $requiredCash);
      } else if ("BOLETO_BANCARIO" == $paymentMethodParameter) {
        $requiredBoletoBancario = array(Eloom_PayU_Util_PayUParameters::PAYER_NAME,
            Eloom_PayU_Util_PayUParameters::PAYER_DNI,
            Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD,
            Eloom_PayU_Util_PayUParameters::PAYER_STREET,
            Eloom_PayU_Util_PayUParameters::PAYER_STREET_2,
            Eloom_PayU_Util_PayUParameters::PAYER_CITY,
            Eloom_PayU_Util_PayUParameters::PAYER_STATE,
            Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE
        );

        $required = array_merge($requiredAll, $requiredBoletoBancario);
      } else if ("PIX" == $paymentMethodParameter) {
	      $requiredPix = array(Eloom_PayU_Util_PayUParameters::PAYER_NAME,
		      Eloom_PayU_Util_PayUParameters::PAYER_DNI,
		      Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD,
		      Eloom_PayU_Util_PayUParameters::PAYER_STREET,
		      Eloom_PayU_Util_PayUParameters::PAYER_STREET_2,
		      Eloom_PayU_Util_PayUParameters::PAYER_CITY,
		      Eloom_PayU_Util_PayUParameters::PAYER_STATE,
		      Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE
	      );

	      $required = array_merge($requiredAll, $requiredPix);
      } else if ("PSE" == $paymentMethodParameter) {
        $requiredPSE = array(
            Eloom_PayU_Util_PayUParameters::REFERENCE_CODE,
            Eloom_PayU_Util_PayUParameters::DESCRIPTION,
            Eloom_PayU_Util_PayUParameters::CURRENCY,
            Eloom_PayU_Util_PayUParameters::VALUE,
            Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD,
            Eloom_PayU_Util_PayUParameters::PAYER_NAME,
            Eloom_PayU_Util_PayUParameters::PAYER_DOCUMENT_TYPE,
            Eloom_PayU_Util_PayUParameters::PAYER_DNI,
            Eloom_PayU_Util_PayUParameters::PAYER_EMAIL,
            Eloom_PayU_Util_PayUParameters::PAYER_CONTACT_PHONE,
            Eloom_PayU_Util_PayUParameters::PSE_FINANCIAL_INSTITUTION_CODE,
            Eloom_PayU_Util_PayUParameters::PAYER_PERSON_TYPE,
            Eloom_PayU_Util_PayUParameters::IP_ADDRESS,
            Eloom_PayU_Util_PayUParameters::PAYER_COOKIE,
            Eloom_PayU_Util_PayUParameters::USER_AGENT);
        $required = array_merge($requiredAll, $requiredPSE);
      } else if ($paymentMethod != null && ($paymentMethod->type == PayUPaymentMethodType::CREDIT_CARD)) {
        throw new InvalidArgumentException("Payment method credit card require at least one of two parameters ["
        . Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER . '] or [' . Eloom_PayU_Util_PayUParameters::TOKEN_ID . ']');
      } else {
        $required = $requiredAll;
      }
    } else {
      throw new InvalidArgumentException(sprintf("The payment method value is invalid"));
    }

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $request = Eloom_PayU_Util_RequestPaymentsUtil::buildPaymentRequest($parameters, $transactionType, $lang);
    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Process a transaction already authorizated
   *
   * @param parameters The parameters to be sent to the server
   * @param transactionType
   *            The type of the payment transaction
   * @param string $lang language of request see SupportedLanguages class            
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  private static function processTransactionAlreadyAuthorizated($parameters, $transactionType, $lang) {
    $required = array(Eloom_PayU_Util_PayUParameters::TRANSACTION_ID,
        Eloom_PayU_Util_PayUParameters::ORDER_ID);

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $request = Eloom_PayU_Util_RequestPaymentsUtil::buildPaymentRequest($parameters, $transactionType, $lang);

    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Do an authorization transaction
   *
   * @param parameters to build the request
   * @param string $lang language of request see SupportedLanguages class 
   * @return The request response
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function doAuthorization($parameters, $lang = null) {
    return Eloom_PayU_PayUPayments::doPayment($parameters, Eloom_PayU_Api_TransactionType::AUTHORIZATION, $lang);
  }

  /**
   * Do a capture transaction
   *
   * @param parameters to build the request
   * @param string $lang language of request see SupportedLanguages class 
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function doCapture($parameters, $lang = NULL) {
    return Eloom_PayU_PayUPayments::processTransactionAlreadyAuthorizated($parameters, Eloom_PayU_Api_TransactionType::CAPTURE, $lang);
  }

  /**
   * Do a void (Cancel) transaction
   *
   * @param parameters to build the request
   * @param string $lang language of request see SupportedLanguages class 
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function doVoid($parameters, $lang = NULL) {
    return Eloom_PayU_PayUPayments::processTransactionAlreadyAuthorizated($parameters, Eloom_PayU_Api_TransactionType::VOID, $lang);
  }

  /**
   * Do a refund transaction
   *
   * @param parameters to build the request
   * @param string $lang language of request see SupportedLanguages class
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function doRefund($parameters, $lang = NULL) {
    return Eloom_PayU_PayUPayments::processTransactionAlreadyAuthorizated($parameters, Eloom_PayU_Api_TransactionType::REFUND, $lang);
  }

  /**
   * Get the value for parameter processWithoutCvv2 if the parameter doesn't exist
   * in the parameters array or the parameter value isn't valid boolean representation return false
   * the otherwise return the parameter value
   * @param array $parameters
   * @return boolean whith the value for processWithoutCvv2 parameter, if the parameter doesn't exist in the array or 
   * it has a invalid boolean value returs false;
   */
  private static function isProcessWithoutCvv2Param($parameters) {
    $processWithoutCvv2 = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PROCESS_WITHOUT_CVV2);

    if (is_bool($processWithoutCvv2)) {
      return $processWithoutCvv2;
    } else {
      return false;
    }
  }

}
