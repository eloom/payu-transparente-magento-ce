<?php

/**
 * Manages all PayU customers  operations 
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 22/12/2013
 *
 */
class Eloom_PayU_PayUCustomers {

  /**
   * Creates a customer 
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function create($parameters, $lang = null) {

    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCustomer($parameters);

    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCustomer($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMER_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Creates a customer with bank account information
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function createCustomerWithBankAccount($parameters, $lang = null) {

    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCustomer($parameters);

    $customer = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCustomer($parameters);
    $bankAccount = Eloom_PayU_Util_RequestPaymentsUtil::buildBankAccountRequest($parameters);

    $customer->bankAccounts = array($bankAccount);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMER_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($customer, $payUHttpRequestInfo);
  }

  /**
   * Creates a customer with credit card information
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function createCustomerWithCreditCard($parameters, $lang = null) {

    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCustomer($parameters);
    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCreditCard($parameters);

    $customer = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCustomer($parameters);
    $creditCard = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCreditCard($parameters);

    $creditCards = array($creditCard);
    $customer->creditCards = $creditCards;


    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMER_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($customer, $payUHttpRequestInfo);
  }

  /**
   * Finds a customer by id
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $customer = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCustomer($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMER_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_OPERATION, array($customer->id));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($customer, $payUHttpRequestInfo);
  }

  /**
   * Updates a customer
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function update($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCustomer($parameters);
    $customer = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCustomer($parameters);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMER_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::EDIT_OPERATION, array($customer->id));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::PUT);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($customer, $payUHttpRequestInfo);
  }

  /**
   * Deletes a customer
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function delete($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $customer = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCustomer($parameters);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMER_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::DELETE_OPERATION, array($customer->id));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::DELETE);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($customer, $payUHttpRequestInfo);
  }

  /**
   * Finds the customers associated to a plan by plan id or by plan code
   * 
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * 
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function findCustomerListByPlanIdOrPlanCode($parameters, $lang = null) {
    $request = new stdClass();
    $request->planId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_ID);
    $request->planCode = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_CODE);
    $request->limit = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::LIMIT);
    $request->offset = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::OFFSET);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMER_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::CUSTOMERS_PARAM_SEARCH, null);

    $urlSegment = Eloom_PayU_Util_CommonRequestUtil::addQueryParamsToUrl($urlSegment, $request);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(null, $payUHttpRequestInfo);
  }

}
