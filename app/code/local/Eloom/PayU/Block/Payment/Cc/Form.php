<?php

##eloom.licenca##

class Eloom_PayU_Block_Payment_Cc_Form extends Mage_Payment_Block_Form {

  protected $_installmentNoInterest;

	/**
	 * Instructions text
	 *
	 * @var string
	 */
	protected $_instructions;

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('eloom/payu/payment/cc/form.phtml');
  }

  public function getGrandTotal() {
    return Mage::getSingleton('checkout/session')->getQuote()->getBaseGrandTotal();
  }

  public function getFirstInstallmentAmount() {
    $paymentAmount = $this->getGrandTotal();
    $installmentAmount = $paymentAmount;

    $percentualDiscount = $this->getPercentualDiscount();
    if ($percentualDiscount > 0) {
      $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
      $baseShippingAmount = $sessionQuote->getShippingAddress()->getBaseShippingAmount();
      $paymentAmount = $paymentAmount - ((($paymentAmount - $baseShippingAmount) * $percentualDiscount) / 100);

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

	/**
	 * Get instructions text from config
	 *
	 * @return string
	 */
	public function getInstructions() {
		if (is_null($this->_instructions)) {
			$this->_instructions = $this->getMethod()->getInstructions();
		}

		return $this->_instructions;
	}

	public function getTotalInstallments() {
		return Mage::helper('eloom_payu/config')->getPaymentCcTotalInstallments();
	}
}