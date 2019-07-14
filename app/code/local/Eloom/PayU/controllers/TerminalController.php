<?php

##eloom.licenca##

class Eloom_PayU_TerminalController extends Mage_Core_Controller_Front_Action {

	const FAILURE = 'eloompayu/terminal/failure';

	const SUCCESS = 'eloompayu/terminal/success';

	private $logger;

	protected function _construct() {
		$this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
		parent::_construct();
	}

	public function indexAction() {
		$orderIncrementId = $this->getRequest()->getParam('id');
		$hash = $this->getRequest()->getParam('hash');

		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

		$state = $order->getState();
		if ($state != Mage_Sales_Model_Order::STATE_NEW) {
			Mage::getSingleton('core/session')->addNotice('Somente pedidos com status "Pendente" poderão ser pagos nesta modalidade.');
			$this->_redirect('checkout/cart');
			return;
		}
		Mage::getSingleton('eloom_payu/session')->setOrder($order);

		$helper = Mage::helper('eloom_payu');
		$config = Mage::helper('eloom_payu/config');

		$merchantId = $config->getMerchantId();
		$apiKey = $config->getApiKey();
		$amount = $order->getBaseGrandTotal();
		$currency = 'BRL';

		$signature = $helper->generateSignature($apiKey, $merchantId, $orderIncrementId, $currency);

		if ($signature != $hash) {
			Mage::getSingleton('core/session')->addNotice('A Hash recebida na URL não confere com a Hash gerada no pedido. Verifique se o link informado é igual ao que recebeu por email.');
			$this->_redirect('checkout/cart');
			return;
		}

		$this->loadLayout();
		$this->renderLayout();
	}

