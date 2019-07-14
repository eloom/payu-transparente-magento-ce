<?php

##eloom.licenca##

class Eloom_PayU_Model_System_Config_Source_Installments {

  public function toOptionArray() {
    $options = array();
    for ($i = 1; $i < 13; $i++) {
      $options[] = array('value' => $i, 'label' => $i);
    }
    return $options;
  }

}
