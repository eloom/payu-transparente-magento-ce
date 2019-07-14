<?php

##eloom.licenca##

class Eloom_PayU_IndexController extends Mage_Core_Controller_Front_Action {

  /**
   * Initialize resource model
   */
  protected function _construct() {
    parent::_construct();
  }

  public function installmentsAction() {
    if (!$this->getRequest()->isPost()) {
      //return;
    }
    $paymentMethod = $this->getRequest()->getParam('paymentMethod');
    $amount = $this->getRequest()->getParam('amount');
		$response = null;

		$config = Mage::helper('eloom_payu/config');
		if($config->isReceiptByAntecipacao()) {
			$response = Mage::getModel('eloom_payu/installments')->calculateInstallmentsByAntecipacao($paymentMethod, $amount);
		} else {
			$response = Mage::getModel('eloom_payu/installments')->calculateInstallmentsByFluxo($paymentMethod, $amount);
		}

    $this->getResponse()->setHeader('Content-type', 'application/json', true);
    $this->getResponse()->setBody($response);
  }

}
