<?php

##eloom.licenca##

class Eloom_PayU_Helper_Config extends Mage_Core_Helper_Abstract {

	const WEB_CHECKOUT_PAYMENT_LINK = 'eloompayu/terminal/index/id/%s/hash/%s';

  /**
   * Merchant ID
   */
  const XML_PATH_MERCHANT_ID = 'payment/eloom_payu/merchant_id'; //668978

  /**
   * API Key
   */
  const XML_PATH_API_KEY = 'payment/eloom_payu/api_key'; //LHzRB8CDNSE0HScwJxGz4i4ug0

  /**
   * Account ID
   */
  const XML_PATH_ACCOUNT_ID = 'payment/eloom_payu/account_id'; //671601

  /**
   * Public Key
   */
  const XML_PATH_PUBLIC_KEY = 'payment/eloom_payu/public_key'; // PK633jwAesg102D1KY85O8I56f

  /**
   * Login API
   */
  const XML_PATH_LOGIN_API = 'payment/eloom_payu/login_api'; // PK633jwAesg102D1KY85O8I56f

  /**
   * Ambiente
   */
  const XML_PATH_ENVIRONMENT = 'payment/eloom_payu/environment';

	/**
	 * Status para Novos Pedidos
	 */
	const XML_PATH_NEW_ORDER_STATUS = 'payment/eloom_payu/new_order_status';

	/**
	 * Status para Pedidos Aprovados
	 */
	const XML_PATH_APPROVED_ORDER_STATUS = 'payment/eloom_payu/approved_order_status';

	/**
	 * Forma de Recebimento
	 */
	const XML_PATH_PAYMENT_CC_RECEIPT = 'payment/eloom_payu_cc/receipt';

	/**
	 * Total de Parcelas
	 */
	const XML_PATH_PAYMENT_CC_TOTAL_INSTALLMENTS = 'payment/eloom_payu_cc/total_installmens';

	/**
	 * Parcelas sem Juros
	 */
	const XML_PATH_PAYMENT_CC_INSTALLMENTS_WITHOU_INTEREST = 'payment/eloom_payu_cc/installmens_without_interest';

	/**
	 * Juros
	 */
	const XML_PATH_PAYMENT_CC_INTEREST = 'payment/eloom_payu_cc/interest';

  /**
   * Desconto à Vista
   */
  const XML_PATH_PAYMENT_CC_DISCOUNT = 'payment/eloom_payu_cc/discount';

  /**
   * Parcela Mínima
   */
  const XML_PATH_PAYMENT_CC_MIN_INSTALLMENT = 'payment/eloom_payu_cc/min_installment';


	/**
	 * Instruções do Boleto
	 */
	const XML_PATH_PAYMENT_BOLETO_INSTRUCTIONS = 'payment/eloom_payu_boleto/instructions';

  /**
   * Expiração do Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_EXPIRATION = 'payment/eloom_payu_boleto/expiration';

  /**
   * Cancelamento do Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL = 'payment/eloom_payu_boleto/cancel';

  /**
   * Prazo de Expiração para compras realizadas na Sexta-Feira via Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL_ON_FRIDAY = 'payment/eloom_payu_boleto/cancel_on_friday';

  /**
   * Prazo de Expiração para compras realizadas no Sábado via Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL_ON_SATURDAY = 'payment/eloom_payu_boleto/cancel_on_saturday';

  /**
   * Prazo de Expiração para compras realizadas entre Domingo e Quinta-Feira via Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL_ON_SUNDAY = 'payment/eloom_payu_boleto/cancel_on_sunday';

  /**
   * 
   */
  public function _construct() {
    parent::_construct();
  }

  /**
   * Retrieve store model instance
   *
   * @return Mage_Core_Model_Store
   */
  public function getStore() {
    return Mage::app()->getStore();
  }

  public function getConfig($path) {
    return Mage::getStoreConfig($path, Mage::app()->getStore()->getStoreId());
  }

  public function getConfigFlag($path) {
    return Mage::getStoreConfigFlag($path, Mage::app()->getStore()->getStoreId());
  }

  public function getMerchantId() {
    return trim($this->getConfig(self::XML_PATH_MERCHANT_ID));
  }

  public function getApiKey() {
    return trim($this->getConfig(self::XML_PATH_API_KEY));
  }

  public function getAccountId() {
    return trim($this->getConfig(self::XML_PATH_ACCOUNT_ID));
  }

  public function getLoginApi() {
    return trim($this->getConfig(self::XML_PATH_LOGIN_API));
  }

  public function getPublicKey() {
    return trim($this->getConfig(self::XML_PATH_PUBLIC_KEY));
  }

  public function getEnvironment() {
    return trim($this->getConfig(self::XML_PATH_ENVIRONMENT));
  }

  public function isInProduction() {
    $env = trim($this->getConfig(self::XML_PATH_ENVIRONMENT));
    if ($env === Eloom_PayU_Api_Environment::PRODUCTION) {
      return true;
    }

    return false;
  }

  public function getPaymentCcDiscount() {
    return trim($this->getConfig(self::XML_PATH_PAYMENT_CC_DISCOUNT));
  }

  public function getPaymentCcMinInstallment() {
    return trim($this->getConfig(self::XML_PATH_PAYMENT_CC_MIN_INSTALLMENT));
  }

	public function getPaymentCcTotalInstallments() {
		return (int) $this->getConfig(self::XML_PATH_PAYMENT_CC_TOTAL_INSTALLMENTS);
	}

	public function getPaymentCcInstallmentsWithoutInterest() {
		return (int) $this->getConfig(self::XML_PATH_PAYMENT_CC_INSTALLMENTS_WITHOU_INTEREST);
	}

	public function getPaymentCcInterest() {
		return trim($this->getConfig(self::XML_PATH_PAYMENT_CC_INTEREST));
	}

  public function getBilletExpiration() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_EXPIRATION));
  }

	public function getBilletInstructions() {
		return trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_INSTRUCTIONS));
	}

  public function isBoletoCancel() {
    return $this->getConfigFlag(self::XML_PATH_PAYMENT_BOLETO_CANCEL);
  }

  public function getBilletCancelOnFriday() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_CANCEL_ON_FRIDAY));
  }

  public function getBilletCancelOnSaturday() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_CANCEL_ON_FRIDAY));
  }

  public function getBilletCancelOnSunday() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_CANCEL_ON_SUNDAY));
  }

	/**
	 * Retorna verdadeiro se a forma de recebimento é por Antecipação
	 *
	 * @return bool
	 */
	public function isReceiptByAntecipacao() {
  	return ($this->getConfig(self::XML_PATH_PAYMENT_CC_RECEIPT) == 'A');
	}

	public function getNewOrderStatus() {
		return $this->getConfig(self::XML_PATH_NEW_ORDER_STATUS);
	}

	public function getApprovedOrderStatus() {
		return $this->getConfig(self::XML_PATH_APPROVED_ORDER_STATUS);
	}

}
