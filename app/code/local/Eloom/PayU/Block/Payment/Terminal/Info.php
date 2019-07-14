<?php

##eloom.licenca##

class Eloom_PayU_Block_Payment_Terminal_Info extends Mage_Payment_Block_Info {

	private $helper = null;

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('eloom/payu/payment/terminal/info.phtml');
    $this->helper = Mage::helper('eloom_payu');
  }

  /**
   * Prepare credit card related payment info
   *
   * @param Varien_Object|array $transport
   * @return Varien_Object
   */
  protected function _prepareSpecificInformation($transport = null) {
    if (null !== $this->_paymentSpecificInformation) {
      return $this->_paymentSpecificInformation;
    }
    $transport = parent::_prepareSpecificInformation($transport);
    $info = $this->getInfo();
    $data = array();

    $status = $info->getCcStatus();
    if (!empty($status)) {
      $data[$this->helper->__('Status')] = $this->helper->__('Transaction.State.' . $status);
    }

    if ($lastTransactionId = $info->getLastTransId()) {
      $data[$this->helper->__('PayU Transaction ID')] = $lastTransactionId;
    }

    $additionalData = json_decode($info->getAdditionalData());
    if (!empty($additionalData)) {
      if (isset($additionalData->payuOrderId)) {
        $data[$this->helper->__('PayU Order ID')] = $additionalData->payuOrderId;
      }
      if (isset($additionalData->barCode)) {
        $data[$this->helper->__('PayU Bar Code')] = $additionalData->barCode;
      }
    }

		if ($ccType = $info->getCcType()) {
			$data[$this->helper->__('Credit Card Type')] = $ccType;
		}
		if ($info->getCcLast4()) {
			$data[$this->helper->__('Last Credit Card Number')] = sprintf('xxxx xxxx xxxx %s', $info->getCcLast4());
		}

		if (!empty($additionalData)) {
			if (isset($additionalData->creditCardHolderName)) {
				$data[$this->helper->__('CC Owner')] = strtoupper($additionalData->creditCardHolderName);
			}
			if (isset($additionalData->installments)) {
				$data[$this->helper->__('Installments')] = sprintf("Em %sx de %s", $additionalData->installments, $additionalData->installmentAmount);
			}
		}

    $errors = json_decode($info->getCcDebugResponseBody());
    if (!empty($errors)) {
      foreach ($errors as $error) {
        $data[$this->helper->__('Error')] = $error;
      }
    }

    return $transport->setData(array_merge($data, $transport->getData()));
  }

	public function getPaymentLink() {
		$info = $this->getInfo();
    $show = true;

    if ($info->getOrder()->getState() != Mage_Sales_Model_Order::STATE_NEW) {
      $show = false;
    }

    $additionalData = json_decode($info->getAdditionalData());
    if (!empty($additionalData)) {
      if (isset($additionalData->payuOrderId)) {
        $show = false;
      }
    }

		if($show) {
			$link = $this->helper->generateWebCheckoutPaymentLink($info->getOrder()->getIncrementId(), $info->getOrder()->getBaseGrandTotal());
			return Mage::getUrl($link, array('_secure' => true));
		}
	}

  public function getBilletLink() {
    $info = $this->getInfo();
    if(!$info->getOrder()) {
      return null;
    }

    $lastTransId = $info->getLastTransId();
    $config = Mage::helper('eloom_payu/config');

    $status = $info->getOrder()->getStatus();
    if (!empty($lastTransId) && ($status == $config->getNewOrderStatus())) {
      $additionalData = json_decode($info->getAdditionalData());
      return $additionalData->paymentLink;
    }

    return null;
  }
}