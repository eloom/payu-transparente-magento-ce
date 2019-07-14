<?php

class Eloom_PayU_Api_Environment {

  const MERCHANT_ID_TEST  = '508029';
  
  const API_KEY_TEST = '4Vj8eK4rloUd272L48hsrarnUA';
  
  const API_LOGIN_TEST = 'pRRXKOl8ikMmt9u';
  
  const ACCOUNT_ID_TEST = '512327';
  
  const PUBLIC_KEY_TEST = 'PKaC6H4cEDJD919n705L544kSU';
      
  const PRODUCTION = 'production';
  
  const TEST = 'test';
  
  /** name for payments api */
  const PAYMENTS_API = "PAYMENTS_API";

  /** name for reports api */
  const REPORTS_API = "REPORTS_API";

  /** name for subscriptions api */
  const SUBSCRIPTIONS_API = "SUBSCRIPTIONS_API";

  /** url used to payments service api  */
  private static $paymentsUrl = "https://api.payulatam.com/payments-api/4.0/service.cgi";

  /** url used to reports service api  */
  private static $reportsUrl = "https://api.payulatam.com/reports-api/4.0/service.cgi";

  /** url used to subscriptions service api  */
  private static $subscriptionsUrl = "https://api.payulatam.com/payments-api/rest/v4.3";

  /** url used to subscriptions service api  if the test variable is true */
  private static $paymentsTestUrl = "https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi";

  /** url used to reports service api  if the test variable is true */
  private static $reportsTestUrl = "https://sandbox.api.payulatam.com/reports-api/4.0/service.cgi";

  /** url used to subscriptions service api  if the test variable is true */
  private static $subscriptionsTestUrl = "https://sandbox.api.payulatam.com/payments-api/rest/v4.3";

  /** url used to subscriptions service api  if is not null */
  private static $paymentsCustomUrl = null;

  /** url used to reports service api  if is not null */
  private static $reportsCustomUrl = null;

  /** url used to subscriptions service api  if is not null */
  private static $subscriptionsCustomUrl = null;

  /** if this is true the test url is used to request */
  static $test = false;

  /**
   * Gets the suitable url to the api sent
   * @param  the api to get the url it can have three values 
   * PAYMENTS_API, REPORTS_API, SUBSCRIPTIONS_API
   * @throws InvalidArgumentException if the api value doesn't have a valid value
   * @return string with the url  
   */
  static function getApiUrl($api) {
    switch ($api) {
      case self::PAYMENTS_API:
        return self::getPaymentsUrl();
      case self::REPORTS_API:
        return self::getReportsUrl();
      case self::SUBSCRIPTIONS_API:
        return self::getSubscriptionUrl();
      default:
        throw new InvalidArgumentException(sprintf('the api argument [%s] is invalid please check the Environment class ', $api));
    }
  }

  /**
   * Returns the payments url
   * @return  the paymets url configured
   */
  static function getPaymentsUrl() {
    if (isset(self::$paymentsCustomUrl)) {
      return self::$paymentsCustomUrl;
    }

    if (!self::$test) {
      return self::$paymentsUrl;
    } else {
      return self::$paymentsTestUrl;
    }
  }

  /**
   * Returns the reports url
   * @return the reports url
   */
  static function getReportsUrl() {
    if (self::$reportsCustomUrl != null) {
      return self::$reportsCustomUrl;
    }

    if (!self::$test) {
      return self::$reportsUrl;
    } else {
      return self::$reportsTestUrl;
    }
  }

  /**
   * Returns the subscriptions url
   * @return the subscriptions url
   */
  static function getSubscriptionUrl() {
    if (self::$subscriptionsCustomUrl != null) {
      return self::$subscriptionsCustomUrl;
    }

    if (!self::$test) {
      return self::$subscriptionsUrl;
    } else {
      return self::$subscriptionsTestUrl;
    }
  }

  /**
   * Set a  custom payments url
   * @param string $paymentsCustomUrl
   */
  static function setPaymentsCustomUrl($paymentsCustomUrl) {
    self::$paymentsCustomUrl = $paymentsCustomUrl;
  }

  /**
   * Set a custom reports url
   * @param string $reportsCustomUrl
   */
  static function setReportsCustomUrl($reportsCustomUrl) {
    self::$reportsCustomUrl = $reportsCustomUrl;
  }

  /**
   * Set a custom subscriptions url
   * @param string $subscriptionsCustomUrl
   */
  static function setSubscriptionsCustomUrl($subscriptionsCustomUrl) {
    self::$subscriptionsCustomUrl = $subscriptionsCustomUrl;
  }

  /**
   * Validates the Environment before process any request
   * @throws ErrorException
   */
  static function validate() {
    if (version_compare(PHP_VERSION, '5.2.1', '<')) {
      throw new ErrorException('PHP version >= 5.2.1 required');
    }


    $requiredExtensions = array('curl', 'xml', 'mbstring', 'json');
    foreach ($requiredExtensions AS $ext) {
      if (!extension_loaded($ext)) {
        throw new ErrorException('The Payu library requires the ' . $ext . ' extension.');
      }
    }
  }
  
}
