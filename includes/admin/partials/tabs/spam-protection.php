<?php
/**
 * Spam protection settings partial
 *
 * @package MagicLogin\Admin
 */

use function MagicLogin\Utils\get_doc_url;
use function MagicLogin\Utils\mask_string;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

?>

<div role="tabpanel" tabindex="0" id="spam_protection__content" class="sui-tab-content magic-login-main-tab-content sui-disabled" aria-labelledby="spam_protection__tab">
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label" id="spam_protection_service_label"><?php esc_html_e( 'Spam Protection Service', 'magic-login' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'A CAPTCHA is an anti-spam technique which helps to protect your website from spam and abuse.', 'magic-login' ); ?>
				<?php esc_html_e( 'Magic Login currently supports both reCAPTCHA and Cloudflare Turnstile if you do not want to use captcha service.', 'magic-login' ); ?>
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: 1: Documentation URL 2: 'Learn More' text */
						__( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', 'magic-login' ),
						get_doc_url( 'docs/magic-login-spam-protection/' ),
						__( 'Learn More.', 'magic-login' )
					)
				);
				?>
			</span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field sui-box-selectors">
				<ul role="radiogroup">
					<li>
						<label for="recaptcha" class="sui-box-selector ">
							<input
								<?php checked( $settings['spam_protection']['service'], 'recaptcha' ); ?>
								type="radio"
								name="spam_protection_service"
								value="recaptcha"
								id="recaptcha"
								aria-labelledby="recaptcha-label"
								aria-controls="recaptcha-details"
							>
							<span aria-hidden="true">
								<span id="recaptcha-label" aria-hidden="true"><?php esc_html_e( 'reCAPTCHA', 'magic-login' ); ?></span>
							</span>
						</label>
					</li>
					<li>
						<label for="cf_turnstile" class="sui-box-selector">
							<input
								<?php checked( $settings['spam_protection']['service'], 'cf_turnstile' ); ?>
								type="radio"
								name="spam_protection_service"
								value="cf_turnstile"
								id="cf_turnstile"
								aria-labelledby="cf_turnstile-label"
								aria-controls="cf-turnstile-details"
							>
							<span aria-hidden="true">
								<span id="cf_turnstile-label" aria-hidden="true"><?php esc_html_e( 'Cloudflare Turnstile', 'magic-login' ); ?></span>
							</span>
						</label>
					</li>
				</ul>
			</div>

			<div class="sui-form-field">
				<div id="recaptcha-details" class="spam-protection-service-settings" style=" <?php echo( 'recaptcha' !== $settings['spam_protection']['service'] ? 'display:none' : '' ); ?>" tabindex="0">
					<div tabindex="1" role="tabpanel" id="g-recaptcha-tab" class="sui-tab-content <?php echo esc_attr( 'recaptcha' === $settings['spam_protection']['service'] ? 'active' : '' ); ?>" aria-labelledby="g-recaptcha-btn">

						<span class="sui-settings-label"><?php esc_html_e( 'reCaptcha API Keys', 'magic-login' ); ?></span>
						<span class="sui-description" style="margin-bottom: 10px;">
							<?php
							printf(
							/* Translators: 1. Opening <a> tag with link to Google recaptcha, 2. closing <a> tag. */
								esc_html__( 'Enter the API keys for each reCAPTCHA type you want to use in your forms. Note that each reCAPTCHA type requires a different set of API keys. %1$sGenerate API keys%2$s', 'magic-login' ),
								'<a href="https://www.google.com/recaptcha/admin#list" target="_blank">',
								'</a>'
							);
							?>
						</span>

						<div class="sui-tabs sui-side-tabs">
							<div role="tablist" class="sui-tabs-menu">
								<button
									type="button"
									role="tab"
									id="v2-checkbox"
									class="sui-tab-item <?php echo( 'v2_checkbox' === $settings['recaptcha']['type'] ? 'active' : '' ); ?>"
									aria-controls="v2-checkbox-tab"
									aria-selected="<?php echo( 'v2_checkbox' === $settings['recaptcha']['type'] ? 'true' : 'false' ); ?>"
								>
									<?php esc_attr_e( 'v2 Checkbox', 'magic-login' ); ?>
								</button>
								<input
									type="radio"
									name="recaptcha_type"
									value="v2_checkbox"
									class="sui-screen-reader-text"
									aria-label="<?php esc_attr_e( 'v2 Checkbox', 'magic-login' ); ?>"
									aria-hidden="true"
									<?php checked( 'v2_checkbox', $settings['recaptcha']['type'] ); ?>
								/>

								<button
									type="button"
									role="tab"
									id="v2-invisible"
									class="sui-tab-item <?php echo( 'v2_invisible' === $settings['recaptcha']['type'] ? 'active' : '' ); ?>"
									aria-controls="v2-invisible-tab"
									aria-selected="<?php echo( 'v2_invisible' === $settings['recaptcha']['type'] ? 'true' : 'false' ); ?>">
									<?php esc_attr_e( 'v2 Invisible', 'magic-login' ); ?>
								</button>
								<input
									type="radio"
									name="recaptcha_type"
									value="v2_invisible"
									class="sui-screen-reader-text"
									aria-label="<?php esc_attr_e( 'v2 Invisible', 'magic-login' ); ?>"
									<?php checked( 'v2_invisible', $settings['recaptcha']['type'] ); ?>
									aria-hidden="true" />
								<button
									type="button"
									role="tab"
									id="recaptcha-v3"
									class="sui-tab-item <?php echo( 'v3' === $settings['recaptcha']['type'] ? 'active' : '' ); ?>"
									aria-controls="v3-recaptcha-tab"
									aria-selected="<?php echo( 'v3' === $settings['recaptcha']['type'] ? 'true' : 'false' ); ?>">
									<?php esc_attr_e( 'v3 reCaptcha', 'magic-login' ); ?>
								</button>
								<input
									type="radio"
									name="recaptcha_type"
									value="v3"
									class="sui-screen-reader-text"
									aria-label="<?php esc_attr_e( 'v3 reCaptcha', 'magic-login' ); ?>"
									<?php checked( 'v3', $settings['recaptcha']['type'] ); ?>
									aria-hidden="true" />

							</div>

							<div class="sui-tabs-content">

								<?php // TAB: v2 Checkbox ?>
								<div tabindex="0" role="tabpanel" id="v2-checkbox-tab" class="sui-tab-content sui-tab-boxed <?php echo( 'v2_checkbox' === $settings['recaptcha']['type'] ? 'active' : '' ); ?>" aria-labelledby="v2-checkbox">

									<span class="sui-description"><?php esc_html_e( 'Enter the API keys for reCAPTCHA v2 Checkbox type below:', 'magic-login' ); ?></span>

									<div class="sui-form-field">
										<label for="v2_captcha_key" id="v2checkbox-sitekey-label" class="sui-label"><?php esc_html_e( 'Site Key', 'magic-login' ); ?></label>
										<input
											type="text"
											name="v2_captcha_key"
											placeholder="<?php esc_attr_e( 'Enter your site key here', 'magic-login' ); ?>"
											value="<?php echo esc_attr( $settings['recaptcha']['v2_checkbox']['site_key'] ); ?>"
											id="v2_captcha_key"
											class="sui-form-control"
											aria-labelledby="v2checkbox-sitekey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label for="v2_captcha_secret" id="v2checkbox-secretkey-label" class="sui-label"><?php esc_html_e( 'Secret Key', 'magic-login' ); ?></label>
										<input
											type="text"
											name="v2_captcha_secret"
											placeholder="<?php esc_attr_e( 'Enter your secret key here', 'magic-login' ); ?>"
											value="<?php echo esc_attr( mask_string( \MagicLogin\Utils\get_decrypted_value( $settings['recaptcha']['v2_checkbox']['secret_key'] ), 5 ) ); ?>"
											id="v2_captcha_secret"
											class="sui-form-control"
											aria-labelledby="v2checkbox-secretkey-label"
										/>
									</div>

								</div>

								<?php // TAB: v2 Invisible. ?>
								<div tabindex="0" role="tabpanel" id="v2-invisible-tab" class="sui-tab-content sui-tab-boxed  <?php echo( 'v2_invisible' === $settings['recaptcha']['type'] ? 'active' : '' ); ?>" aria-labelledby="v2-invisible" hidden>

									<span class="sui-description"><?php esc_html_e( 'Enter the API keys for reCAPTCHA v2 Invisible type below:', 'magic-login' ); ?></span>

									<div class="sui-form-field">
										<label for="invisible_captcha_key" id="v2invisible-sitekey-label" class="sui-label"><?php esc_html_e( 'Site Key', 'magic-login' ); ?></label>
										<input
											type="text"
											name="v2_invisible_captcha_key"
											placeholder="<?php esc_attr_e( 'Enter your site key here', 'magic-login' ); ?>"
											value="<?php echo esc_attr( $settings['recaptcha']['v2_invisible']['site_key'] ); ?>"
											id="invisible_captcha_key"
											class="sui-form-control"
											aria-labelledby="v2invisible-sitekey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label for="invisible_captcha_secret" id="v2invisible-secretkey-label" class="sui-label"><?php esc_html_e( 'Secret Key', 'magic-login' ); ?></label>
										<input
											type="text"
											name="v2_invisible_captcha_secret"
											placeholder="<?php esc_attr_e( 'Enter your secret key here', 'magic-login' ); ?>"
											value="<?php echo esc_attr( mask_string( \MagicLogin\Utils\get_decrypted_value( $settings['recaptcha']['v2_invisible']['secret_key'] ), 5 ) ); ?>"
											id="invisible_captcha_secret"
											class="sui-form-control"
											aria-labelledby="v2invisible-secretkey-label"
										/>
									</div>

								</div>

								<?php // TAB: v3 reCaptcha. ?>
								<div tabindex="0" role="tabpanel" id="v3-recaptcha-tab" class="sui-tab-content sui-tab-boxed <?php echo( 'v3' === $settings['recaptcha']['type'] ? 'active' : '' ); ?>" aria-labelledby="recaptcha-v3" hidden>

									<span class="sui-description"><?php esc_html_e( 'Enter the API keys for reCAPTCHA v3 type below:', 'magic-login' ); ?></span>

									<div class="sui-form-field">
										<label for="v3_captcha_key" id="v3recaptcha-sitekey-label" class="sui-label"><?php esc_html_e( 'Site Key', 'magic-login' ); ?></label>
										<input
											type="text"
											name="v3_captcha_key"
											placeholder="<?php esc_attr_e( 'Enter your site key here', 'magic-login' ); ?>"
											value="<?php echo esc_attr( $settings['recaptcha']['v3']['site_key'] ); ?>"
											id="v3_captcha_key"
											class="sui-form-control"
											aria-labelledby="v3recaptcha-sitekey-label"
										/>
									</div>

									<div class="sui-form-field">
										<label for="v3_captcha_secret" id="v3recaptcha-secretkey-label" class="sui-label"><?php esc_html_e( 'Secret Key', 'magic-login' ); ?></label>
										<input
											type="text"
											name="v3_captcha_secret"
											placeholder="<?php esc_attr_e( 'Enter your secret key here', 'magic-login' ); ?>"
											value="<?php echo esc_attr( mask_string( \MagicLogin\Utils\get_decrypted_value( $settings['recaptcha']['v3']['secret_key'] ), 5 ) ); ?>"
											id="v3_captcha_secret"
											class="sui-form-control"
											aria-labelledby="v3recaptcha-secretkey-label"
										/>
									</div>

								</div>

							</div>

						</div>

					</div>
				</div>

				<div id="cf_turnstile-details" class="spam-protection-service-settings " style=" <?php echo( 'cf_turnstile' !== $settings['spam_protection']['service'] ? 'display:none' : '' ); ?>" tabindex="0">
					<div class="sui-tabs sui-side-tabs">
						<div class="sui-tabs-content">
							<div class="sui-tab-content sui-tab-boxed active">
								<span class="sui-settings-label"><?php esc_html_e( 'Cloudflare Turnstile API Keys', 'magic-login' ); ?></span>
								<span class="sui-description">
									<?php
									printf(
									/* Translators: 1. Opening <a> tag with link to Cloudflare Turnstile, 2. closing <a> tag. */
										esc_html__( 'Enter the API keys for Cloudflare Turnstile. %1$sLearn More%2$s', 'magic-login' ),
										'<a href="https://www.cloudflare.com/products/turnstile/"  rel="noopener" target="_blank">',
										'</a>'
									);
									?>
								</span>
								<div class="sui-form-field">
									<label for="cf_turnstile_key" id="cf_turnstile_key_label" class="sui-label"><?php esc_html_e( 'Site Key', 'magic-login' ); ?></label>
									<input type="text"
									       name="cf_turnstile_key"
									       placeholder="Enter your site key here"
									       value="<?php echo esc_attr( $settings['cf_turnstile']['site_key'] ); ?>"
									       id="cf_turnstile_key"
									       class="sui-form-control"
									       aria-labelledby="cf_turnstile_key_label"
									>
								</div>

								<div class="sui-form-field">
									<label for="cf_turnstile_secret" id="cf_turnstile_secret_label" class="sui-label"><?php esc_html_e( 'Site Secret', 'magic-login' ); ?></label>
									<input type="text"
									       name="cf_turnstile_secret"
									       placeholder="<?php esc_attr_e( 'Enter your site secret here', 'magic-login' ); ?>"
									       value="<?php echo esc_attr( mask_string( \MagicLogin\Utils\get_decrypted_value( $settings['cf_turnstile']['secret_key'] ), 5 ) ); ?>"
									       id="cf_turnstile_secret"
									       class="sui-form-control"
									       aria-labelledby="cf_turnstile_secret_label"
									>
								</div>

							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Spam Protection for Registration', 'magic-login' ); ?></span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="enable_spam_protection_registration" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="enable_spam_protection_registration"
					       id="enable_spam_protection_registration"
					       aria-labelledby="enable-spam-protection-label"
						<?php checked( 1, $settings['spam_protection']['enable_registration'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="enable-spam-protection-label" class="sui-toggle-label"><?php esc_html_e( 'Enable spam protection for registration form.', 'magic-login' ); ?></span>
				</label>
			</div>
		</div>
	</div>

	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Spam Protection for Login', 'magic-login' ); ?></span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="enable_spam_protection_login" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="enable_spam_protection_login"
					       id="enable_spam_protection_login"
					       aria-labelledby="enable-spam-protection-login-label"
						<?php checked( 1, $settings['spam_protection']['enable_login'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="enable-spam-protection-login-label" class="sui-toggle-label"><?php esc_html_e( 'Enable spam protection for login form.', 'magic-login' ); ?></span>
				</label>
			</div>
		</div>
	</div>

	<!-- Upsell ads -->
	<div class="sui-box-settings-row sui-upsell-row">
		<div class="sui-upsell-notice" style="padding-left: 0;">
			<p><?php esc_html_e( 'With our pro version of magic login, you will unlock the spam protection features and get access to the WP-CLI command along with our premium support.' ); ?><br>
				<a href="https://handyplugins.co/magic-login-pro/?utm_source=wp_admin&utm_medium=plugin&utm_campaign=settings_page_spam_protection" rel="noopener noreferrer nofollow" target="_blank" class="sui-button sui-button-purple" style="margin-top: 10px;color:#fff;"><?php esc_html_e( 'Try Magic Login Pro Today', 'magic-login' ); ?></a>
			</p>
		</div>
	</div>

</div>
