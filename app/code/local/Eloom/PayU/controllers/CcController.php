<?php

##eloom.licenca##

class Eloom_PayU_CcController extends Mage_Core_Controller_Front_Action {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
    parent::_construct();
  }

  /**
   * Send expire header to ajax response
   *
   */
  protected function _expireAjax() {
    if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
      $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
      exit;
    }
  }

  public function paymentAction() {
    $session = Mage::getSingleton('checkout/session');
    $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
    if ($order->getId() == '') {
      $this->_redirect('checkout/onepage/failure', array('_secure' => true));
      return;
    }
    try {
      // envia a requisição
      $response = Mage::getModel('eloom_payu/cc_request')->generatePaymentRequest($order);
			$this->logger->info($response);

			$additionalData = json_decode($order->getPayment()->getAdditionalData());
			$additionalData->creditCardNumber = null;
			$additionalData->creditCardCvc = null;

			$order->getPayment()->setCcStatus($response->transactionResponse->state);
			if(isset($response->transactionResponse->orderId)) {
				$additionalData->payuOrderId = $response->transactionResponse->orderId;
			}
			$order->getPayment()->setAdditionalData(json_encode($additionalData));
			if(isset($response->transactionResponse->transactionId)) {
				$order->getPayment()->setLastTransId($response->transactionResponse->transactionId);
			}
			$order->getPayment()->setCcDebugResponseBody('');
			$order->getPayment()->save();

			$state = $response->transactionResponse->state;
			$status = Eloom_PayU_Enum_Transaction_State::getStatus($state);
      Mage::dispatchEvent('eloom_payu_process_transaction', array('order' => $order, 'status' => $status));

      switch ($state) {
        case Eloom_PayU_Enum_Transaction_State::PENDING:
        case Eloom_PayU_Enum_Transaction_State::APPROVED:
          Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastSuccessQuoteId(true);
          if ($order->getCanSendNewEmailFlag() && !$order->getEmailSent()) {
            try {
              $order->sendNewOrderEmail();
            } catch (Exception $e) {
              $this->logger->fatal($e->getTraceAsString());
            }
          }
          $this->_redirect('checkout/onepage/success', array('_secure' => true));
          break;

        default:
          $error = new Eloom_PayU_Errors($response->transactionResponse->responseCode);
          $errors = array($error->getMessage());
          $order->getPayment()->setCcDebugResponseBody(json_encode($errors));
          $order->getPayment()->save();

          Mage::dispatchEvent('eloom_payu_cancel_order', array('order' => $order, 'comment' => 'Falha no Pagamento.'));
          Mage::getSingleton('checkout/session')->setErrorMessage("<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");

          $this->_redirect('checkout/onepage/failure', array('_secure' => true));
          break;
      }
    } catch (Exception $e) {
      $this->logger->fatal($e->getCode() . ' - ' . $e->getMessage());
      $this->logger->fatal($e->getTraceAsString());

      $error = new Eloom_PayU_Errors($e->getMessage());
      $errors = array($error->getMessage());

      $order->getPayment()->setCcStatus(Eloom_PayU_Enum_Transaction_State::DECLINED);
      $order->getPayment()->setCcDebugResponseBody(json_encode($errors));
      $order->getPayment()->save();

      Mage::dispatchEvent('eloom_payu_cancel_order', array('order' => $order, 'comment' => 'Falha no Pagamento.'));

      Mage::getSingleton('checkout/session')->setErrorMessage("<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
      $this->_redirect('checkout/onepage/failure', array('_secure' => true));
    }
  }

}
