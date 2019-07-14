<?php

/**
 * Manages all PayU recurring bill item operations 
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 22/12/2013
 *
 */
class Eloom_PayU_PayURecurringBillItem {

  /**
   * Creates a recurring bill item 
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function create($parameters, $lang = null) {

    $required = array(
        Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID,
        Eloom_PayU_Util_PayUParameters::DESCRIPTION,
        Eloom_PayU_Util_PayUParameters::ITEM_VALUE,
        Eloom_PayU_Util_PayUParameters::CURRENCY
    );

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $request = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildRecurringBillItem($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::RECURRING_BILL_ITEM_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::ADD_OPERATION, array($parameters[Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID]));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::POST);

    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($request, $payUHttpRequestInfo);
  }

  /**
   * Finds recurring bill items by id
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function find($parameters, $lang = null) {

    $required = array(Eloom_PayU_Util_PayUParameters::RECURRING_BILL_ITEM_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);
    $recurringBillItemId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RECURRING_BILL_ITEM_ID);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::RECURRING_BILL_ITEM_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_OPERATION, array($recurringBillItemId));

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(NULL, $payUHttpRequestInfo);
  }

  /**
   * Returns the recurring bill items with the query params
   *
   * @param parameters
   *            The parameters to be sent to the server
   * @return the recurring bill items found
   * @throws PayUException
   * @throws InvalidParametersException
   * @throws ConnectionException
   */
  public static function findList($parameters, $lang = null) {

    $subscriptionId = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);
    $description = Eloom_PayU_Util_CommonRequestUtil::getParameter($parameters, Eloom_PayU_Util_PayUParameters::DESCRIPTION);

    $request = new stdClass();
    $request->subscriptionId = $subscriptionId;
    $request->description = $description;

    if (isset($subscriptionId) || isset($description)) {

      $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::RECURRING_BILL_ITEM_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::GET_LIST_OPERATION, null);

      $urlSegment = Eloom_PayU_Util_CommonRequestUtil::addQueryParamsToUrl($urlSegment, $request);
    } else {
      throw new InvalidArgumentException('You must send ' . Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID . ' or ' . Eloom_PayU_Util_PayUParameters::DESCRIPTION . ' parameters');
    }

    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::GET);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(null, $payUHttpRequestInfo);
  }

  /**
   * Updates a recurring bill item
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function update($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::RECURRING_BILL_ITEM_ID);

    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $recurrinbBillItem = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildRecurringBillItem($parameters);
    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::RECURRING_BILL_ITEM_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::EDIT_OPERATION, array($recurrinbBillItem->id));
    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::PUT);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest($recurrinbBillItem, $payUHttpRequestInfo);
  }

  /**
   * Deletes a recurring bill item
   * @param parameters The parameters to be sent to the server
   * @param string $lang language of request see SupportedLanguages class
   * @return The response to the request sent
   * @throws PayUException
   * @throws InvalidArgumentException
   */
  public static function delete($parameters, $lang = null) {
    $required = array(Eloom_PayU_Util_PayUParameters::RECURRING_BILL_ITEM_ID);
    Eloom_PayU_Util_CommonRequestUtil::validateParameters($parameters, $required);

    $recurrinbBillItem = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildRecurringBillItem($parameters);

    $urlSegment = Eloom_PayU_Util_PayUSubscriptionsUrlResolver::getInstance()->getUrlSegment(Eloom_PayU_Util_PayUSubscriptionsUrlResolver::RECURRING_BILL_ITEM_ENTITY, Eloom_PayU_Util_PayUSubscriptionsUrlResolver::DELETE_OPERATION, array($recurrinbBillItem->id));
    $payUHttpRequestInfo = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildHttpRequestInfo($urlSegment, $lang, Eloom_PayU_Api_RequestMethod::DELETE);
    return Eloom_PayU_Util_PayUApiServiceUtil::sendRequest(NULL, $payUHttpRequestInfo);
  }

}