	public function savePaymentAction() {
		if (!$this->getRequest()->isPost()) {
			return;
		}

		$data = $this->getRequest()->getPost('payment', array());
		$data = new Varien_Object($data);
		$message = null;
		if ($data->getMethod() == 'terminal_cc') {
			$message = $this->processPaymentCc($data);
		} else if ($data->getMethod() == 'terminal_boleto') {
			$message = $this->processPaymentBoleto();
		}

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($message));
	}

	private function processPaymentBoleto() {
		$order = Mage::getSingleton('eloom_payu/session')->getOrder();
		$message = array();

		try {
			$response = Mage::getModel('eloom_payu/boleto_request')->generatePaymentRequest($order);

			/* link boleto */
			$additionalData = new stdClass();
			$additionalData->payuOrderId = $response->transactionResponse->orderId;
			if (isset($response->transactionResponse->extraParameters)) {
				$additionalData->paymentLink = $response->transactionResponse->extraParameters->URL_BOLETO_BANCARIO;
				$additionalData->barCode = $response->transactionResponse->extraParameters->BAR_CODE;
			}

			$order->getPayment()->setAdditionalData(json_encode($additionalData));
			$order->getPayment()->setCcStatus($response->transactionResponse->state);
			$order->getPayment()->setLastTransId($response->transactionResponse->transactionId);
			$order->getPayment()->setCcDebugResponseBody('');

			// calcular data para cancelar boleto
			$orderCreatedAt = $order->getCreatedAt();
			$config = Mage::helper('eloom_payu/config');
			$dayOfWeek = date("w", strtotime($orderCreatedAt));
			$incrementDays = null;

			switch($dayOfWeek) {
				case 5: // Sexta-Feira
					$incrementDays = $config->getBilletCancelOnFriday();
					break;

				case 6: // Sabado
					$incrementDays = $config->getBilletCancelOnSaturday();
					break;

				default:
					$incrementDays = $config->getBilletCancelOnSunday();
					break;
			}

			$totalDays = $config->getBilletExpiration() + $incrementDays;
			$cancellationDate = strftime("%Y-%m-%d %H:%M:%S", strtotime("$orderCreatedAt +$totalDays day"));
			$order->getPayment()->setBoletoCancellation($cancellationDate);
			$order->getPayment()->save();

			$state = $response->transactionResponse->state;
			$status = Eloom_PayU_Enum_Transaction_State::getStatus($state);
			Mage::dispatchEvent('eloom_payu_process_transaction', array('order' => $order, 'status' => $status));

			switch($state) {
				case Eloom_PayU_Enum_Transaction_State::PENDING:
					if ($order->getCanSendNewEmailFlag() && !$order->getEmailSent()) {
						try {
							$order->sendNewOrderEmail();
						} catch(Exception $e) {
							$this->logger->fatal($e->getTraceAsString());
						}
					}

					$message = array('url' => Mage::getBaseUrl(), 'message' => sprintf("<ul><li>%s</li><li>%s</li></ul>", $this->__('Your payment was processed by PayU'), $this->__('Billett will be sent to your email.')));
					break;

				default:
					$error = new Eloom_PayU_Errors($response->transactionResponse->responseCode);
					$errors = array($error->getMessage());
					$order->getPayment()->setCcDebugResponseBody(json_encode($errors));
					$order->getPayment()->save();

					//Mage::dispatchEvent('eloom_payu_cancel_order', array('order' => $order, 'comment' => 'Falha no Pagamento.'));

					$message = array('error' => "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
					break;
			}
		} catch(Exception $e) {
			$this->logger->fatal($e->getCode() . ' - ' . $e->getMessage());
			$this->logger->fatal($e->getTraceAsString());

			$error = new Eloom_PayU_Errors($e->getMessage());
			$errors = array($error->getMessage());

			$order->getPayment()->setCcStatus(Eloom_PayU_Enum_Transaction_State::DECLINED);
			$order->getPayment()->setCcDebugResponseBody(json_encode($errors));
			$order->getPayment()->save();

			//Mage::dispatchEvent('eloom_payu_cancel_order', array('order' => $order, 'comment' => 'Falha no Pagamento.'));

			$message = array('error' => "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}

		return $message;
	}

	private function processPaymentCc(Varien_Object $data) {
		$order = Mage::getSingleton('eloom_payu/session')->getOrder();
		$message = array();

		$ccNumber = preg_replace('/\D/', '', $data->getPayuCcNumber());
		$order->getPayment()->setCcName($data->getPayuCcName())
			->setCcOwner($data->getPayuCcOwner())
			->setCcLast4(substr($ccNumber, -4))
			->setCcType($data->getPayuCcType())
			->setCcCvc($data->getPayuCcCvc())
			->setCcInstallments($data->getPayuCcInstallments())
			->setCcNumber($ccNumber);

		$expiry = null;
		if ($data->getPayuCcExpiry() && $data->getPayuCcExpiry() != '') {
			$expiry = explode("/", trim($data->getPayuCcExpiry()));
			$day = trim($expiry[0]);
			$year = trim($expiry[1]);
			if (strlen($year) == 2) {
				$year = '20' . $year;
			}
			$expiry = $year . '/' . $day;
			$order->getPayment()->setCcExpiry($expiry);
		}

		// salva
		$additional = new stdClass();
		$additional->creditCardNumber = Mage::helper('core')->encrypt($ccNumber);
		$additional->creditCardHolderName = $data->getPayuCcOwner();
		$additional->creditCardCvc = $data->getPayuCcCvc();
		$additional->creditCardExpiry = $expiry;

		$additional->installments = $data->getPayuCcInstallments();
		if ($data->getPayuCcHolderAnother() && $data->getPayuCcHolderAnother() == 1) {
			$additional->creditCardHolderAnother = $data->getPayuCcHolderAnother();
			$additional->creditCardHolderCpf = $data->getPayuCcHolderCpf();
			$additional->creditCardHolderPhone = $data->getPayuCcHolderPhone();
			$additional->creditCardHolderBirthDate = $data->getPayuCcHolderBirthDate();
		}

		$serializedValue = json_encode($additional);
		$order->getPayment()->setAdditionalData($serializedValue);
		$order->getPayment()->save();

		/**
		 * Envia o Pagamento
		 */
		try {
			$response = Mage::getModel('eloom_payu/cc_request')->generatePaymentRequest($order);

			$order->getPayment()->setCcStatus($response->transactionResponse->state);
			if(isset($response->transactionResponse->orderId)) {
				$additionalData = json_decode($order->getPayment()->getAdditionalData());
				$additionalData->payuOrderId = $response->transactionResponse->orderId;
				$order->getPayment()->setAdditionalData(json_encode($additionalData));
			}
			if(isset($response->transactionResponse->transactionId)) {
				$order->getPayment()->setLastTransId($response->transactionResponse->transactionId);
			}
			$order->getPayment()->setCcDebugResponseBody('');
			$order->getPayment()->save();

			$state = $response->transactionResponse->state;
			$status = Eloom_PayU_Enum_Transaction_State::getStatus($state);
			Mage::dispatchEvent('eloom_payu_process_transaction', array('order' => $order, 'status' => $status));

			switch($state) {
				case Eloom_PayU_Enum_Transaction_State::PENDING:
				case Eloom_PayU_Enum_Transaction_State::APPROVED:
					if ($order->getCanSendNewEmailFlag() && !$order->getEmailSent()) {
						try {
							$order->sendNewOrderEmail();
						} catch(Exception $e) {
							$this->logger->fatal($e->getTraceAsString());
						}
					}
				$message = array('url' => Mage::getBaseUrl(), 'message' => "<ul><li>" . $this->__('Your payment was processed by PayU') . "</li></ul>");
					break;

				default:
					$error = new Eloom_PayU_Errors($response->transactionResponse->responseCode);
					$errors = array($error->getMessage());
					$order->getPayment()->setCcDebugResponseBody(json_encode($errors));
					$order->getPayment()->save();

					//Mage::dispatchEvent('eloom_payu_cancel_order', array('order' => $order, 'comment' => 'Falha no Pagamento.'));
					$message = array('error' => "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
					break;
			}
		} catch(Exception $e) {
			$this->logger->fatal($e->getCode() . ' - ' . $e->getMessage());
			$this->logger->fatal($e->getTraceAsString());

			$error = new Eloom_PayU_Errors($e->getMessage());
			$errors = array($error->getMessage());

			$order->getPayment()->setCcStatus(Eloom_PayU_Enum_Transaction_State::DECLINED);
			$order->getPayment()->setCcDebugResponseBody(json_encode($errors));
			$order->getPayment()->save();

			Mage::dispatchEvent('eloom_payu_cancel_order', array('order' => $order, 'comment' => 'Falha no Pagamento.'));

			$message = array('error' => "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
		}

		return $message;
	}
}
