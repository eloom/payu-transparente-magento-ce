<?php

##eloom.licenca##

class Eloom_PayU_Block_Payment_Terminal_Cc extends Mage_Core_Block_Template {

	protected $_instructions;

  protected function _construct() {
    parent::_construct();
		$this->setTemplate('eloom/payu/payment/terminal/cc.phtml');
  }

	public function getGrandTotal() {
		return Mage::getSingleton('eloom_payu/session')->getOrder()->getBaseGrandTotal();
	}

	public function getFirstInstallmentAmount() {
		$paymentAmount = $this->getGrandTotal();
		$installmentAmount = $paymentAmount;

		$percentualDiscount = $this->getPercentualDiscount();
		if ($percentualDiscount > 0) {
			$paymentAmount = ($paymentAmount * $percentualDiscount) / 100;

			$installmentAmount = $paymentAmount;
		}

		return Zend_Locale_Math::round($installmentAmount, 2);
	}

	public function getPercentualDiscount() {
		$value = Mage::helper('eloom_payu/config')->getPaymentCcDiscount();
		if ($value) {
			return str_replace(',', '.', $value);
		}

		return 0.00;
	}

	public function getMinInstallment() {
		$value = Mage::helper('eloom_payu/config')->getPaymentCcMinInstallment();
		if ($value) {
			return str_replace(',', '.', $value);
		}

		return 0.00;
	}

	public function getTotalInstallments() {
		return Mage::helper('eloom_payu/config')->getPaymentCcTotalInstallments();
	}
}