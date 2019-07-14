<?php

##eloom.licenca##

class Eloom_PayU_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Truncate a float number, example: <code>truncate(-1.49999, 2); // returns -1.49
	 * truncate(.49999, 3); // returns 0.499
	 * </code>
	 * @param float $val Float number to be truncate
	 * @param int f Number of precision
	 * @return float
	 */
	function truncate($val, $f = '2') {
		if (($p = strpos($val, '.')) !== false) {
			$val = floatval(substr($val, 0, $p + 1 + $f));
		}
		return $val;
	}

	public function getPayment() {
		$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->load($orderId);
		return $order->getPayment();
	}

	/**
	 * Gera um link de pagamento para o WebCheckout
	 *
	 * @param $orderIncrementId
	 * @param $amount
	 * @return string
	 */
	public function generateWebCheckoutPaymentLink($orderIncrementId, $amount) {
		$config = Mage::helper('eloom_payu/config');

		$merchantId = $config->getMerchantId();
		$apiKey = $config->getApiKey();
		$currency = 'BRL';
		$signature = $this->generateSignature($apiKey, $merchantId, $orderIncrementId, $currency);

		return sprintf(Eloom_PayU_Helper_Config::WEB_CHECKOUT_PAYMENT_LINK, $orderIncrementId, $signature);
	}

	/**
	 * Gera a assinatura no formato do PayU
	 *
	 * @param $apiKey
	 * @param $merchantId
	 * @param $orderIncrementId
	 * @param $currency
	 * @return string
	 */
	public function generateSignature($apiKey, $merchantId, $orderIncrementId, $currency) {
		return $signature = hash('sha256', ($apiKey . '~' . $merchantId . '~' . $orderIncrementId . '~' . $currency));
	}
}
