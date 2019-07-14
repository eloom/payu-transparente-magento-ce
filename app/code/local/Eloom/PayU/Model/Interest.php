<?php

##eloom.licenca##

class Eloom_PayU_Model_Interest extends Mage_Core_Model_Abstract {

  public function canApply($address) {
    $data = Mage::app()->getRequest()->getPost('payment', array());
    if (!count($data) || !isset($data['payu_cc_installments'])) {
      return false;
    }

    $currentPaymentMethod = null;

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    if ($sessionQuote->getPayment() != null && $sessionQuote->getPayment()->hasMethodInstance()) {
      $currentPaymentMethod = $sessionQuote->getPayment()->getMethodInstance()->getCode();
    } elseif (isset($data['method'])) {
      $currentPaymentMethod = $data['method'];
    }

    if ($currentPaymentMethod == 'eloom_payu_cc') {
			$arrayex = explode('-', $data['payu_cc_installments']);
			$installments = $arrayex[0];

			$config = Mage::helper('eloom_payu/config');
    	if ($installments != null && $installments > $config->getPaymentCcInstallmentsWithoutInterest()) {
				return true;
			}
    }

    return false;
  }

  public function getInterest() {
		$data = Mage::app()->getRequest()->getPost('payment', array());
		$installments = 1;
		$arrayex = explode('-', $data['payu_cc_installments']);
		if (isset($arrayex[0])) {
			$installments = $arrayex[0];
		}
		$interest = str_replace(',', '.', Mage::helper('eloom_payu/config')->getPaymentCcInterest());

    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$baseSubtotalWithDiscount = 0;
    $baseTax = 0;

    $quote = Mage::getSingleton('checkout/session')->getQuote();
    if ($quote->isVirtual()) {
      $address = $quote->getBillingAddress();
    } else {
      $address = $quote->getShippingAddress();
    }
    if ($address) {
			$baseSubtotalWithDiscount = $address->getBaseSubtotalWithDiscount();
			$baseTax = $address->getBaseTaxAmount();
    }

    return Eloom_PayU_Interest::getInstance($baseCurrencyCode, $interest, $baseSubtotalWithDiscount, $baseTax, $installments);
  }

  public function getModuleInterest($order) {
    return $order->getPayuInterestAmount();
  }

  public function getModuleBaseInterest($order) {
    return $order->getPayuBaseInterestAmount();
  }

  public function getModuleInterestCode() {
    return 'eloom_payu_interest';
  }

}
