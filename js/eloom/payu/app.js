if (typeof($j) == "undefined") {
	$j = jQuery;
}
var Eloom = Eloom || {};
Eloom.PayU = {
  config: null,
  init: function () {
    if (this.config == null) {
      return;
    }
  },

  PaymentBoleto: {
    config: null,
    init: function () {
      if (this.config == null) {
        return;
      }
      $j('#p_method_' + Eloom.PayU.PaymentBoleto.config.code).after('<img src="' + Eloom.PayU.config.logo + '">');
      if (payment.currentMethod === Eloom.PayU.PaymentBoleto.config.code) {
        $j('#p_method_' + Eloom.PayU.PaymentBoleto.config.code).click();
      }
    }
  },
  PaymentCc: {
    config: null,
    init: function () {
      if (this.config == null) {
        return;
      }
      $j('#p_method_' + Eloom.PayU.PaymentCc.config.code).after('<img src="' + Eloom.PayU.config.logo + '">');
      if (payment.currentMethod === Eloom.PayU.PaymentCc.config.code) {
        $j('#p_method_' + Eloom.PayU.PaymentCc.config.code).click();
      }

      this._bindPaymentOption();
      this._bindCreditCardAssistance();
      this._bindSenderInformation();
      this._bindPayUBehavior();
    },

    _bindPayUBehavior: function () {
      $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-number').on('blur', function (event) {
        var value = $j(this).val().replace(/\D/g, '');
        var cardType = '';

        payU.setLanguage('pt');
		cardType = payU.cardPaymentMethod(value);

		/**
          * FIXME: tratamento para cartoes Elo não reconhecidos pelo SDK
         */
		if(cardType.indexOf('Tipo') === 0) {
            var elo = /^((509091)|(636368)|(636297)|(504175)|(438935)|(40117[8-9])|(45763[1-2])|(457393)|(431274)|(50990[0-2])|(5099[7-9][0-9])|(50996[4-9])|(509[1-8][0-9][0-9])|(5090(0[0-2]|0[4-9]|1[2-9]|[24589][0-9]|3[1-9]|6[0-46-9]|7[0-24-9]))|(5067(0[0-24-8]|1[0-24-9]|2[014-9]|3[0-379]|4[0-9]|5[0-3]|6[0-5]|7[0-8]))|(6504(0[5-9]|1[0-9]|2[0-9]|3[0-9]))|(6504(8[5-9]|9[0-9])|6505(0[0-9]|1[0-9]|2[0-9]|3[0-8]))|(6505(4[1-9]|5[0-9]|6[0-9]|7[0-9]|8[0-9]|9[0-8]))|(6507(0[0-9]|1[0-8]))|(65072[0-7])|(6509(0[1-9]|1[0-9]|20))|(6516(5[2-9]|6[0-9]|7[0-9]))|(6550(0[0-9]|1[0-9]))|(6550(2[1-9]|3[0-9]|4[0-9]|5[0-8])))\d*$/;
            if(elo.test(value)) {
                cardType = 'ELO';
            };
		}
        if (cardType.indexOf('Tipo') === -1) {
          $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-type').val(cardType);
          $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-owner').focus();

          $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-number').css('background-image', 'url("' + Eloom.PayUCardTypes.getPath(cardType) + '")');

          var installmentsBox = $j('#' + Eloom.PayU.PaymentCc.config.code + '-installments');
          installmentsBox.empty();

          var installmentsUrl = Eloom.PayU.config.installmentsUrl;
          jQuery.ajax({
						dataType: 'json',
            method: 'POST',
            data: {amount: Eloom.PayU.PaymentCc.config.amount, paymentMethod: cardType},
            url: installmentsUrl,
            context: document.body
          }).done(function (response) {
            var installments = response.paymentMethodFee[0].pricingFees;
            var installmentAmount = null;
	          var installmentAmountOriginal = null;
            var text = null;
            var totalAmount = null;
            var interest = 0;
						var installment = 0;
						var tmpValue = 0;

            $j(installments).each(function (index, element) {
							installment = parseInt(element.installments);
              //installmentAmount = (element.pricing.totalValue / installment);
							installmentAmount = parseNumber(numeral(element.pricing.totalValue).divide(installment).format('0.00').replace(',', '.'));
							interest = element.pricing.payerDetail.interest;

              if (installment === 1) {
	              installmentAmountOriginal = Eloom.PayU.PaymentCc.config.firstInstallmentAmount;
                installmentAmount = numeral(Eloom.PayU.PaymentCc.config.firstInstallmentAmount).format('0,0.00');
                totalAmount = numeral(Eloom.PayU.PaymentCc.config.firstInstallmentAmount).format('0,0.00');
              } else {
								if(installment > Eloom.PayU.PaymentCc.config.totalInstallmens) {
									return true;
								}
								if (Eloom.PayU.PaymentCc.config.minInstallment > 0) {
									if (installmentAmount < Eloom.PayU.PaymentCc.config.minInstallment) {
										return true;
									}
								}
								totalAmount = numeral(installmentAmount * installment).format('0,0.00');
	              installmentAmountOriginal = installmentAmount;
                installmentAmount = numeral(installmentAmount).format('0.00');
              }

              text = installment + 'x de R$ '.concat(installmentAmount).concat(' = R$ ' + totalAmount).concat((installment === 1 && Eloom.PayU.PaymentCc.config.percentualDiscount > 0 ? ' (' + numeral(Eloom.PayU.PaymentCc.config.percentualDiscount).format('0,0.00') + '% off)' : '')).concat(interest > 0 ? ' c/ juros' : '');
              value = installment + '-' + installmentAmountOriginal;

              installmentsBox.append($j('<option />').text(text).val(value));
            });
						installmentsBox.change(function () {
							payment.update();
						});
						payment.update();
          });
        }
      });
    }
    ,
    _bindSenderInformation: function () {
      $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-holder-another').on('click', function () {
        var elements = $j('#payment_form_' + Eloom.PayU.PaymentCc.config.code + ' li.payu-cc-holder');
        if (elements.hasClass('open')) {
          elements.slideToggle().removeClass('open');
          $j('#payment_form_' + Eloom.PayU.PaymentCc.config.code + ' li.payu-cc-holder input[type="text"]').removeClass('required-entry');
        } else {
          elements.slideToggle().addClass('open');
          $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-holder-cpf').focus();
          $j('#payment_form_' + Eloom.PayU.PaymentCc.config.code + ' li.payu-cc-holder input[type="text"]').addClass('required-entry');
        }
      });
    }
    ,
    _bindPaymentOption: function () {
      $j('#p_method_' + Eloom.PayU.PaymentCc.config.code).on('click', function () {
        $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-number').focus();
      });
    },

    _bindCreditCardAssistance: function () {
      $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-number').payment('formatCardNumber');
      $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-expiry').payment('formatCardExpiry');
      $j('#' + Eloom.PayU.PaymentCc.config.code + '-payu-cc-cvc').payment('formatCardCVC');
    }
  },

  Errors: {
    config: null,
    init: function () {
      if (this.config == null) {
        return;
      }
    },

    getError: function (response) {
      var m = null;
      try {
        var key = null;
        $j(response.errors).each(function (index, element) {
          for (var k in element) {
            if (!element.hasOwnProperty(k)) {
              continue;
            }
            key = k;
          }
        });
        m = Eloom.PayU.Errors.config[key].message;
      } catch (exception) {
        m = Eloom.PayU.Errors.config[-999].message;
      }

      return m;
    }
  }
};

Eloom.PayUCardTypes = {
	path: null,
	url: [],
	cardTypes: ['visa', 'master', 'mastercard', 'amex', 'aura', 'brasilcard', 'cabal', 'cardban', 'diners', 'elo', 'fortbrasil', 'grandcard', 'hipercard', 'mais', 'personalcard', 'plenocard', 'sorocred', 'valecard'],

	init: function (opt) {
		this.path = opt.mediaUrl + 'idecheckoutvm/payment/';
		jQuery(this.cardTypes).each(function (index, element) {
			Eloom.PayUCardTypes.url[element] = Eloom.PayUCardTypes.path + element + '.png';
		});
	},

	getPath: function (cardType) {
		cardType = cardType.toLowerCase();
		return Eloom.PayUCardTypes.url[cardType];
	}
}