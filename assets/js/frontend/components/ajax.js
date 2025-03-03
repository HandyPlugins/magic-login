// Ajaxify the form submission for shortcode and block
window.magicLoginAjaxEnabled = true;
(function ($) {
	$(document).on('submit', '#magicloginform', function (e) {
		e.preventDefault();

		const $form = $(this);
		$form.trigger('magic-login:login:before-submit');

		$.post(
			$form.data('ajax-url'),
			{
				beforeSend() {
					// show spinner
					const spinnerImg = '<img src="' + $form.data('ajax-spinner') + '" alt="'+ $form.data('ajax-sending-msg') +'" class="magic-login-spinner-image" />';
					const spinnerMessage = '<span class="magic-login-spinner-message">'+$form.data('ajax-sending-msg')+'</span>';
					const spinnerHtml = '<div class="magic-login-spinner-container">' + spinnerImg + spinnerMessage + '</div>';

					$('.magic-login-form-header').html(spinnerHtml);

					$form
						.find('.magic-login-submit ')
						.attr('disabled', 'disabled');

					$form.trigger('magic-login:login:before-send');
				},
				action: 'magic_login_ajax_request',
				data: $('#magicloginform').serialize(),
			},
			function (response) {
				$('.magic-login-form-header').html(response.data.message);
				if (!response.data.show_form) {
					$form.hide();
				}

				if (response.data.code_form) {
					$form.parent().replaceWith(response.data.code_form);
					$('.magic-login-code-form-header').find('.info').html($(response.data.info).text()).show();
					$('#magiclogincodeform').find('.magic-login-code-cancel').hide();
					maybeInitializeSpamProtection();
				}

				// Trigger a custom event after the AJAX request is complete
				$form.trigger('magic-login:login:ajax-success', [response]);
			}
		).fail(function (response) {
			// Trigger a custom event after the failed AJAX request
			$form.trigger('magic-login:login:ajax-fail', [response]);
		}).always(function () {
			$form.find('.magic-login-submit ').attr('disabled', false);
			$form.trigger('magic-login:login:always');
		});
	});

	$(document).on('submit', '#magiclogincodeform', function (e) {
		e.preventDefault();

		const $form = $(this);

		$form.trigger('magic-login:code-login:before-submit');

		$.ajax({
			type      : 'POST',
			url       : $form.data('ajax-url'),
			data      : $form.serialize() + '&action=magic_login_code_login',
			beforeSend: function () {
				const spinnerImg = '<img src="' + $form.data('ajax-spinner') + '" alt="' + $form.data('ajax-sending-msg') + '" class="magic-login-spinner-image" />';
				const spinnerMessage = '<span class="magic-login-spinner-message">' + $form.data('ajax-sending-msg') + '</span>';
				const spinnerHtml = '<div class="magic-login-spinner-container">' + spinnerImg + spinnerMessage + '</div>';
				$('.magic-login-code-form-header').find('.spinner').html(spinnerHtml).show();
				$form.find('.magic-login-code-submit').attr('disabled', 'disabled');
				$form.trigger('magic-login:code-login:before-send');
				$('.magic-login-code-form-header').find('.ajax-result').html('').removeClass('success error').hide();
			},
			success   : function (response) {
				if(response.success && response.data.message) {
					$('.magic-login-code-form-header').find('.ajax-result').html(response.data.message).addClass('success').removeClass('error').show();
				}

				if (response.data.redirect_to) {
					window.location.href = response.data.redirect_to;
				}

				if (!response.success) {
					$('.magic-login-code-form-header').find('.ajax-result').html(response.data.message).addClass('error').removeClass('success').show();
				}

				$form.trigger('magic-login:code-login:ajax-success', [response]);
			}
		})
			.fail(function (response) {
				$form.trigger('magic-login:code-login:ajax-fail', [response]);
			})
			.always(function () {
				$form.find('.magic-login-code-submit').attr('disabled', false);
				$form.trigger('magic-login:code-login:always');
				$form.find('.spinner').hide();
			});
	});

})(jQuery);
