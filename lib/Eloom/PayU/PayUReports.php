<?php

/**
 * Manages all PayU reports operations
 *
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 17/10/2013
 * 
 */
class Eloom_PayU_PayUReports {

  /**
   * Makes a ping request
   * @param string $lang language of request see SupportedLanguages class
   * @throws PayUException 
   * @return The response to the ping request sent
   */
  public static function doPing($lang = null) {

    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::REPORTS_API, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(Eloom_PayU_Util_PayUReportsRequestUtil::buildPingRequest(), $payUHttpRequestInfo);
  }

  /**
   * Makes an order details reporting petition by the id
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return order found
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function getOrderDetail($parameters, $lang = null) {

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, array(Eloom_PayU_Util_PayUParameters::ORDER_ID));

    $request = Eloom_PayU_Util_PayUReportsRequestUtil::buildOrderReportingDetails($parameters, $lang);

    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::REPORTS_API, Eloom_PayU_Api_RequestMethod::POST);

    $response = Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);

    if (isset($response) && isset($response->result)) {
      return $response->result->payload;
    }

    return null;
  }

  /**
   * Makes an order details reporting petition by reference code
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The order list corresponding whit the given reference code
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function getOrderDetailByReferenceCode($parameters, $lang = null) {

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, array(Eloom_PayU_Util_PayUParameters::REFERENCE_CODE));

    $request = Eloom_PayU_Util_PayUReportsRequestUtil::buildOrderReportingByReferenceCode($parameters, $lang);

    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::REPORTS_API, Eloom_PayU_Api_RequestMethod::POST);

    $response = Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);

    if (isset($response) && isset($response->result)) {
      return $response->result->payload;
    } else {
      throw new Eloom_PayU_Exceptions_PayUException(Eloom_PayU_Exceptions_PayUErrorCodes::INVALID_PARAMETERS, "the reference code doesn't exist ");
    }
  }

  /**
   * Makes a transaction reporting petition by the id
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The transaction response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function getTransactionResponse($parameters, $lang = null) {

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, array(Eloom_PayU_Util_PayUParameters::TRANSACTION_ID));

    $request = Eloom_PayU_Util_PayUReportsRequestUtil::buildTransactionResponse($parameters, $lang);

    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::REPORTS_API, Eloom_PayU_Api_RequestMethod::POST);

    $response = Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);

    if (isset($response) && isset($response->result)) {
      return $response->result->payload;
    }

    return null;
  }

}
