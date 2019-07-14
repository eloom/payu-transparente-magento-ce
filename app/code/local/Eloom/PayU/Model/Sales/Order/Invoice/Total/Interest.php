<?php

##eloom.licenca##

class Eloom_PayU_Model_Sales_Order_Invoice_Total_Interest extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

  protected $_code = 'eloom_payu_interest';

  public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
    parent::collect($invoice);
    $order = $invoice->getOrder();
    $baseTotalInterestAmount = $order->getPayuBaseInterestAmount();
    $totalInterestAmount = Mage::app()->getStore()->convertPrice($baseTotalInterestAmount);

    $invoice->setPayuInterestAmount($totalInterestAmount);
    $invoice->setPayuBaseInterestAmount($baseTotalInterestAmount);

    $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getPayuInterestAmount());
    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getPayuBaseInterestAmount());

    return $this;
  }

}
