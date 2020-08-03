var EloomPayment = Class.create();
EloomPayment.prototype = {
	initialize: function (form, savePayment) {
		this.form = form;
		this.savePayment = savePayment;
	},
	init: function () {
		var elements = Form.getElements(this.form);
		var method = null;
		for (var i = 0; i < elements.length; i++) {
			if (elements[i].name == 'payment[method]') {
				if (elements[i].checked) {
					method = elements[i].value;
				}
			}
			elements[i].setAttribute('autocomplete', 'off');
		}
		if (method) {
			this.switchMethod(method);
		}
	},
	switchMethod: function (method) {
		if (this.currentMethod && $('payment_form_' + this.currentMethod)) {
			this.changeVisible(this.currentMethod, true);
			$('payment_form_' + this.currentMethod).fire('payment-method:switched-off', {
				method_code: this.currentMethod
			});
		}
		if ($('payment_form_' + method)) {
			this.changeVisible(method, false);
			$('payment_form_' + method).fire('payment-method:switched', {
				method_code: method
			});
		} else {
			document.body.fire('payment-method:switched', {
				method_code: method
			});
		}
		if (method) {
			this.lastUsedMethod = method;
		}
		this.currentMethod = method;
	},
	changeVisible: function (method, mode) {
		var block = 'payment_form_' + method;
		[block + '_before', block, block + '_after'].each(function (el) {
			var element = $(el);
			if (element) {
				element.style.display = (mode) ? 'none' : '';
				element.select('input', 'select', 'textarea', 'button').each(function (field) {
					field.disabled = mode;
				});
				$('messages-payu').down('li').removeClassName('notice-msg').removeClassName('success-msg').innerHTML = '';
			}
		});
	},
	validate: function () {
		if ($$('[name="payment[method]"]:checked').length == 0) {
			alert(Translator.translate('Please specify payment method.').stripTags());
			return false;
		}

		var methods = document.getElementsByName('payment[method]');
		if (methods.length == 0) {
			alert(Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.').stripTags());
			return false;
		}

		return true;
	},
	save: function (btn) {
		var validator = new Validation(this.form);
		if (!validator.validate()) {
			return false;
		}
		if (!payment.validate()) {
			return false;
		}
		validator.reset();

		$(btn).disable().next().show();

		var params = Form.serialize(this.form);
		var request = new Ajax.Request(
			this.savePayment, {
				asynchronous: true,
				method: 'post',
				parameters: params,
				onSuccess: this.nextStep.bindAsEventListener(this),
				onComplete: setTimeout(function() {
					$(btn).next().hide();
				}, 5000)
			});
	},
	nextStep: function (transport) {
		var response = null;
		if (transport && transport.responseText) {
			try {
				response = eval('(' + transport.responseText + ')');
			} catch (e) {
				response = {};
			}
		}
		if (response.error) {
			$('messages-payu').down('li').addClassName('notice-msg').innerHTML = response.error;
		} else {
			$('messages-payu').down('li').addClassName('success-msg').innerHTML = response.message;
			$('co-payment-form').down('fieldset').hide();
		}
		$('messages-payu').show();
	}
};

Eloom.PayU.Terminal = {
	config: null,

	PaymentBoleto: {
		config: null,
		init: function () {
			if (this.config == null) {
				return;
			}
			$j('#p_method_' + Eloom.PayU.Terminal.PaymentBoleto.config.code).after('<img src="' + Eloom.PayU.config.logo + '">');
			if (payment.currentMethod === Eloom.PayU.Terminal.PaymentBoleto.config.code) {
				$j('#p_method_' + Eloom.PayU.Terminal.PaymentBoleto.config.code).click();
			}
		}
	},
	PaymentCc: {
		config: null,
		init: function () {
			if (this.config == null) {
				return;
			}
			$j('#p_method_' + Eloom.PayU.Terminal.PaymentCc.config.code).after('<img src="' + Eloom.PayU.config.logo + '">');
			if (payment.currentMethod === Eloom.PayU.Terminal.PaymentCc.config.code) {
				$j('#p_method_' + Eloom.PayU.Terminal.PaymentCc.config.code).click();
			}

			this._bindPaymentOption();
			this._bindCreditCardAssistance();
			this._bindSenderInformation();
			this._bindPayUBehavior();
		},

		_bindPayUBehavior: function () {
			$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-number').on('blur', function (event) {
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
					$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-type').val(cardType);
					$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-owner').focus();

					$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-number').css('background-image', 'url("' + Eloom.PayUCardTypes.getPath(cardType) + '")');

					var installmentsBox = $j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-installments');
					installmentsBox.empty();

					var installmentsUrl = Eloom.PayU.config.installmentsUrl;
					jQuery.ajax({
						dataType: 'json',
						method: 'POST',
						data: {amount: Eloom.PayU.Terminal.PaymentCc.config.amount, paymentMethod: cardType},
						url: installmentsUrl,
						context: document.body
					}).done(function (response) {
						var installments = response.paymentMethodFee[0].pricingFees;
						var installmentAmount = null;
						var text = null;
						var totalAmount = null;
						var installmentAmountOriginal = null;
						var interest = 0;
						var installment = 0;

						$j(installments).each(function (index, element) {
							installment = parseInt(element.installments);
							//installmentAmount = (element.pricing.totalValue / installment);
							installmentAmount = parseNumber(numeral(element.pricing.totalValue).divide(installment).format('0.00').replace(',', '.'));
							interest = element.pricing.payerDetail.interest;

							if (installment === 1) {
								installmentAmount = numeral(Eloom.PayU.Terminal.PaymentCc.config.firstInstallmentAmount).format('0,0.00');
								totalAmount = numeral(Eloom.PayU.Terminal.PaymentCc.config.firstInstallmentAmount).format('0,0.00');
							} else {
								if(installment > Eloom.PayU.Terminal.PaymentCc.config.totalInstallmens) {
									return true;
								}
								if (Eloom.PayU.Terminal.PaymentCc.config.minInstallment > 0) {
									if (installmentAmount < Eloom.PayU.Terminal.PaymentCc.config.minInstallment) {
										return true;
									}
								}
								totalAmount = numeral(installmentAmount * installment).format('0,0.00');
								installmentAmountOriginal = installmentAmount;
								installmentAmount = numeral(installmentAmount).format('0,0.00');
							}

							text = installment + 'x de R$ '.concat(installmentAmount).concat(' = R$ ' + totalAmount).concat((installment === 1 && Eloom.PayU.Terminal.PaymentCc.config.percentualDiscount > 0 ? ' (' + numeral(Eloom.PayU.Terminal.PaymentCc.config.percentualDiscount).format('0,0.00') + '% off)' : '')).concat(interest > 0 ? ' c/ juros' : '');
							value = installment + '-' + installmentAmountOriginal;

							installmentsBox.append($j('<option />').text(text).val(value));
						});
					});
				}
			});
		}
		,
		_bindSenderInformation: function () {
			$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-holder-another').on('click', function () {
				var elements = $j('#payment_form_' + Eloom.PayU.Terminal.PaymentCc.config.code + ' li.payu-cc-holder');
				if (elements.hasClass('open')) {
					elements.slideToggle().removeClass('open');
					$j('#payment_form_' + Eloom.PayU.Terminal.PaymentCc.config.code + ' li.payu-cc-holder input[type="text"]').removeClass('required-entry');
				} else {
					elements.slideToggle().addClass('open');
					$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-holder-cpf').focus();
					$j('#payment_form_' + Eloom.PayU.Terminal.PaymentCc.config.code + ' li.payu-cc-holder input[type="text"]').addClass('required-entry');
				}
			});
		}
		,
		_bindPaymentOption: function () {
			$j('#p_method_' + Eloom.PayU.Terminal.PaymentCc.config.code).on('click', function () {
				$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-number').focus();
			});
		},

		_bindCreditCardAssistance: function () {
			$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-number').payment('formatCardNumber');
			$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-expiry').payment('formatCardExpiry');
			$j('#' + Eloom.PayU.Terminal.PaymentCc.config.code + '-payu-cc-cvc').payment('formatCardCVC');
		}
	}
};