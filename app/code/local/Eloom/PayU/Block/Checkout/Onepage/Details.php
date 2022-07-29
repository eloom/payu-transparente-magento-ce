<?php

##eloom.licenca##

class Eloom_PayU_Block_Checkout_Onepage_Details extends Mage_Core_Block_Template {

  protected $additionalData;

  protected function _construct() {
    parent::_construct();
    if($this->isBoleto() || $this->isCc() || $this->isPix()) {
	    $this->setTemplate('eloom/payu/checkout/onepage/success-details.phtml');
	    $info = $this->getPayment();
	    $this->additionalData = json_decode($info->getAdditionalData());
    }
  }

  public function isBoleto() {
    $method = $this->getPayment()->getMethodInstance()->getCode();
    if ($method == Eloom_PayU_Model_Method_Boleto::PAYMENT_METHOD_BOLETO_CODE) {
      return true;
    }

    return false;
  }

	public function isCc() {
		$method = $this->getPayment()->getMethodInstance()->getCode();
		if ($method == Eloom_PayU_Model_Method_Cc::PAYMENT_METHOD_CC_CODE) {
			return true;
		}

		return false;
	}

	public function isPix() {
		$method = $this->getPayment()->getMethodInstance()->getCode();
		if ($method == Eloom_PayU_Model_Method_Pix::PAYMENT_METHOD_PIX_CODE) {
			return true;
		}

		return false;
	}

  public function getBilletLink() {
    if (isset($this->additionalData->paymentLink)) {
      return $this->additionalData->paymentLink;
    }

    return null;
  }

  public function getBilletDateOfExpiration() {
    if (isset($this->additionalData->dateOfExpiration)) {
      return $this->additionalData->dateOfExpiration;
    }

    return null;
  }

  public function getPayUOrderId() {
    if (isset($this->additionalData->payuOrderId)) {
      return $this->additionalData->payuOrderId;
    }
    return null;
  }

  public function getBilletBarcode() {
    if (isset($this->additionalData->barCode)) {
      return $this->additionalData->barCode;
    }

    return null;
  }

	public function getExpirationDate() {
		if (isset($this->additionalData->expirationDate)) {
      $date = new \DateTime($this->additionalData->expirationDate);

			return $date->format('d-m-Y H:i:s');
		}

		return null;
	}

	public function getQrCodeEmv() {
		if (isset($this->additionalData->qrCodeEmv)) {
			return $this->additionalData->qrCodeEmv;
		}

		return null;
	}

	public function getQrCodeImageBase64() {
		if (isset($this->additionalData->qrCodeImageBase64)) {
			return $this->additionalData->qrCodeImageBase64;
		}

		return null;
	}

  public function getPayment() {
    return Mage::helper('eloom_payu')->getPayment();
  }

}
