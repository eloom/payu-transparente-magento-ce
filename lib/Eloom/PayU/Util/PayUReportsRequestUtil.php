<?php

/**
 *
 * Utility class to process parameters and send reports requests
 *
 *
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 29/10/2013
 *
 */
class Eloom_PayU_Util_PayUReportsRequestUtil extends Eloom_PayU_Util_CommonRequestUtil {

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
   * Builds an order details reporting request. The order will be query by id
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return the request built
   */
  public static function buildOrderReportingDetails($parameters, $lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::ORDER_DETAIL);

    $orderId = intval(self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ORDER_ID));


    $request->details = self::addMapEntry(null, Eloom_PayU_Api_PayUKeyMapName::ORDER_ID, $orderId);

    return $request;
  }

  /**
   * Builds an order details reporting request. The order will be query by reference code
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return the request built
   * 
   */
  public static function buildOrderReportingByReferenceCode($parameters, $lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::ORDER_DETAIL_BY_REFERENCE_CODE);

    $referenceCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::REFERENCE_CODE);

    $request->details = self::addMapEntry(null, Eloom_PayU_Api_PayUKeyMapName::REFERENCE_CODE, $referenceCode);

    return $request;
  }

  /**
   * Builds a transaction reporting request the transaction will be query by id
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The complete reporting request to be sent to the server
   */
  public static function buildTransactionResponse($parameters, $lang = null) {

    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $request = self::buildCommonRequest($lang, Eloom_PayU_Api_PayUCommands::TRANSACTION_RESPONSE_DETAIL);

    $transactionId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TRANSACTION_ID);

    $request->details = self::addMapEntry(null, Eloom_PayU_Api_PayUKeyMapName::TRANSACTION_ID, $transactionId);

    return $request;
  }

}
