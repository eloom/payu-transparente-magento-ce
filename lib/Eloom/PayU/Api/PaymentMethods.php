<?php

/**
 * This class contains the payments methods 
 * availables in payu platform
 *
 * @author PayU Latam
 * @since 1.0.0
 * @version 1.0.0, 17/10/2013
 *
 */
class Eloom_PayU_Api_PaymentMethods {

  const VISA = 'VISA';
  const AMEX = 'AMEX';
  const DINERS = 'DINERS';
  const MASTERCARD = 'MASTERCARD';
  const DISCOVER = 'DISCOVER';
  const ELO = 'ELO';
  const PSE = 'PSE';
  const BALOTO = 'BALOTO';
  const EFECTY = 'EFECTY';
  const BCP = 'BCP';
  const SEVEN_ELEVEN = 'SEVEN_ELEVEN';
  const OXXO = 'OXXO';
  const BOLETO_BANCARIO = 'BOLETO_BANCARIO';
  const RAPIPAGO = 'RAPIPAGO';
  const PAGOFACIL = 'PAGOFACIL';
  const BAPRO = 'BAPRO';
  const COBRO_EXPRESS = 'COBRO_EXPRESS';
  const SERVIPAG = 'SERVIPAG';
  const BANK_REFERENCED = 'BANK_REFERENCED';
  const VISANET = 'VISANET';
  const RIPSA = 'RIPSA';
  const CODENSA = 'CODENSA';

  /**
   * payment methods availables in payu including its payment method type
   * 
   */
  private static $methods = array(
      self::VISA => array('name' => self::VISA, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD),
      self::AMEX => array('name' => self::AMEX, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD),
      self::DINERS => array('name' => self::DINERS, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD),
      self::MASTERCARD => array('name' => self::MASTERCARD, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD),
      self::DISCOVER => array('name' => self::DISCOVER, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD),
      self::ELO => array('name' => self::ELO, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD),
      self::PSE => array('name' => self::PSE, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::PSE),
      self::BALOTO => array('name' => self::BALOTO, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::EFECTY => array('name' => self::EFECTY, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::BCP => array('name' => self::BCP, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::SEVEN_ELEVEN => array('name' => self::SEVEN_ELEVEN, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::REFERENCED),
      self::OXXO => array('name' => self::OXXO, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::REFERENCED),
      self::BOLETO_BANCARIO => array('name' => self::BOLETO_BANCARIO, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::BOLETO_BANCARIO),
      self::RAPIPAGO => array('name' => self::RAPIPAGO, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::PAGOFACIL => array('name' => self::PAGOFACIL, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::BAPRO => array('name' => self::BAPRO, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH), 'BAPRO',
      self::COBRO_EXPRESS => array('name' => self::COBRO_EXPRESS, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::SERVIPAG => array('name' => self::SERVIPAG, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::BANK_REFERENCED => array('name' => self::BANK_REFERENCED, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::BANK_REFERENCED),
      self::VISANET => array('name' => self::VISANET, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD),
      self::RIPSA => array('name' => self::RIPSA, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CASH),
      self::CODENSA => array('name' => self::CODENSA, 'type' => Eloom_PayU_Api_PayUPaymentMethodType::CREDIT_CARD)
  );

  /**
   * Allowed cash payment methods available in the api
   */
  private static $allowedCashPaymentMethods = array(
      self::EFECTY,
      self::BALOTO,
      self::BCP,
      self::OXXO,
      self::RIPSA
  );

  /**
   * validates if a payment method exist in payu platform 
   * @param string $paymentMethod
   * @return true if the payment method exist false the otherwise
   */
  static function isValidPaymentMethod($paymentMethod) {
    return array_key_exists($paymentMethod, self::$methods);
  }

  /**
   * Returns the payment method info
   * @param string $paymentMethod
   * @return paymentMethod
   */
  static function getPaymentMethod($paymentMethod) {
    return self::$methods[$paymentMethod];
  }

  /**
   * verify if the cash payment method is valid to process payments
   * by api
   * @param string $paymentMethod
   * @return boolean
   */
  static function isAllowedCashPaymentMethod($paymentMethod) {
    return in_array($paymentMethod, self::$allowedCashPaymentMethods);
  }

}
