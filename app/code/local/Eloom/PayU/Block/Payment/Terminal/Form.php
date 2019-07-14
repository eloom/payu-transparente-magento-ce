<?php

##eloom.licenca##

class Eloom_PayU_Block_Payment_Terminal_Form extends Mage_Payment_Block_Form {

  /**
   * Instructions text
   *
   * @var string
   */
  protected $_instructions;

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('eloom/payu/payment/terminal/form.phtml');
  }

  protected function _prepareLayout() {
    return parent::_prepareLayout();
  }

  public function getGrandTotal() {
  	throw new Exception('Comportamento inesperado');
    //return Mage::getSingleton('checkout/session')->getQuote()->getBaseGrandTotal();
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
}
