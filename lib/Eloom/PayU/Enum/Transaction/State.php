<?php

##eloom.licenca##

class Eloom_PayU_Enum_Transaction_State extends Eloom_PayU_Enum_Enum {

  /**
   * Transação aprovada
   */
  const APPROVED = 'APPROVED';

  /**
   * Transação rejeitada
   */
  const DECLINED = 'DECLINED';

  /**
   * Erro processando a transação
   */
  const ERROR = 'ERROR';

  /**
   * Transação expirada
   */
  const EXPIRED = 'EXPIRED';

  /**
   * Transação pendente ou em validação
   */
  const PENDING = 'PENDING';

  /**
   * Transação enviada para a entidade financeira e por algum motivo não terminou seu processamento. 
   */
  const SUBMITTED = 'SUBMITTED';

  /**
   * Cancelado
   */
  const CANCELLED = 'CANCELLED';

	/**
	 * Retorna o Status pelo State
	 */
	protected static $_map = array(self::APPROVED => Eloom_PayU_Enum_Order_Status::CAPTURED,
		self::DECLINED => Eloom_PayU_Enum_Order_Status::DECLINED,
		self::ERROR => Eloom_PayU_Enum_Order_Status::DECLINED,
		self::EXPIRED => Eloom_PayU_Enum_Order_Status::DECLINED,
		self::PENDING => Eloom_PayU_Enum_Order_Status::IN_PROGRESS,
		self::SUBMITTED => Eloom_PayU_Enum_Order_Status::IN_PROGRESS,
		self::CANCELLED => Eloom_PayU_Enum_Order_Status::CANCELLED);

	/**
	 * Retorna o Status pelo State
	 *
	 * @return type String
	 */
	public static function getStatus($state) {
		return self::$_map[$state];
	}
}
