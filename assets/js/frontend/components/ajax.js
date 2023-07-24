// Ajaxify the form submission for shortcode and block
(function ($) {
	$(document).on('submit', '#magicloginform', function (e) {
		e.preventDefault();

		const $form = $(this);

		$.post(
			$form.data('ajax-url'),
			{
				beforeSend() {
					$form
						.find('.magic-login-submit ')
						.attr('disabled', 'disabled');
				},
				action: 'magic_login_ajax_request',
				data: $('#magicloginform').serialize(),
			},
			function (response) {
				$('.magic-login-form-header').html(response.data.message);
				if (!response.data.show_form) {
					$form.hide();
				}
			}
		).done(function () {
			$form.find('.magic-login-submit ').attr('disabled', false);
		});
	});
})(jQuery);
