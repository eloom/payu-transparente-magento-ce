<?php

##eloom.licenca##

class Eloom_PayU_Model_System_Config_Source_Environment {

  public function toOptionArray() {
    return array(
        array(
            'value' => Eloom_PayU_Api_Environment::PRODUCTION,
            'label' => Mage::helper('adminhtml')->__('Production')
        ),
        array(
            'value' => Eloom_PayU_Api_Environment::TEST,
            'label' => Mage::helper('adminhtml')->__('Test')
        )
    );
  }

}
