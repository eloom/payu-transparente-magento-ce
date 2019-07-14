<?php

##eloom.licenca##

class Eloom_PayU_Model_Installments extends Mage_Core_Model_Abstract {

	/**
	 * Retorna o parcelamento na opção de recebimento por Antecipação. Os cálculos das parcelas são realizados na PayU.
	 *
	 * @param $paymentMethod
	 * @param $amount
	 * @return array
	 */
	public function calculateInstallmentsByAntecipacao($paymentMethod, $amount) {
		$apiKey = null;
		$accountId = null;
		$publicKey = null;
		$config = Mage::helper('eloom_payu/config');

		if ($config->isInProduction()) {
			$apiKey = $config->getApiKey();
			$accountId = $config->getAccountId();
			$publicKey = $config->getPublicKey();
		} else {
			Eloom_PayU_Api_Environment::$test = true;
			$apiKey = Eloom_PayU_Api_Environment::API_KEY_TEST;
			$accountId = Eloom_PayU_Api_Environment::ACCOUNT_ID_TEST;
			$publicKey = Eloom_PayU_Api_Environment::PUBLIC_KEY_TEST;
		}

		$date = gmdate("D, d M Y H:i:s", time()) . " GMT";
		$contentToSign = utf8_encode('GET' . "\n" . "\n" . "\n" . $date . "\n" . '/payments-api/rest/v4.3/pricing');
		$signature = base64_encode(hash_hmac('sha256', $contentToSign, $apiKey, true));

		$query = '?accountId=' . $accountId .
			'&currency=BRL' .
			'&amount=' . $amount .
			'&paymentMethod=' . $paymentMethod;

		$url = Eloom_PayU_Api_Environment::getSubscriptionUrl() . '/pricing';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url . $query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Hmac ' . $publicKey . ':' . $signature,
				'Content-Type: application/json',
				'Accept: application/json',
				'Date: ' . $date)
		);
		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	/**
	 * Retorna o parcelamento na opção de recebimento por Fluxo. Os cálculos das parcelas são realizados localmente.
	 *
	 * @param $paymentMethod
	 * @param $amount
	 * @return array
	 */
	public function calculateInstallmentsByFluxo($paymentMethod, $amount) {
		$config = Mage::helper('eloom_payu/config');

		$valorMinimoParcela = str_replace(',', '.', $config->getPaymentCcMinInstallment());
		$juros = str_replace(',', '.', $config->getPaymentCcInterest());
		$juros = floatval($juros);

		$parcelasTotal = 1;
		$parcelasSemJuros = 1;
		$parcelasTotal = $config->getPaymentCcTotalInstallments();
		$parcelasSemJuros = $config->getPaymentCcInstallmentsWithoutInterest();
		$totalValue = 0;
		$j = 1;

		$pricingFees = [];
		$store = Mage::getSingleton('checkout/session')->getQuote()->getStore();

		while($j <= $parcelasTotal) {
			$paymentMode = null;
			$valorParcela = 0;

			if ($j <= $parcelasSemJuros) {
				$valorParcela = $amount / $j;
			} else {
				$valorParcela = Mage::helper('eloom_payu/math')->calculatePayment($amount, $juros / 100, $j);
			}
			$totalValue = $store->roundPrice($valorParcela * $j);

			$pricingFees[] = array('installments' => strval($j),
				                     'pricing' => array('payerDetail' => array('commission' => 0, 'interest' => ($j <= $parcelasSemJuros ? 0 : $juros), 'total' => $amount),
																								'merchantDetail' => array('commission' => 0, 'interest' => 0, 'total' => 0),
																								'totalValue' => $totalValue,
																								'totalIncomeTransaction' => $totalValue));

			$j++;
		}

		$pricing = array('amount' => array('value' => $amount, 'tax' => 0, 'purchaseValue' => $amount, 'currency' => 'BRL'),
				'convertedAmount' => array('value' => $amount, 'tax' => 0, 'purchaseValue' => $amount, 'currency' => 'BRL'),
				'paymentMethodFee' => array(array('paymentMethod' => $paymentMethod, 'pricingFees' => $pricingFees))
			);

		$response = Mage::helper('core')->jsonEncode($pricing);

		return $response;
	}
}
