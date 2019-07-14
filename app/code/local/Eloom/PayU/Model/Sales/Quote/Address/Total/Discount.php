<?php

##eloom.licenca##

class Eloom_PayU_Model_Sales_Quote_Address_Total_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract {

  protected $_code = 'eloom_payu_discount';

  public function collect(Mage_Sales_Model_Quote_Address $address) {
    parent::collect($address);
    $this->_setAmount(0);
    $this->_setBaseAmount(0);
    $address->setPayuDiscountAmount(0);
    $address->setPayuBaseDiscountAmount(0);

    $items = $this->_getAddressItems($address);
    if (!count($items)) {
      return $this;
    }

    $discount = Mage::getSingleton('eloom_payu/discount');
    if ($discount->canApply($address)) {
      $paymentDiscount = $discount->getDiscount();

      $baseTotalDiscountAmount = (($paymentDiscount->baseSubtotalWithDiscount + $paymentDiscount->baseTax) * $paymentDiscount->totalPercent) / 100;
      $baseTotalDiscountAmount = Mage::helper('eloom_payu')->truncate($baseTotalDiscountAmount, 2);

      $totalDiscountAmount = Mage::helper('directory')->currencyConvert($baseTotalDiscountAmount, $paymentDiscount->baseCurrencyCode);
      $address->setPayuDiscountAmount(-$totalDiscountAmount);
      $address->setPayuBaseDiscountAmount(-$baseTotalDiscountAmount);
      $address->setGrandTotal($address->getGrandTotal() + $address->getPayuDiscountAmount());
      $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getPayuBaseDiscountAmount());
    }

    return $this;
  }

  public function fetch(Mage_Sales_Model_Quote_Address $address) {
    $amount = $address->getPayuDiscountAmount();
    if ($amount < 0) {
      $address->addTotal(array('code' => $this->getCode(),
          'title' => Mage::helper('eloom_payu')->__('Discount'),
          'value' => $amount
      ));
    }
    return $this;
  }

}
