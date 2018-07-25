/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	/**
	 *
	 * @param jQuery class BRCPMpurchaseForm
	 */
	XenForo.BRCPMpurchaseForm = function($form) { this.__construct($form); };
	XenForo.BRCPMpurchaseForm.prototype =
	{
		__construct: function($form)
		{
			this.$form = $form;
			this.$url = 'index.php?payment-perfectmoney/get-forum-amount.json';
			this.$input = $form.find('.brcAmount')
				.change($.context(this, 'update'));

			this.$select = $form.find('.brcCurrency')
				.change($.context(this, 'update'));

			this.update(this);
		},

		update: function(e)
		{
			var $amount = this.$input.val(),
			$currencyId = this.$select.val();
			if (this.xhr)
			{
				this.xhr.abort();
				this.xhr = false;
			}
			if (!this.xhr)
			{
				$triggerLink = this.$url+'&_xfResponseType=json';
				this.xhr = XenForo.ajax(
					$triggerLink,
					{
						amount: $amount,
						currency_id: $currencyId
					},
					$.context(this, 'addComplete')
					,{cache: false}
				);
			}
		},


		addComplete: function(ajaxData)
		{
			if (XenForo.hasResponseError(ajaxData))
			{
				return false;
			}
			delete(this.xhr);

			this.$form.find('#amountMessage').html(ajaxData.amountMessage);
			this.$form.find('#transactionTitle').val(ajaxData.transactionTitle);
			this.$form.find('#ctrl_custom').val(ajaxData.custom);
		}
	};


	XenForo.register('.BRCPMpurchaseForm', 'XenForo.BRCPMpurchaseForm');

}
(jQuery, this, document);