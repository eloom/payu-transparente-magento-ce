<?php

class Eloom_PayU_PayUBankAccounts {

  /**
   * Creates a bank account to payments
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function create($parameters, $lang = null) {

    $customerId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    if (!isset($customerId)) {
      throw new InvalidArgumentException(" The parameter customer id is mandatory ");
    }

    $request = Eloom_PayU_Util_RequestPaymentsUtil::buildBankAccountRequest($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::BANK_ACCOUNT_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION, array(
        $parameters [Eloom_PayU_Util_PayUParameters::CUSTOMER_ID]
    ));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Deletes a bank account
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function delete($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::CUSTOMER_ID, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $customerId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    $bankAccountId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::BANK_ACCOUNT_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::DELETE_OPERATION, array($customerId, $bankAccountId));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::DELETE);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(null, $payUHttpRequestInfo);
  }

  /**
   * Updates a bank account
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function update($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $request = Eloom_PayU_Util_RequestPaymentsUtil::buildBankAccountRequest($parameters);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::BANK_ACCOUNT_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::EDIT_OPERATION, array($request->id));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::PUT);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Return a bank account with the given id
   *
   * @param parameters The parameters to be sent to the server
   * @return the find bank account
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $bankAccountRequest = Eloom_PayU_Util_RequestPaymentsUtil::buildBankAccountRequest($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::BANK_ACCOUNT_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_OPERATION, array($bankAccountRequest->id));
    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($bankAccountRequest, $payUHttpRequestInfo);
  }

  /**
   * Finds the bank accounts associated to a customer by customer id
   * 
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * 
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function findListByCustomer($parameters, $lang = null) {
    $request = new stdClass();
    $request->customerId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::BANK_ACCOUNT_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_LIST_OPERATION, array($request->customerId));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Returns all parameter names of Bank Account
   * @return list of parameter names
   */
  public static function getParameterNames() {

    $parameterNames = array(Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER_TYPE,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_CUSTOMER_NAME,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_AGENCY_NUMBER,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_AGENCY_DIGIT,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ACCOUNT_DIGIT,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_NUMBER,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_BANK_NAME,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_TYPE,
        Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_STATE);
    return $parameterNames;
  }

  /**
   * Indicates whether any of the parameters for Bank Account is within the parameters list
   * @param parameters The parametrs to evaluate
   * @return <boolean> returns true if the parameter is in the set
   */
  public static function existParametersBankAccount($parameters) {
    $keyNamesSet = self::getParameterNames();
    return Eloom_PayU_Util_CommonRequestUtil::isParameterInSet($parameters, $keyNamesSet);
  }

}
