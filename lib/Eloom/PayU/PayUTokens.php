<?php

/**
 * Manages all PayU tokens operations 
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 31/10/2013
 *
 */
class Eloom_PayU_PayUTokens {

  /**
   * Creates a credit card token
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function create($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER,
        Eloom_PayU_Util_PayUParameters::PAYER_NAME,
        Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD,
        Eloom_PayU_Util_PayUParameters::PAYER_ID,
        Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE);

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $request = PayUTokensRequestUtil::buildCreateTokenRequest($parameters, $lang);
    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Finds a credit card token
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {

    $tokenId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    $required = null;

    if ($tokenId == null) {
      $required = array(Eloom_PayU_Util_PayUParameters::START_DATE, Eloom_PayU_Util_PayUParameters::END_DATE);
    } else {
      $required = array(Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    }

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $request = PayUTokensRequestUtil::buildGetCreditCardTokensRequest($parameters, $lang);
    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Removes a credit card token
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function remove($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::TOKEN_ID,
        Eloom_PayU_Util_PayUParameters::PAYER_ID);

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $request = PayUTokensRequestUtil::buildRemoveTokenRequest($parameters, $lang);

    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::PAYMENTS_API, Eloom_PayU_Api_RequestMethod::POST);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

}
