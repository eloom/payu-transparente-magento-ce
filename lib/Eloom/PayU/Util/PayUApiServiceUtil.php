<?php

class Eloom_PayU_Util_PayUApiServiceUtil {

  /**
   * Sends a request type json 
   * 
   * @param Object $request this object is encode to json is used to request data
   * @param PayUHttpRequestInfo $payUHttpRequestInfo object with info to send an api request
   * @param bool $removeNullValues if remove null values in request and response object 
   * @return string response
   * @throws RuntimeException
   */
  public static function sendRequest($request, Eloom_PayU_Api_PayUHttpRequestInfo $payUHttpRequestInfo, $removeNullValues = NULL) {
    if (!isset($removeNullValues)) {
      $removeNullValues = Eloom_PayU_Api_PayUConfig::REMOVE_NULL_OVER_REQUEST;
    }

    if ($removeNullValues && $request != null) {
      $request = Eloom_PayU_Util_PayURequestObjectUtil::removeNullValues($request);
    }
    if ($request != NULL) {
      $request = Eloom_PayU_Util_PayURequestObjectUtil::encodeStringUtf8($request);
    }

    if (isset($request->transaction->order->signature)) {
      $request->transaction->order->signature = Eloom_PayU_Util_SignatureUtil::buildSignature($request->transaction->order, Eloom_PayU_PayU::$merchantId, Eloom_PayU_PayU::$apiKey, Eloom_PayU_Util_SignatureUtil::MD5_ALGORITHM);
    }

    $requestJson = json_encode($request);

    $responseJson = Eloom_PayU_Util_HttpClientUtil::sendRequest($requestJson, $payUHttpRequestInfo);
    if ($responseJson == 200 || $responseJson == 204) {
      return true;
    } else {
      $response = json_decode($responseJson);
      if (!isset($response)) {
        throw new Eloom_PayU_Exceptions_PayUException(Eloom_PayU_Exceptions_PayUErrorCodes::JSON_DESERIALIZATION_ERROR, sprintf(' Error decoding json. Please verify the json structure received. the json isn\'t added in this message ' .
                ' for security reasons please verify the variable $responseJson on class PayUApiServiceUtil'));
      }
      if ($removeNullValues) {
        $response = Eloom_PayU_Util_PayURequestObjectUtil::removeNullValues($response);
      }

      $response = Eloom_PayU_Util_PayURequestObjectUtil::formatDates($response);

      if ($payUHttpRequestInfo->environment === Eloom_PayU_Api_Environment::PAYMENTS_API || $payUHttpRequestInfo->environment === Eloom_PayU_Api_Environment::REPORTS_API) {
        if (Eloom_PayU_Api_PayUResponseCode::SUCCESS == $response->code) {
          return $response;
        } else {
          throw new Eloom_PayU_Exceptions_PayUException(Eloom_PayU_Exceptions_PayUErrorCodes::API_ERROR, $response->error);
        }
      } else if ($payUHttpRequestInfo->environment === Eloom_PayU_Api_Environment::SUBSCRIPTIONS_API) {
        if (!isset($response->type) || ($response->type != 'BAD_REQUEST' && $response->type != 'NOT_FOUND' && $response->type != 'MALFORMED_REQUEST')) {
          return $response;
        } else {
          throw new Eloom_PayU_Exceptions_PayUException(Eloom_PayU_Exceptions_PayUErrorCodes::API_ERROR, $response->description);
        }
      }
    }
  }

}
