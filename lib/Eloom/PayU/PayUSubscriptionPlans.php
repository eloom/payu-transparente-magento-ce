<?php

/**
 * Manages all PayU Subscription plans operations
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 22/12/2013
 *
 */
class Eloom_PayU_PayUSubscriptionPlans {

  /**
   * Creates a subscription plans
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function create($parameters, $lang = null) {
    Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateSubscriptionPlan($parameters);

    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscriptionPlan($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::PLAN_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Find a subscription plan by plan code
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::PLAN_CODE);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $plan = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscriptionPlan($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::PLAN_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_OPERATION, array($plan->planCode));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($plan, $payUHttpRequestInfo);
  }

  /**
   * Updates a subscription plan
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function update($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::PLAN_CODE);
    $invalid = array(
        Eloom_PayU_Util_PayUParameters::PLAN_INTERVAL_COUNT,
        Eloom_PayU_Util_PayUParameters::ACCOUNT_ID, Eloom_PayU_Util_PayUParameters::PLAN_MAX_PAYMENTS,
        Eloom_PayU_Util_PayUParameters::PLAN_INTERVAL
    );

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required, $invalid);

    $plan = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscriptionPlan($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::PLAN_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::EDIT_OPERATION, array($plan->planCode));
    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::PUT);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($plan, $payUHttpRequestInfo);
  }

  /**
   * Deletes a subscription plan
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function delete($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::PLAN_CODE);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $plan = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscriptionPlan($parameters);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::PLAN_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::DELETE_OPERATION, array($plan->planCode));
    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::DELETE);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($plan, $payUHttpRequestInfo);
  }

  /**
   * Finds all subscription plans filtered by merchant or account
   * by default the function filter by merchant if you need filter by account
   * you can send in the parameters the account id
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class 
   * @return the subscription plan list
   * @throws PayUException
   * @throws InvalidParametersException
   * @throws InvalidArgumentException
   */
  public static function listPlans($parameters, $lang = null) {

    $request = new stdClass();
    $request->accountId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ACCOUNT_ID);
    $request->limit = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::LIMIT);
    $request->offset = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::OFFSET);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::PLAN_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::QUERY_OPERATION);

    $urlSegment = Eloom_PayU_Util_CommonRequestUtil::addQueryParamsToUrl($urlSegment, $request);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(null, $payUHttpRequestInfo);
  }

}
