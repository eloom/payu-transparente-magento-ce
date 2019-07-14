<?php

/**
 * Manages all PayU credit card operations
 * over subscriptions
 *
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 22/12/2013
 *
 */
class Eloom_PayU_PayUCreditCards {

  /**
   * Creates a credit card 
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function create($parameters, $lang = null) {

    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCreditCard($parameters);

    $customerId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    if (!isset($customerId)) {
      throw new InvalidArgumentException(" The parameter customer id is mandatory ");
    }


    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCreditCard($parameters);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CREDIT_CARD_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION, array($parameters[Eloom_PayU_Util_PayUParameters::CUSTOMER_ID]));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * finds a credit card
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $creditCard = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCreditCard($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CREDIT_CARD_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_OPERATION, array($creditCard->token));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($creditCard, $payUHttpRequestInfo);
  }

  /**
   * Returns the credit card list with the query params
   *
   * @param parameters
   *            The parameters to be sent to the server
   * @return the credit card list
   * @throws PayUException
   * @throws InvalidParametersException
   * @throws ConnectionException
   */
  public static function findList($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $request = new stdClass();
    $request->customerId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    $creditCard = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCreditCard($parameters);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CREDIT_CARD_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_LIST_OPERATION, array($creditCard->customerId));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($creditCard, $payUHttpRequestInfo);
  }

  /**
   * Updates a credit card
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function update($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    $invalid = array(Eloom_PayU_Util_PayUParameters::CUSTOMER_ID,
        Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER,
        Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD);

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required, $invalid);
    $creditCard = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCreditCard($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CREDIT_CARD_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::EDIT_OPERATION, array($creditCard->token));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::PUT);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($creditCard, $payUHttpRequestInfo);
  }

  /**
   * Deletes a credit card
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function delete($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::TOKEN_ID, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $creditCard = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCreditCard($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CREDIT_CARD_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::DELETE_OPERATION, array($creditCard->customerId, $creditCard->token));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::DELETE);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($creditCard, $payUHttpRequestInfo);
  }

  /**
   * Returns all parameter names of Credit Card
   * @return list of parameter names
   */
  public static function getParameterNames() {

    $parameterNames = array(Eloom_PayU_Util_PayUParameters::TOKEN_ID,
        Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER,
        Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE,
        Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD,
        Eloom_PayU_Util_PayUParameters::PAYER_NAME,
        Eloom_PayU_Util_PayUParameters::PAYER_STREET,
        Eloom_PayU_Util_PayUParameters::PAYER_STREET_2,
        Eloom_PayU_Util_PayUParameters::PAYER_STREET_3,
        Eloom_PayU_Util_PayUParameters::PAYER_CITY,
        Eloom_PayU_Util_PayUParameters::PAYER_STATE,
        Eloom_PayU_Util_PayUParameters::PAYER_COUNTRY,
        Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE,
        Eloom_PayU_Util_PayUParameters::PAYER_PHONE);
    return $parameterNames;
  }

  /**
   * Indicates whether any of the parameters of CRedit Card is within the parameters list
   * @param parameters The parametrs to evaluate
   * @return <boolean> returns true if the parameter is in the set
   */
  public static function existParametersCreditCard($parameters) {
    $keyNamesSet = self::getParameterNames();
    return Eloom_PayU_Util_CommonRequestUtil::isParameterInSet($parameters, $keyNamesSet);
  }

}
