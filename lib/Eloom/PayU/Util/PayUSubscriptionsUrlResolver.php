<?php

/**
 *
 * Util class to build  the url to subscriptions api operations
 *
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0, 22/12/2013
 *
 */
class Eloom_PayU_Util_PayUSubscriptionsUrlResolver extends Eloom_PayU_Util_UrlResolver {

  /** constant to plan entity */
  const PLAN_ENTITY = 'Plan';

  /** constant to customer entity */
  const CUSTOMER_ENTITY = 'Customer';

  /** constant to credit card entity */
  const CREDIT_CARD_ENTITY = 'CreditCard';

  /** constant to bank account entity */
  const BANK_ACCOUNT_ENTITY = 'BankAccount';

  /** constant to subscription entity */
  const SUBSCRIPTIONS_ENTITY = 'subscription';

  /** constant to recurring bill entity */
  const RECURRING_BILL_ENTITY = 'RecurringBill';

  /** constant to recurring bill item entity */
  const RECURRING_BILL_ITEM_ENTITY = 'RecurringBillItem';

  /**
   * Specifies the verb to find customers by  planId,
   * planCode with limit and offset
   */
  const CUSTOMERS_PARAM_SEARCH = "getByParam";

  /** instancia to singleton pattern */
  private static $instancia;

  /**
   * the constructor class
   */
  private function __construct() {
    $planBaseUrl = '/plans';
    $planUrlInfo = array(
        self::ADD_OPERATION => array('segmentPattern' => $planBaseUrl, 'numberParams' => 0),
        self::GET_OPERATION => array('segmentPattern' => $planBaseUrl . '/%s', 'numberParams' => 1),
        self::EDIT_OPERATION => array('segmentPattern' => $planBaseUrl . '/%s', 'numberParams' => 1),
        self::QUERY_OPERATION => array('segmentPattern' => $planBaseUrl, 'numberParams' => 0),
        self::DELETE_OPERATION => array('segmentPattern' => $planBaseUrl . '/%s', 'numberParams' => 1));

    $customerBaseUrl = '/customers';
    $customerUrlInfo = array(
        self::ADD_OPERATION => array('segmentPattern' => $customerBaseUrl, 'numberParams' => 0),
        self::GET_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s', 'numberParams' => 1),
        self::EDIT_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s', 'numberParams' => 1),
        self::DELETE_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s', 'numberParams' => 1),
        self::CUSTOMERS_PARAM_SEARCH => array('segmentPattern' => $customerBaseUrl, 'numberParams' => 0));

    $creditCardBaseUrl = '/creditCards';
    $creditCardsUrlInfo = array(
        self::ADD_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s' . $creditCardBaseUrl, 'numberParams' => 1),
        self::GET_OPERATION => array('segmentPattern' => $creditCardBaseUrl . '/%s', 'numberParams' => 1),
        self::EDIT_OPERATION => array('segmentPattern' => $creditCardBaseUrl . '/%s', 'numberParams' => 1),
        self::GET_LIST_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s' . $creditCardBaseUrl, 'numberParams' => 1),
        self::DELETE_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s' . $creditCardBaseUrl . '/%s/', 'numberParams' => 2));

    $bankAccountBaseUrl = '/bankAccounts';
    $bankAccountUrlInfo = array(
        self::ADD_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s' . $bankAccountBaseUrl, 'numberParams' => 1),
        self::GET_OPERATION => array('segmentPattern' => $bankAccountBaseUrl . '/%s', 'numberParams' => 1),
        self::QUERY_OPERATION => array('segmentPattern' => $bankAccountBaseUrl . '/params%s', 'numberParams' => 1),
        self::EDIT_OPERATION => array('segmentPattern' => $bankAccountBaseUrl . '/%s', 'numberParams' => 1),
        self::DELETE_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s' . $bankAccountBaseUrl . '/%s', 'numberParams' => 2),
        self::GET_LIST_OPERATION => array('segmentPattern' => $customerBaseUrl . '/%s' . $bankAccountBaseUrl, 'numberParams' => 1));

    $subscriptionsCardBaseUrl = '/subscriptions';
    $subscriptionsUrlInfo = array(
        self::ADD_OPERATION => array('segmentPattern' => $subscriptionsCardBaseUrl, 'numberParams' => 0),
        self::GET_OPERATION => array('segmentPattern' => $subscriptionsCardBaseUrl . '/%s', 'numberParams' => 1),
        self::EDIT_OPERATION => array('segmentPattern' => $subscriptionsCardBaseUrl . '/%s', 'numberParams' => 1),
        self::DELETE_OPERATION => array('segmentPattern' => $subscriptionsCardBaseUrl . '/%s', 'numberParams' => 1),
        self::GET_LIST_OPERATION => array('segmentPattern' => $subscriptionsCardBaseUrl, 'numberParams' => 0));

    $recurringBillBaseUrl = '/recurringBill';
    $recurringBillUrlInfo = array(
        self::GET_OPERATION => array('segmentPattern' => $recurringBillBaseUrl . '/%s', 'numberParams' => 1),
        self::QUERY_OPERATION => array('segmentPattern' => $recurringBillBaseUrl, 'numberParams' => 0));

    $recurringBillItemBaseUrl = '/recurringBillItems';
    $recurringBillItemUrlInfo = array(
        self::ADD_OPERATION => array('segmentPattern' => $subscriptionsCardBaseUrl . '/%s' . $recurringBillItemBaseUrl, 'numberParams' => 1),
        self::GET_OPERATION => array('segmentPattern' => $recurringBillItemBaseUrl . '/%s', 'numberParams' => 1),
        self::EDIT_OPERATION => array('segmentPattern' => $recurringBillItemBaseUrl . '/%s', 'numberParams' => 1),
        self::GET_LIST_OPERATION => array('segmentPattern' => $recurringBillItemBaseUrl, 'numberParams' => 0),
        self::DELETE_OPERATION => array('segmentPattern' => $recurringBillItemBaseUrl . '/%s', 'numberParams' => 1));

    $this->urlInfo = array(self::PLAN_ENTITY => $planUrlInfo,
        self::CUSTOMER_ENTITY => $customerUrlInfo,
        self::CREDIT_CARD_ENTITY => $creditCardsUrlInfo,
        self::SUBSCRIPTIONS_ENTITY => $subscriptionsUrlInfo,
        self::RECURRING_BILL_ENTITY => $recurringBillUrlInfo,
        self::RECURRING_BILL_ITEM_ENTITY => $recurringBillItemUrlInfo,
        self::BANK_ACCOUNT_ENTITY => $bankAccountUrlInfo
    );
  }

  /**
   * return a instance of this class
   * @return PayUSubscriptionsUrlResolver
   */
  public static function getInstance() {
    if (!self::$instancia instanceof self) {
      self::$instancia = new self;
    }
    return self::$instancia;
  }

}
