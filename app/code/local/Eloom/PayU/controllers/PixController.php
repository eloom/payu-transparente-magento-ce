<?php

##eloom.licenca##

class Eloom_PayU_PixController extends Mage_Core_Controller_Front_Action {

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
      $response = Mage::getModel('eloom_payu/pix_request')->generatePaymentRequest($order);

      $additionalData = new stdClass();
      $additionalData->payuOrderId = $response->transactionResponse->orderId;
      if (isset($response->transactionResponse->extraParameters)) {
        $additionalData->expirationDate = $response->transactionResponse->extraParameters->EXPIRATION_DATE;
        $additionalData->qrCodeEmv = $response->transactionResponse->extraParameters->QRCODE_EMV;
        $additionalData->qrCodeImageBase64 = $response->transactionResponse->extraParameters->QRCODE_IMAGE_BASE64;
      }

			$order->getPayment()->setAdditionalData(json_encode($additionalData));
			$order->getPayment()->setCcStatus($response->transactionResponse->state);
			$order->getPayment()->setLastTransId($response->transactionResponse->transactionId);
      $order->getPayment()->save();

			$state = $response->transactionResponse->state;
			$status = Eloom_PayU_Enum_Transaction_State::getStatus($state);
      Mage::dispatchEvent('eloom_payu_process_transaction', array('order' => $order, 'status' => $status));

      switch ($state) {
        case Eloom_PayU_Enum_Transaction_State::PENDING:
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
