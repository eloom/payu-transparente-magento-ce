<?php

/**
 * Manages all PayU subscriptions operations 
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 17/12/2013
 *
 */
class Eloom_PayU_PayUSubscriptions {

  /**
   * Creates a subscription
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function createSubscription($parameters, $lang = null) {

    $planCode = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_CODE);
    $tokenId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    if (!isset($planCode)) {
      Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateSubscriptionPlan($parameters);
    }

    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCustomerToSubscription($parameters);

    $existParamBankAccount = PayUBankAccounts::existParametersBankAccount($parameters);
    $existParamCreditCard = PayUCreditCards::existParametersCreditCard($parameters);

    self::validatePaymentMethod($parameters, $existParamBankAccount, $existParamCreditCard);

    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscription($parameters, $existParamBankAccount, $existParamCreditCard);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::SUBSCRIPTIONS_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Update a subscription
   * @param parameters The parameters to be sent to the server
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function update($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $subscriptionId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);

    //validates in edition mode
    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCustomerToSubscription($parameters, TRUE);

    $existParamBankAccount = PayUBankAccounts::existParametersBankAccount($parameters);
    $existParamCreditCard = PayUCreditCards::existParametersCreditCard($parameters);

    //Validate in edition mode
    self::validatePaymentMethod($parameters, $existParamBankAccount, $existParamCreditCard, TRUE);

    //Build request in edition mode
    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscription($parameters, $existParamBankAccount, $existParamCreditCard, TRUE);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::SUBSCRIPTIONS_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::EDIT_OPERATION, array($subscriptionId));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::PUT);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Cancels a subscription
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function cancel($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscription($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::SUBSCRIPTIONS_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::DELETE_OPERATION, array($parameters[Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID]));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::DELETE);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Find the subscription with the given id
   *
   * @param parameters The parameters to be sent to the server
   * @return the finded Subscription
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $subscriptionId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);

    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscription($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::SUBSCRIPTIONS_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_OPERATION, array($subscriptionId));
    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Finds the subscriptions associated to a customer by either
   * payer id, plan id, plan code, accoun id and account status
   * using an offset and a limit 
   *
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   *
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function findSubscriptionsByPlanOrCustomerOrAccount($parameters, $lang = null) {
    $request = new stdClass();
    $request->planId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_ID);
    $request->planCode = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_CODE);
    $request->state = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ACCOUNT_STATE);
    $request->customerId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    $request->accountId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ACCOUNT_ID);
    $request->limit = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::LIMIT);
    $request->offset = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::OFFSET);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::SUBSCRIPTIONS_ENTITY, UrlResolver::GET_LIST_OPERATION, null);

    $urlSegment = Eloom_PayU_Util_CommonRequestUtil::addQueryParamsToUrl($urlSegment, $request);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(null, $payUHttpRequestInfo);
  }

  /**
   * validate the Payment Method parameters. Only one payment methos is permitted
   * @param $parameters
   * @param $existParamBankAccount
   * @param $existParamCreditCard
   * @throws PayUException
   */
  public static function validatePaymentMethod($parameters, $existParamBankAccount, $existParamCreditCard, $edit = FALSE) {
    if ($existParamBankAccount == TRUE && $existParamCreditCard == TRUE) {
      throw new Eloom_PayU_Exceptions_PayUException(Eloom_PayU_Exceptions_PayUErrorCodes::INVALID_PARAMETERS, "The subscription must have only one payment method");
    } else if ($existParamBankAccount == TRUE) {
      Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateBankAccount($parameters);
      if ($edit == FALSE) {
        //The TERMS_AND_CONDITIONS_ACEPTED Parameter is required for Bank Account
        $required = array(Eloom_PayU_Util_PayUParameters::TERMS_AND_CONDITIONS_ACEPTED);
        Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
      }
    } else if ($existParamCreditCard == TRUE) {
      Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCreditCard($parameters);
    } else {
      throw new Eloom_PayU_Exceptions_PayUException(Eloom_PayU_Exceptions_PayUErrorCodes::INVALID_PARAMETERS, "The subscription must have one payment method");
    }
  }

}
