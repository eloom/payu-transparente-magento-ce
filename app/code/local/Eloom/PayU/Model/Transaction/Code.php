<?php

##eloom.licenca##

class Eloom_PayU_Model_Transaction_Code extends Mage_Core_Model_Abstract {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  public function synchronizeTransaction($accountId, $payment, $order) {
    if (empty($payment->getLastTransId())) {
      throw new InvalidArgumentException("Transação não encontrada.");
    }
    if ($order->isCanceled()) {
			if($this->logger->isDebugEnabled()) {
				$this->logger->debug(sprintf("Pedido [%s] está cancelado. Sistema irá cancelar o status do pagamento.", $order->getIncrementId()));
			}
      $payment->setCcStatus(Eloom_PayU_Enum_Transaction_State::CANCELLED);
      $payment->save();

      return true;
    }

    $parameters = array(
        Eloom_PayU_Util_PayUParameters::ACCOUNT_ID => $accountId,
        Eloom_PayU_Util_PayUParameters::REFERENCE_CODE => $order->getIncrementId()
    );
    $result = Eloom_PayU_PayUReports::getOrderDetailByReferenceCode($parameters);

    if (is_array($result)) {
      $result = $result[0];
    }

    if ($result->id) {
      $transaction = $result->transactions[0];
      $payuStatus = $result->status;
			$state = $transaction->transactionResponse->state;

			if($payuStatus == Eloom_PayU_Enum_Order_Status::DECLINED) {
				$state = Eloom_PayU_Enum_Transaction_State::DECLINED;
			}
			/**
			 * em boleto expirado não vem status, tem de setar na mão
			 */
			if($state == Eloom_PayU_Enum_Transaction_State::EXPIRED) {
				$payuStatus = Eloom_PayU_Enum_Order_Status::CANCELLED;
			}

      if ($state != Eloom_PayU_Enum_Transaction_State::PENDING) {
        $payment->setCcStatus($state);
        $payment->save();

        $order = Mage::getModel('sales/order')->load($payment->getParentId());
        Mage::dispatchEvent('eloom_payu_process_transaction', array('order' => $order, 'status' => $payuStatus));
      }
    }

    return true;
  }

}
