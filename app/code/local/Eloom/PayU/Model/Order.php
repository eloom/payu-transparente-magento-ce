<?php

##eloom.licenca##

class Eloom_PayU_Model_Order extends Mage_Core_Model_Abstract {

  private $logger;
  private $_messages = array(
      'NEW' => 'Pedido criado na PayU.',
      'IN_PROGRESS' => 'Pedido está sendo processado na PayU.',
      'AUTHORIZED' => 'Pedido autorizado na PayU.',
      'CAPTURED' => 'Pedido capturado na PayU.',
      'CANCELLED' => 'Pedido cancelado na PayU.',
      'DECLINED' => 'Pedido rejeitado na PayU.',
      'REFUNDED' => 'Pedido com reembolso aprovado na PayU.'
  );

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  /**
   * Initialize order model instance
   *
   * @return Mage_Sales_Model_Order || false
   */
  protected function _initOrder($orderId) {
    $order = Mage::getModel('sales/order')->load($orderId);
    if (!$order->getId()) {
      throw new Exception($this->__('This order no longer exists.'));
    }
    return $order;
  }

  public function cancel($order, $comment) {
    try {
      if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
        return true;
      }
      $c = trim($comment);
      $order->cancel()
              ->addStatusHistoryComment($c)
              ->setIsVisibleOnFront(true)
              ->setIsCustomerNotified(true);

      if ($order->hasInvoices() != '') {
        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $this->__('Não foi possível retornar os produtos ao estoque pois há faturas relacionadas a este pedido.'), false);
      }
      $order->save();

      if ($order->getPayment()) {
        if (!$order->getPayment()->getCcStatus() || $order->getPayment()->getCcStatus() == '') {
          $order->getPayment()->setCcStatus(Eloom_PayU_Enum_Transaction_State::CANCELLED);
        }
        $order->getPayment()->save();
      }

      try {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE " . Mage::getConfig()->getTablePrefix() . "sales_flat_order_grid SET status = 'canceled' WHERE increment_id = " . $order->getIncrementId();
        $connection->query($sql);
      } catch (Exception $ex) {
        
      }
			if($this->logger->isDebugEnabled()) {
				$this->logger->debug(sprintf("Pedido %s [CANCELADO]. Motivo [%s]", $order->getIncrementId(), $c));
			}
    } catch (Exception $e) {
      try {
        $this->logger->info(sprintf("Forçando cancelamento do pedido [%s].", $order->getIncrementId()));
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE " . Mage::getConfig()->getTablePrefix() . "sales_flat_order SET state = 'canceled', status = 'canceled' WHERE increment_id = " . $order->getIncrementId();
        $connection->query($sql);

        $sql = "UPDATE " . Mage::getConfig()->getTablePrefix() . "sales_flat_order_grid SET status = 'canceled' WHERE increment_id = " . $order->getIncrementId();
        $connection->query($sql);
      } catch (Exception $ex) {
        
      }
    }
  }

  public function processTransaction($order, $status) {
    $comment = $this->_messages[$status];
		$config = Mage::helper('eloom_payu/config');
    switch ($status) {
      case Eloom_PayU_Enum_Order_Status::_NEW:
			case Eloom_PayU_Enum_Order_Status::IN_PROGRESS:
        $order->addStatusHistoryComment($comment, $config->getNewOrderStatus());
        $order->setIsVisibleOnFront(true);
        $order->save();
        $order->sendOrderUpdateEmail(true, $comment);
        break;

      case Eloom_PayU_Enum_Order_Status::AUTHORIZED:
				$order->addStatusHistoryComment($comment, null);
				$order->setIsVisibleOnFront(true);
				$order->save();
				$order->sendOrderUpdateEmail(true, $comment);
				break;

			case Eloom_PayU_Enum_Order_Status::CAPTURED:
				$status = $config->getApprovedOrderStatus();

        if ($order->getState() != $status) {
          $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
          $order->addStatusHistoryComment($comment, $status);
          $order->setIsVisibleOnFront(true);
          $order->save();
          $order->sendOrderUpdateEmail(true, $comment);

          // invoice
          if ($order->canInvoice()) {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
            $transactionSave->save();

            $invoice->getOrder()->setIsInProcess(true);
            $invoice->getOrder()->addStatusHistoryComment('Fatura gerada automaticamente.');
            $invoice->sendEmail(true, '');

            $order->save();
          }
        }
        break;

      case Eloom_PayU_Enum_Order_Status::DECLINED:
			case Eloom_PayU_Enum_Order_Status::CANCELLED:
        $this->cancel($order, $comment);
        break;

			case Eloom_PayU_Enum_Order_Status::REFUNDED:
				$order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true);
				$order->addStatusHistoryComment($comment, Mage_Sales_Model_Order::STATE_HOLDED);
				$order->setIsVisibleOnFront(true);
				$order->save();
				$order->sendOrderUpdateEmail(true, $comment);
				break;
    }
  }
}
