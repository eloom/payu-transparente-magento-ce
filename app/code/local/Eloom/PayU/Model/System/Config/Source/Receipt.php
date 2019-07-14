<?php

##eloom.licenca##

class Eloom_PayU_Model_System_Config_Source_Receipt {

  public function toOptionArray() {
    return array(
        array(
            'value' => 'A',
            'label' => Mage::helper('eloom_payu')->__('Por Antecipação')
        ),
        array(
            'value' => 'F',
            'label' => Mage::helper('eloom_payu')->__('No Fluxo')
        )
    );
  }

}
