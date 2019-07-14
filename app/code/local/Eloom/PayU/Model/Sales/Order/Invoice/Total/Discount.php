<?php

##eloom.licenca##

class Eloom_PayU_Model_Sales_Order_Invoice_Total_Discount extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

  protected $_code = 'eloom_payu_discount';

  public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
    parent::collect($invoice);
    $order = $invoice->getOrder();
    $baseTotalDiscountAmount = $order->getPayuBaseDiscountAmount();
    $totalDiscountAmount = Mage::app()->getStore()->convertPrice($baseTotalDiscountAmount);

    $invoice->setPayuDiscountAmount($totalDiscountAmount);
    $invoice->setPayuBaseDiscountAmount($baseTotalDiscountAmount);

    $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getPayuDiscountAmount());
    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getPayuBaseDiscountAmount());

    return $this;
  }

}
