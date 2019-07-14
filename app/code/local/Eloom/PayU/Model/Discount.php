<?php

##eloom.licenca##

class Eloom_PayU_Model_Discount extends Mage_Core_Model_Abstract {

  public function canApply($address) {
    $data = Mage::app()->getRequest()->getPost('payment', array());
    if (!count($data) || !isset($data['payu_cc_installments'])) {
      return false;
    }

    $currentPaymentMethod = null;
    $installments = $data['payu_cc_installments'];

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    if ($sessionQuote->getPayment() != null && $sessionQuote->getPayment()->hasMethodInstance()) {
      $currentPaymentMethod = $sessionQuote->getPayment()->getMethodInstance()->getCode();
    } elseif (isset($data['method'])) {
      $currentPaymentMethod = $data['method'];
    }

    if ($currentPaymentMethod == 'eloom_payu_cc' && $installments != null && $installments == 1) {
      return true;
    }

    return false;
  }

  public function getDiscount() {
    $discount = str_replace(',', '.', Mage::helper('eloom_payu/config')->getPaymentCcDiscount());
    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
    $baseSubtotalWithDiscount = 0;
    $baseTax = 0;

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    if ($sessionQuote->isVirtual()) {
      $address = $sessionQuote->getBillingAddress();
    } else {
      $address = $sessionQuote->getShippingAddress();
    }
    if ($address) {
      $baseSubtotalWithDiscount = $address->getBaseSubtotalWithDiscount();
      $baseTax = $address->getBaseTaxAmount();
    }

    return Eloom_PayU_Discount::getInstance($baseCurrencyCode, $discount, $baseSubtotalWithDiscount, $baseTax);
  }

  public function getModuleDiscount($order) {
    return $order->getPayuDiscountAmount();
  }

  public function getModuleBaseDiscount($order) {
    return $order->getPayuBaseDiscountAmount();
  }

  public function getModuleDiscountCode() {
    return 'eloom_payu_discount';
  }

}
