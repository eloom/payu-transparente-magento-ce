<?php

##eloom.licenca##

class Eloom_PayU_Model_Session extends Mage_Core_Model_Session_Abstract {

	/**
	 * Class constructor. Initialize session namespace
	 */
	public function __construct() {
		$namespace = 'eloom_payu';
		$namespace .= '_' . (Mage::app()->getStore()->getWebsite()->getCode());

		$this->init($namespace);
		Mage::dispatchEvent('eloom_payu_session_init', array('eloom_payu_session' => $this));
	}

	/**
	 * Unset all data associated with object
	 */
	public function unsetAll() {
		parent::unsetAll();
	}
}
