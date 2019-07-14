<?php

##eloom.licenca##

class Eloom_PayU_Errors {

  public static $list = array(
      'APPROVED' => array('message' => 'A transação foi aprovada.'),
      'ANTIFRAUD_REJECTED' => array('message' => 'A transação foi rejeitada. Verifique se você informou os dados do cartão de crédito e endereço corretamente. O Endereço de Cobrança deve coincidir com o Endereço da Fatura do Cartão de Crédito.'),
      'PAYMENT_NETWORK_REJECTED' => array('message' => 'A intituição financeira rejeitou sua transação. Entre em contato com seu banco para mais detalhes.'),
      'ENTITY_DECLINED' => array('message' => 'A transação foi rejeitada pelo banco ou pela rede financeira devido a um erro. Entre em contato com o banco emissor para mais detalhes.'),
      'INTERNAL_PAYMENT_PROVIDER_ERROR' => array('message' => 'Ocorreu um erro no sistema tentando processar o pagamento. Tente novamente mais tarde.'),
      'INACTIVE_PAYMENT_PROVIDER' => array('message' => 'O fornecedor de pagamentos não estava ativo. Tente novamente mais tarde.'),
      'DIGITAL_CERTIFICATE_NOT_FOUND' => array('message' => 'A rede financeira relatou um erro na autenticação. Entre em contato com a loja e informe o problema.'),
      'INVALID_EXPIRATION_DATE_OR_SECURITY_CODE' => array('message' => 'O código de segurança ou a data de expiração do cartão de crédito é inválida. Verifique os dados e tente novamente.'),
      'INVALID_RESPONSE_PARTIAL_APPROVAL' => array('message' => 'Tipo de resposta inválida. A entidade financeira aprovou parcialmente a transação, mas será cancelado automaticamente pelo sistema.'),
      'INSUFFICIENT_FUNDS' => array('message' => 'Verifique se você têm saldo suficiente para realizar esta compra.'),
      'CREDIT_CARD_NOT_AUTHORIZED_FOR_INTERNET_TRANSACTIONS' => array('message' => 'O cartão de crédito não estava autorizado para transações pela Internet. Entre em contato com o banco emissor e solicite a liberação.'),
      'INVALID_TRANSACTION' => array('message' => 'A rede financeira relatou que a transação foi inválida. Verifique se informou os dados corretamente.'),
      'INVALID_CARD' => array('message' => 'O cartão de crédito é inválido. Tente novamente e informe o cartão corretamente.'),
      'EXPIRED_CARD' => array('message' => 'O cartão de crédito já expirou.'),
      'RESTRICTED_CARD' => array('message' => 'O cartão de crédito apresenta uma restrição. Entre em contato com o banco emissor para mais detalhes.'),
      'CONTACT_THE_ENTITY' => array('message' => 'Você deve entrar em contato com o banco emissor para saber qual o motivo da recusa da transação.'),
      'REPEAT_TRANSACTION' => array('message' => 'Houve um problema ao processar sua requisição. Tente novamente.'),
      'ENTITY_MESSAGING_ERROR' => array('message' => 'A rede financeira relatou um erro de comunicações com o banco. Tente novamente.'),
      'BANK_UNREACHABLE' => array('message' => 'O banco não se encontrava disponível. Tente novamente mais tarde.'),
      'EXCEEDED_AMOUNT' => array('message' => 'O valor da transação excede o montante estabelecido pelo banco. Entre em contato com seu banco emissor para resolver o problema.'),
      'NOT_ACCEPTED_TRANSACTION' => array('message' => 'A transação não foi aceita pelo banco emissor por algum motivo. Entre em contato com seu banco emissor para resolver o problema.'),
      'ERROR_CONVERTING_TRANSACTION_AMOUNTS' => array('message' => 'Ocorreu um erro convertendo os montantes para a moeda de pagamento.'),
      'EXPIRED_TRANSACTION' => array('message' => 'A transação expirou. Tente novamente.'),
      'PENDING_TRANSACTION_REVIEW' => array('message' => 'A transação foi parada e deve ser revista, isto pode ocorrer por filtros de segurança.'),
      'PENDING_TRANSACTION_CONFIRMATION' => array('message' => 'A transação está pendente de confirmação.'),
      'PENDING_TRANSACTION_TRANSMISSION' => array('message' => 'A transação está pendente para ser transmitida para a rede financeira. Normalmente isto se aplica para transações com formas de pagamento em dinheiro.'),
      'PAYMENT_NETWORK_BAD_RESPONSE' => array('message' => 'A mensagem retornada pela rede financeira é inconsistente.'),
      'PAYMENT_NETWORK_NO_CONNECTION' => array('message' => 'Não foi possível realizar a conexão com a rede financeira.'),
      'PAYMENT_NETWORK_NO_RESPONSE' => array('message' => 'A rede financeira não respondeu.'),
      'FIX_NOT_REQUIRED' => array('message' => 'Clínica de transações: Código de operação interna.'),
      'AUTOMATICALLY_FIXED_AND_SUCCESS_REVERSAL' => array('message' => 'Clínica de transações: Código de operação interna. Somente aplicável para o API de consultas.'),
      'AUTOMATICALLY_FIXED_AND_UNSUCCESS_REVERSAL' => array('message' => 'Clínica de transações: Código de operação interna. Somente aplicável para o API de consultas.'),
      'AUTOMATIC_FIXED_NOT_SUPPORTED' => array('message' => 'Clínica de transações: Código de operação interna. Somente aplicável para o API de consultas.'),
      'NOT_FIXED_FOR_ERROR_STATE' => array('message' => 'Clínica de transações: Código de operação interna. Somente aplicável para o API de consultas.'),
      'ERROR_FIXING_AND_REVERSING' => array('message' => 'Clínica de transações: Código de operação interna. Somente aplicável para o API de consultas.'),
      'ERROR_FIXING_INCOMPLETE_DATA' => array('message' => 'Clínica de transações: Código de operação interna. Somente aplicável para o API de consultas.'),
      'DECLINED_TEST_MODE_NOT_ALLOWED' => array('message' => 'Esta conta não está liberada para pedidos de teste.'),
			'Parameter [payerDNI] is required' => array('message' => 'CPF/CNPJ é obrigatório.'),
      'The credit card expiration date is not valid' => array('message' => 'A data de validade do cartão de crédito é inválida.'),
      'ERROR' => array('message' => 'Houve um erro ao processar sua requisição. Por favor tente novamente, ou escolha outra forma de pagamento.'),
  );

  /**
   *
   * @var string
   */
  private $code;

  /**
   *
   * @var string
   */
  private $message;

  public function __construct($code) {
    if (array_key_exists($code, self::$list)) {
      $v = self::$list[$code];
      $this->message = $v['message'];
    } else {
      $this->message = $code;
    }
  }

  public function getCode() {
    return $this->code;
  }

  public function getMessage() {
    return $this->message;
  }

  public function setCode($code) {
    $this->code = $code;
  }

  public function setMessage($message) {
    $this->message = $message;
  }

  public static function listAll() {
    return self::$list;
  }

  public function getFullMessage() {
    return $this->code . ' - ' . $this->message;
  }

}
