<?php

/**
 *
 * Utility class to process parameters and send requests
 * over subscription service
 *
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 17/12/2013
 *
 */
class Eloom_PayU_Util_PayUSubscriptionsRequestUtil extends Eloom_PayU_Util_CommonRequestUtil {

  /**
   * Build a subscription request
   * @param array $parameters
   * @return stdClass with the subscriptionrequest built
   */
  public static function buildSubscription($parameters, $existParamBankAccount = FALSE, $existParamCreditCard = FALSE, $edit = FALSE) {
    $subscription = new stdClass();

    if ($edit == TRUE) {
      if ($existParamBankAccount == TRUE) {
        //In edition mode set the 'bankAccountId' property in the subscription
        $subscription->bankAccountId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
      }
      if ($existParamCreditCard == TRUE) {
        $creditCard = self::buildCreditCardForSubscription($parameters);
        if (isset($creditCard)) {
          //In edition mode set the 'creditCard' object in the subscription
          $subscription->creditCard = $creditCard;
        }
      }
    } else {

      $subscription->trialDays = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TRIAL_DAYS);
      $subscription->quantity = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::QUANTITY);
      $subscription->installments = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::INSTALLMENTS_NUMBER);

      $customer = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCustomer($parameters);

      //creates the credit card object and associate to the customer
      if ($existParamCreditCard == TRUE) {
        $creditCard = self::buildCreditCardForSubscription($parameters);
        if (isset($creditCard)) {
          $creditCards = array($creditCard);
          $customer->creditCards = $creditCards;
        }
      }

      //creates the Bank Account object and associate to the customer
      if ($existParamBankAccount == TRUE) {
        $bankAccount = self::buildBankAccountForSubscription($parameters);

        if (isset($bankAccount)) {
          $bankAccounts = array($bankAccount);
          $customer->bankAccounts = $bankAccounts;
        }
      }

      $subscription->customer = $customer;
      $subscription->plan = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscriptionPlan($parameters);
      $subscription->plan->id = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_ID);

      $termsAndConditionsAcepted = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TERMS_AND_CONDITIONS_ACEPTED);
      if (isset($termsAndConditionsAcepted)) {
        $subscription->termsAndConditionsAcepted = $termsAndConditionsAcepted;
      }
    }
    return $subscription;
  }

  /**
   * Build the Credit card object for subscription
   * @param array $parameters
   */
  protected static function buildCreditCardForSubscription($parameters) {
    $creditCard = null;
    $tokenId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    if (!isset($tokenId)) {
      $creditCard = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildCreditCard($parameters);
      $creditCard->customerId = NULL;
    } else {
      $creditCard = new stdClass();
      $creditCard->token = $tokenId;
      $creditCard->address = NULL;
    }
    return $creditCard;
  }

  /**
   * Build the Credit card object for subscription
   * @param array $parameters
   */
  protected static function buildBankAccountForSubscription($parameters) {
    $bankAccountId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
    if (!isset($bankAccountId)) {
      $bankAccount = Eloom_PayU_Util_RequestPaymentsUtil::buildBankAccountRequest($parameters);
      $bankAccount->customerId = NULL;
    } else {
      $bankAccount = new stdClass();
      $bankAccount->id = $bankAccountId;
    }
    return $bankAccount;
  }

  /**
   * Build a subscription plan request
   * @param array $parameters
   * @return stdClass with the subscription plan request built
   */
  public static function buildSubscriptionPlan($parameters) {

    $subscriptionPlan = new stdClass();

    $subscriptionPlan->accountId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ACCOUNT_ID);
    $subscriptionPlan->planCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_CODE);
    $subscriptionPlan->description = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_DESCRIPTION);
    $subscriptionPlan->interval = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_INTERVAL);
    $subscriptionPlan->intervalCount = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_INTERVAL_COUNT);
    $subscriptionPlan->trialDays = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_TRIAL_PERIOD_DAYS);
    $subscriptionPlan->maxPaymentsAllowed = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_MAX_PAYMENTS);
    $subscriptionPlan->paymentAttemptsDelay = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_ATTEMPTS_DELAY);
    $subscriptionPlan->maxPaymentAttempts = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_MAX_PAYMENT_ATTEMPTS);
    $subscriptionPlan->maxPendingPayments = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_MAX_PENDING_PAYMENTS);


    $planCurrency = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_CURRENCY);
    $planValue = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_VALUE);
    $planTaxValue = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_TAX);
    $planTaxReturnBase = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_TAX_RETURN_BASE);

    $subscriptionPlan->additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildSubscriptionPlanAdditionalValues($planCurrency, $planValue, $planTaxValue, $planTaxReturnBase);

    return $subscriptionPlan;
  }

  /**
   * Build a customer request
   * @param array $parameters
   * @return stdClass with the customer request built
   */
  public static function buildCustomer($parameters) {

    $customer = new stdClass();
    $customer->fullName = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_NAME);
    $customer->email = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_EMAIL);
    $customer->id = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);
    return $customer;
  }

  /**
   * Build a credit card request
   * @param array $parameters
   * @return stdClass with the credit card request built
   */
  public static function buildCreditCard($parameters) {

    $creditCard = new stdClass();
    $creditCard->token = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    $creditCard->customerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);

    $creditCard->number = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER);
    $creditCard->name = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_NAME);
    $creditCard->type = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD);
    $creditCard->document = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_DOCUMENT);

    $creditCard->address = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildAddress($parameters);


    $expirationDate = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE);

    if (isset($expirationDate)) {
      Eloom_PayU_Util_PayUSubscriptionsRequestUtil::isValidDate($expirationDate, Eloom_PayU_Api_PayUConfig::PAYU_SECONDARY_DATE_FORMAT, Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE);
      $expirationDateSplit = explode('/', $expirationDate);
      $creditCard->expYear = $expirationDateSplit[0];
      $creditCard->expMonth = $expirationDateSplit[1];
    }

    return $creditCard;
  }

  /**
   * Build an address object to be added to payment request
   * @param array $parameters
   * @return return an address
   */
  private static function buildAddress($parameters) {
    $address = new stdClass();
    $address->city = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_CITY);
    $address->country = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_COUNTRY);
    $address->phone = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_PHONE);
    $address->postalCode = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE);
    $address->state = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STATE);
    $address->line1 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STREET);
    $address->line2 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STREET_2);
    $address->line3 = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PAYER_STREET_3);
    return $address;
  }

  /**
   * Build order additional values
   * @param string $txCurrency
   * @param string $txValue
   * @param string $taxValue
   * @param string $taxReturnBase
   * @return the a map with the valid additional values
   *
   */
  private static function buildSubscriptionPlanAdditionalValues($planCurrency, $planValue, $planTaxValue, $planTaxReturnBase) {

    $additionalValues = null;

    $additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::addAdditionalValue($additionalValues, $planCurrency, Eloom_PayU_Api_PayUKeyMapName::PLAN_VALUE, $planValue);

    $additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::addAdditionalValue($additionalValues, $planCurrency, Eloom_PayU_Api_PayUKeyMapName::PLAN_TAX, $planTaxValue);

    $additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::addAdditionalValue($additionalValues, $planCurrency, Eloom_PayU_Api_PayUKeyMapName::PLAN_TAX_RETURN_BASE, $planTaxReturnBase);

    return $additionalValues;
  }

  /**
   * Build item additional values
   * @param string $txCurrency
   * @param string $txValue
   * @param string $taxValue
   * @param string $taxReturnBase
   * @return the a map with the valid additional values
   *
   */
  private static function buildItemAdditionalValues($currency, $value, $taxValue, $taxReturnBase) {

    $additionalValues = null;

    $additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::addAdditionalValue($additionalValues, $currency, Eloom_PayU_Api_PayUKeyMapName::ITEM_VALUE, $value);

    $additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::addAdditionalValue($additionalValues, $currency, Eloom_PayU_Api_PayUKeyMapName::ITEM_TAX, $taxValue);

    $additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::addAdditionalValue($additionalValues, $currency, Eloom_PayU_Api_PayUKeyMapName::ITEM_TAX_RETURN_BASE, $taxReturnBase);

    return $additionalValues;
  }

  /**
   * Build a additional value and add it to container object
   * @param Object $container
   * @param string $txCurrency the code of the transaction currency
   * @param string $txValueName the parameter name
   * @param string $value the parameter value
   * @return $container whith the valid additional values  added
   *
   */
  private static function addAdditionalValue($container, $txCurrency, $txValueName, $value) {

    if ($value != null && $txCurrency != null) {
      if (!isset($container)) {
        $container = array();
      }
      $additionalValue = new stdClass();
      $additionalValue->name = $txValueName;
      $additionalValue->value = $value;
      $additionalValue->currency = $txCurrency;
      array_push($container, $additionalValue);
    }

    return $container;
  }

  /**
   * Build a PayUHttpRequestInfo with the information to the request
   * @param string $urlSegment the url to complete the url to the api request
   * @param string $lang the language to be sent in header information
   * @param string $requestMethod the request method to be used
   * @return PayUHttpRequestInfo the object build
   */
  public static function buildHttpRequestInfo($urlSegment, $lang, $requestMethod) {
    if (!isset($lang)) {
      $lang = Eloom_PayU_PayU::$language;
    }

    $payUHttpRequestInfo = new Eloom_PayU_Api_PayUHttpRequestInfo(Eloom_PayU_Api_Environment::SUBSCRIPTIONS_API, $requestMethod, $urlSegment);

    $payUHttpRequestInfo->lang = $lang;
    $payUHttpRequestInfo->user = Eloom_PayU_PayU::$apiLogin;
    $payUHttpRequestInfo->password = Eloom_PayU_PayU::$apiKey;

    return $payUHttpRequestInfo;
  }

  /**
   * Build a recurring bill item request
   * @param array $parameters
   * @return stdClass with the recurring bill item request built
   */
  public static function buildRecurringBillItem($parameters) {
    $recurringBillItem = new stdClass();
    $recurringBillItem->id = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::RECURRING_BILL_ITEM_ID);
    $recurringBillItem->description = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::DESCRIPTION);
    $recurringBillItem->subscriptionId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::SUBSCRIPTION_ID);


    $currency = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CURRENCY);
    $itemValue = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ITEM_VALUE);
    $itemTaxValue = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ITEM_TAX);
    $itemTaxReturnBaseValue = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::ITEM_TAX_RETURN_BASE);

    $recurringBillItem->additionalValues = Eloom_PayU_Util_PayUSubscriptionsRequestUtil::buildItemAdditionalValues(
                    $currency, $itemValue, $itemTaxValue, $itemTaxReturnBaseValue);
    return $recurringBillItem;
  }

  /**
   * Validate a subscription plan
   * @param array $parameters
   * @throws InvalidParameterException 
   */
  public static function validateSubscriptionPlan($parameters) {

    $required = array(Eloom_PayU_Util_PayUParameters::PLAN_INTERVAL, Eloom_PayU_Util_PayUParameters::PLAN_CODE,
        Eloom_PayU_Util_PayUParameters::PLAN_INTERVAL_COUNT, Eloom_PayU_Util_PayUParameters::PLAN_CURRENCY,
        Eloom_PayU_Util_PayUParameters::PLAN_VALUE, Eloom_PayU_Util_PayUParameters::ACCOUNT_ID,
        Eloom_PayU_Util_PayUParameters::PLAN_ATTEMPTS_DELAY, Eloom_PayU_Util_PayUParameters::PLAN_DESCRIPTION,
        Eloom_PayU_Util_PayUParameters::PLAN_MAX_PAYMENTS);

    $planId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::PLAN_ID);

    if (isset($planId)) {
      $invalid = $required;
      self::validateParameters($parameters, NULL, $invalid);
    } else {
      self::validateParameters($parameters, $required);
    }
  }

  /**
   * Validate a customer in subscription request
   * @param array $parameters
   * @throws InvalidParameterException 
   */
  public static function validateCustomerToSubscription($parameters, $edit = FALSE) {

    $customerId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_ID);

    if (isset($customerId) || $edit == TRUE) {
      $invalid = array(Eloom_PayU_Util_PayUParameters::CUSTOMER_EMAIL, Eloom_PayU_Util_PayUParameters::CUSTOMER_NAME);
      self::validateParameters($parameters, NULL, $invalid);
    } else {
      Eloom_PayU_Util_PayUSubscriptionsRequestUtil::validateCustomer($parameters, FALSE);
    }
  }

  /**
   * Validate a customer 
   * @param array $parameters
   * @throws InvalidParameterException
   */
  public static function validateCustomer($parameters, $edit = FALSE) {

    $customerEmail = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_EMAIL);
    $customerName = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::CUSTOMER_NAME);

    if ($edit) {
      self::validateParameters($parameters, array(Eloom_PayU_Util_PayUParameters::CUSTOMER_ID));
    }

    if (!isset($customerEmail) && !isset($customerName)) {
      throw new InvalidArgumentException('You must send the [' . Eloom_PayU_Util_PayUParameters::CUSTOMER_EMAIL .
      '] or [' . Eloom_PayU_Util_PayUParameters::CUSTOMER_NAME . '] value');
    }
  }

  /**
   * Validate a Credit Card or Token 
   * @param array $parameters
   * @throws InvalidParameterException
   */
  public static function validateCreditCard($parameters) {
    $tokenId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::TOKEN_ID);
    if (isset($tokenId)) {
      $required = array(Eloom_PayU_Util_PayUParameters::TOKEN_ID);
      $invalid = array(Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER,
          Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE, Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD,
          Eloom_PayU_Util_PayUParameters::PAYER_NAME, Eloom_PayU_Util_PayUParameters::PAYER_STREET,
          Eloom_PayU_Util_PayUParameters::PAYER_STREET_2, Eloom_PayU_Util_PayUParameters::PAYER_STREET_3,
          Eloom_PayU_Util_PayUParameters::PAYER_CITY, Eloom_PayU_Util_PayUParameters::PAYER_STATE,
          Eloom_PayU_Util_PayUParameters::PAYER_COUNTRY, Eloom_PayU_Util_PayUParameters::PAYER_POSTAL_CODE,
          Eloom_PayU_Util_PayUParameters::PAYER_PHONE);

      self::validateParameters($parameters, $required, $invalid);
    } else {
      $required = array(Eloom_PayU_Util_PayUParameters::CREDIT_CARD_NUMBER,
          Eloom_PayU_Util_PayUParameters::PAYER_NAME,
          Eloom_PayU_Util_PayUParameters::PAYMENT_METHOD,
          Eloom_PayU_Util_PayUParameters::CREDIT_CARD_EXPIRATION_DATE);

      self::validateParameters($parameters, $required);
    }
  }

  /**
   * Validate a Bank Account
   * @param array $parameters
   * @throws InvalidParameterException
   */
  public static function validateBankAccount($parameters) {
    $bankAccountId = self::getParameter($parameters, Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
    if (isset($bankAccountId)) {
      $required = array(Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ID);
      $invalid = array(Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER_TYPE,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_CUSTOMER_NAME,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_AGENCY_NUMBER,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_AGENCY_DIGIT,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_ACCOUNT_DIGIT,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_NUMBER,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_BANK_NAME,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_TYPE,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_STATE);
      self::validateParameters($parameters, $required, $invalid);
    } else {
      $required = array(Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_CUSTOMER_NAME,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_DOCUMENT_NUMBER_TYPE,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_BANK_NAME,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_TYPE,
          Eloom_PayU_Util_PayUParameters::BANK_ACCOUNT_NUMBER,
          Eloom_PayU_Util_PayUParameters::COUNTRY);

      self::validateParameters($parameters, $required);
    }
  }

}
