<?php

class Eloom_PayU_Util_SignatureUtil {

  /** MD5 algorithm used */
  const MD5_ALGORITHM = "md5";

  /** SHA algorithm used */
  const SHA_ALGORITHM = "sha";

  /** Decimal format with no decimals */
  const DECIMAL_POINT = ".";

  /** Decimal format with one decimal */
  const THOUSANDS_SEPARATOR = "";

  /** Decimal format with two decimals */
  const DECIMALS = 0;

  /**
   * 
   * @param Object $order the order to be sent in a transaction request
   * @param string $merchantId the identifier of merchant
   * @param string $key authentication key
   * @param string $algorithm the to use
   * @throws IllegalArgumentException
   * @return the signature built
   */
  static function buildSignature($order, $merchantId, $key, $algorithm) {
    $message = self::buildMessage($order, $merchantId, $key);

    if (self::MD5_ALGORITHM == $algorithm) {
      return md5($message);
    } else if (self::SHA_ALGORITHM == $algorithm) {
      return sha1($message);
    } else {
      throw new InvalidArgumentException("Could not create signature. Invalid algoritm");
    }
  }

  /**
   * Build a plain signature
   * @param Object $order the order to be sent in a transaction request
   * @param string $merchantId the identifier of merchant
   * @param string $key authentication key
   * @return the plain message
   */
  static function buildMessage($order, $merchantId, $key) {
    self::validateOrder($order, $merchantId);
    $txValueName = Eloom_PayU_Api_PayUKeyMapName::TX_VALUE;
    $referenceCode = $order->referenceCode;
    $value = $order->additionalValues->$txValueName->value;

    $floatValue = floatval($value);
    $valueRounded = round($floatValue, self::DECIMALS, PHP_ROUND_HALF_EVEN);
    $valueFormatted = number_format($valueRounded, self::DECIMALS, self::DECIMAL_POINT, self::THOUSANDS_SEPARATOR);
    $currency = $order->additionalValues->$txValueName->currency;


    $message = $key . '~' . $merchantId . '~' . $referenceCode . '~' . $valueFormatted . '~' . $currency;

    return $message;
  }

  /**
   * Validates the order values before to create a request signature
   * @param Object $order the order to be sent in a transaction request
   * @param string $merchantId the identifier of merchant
   * @throws InvalidArgumentException
   */
  static function validateOrder($order, $merchantId) {
    $txValueName = Eloom_PayU_Api_PayUKeyMapName::TX_VALUE;
    if (!isset($merchantId)) {

      throw new InvalidArgumentException("The merchant id may not be null");
    } else if (!isset($order->referenceCode)) {

      throw new InvalidArgumentException("The reference code may not be null");
    } else if (!isset($order->additionalValues->$txValueName)) {

      throw new InvalidArgumentException("The order additional value TX_VALUE may not be null");
    } else if (!isset($order->additionalValues->$txValueName->currency)) {

      throw new InvalidArgumentException("The order currency may not be null");
    } else if (!isset($order->additionalValues->$txValueName->value)) {

      throw new InvalidArgumentException("The order value may not be null");
    }
  }

}
