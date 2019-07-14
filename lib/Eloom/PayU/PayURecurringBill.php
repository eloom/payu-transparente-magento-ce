<?php

/**
 * Manages all PayU recurring bill operations 
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 25/09/2014
 *
 */
class Eloom_PayU_PayURecurringBill {

  /**
   * Finds a recurring bill by id
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::RECURRING_BILL_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $recurringBillId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RECURRING_BILL_ID);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::RECURRING_BILL_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_OPERATION, array($recurringBillId));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(NULL, $payUHttpRequestInfo);
  }

  /**
   * Finds all bill filtered by 
   * - customer id
   * - date begin
   * - date final
   * - payment method
   * - subscription Id
   * 
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return the subscription plan list
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function listRecurringBills($parameters, $lang = null) {

    $request = new stdClass();
    $request->customerId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    $request->dateBegin = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RECURRING_BILL_DATE_BEGIN);
    $request->dateFinal = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RECURRING_BILL_DATE_FINAL);
    $request->paymentMethod = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RECURRING_BILL_PAYMENT_METHOD_TYPE);
    $request->state = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RECURRING_BILL_STATE);
    $request->subscriptionId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);
    $request->limit = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::LIMIT);
    $request->offset = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::OFFSET);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::RECURRING_BILL_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::QUERY_OPERATION);

    $urlSegment = Eloom_PayU_Util_CommonRequestUtil::addQueryParamsToUrl($urlSegment, $request);

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(null, $payUHttpRequestInfo);
  }

}
