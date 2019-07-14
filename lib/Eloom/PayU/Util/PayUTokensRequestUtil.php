<?php

/**
 *
 * Utility class to process parameters and send token requests
 *
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 31/10/2013
 *
 */
class Eloom_PayU_Util_PayUTokensRequestUtil extends Eloom_PayU_Util_CommonRequestUtil {

  /**
   * Builds a create credit card token request
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return the request built
   * 
   */
  public static function buildCreateTokenRequest($parameters, $lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::CREATE_TOKEN);

    $request->creditCardToken = PayUTokensRequestUtil::buildCreditCardToken($parameters);

    return $request;
  }

  /**
   * Builds a create credit card token request
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return the request built
   */
  public static function buildGetCreditCardTokensRequest($parameters, $lang) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::GET_TOKENS);

    $creditCardTokenInformation = new stdClass();
    $creditCardTokenInformation->creditCardTokenId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    $creditCardTokenInformation->payerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_ID);


    $startDate = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::START_DATE);
    if ($startDate != null && self::isValidDate($startDate, Eloom_PayU_Api_PayUConfig::PAYU_DATE_FORMAT, Eloom_PayU_Util_PayUParameters::EXPIRATION_DATE)) {
      $creditCardTokenInformation->startDate = $startDate;
    }

    $endDate = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::END_DATE);
    if ($endDate != null && self::isValidDate($endDate, Eloom_PayU_Api_PayUConfig::PAYU_DATE_FORMAT, Eloom_PayU_Util_PayUParameters::EXPIRATION_DATE)) {
      $creditCardTokenInformation->endDate = $endDate;
    }

    $request->creditCardTokenInformation = $creditCardTokenInformation;

    return $request;
  }

  /**
   * Builds a create credit card token remove request
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return the request built
   */
  public static function buildRemoveTokenRequest($parameters, $lang) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::REMOVE_TOKEN);

    $removeCreditCardToken = new stdClass();

    $removeCreditCardToken->creditCardTokenId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    $removeCreditCardToken->payerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_ID);

    $request->removeCreditCardToken = $removeCreditCardToken;

    return $request;
  }

  /**
   * Builds a credit card token to be added to request
   * @param array $parameters
   * @return the credit card token built
   */
  private static function buildCreditCardToken($parameters) {

    $creditCardToken = new stdClass();

    $creditCardToken->name = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_NAME);
    $creditCardToken->payerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_ID);
    $creditCardToken->identificationNumber = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_DNI);
    $creditCardToken->paymentMethod = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD);
    $creditCardToken->expirationDate = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE);
    $creditCardToken->number = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER);
    $creditCardToken->document = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_DOCUMENT);

    return $creditCardToken;
  }

}
