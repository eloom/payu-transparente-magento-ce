<?php

##eloom.licenca##

class Eloom_PayU_Model_Observer {

  public function cancelOrder(Varien_Event_Observer $observer) {
    $order = $observer->getEvent()->getOrder();
    $comment = $observer->getEvent()->getComment();

    Mage::getModel('eloom_payu/order')->cancel($order, $comment);
  }

  public function processTransaction(Varien_Event_Observer $observer) {
    $order = $observer->getEvent()->getOrder();
    $status = $observer->getEvent()->getStatus();

    Mage::getModel('eloom_payu/order')->processTransaction($order, $status);
  }

}
