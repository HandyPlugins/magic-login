<?php
/**
 * Sms settings partial
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
<div role="tabpanel" tabindex="0" id="sms__content" class="sui-tab-content magic-login-main-tab-content sui-disabled" aria-labelledby="sms__tab">

	<!-- Twilio Details -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label" id="twilio_service_label"><?php esc_html_e( 'Twilio Details', 'magic-login' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Twilio is a cloud communications platform that allows you to programmatically send and receive SMS messages.', 'magic-login' ); ?>
				<?php esc_html_e( 'To use Twilio with Magic Login, you need to provide your Twilio Account SID, Auth Token, and From number.', 'magic-login' ); ?>
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: 1: Documentation URL 2: 'Learn More' text */
						__( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', 'magic-login' ),
						get_doc_url( 'docs/magic-login-twilio-integration/' ),
						__( 'Learn More.', 'magic-login' )
					)
				);
				?>
			</span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-tabs sui-side-tabs">
				<div class="sui-tabs-content">
					<div class="sui-tab-content sui-tab-boxed active">
						<div class="sui-form-field">
							<label for="twilio_account_sid" id="twilio_account_sid_label" class="sui-label"><?php esc_html_e( 'Account SID', 'magic-login' ); ?></label>
							<input type="text"
							       name="twilio_account_sid"
							       placeholder="Enter your account SID here"
							       value="<?php echo esc_attr( $settings['sms']['twilio']['account_sid'] ); ?>"
							       id="twilio_account_sid"
							       class="sui-form-control"
							       aria-labelledby="twilio_account_sid_label"
							>
						</div>

						<div class="sui-form-field">
							<label for="twilio_auth_token" id="twilio_auth_token_label" class="sui-label"><?php esc_html_e( 'Auth Token', 'magic-login' ); ?></label>
							<input type="text"
							       name="twilio_auth_token"
							       placeholder="<?php esc_attr_e( 'Enter your auth token here', 'magic-login' ); ?>"
							       value="<?php echo esc_attr( mask_string( \MagicLogin\Utils\get_decrypted_value( $settings['sms']['twilio']['auth_token'] ), 5 ) ); ?>"
							       id="twilio_auth_token"
							       class="sui-form-control"
							       aria-labelledby="twilio_auth_token_label"
							>
						</div>

						<div class="sui-form-field">
							<label for="twilio_from_number" id="twilio_from_number_label" class="sui-label"><?php esc_html_e( 'From', 'magic-login' ); ?></label>
							<input type="text"
							       name="twilio_from_number"
							       placeholder="<?php esc_attr_e( 'Enter phone number here', 'magic-login' ); ?>"
							       value="<?php echo esc_attr( $settings['sms']['twilio']['from'] ); ?>"
							       id="twilio_from_number"
							       class="sui-form-control"
							       aria-labelledby="twilio_from_number_label"
							>
						</div>
					</div>
				</div>
			</div>

			<button role="button" id="open-magic-login-sms-test-modal" value="run_diognastic" class="sui-button sui-button-ghost sui-button-blue">
				<?php esc_html_e( 'Send a test SMS', 'magic-login' ); ?>
			</button>

		</div>

	</div>

	<!-- SMS -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Enable SMS Login', 'magic-login' ); ?></span>
			<span class="sui-description">
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: 1: Documentation URL 2: 'Learn More' text */
						__( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', 'magic-login' ),
						get_doc_url( 'docs/passwordless-authentication-with-sms/' ),
						__( 'Learn More', 'magic-login' )
					)
				);
				?>
			</span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="sms_enable" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="sms_enable"
					       id="sms_enable"
					       aria-labelledby="sms_enable_label"
					       aria-controls="sms-details"
						<?php checked( 1, $settings['sms']['enable'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="sms_enable_label" class="sui-toggle-label"><?php esc_html_e( 'Allow users to receive Magic Login links via SMS.', 'magic-login' ); ?></span>

				</label>
			</div>
		</div>
	</div>

	<div id="sms-details" style="<?php echo( $settings['sms']['enable'] ? '' : 'display:none' ); ?>" class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'SMS Sending Strategy', 'magic-login' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Choose how SMS is sent for login requests.', 'magic-login' ); ?></span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field" role="radiogroup">
				<label for="sms_phone_only" class="sui-radio sui-radio-stacked">
					<input
						type="radio"
						value="phone_only"
						name="sms_sending_strategy"
						id="sms_phone_only"
						value="phone_only"
						<?php checked( 'phone_only', $settings['sms']['sms_sending_strategy'] ); ?>
					>
					<span aria-hidden="true"></span>
					<span class="sui-label-inline">
						<?php esc_html_e( 'Send SMS only when user enters phone number.', 'magic-login' ); ?>
					</span>
				</label>

				<label for="sms_and_email" class="sui-radio sui-radio-stacked">
					<input
						type="radio"
						name="sms_sending_strategy"
						id="sms_and_email"
						value="sms_and_email"

						<?php checked( 'sms_and_email', $settings['sms']['sms_sending_strategy'] ); ?>
					>
					<span aria-hidden="true"></span>
					<span class="sui-label-inline">
						<?php esc_html_e( 'If the user has a phone number linked to their account, send both SMS and email.', 'magic-login' ); ?>
					</span>
				</label>

				<label for="sms_or_email" class="sui-radio sui-radio-stacked">
					<input
						type="radio"
						name="sms_sending_strategy"
						id="sms_or_email"
						value="sms_or_email"

						<?php checked( 'sms_or_email', $settings['sms']['sms_sending_strategy'] ); ?>
					>
					<span aria-hidden="true"></span>
					<span class="sui-label-inline">
						<?php esc_html_e( 'Send SMS if the user has a linked phone number. If no phone number is found, fallback to email.', 'magic-login' ); ?>
					</span>
				</label>
			</div>
		</div>
	</div>

	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'SMS Message', 'magic-login' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="sms_login_message" id="sms_login_message_label" class="sui-label"><?php esc_html_e( 'SMS Message', 'magic-login' ); ?></label>
				<textarea
					name="sms_login_message"
					id="sms_login_message"
					class="sui-form-control"
					aria-labelledby="sms_login_message_label"
					maxlength="300"
				><?php echo esc_textarea( $settings['sms']['login_message'] ); ?></textarea>
				<span class="sui-description">
					<?php esc_html_e( 'Customize the SMS message that will be sent to the user.', 'magic-login' ); ?>
					<?php esc_html_e( 'Supported placeholders: {{SITEURL}}, {{USERNAME}}, {{FIRST_NAME}}, {{LAST_NAME}}, {{FULL_NAME}}, {{DISPLAY_NAME}}, {{USER_EMAIL}, {{SITENAME}}, {{EXPIRES}}, {{MAGIC_LINK}}, {{MAGIC_LOGIN_CODE}}, {{EXPIRES_WITH_INTERVAL}}, {{TOKEN_VALIDITY_COUNT}}', 'magic-login' ); ?>
				</span>
			</div>
		</div>
	</div>


	<!--  Registration SMS -->
	<?php $can_register = get_option( 'users_can_register' ); ?>

	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Add Phone Number Field to Registration Forms', 'magic-login' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">

			<!-- WordPress Registration Toggle -->
			<div class="sui-form-field">
				<label for="sms_wp_registration" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="sms_wp_registration"
					       id="sms_wp_registration"
					       aria-labelledby="sms_wp_registration_label"
					       aria-controls="wp_registration_phone_field_details"
						<?php checked( 1, $settings['sms']['wp_registration'] ); ?>
						<?php echo ! $can_register ? 'disabled' : ''; ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="sms_wp_registration_label" class="sui-toggle-label">
						<?php esc_html_e( 'Add a phone number field to WordPress registration.', 'magic-login' ); ?>
					</span>
				</label>
				<p class="sui-description">
					<?php if ( ! $can_register ) : ?>
						🛑 <?php esc_html_e( 'WordPress registration is currently disabled. Enable it under **Settings → General → Membership**.', 'magic-login' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'This will add a phone number field to the default WordPress registration form.', 'magic-login' ); ?>
					<?php endif; ?>
				</p>
				<!-- Require Phone Field in WP Registration -->
				<div id="wp_registration_phone_field_details" style="<?php echo ( $settings['sms']['wp_registration'] ? '' : 'display:none' ); ?>">
					<label for="sms_wp_require_phone" class="sui-toggle">
						<input type="checkbox"
						       value="1"
						       name="sms_wp_require_phone"
						       id="sms_wp_require_phone"
						       aria-labelledby="sms_wp_require_phone_label"
							<?php checked( 1, $settings['sms']['wp_require_phone'] ); ?>
						>
						<span class="sui-toggle-slider" aria-hidden="true"></span>
						<span id="sms_wp_require_phone_label" class="sui-toggle-label">
							<?php esc_html_e( 'Make phone number required for WordPress registration.', 'magic-login' ); ?>
						</span>
					</label>
				</div>
			</div>



			<!-- Magic Login Registration Toggle -->
			<div class="sui-form-field">
				<label for="sms_magic_registration" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="sms_magic_registration"
					       id="sms_magic_registration"
					       aria-labelledby="sms_magic_registration_label"
					       aria-controls="magic_registration_phone_field_details"
						<?php checked( 1, $settings['sms']['magic_registration'] ); ?>
						<?php echo ! $settings['registration']['enable'] ? 'disabled' : ''; ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="sms_magic_registration_label" class="sui-toggle-label">
						<?php esc_html_e( 'Add a phone number field to Magic Login registration.', 'magic-login' ); ?>
					</span>
				</label>
				<p class="sui-description">
					<?php if ( ! $settings['registration']['enable'] ) : ?>
						🛑 <?php esc_html_e( 'Magic Login registration is disabled. Enable it in the Registration tab above.', 'magic-login' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'This will add a phone number field to the Magic Login registration form.', 'magic-login' ); ?>
					<?php endif; ?>
				</p>
				<!-- Require Phone Field in Magic Login Registration -->
				<div id="magic_registration_phone_field_details" style="<?php echo ( $settings['sms']['magic_registration'] ? '' : 'display:none' ); ?>">
					<label for="sms_magic_registration_require_phone" class="sui-toggle">
						<input type="checkbox"
						       value="1"
						       name="sms_magic_registration_require_phone"
						       id="sms_magic_registration_require_phone"
						       aria-labelledby="sms_magic_registration_require_phone_label"
							<?php checked( 1, $settings['sms']['magic_registration_require_phone'] ); ?>
						>
						<span class="sui-toggle-slider" aria-hidden="true"></span>
						<span id="sms_magic_registration_require_phone_label" class="sui-toggle-label">
							<?php esc_html_e( 'Make phone number required for Magic Login registration.', 'magic-login' ); ?>
						</span>
					</label>
				</div>
			</div>



		</div>
	</div>



	<!-- Registration SMS -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Send SMS After Registration', 'magic-login' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="sms_send_registration_message" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="sms_send_registration_message"
					       id="sms_send_registration_message"
					       aria-labelledby="sms_send_registration_message_label"
					       aria-controls="registration-sms-details"
						<?php checked( 1, $settings['sms']['send_registration_message'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="sms_send_registration_message_enable" class="sui-toggle-label">
						<?php esc_html_e( 'Automatically send an SMS message when a new user registers with a phone number.', 'magic-login' ); ?>
					</span>
				</label>
			</div>
		</div>
	</div>

	<div id="registration-sms-details" style="<?php echo( $settings['sms']['send_registration_message'] ? '' : 'display:none' ); ?>" class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Registration SMS', 'magic-login' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="sms_registration_message" id="sms_registration_message_label" class="sui-label"><?php esc_html_e( 'SMS Message', 'magic-login' ); ?></label>
				<textarea
					cols="50"
					name="sms_registration_message"
					id="sms_registration_message"
					class="sui-form-control"
					aria-labelledby="sms_registration_message_label"
				><?php echo esc_textarea( $settings['sms']['registration_message'] ); ?></textarea>
				<span class="sui-description">
					<?php esc_html_e( 'Customize the SMS message sent to new users after registration.', 'magic-login' ); ?>
					<?php esc_html_e( 'Supported placeholders: {{SITEURL}}, {{USERNAME}}, {{FIRST_NAME}}, {{LAST_NAME}}, {{FULL_NAME}}, {{DISPLAY_NAME}}, {{USER_EMAIL}, {{SITENAME}}, {{EXPIRES}}, {{MAGIC_LINK}}, {{MAGIC_LOGIN_CODE}}, {{EXPIRES_WITH_INTERVAL}}, {{TOKEN_VALIDITY_COUNT}}', 'magic-login' ); ?>
				</span>
			</div>
		</div>
	</div>

	<!-- Upsell ads -->
	<div class="sui-box-settings-row sui-upsell-row">
		<div class="sui-upsell-notice" style="padding-left: 0;">
			<p><?php esc_html_e( 'Upgrade to the Pro version to enable SMS authentication and gain access to all premium features—plus dedicated support to keep your login experience smooth and secure.' ); ?><br>
				<a href="https://handyplugins.co/magic-login-pro/?utm_source=wp_admin&utm_medium=plugin&utm_campaign=settings_page_sms" rel="noopener noreferrer nofollow" target="_blank" class="sui-button sui-button-purple" style="margin-top: 10px;color:#fff;"><?php esc_html_e( 'Try Magic Login Pro Today', 'magic-login' ); ?></a>
			</p>
		</div>
	</div>

</div>
