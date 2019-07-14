<?php

class Eloom_PayU_Api_PayUConfig {

  /** the payu date format */
  const PAYU_DATE_FORMAT = 'Y-m-d\TH:i:s'; //DateTime::ISO8601

  /** the payu credit card secondary date format */
  const PAYU_SECONDARY_DATE_FORMAT = 'Y/m';

  /** the payu birhday format */
  const PAYU_DAY_FORMAT = 'Y-m-d';

  /**
   * if remove null values over object sent in a request api
   * the null values over response will be removed too
   */
  const REMOVE_NULL_OVER_REQUEST = TRUE;

}
