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
})(jQuery);
