<?php

##eloom.licenca##

class Eloom_PayU_Model_Sales_Quote_Address_Total_Interest extends Mage_Sales_Model_Quote_Address_Total_Abstract {

  protected $_code = 'eloom_payu_interest';

  public function collect(Mage_Sales_Model_Quote_Address $address) {
    parent::collect($address);

    $this->_setAmount(0);
    $this->_setBaseAmount(0);
    $address->setPayuInterestAmount(0);
    $address->setPayuBaseInterestAmount(0);

    $items = $this->_getAddressItems($address);
    if (!count($items)) {
      return $this;
    }

    $interest = Mage::getSingleton('eloom_payu/interest');
    if ($interest->canApply($address)) {
      $paymentInterest = $interest->getInterest();
			$store = $address->getQuote()->getStore();

			$shippingAmount = $address->getShippingAmount();
      $amount = ($paymentInterest->baseSubtotalWithDiscount + $paymentInterest->baseTax + $shippingAmount);

      $installmentValue = Mage::helper('eloom_payu/math')->calculatePayment($amount, $paymentInterest->getTotalPercent() / 100, $paymentInterest->getInstallment());
			$baseTotalInterestAmount = ($installmentValue * $paymentInterest->getInstallment()) - $amount;
			$baseTotalInterestAmount = $store->roundPrice($baseTotalInterestAmount);

      $totalInterestAmount = Mage::helper('directory')->currencyConvert($baseTotalInterestAmount, $paymentInterest->baseCurrencyCode);

			$address->setPayuInterestAmount($totalInterestAmount);
      $address->setPayuBaseInterestAmount($baseTotalInterestAmount);

      $address->setGrandTotal($address->getGrandTotal() + $address->getPayuInterestAmount());
      $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getPayuBaseInterestAmount());
    }

    return $this;
  }

  public function fetch(Mage_Sales_Model_Quote_Address $address) {
    $amount = $address->getPayuInterestAmount();
    if ($amount != 0) {
      $address->addTotal(array('code' => $this->getCode(),
          'title' => Mage::helper('eloom_payu')->__('Interest'),
          'value' => $amount
      ));
    }
    return $this;
  }

}
