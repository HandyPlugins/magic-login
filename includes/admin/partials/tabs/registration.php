<?php
/**
 * Registration settings partial
 *
 * @package MagicLogin\Admin
 */

use function MagicLogin\Utils\get_doc_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings = \MagicLogin\Utils\get_settings();


// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

?>

<div role="tabpanel" tabindex="0" id="registration__content" class="sui-tab-content magic-login-main-tab-content sui-disabled" aria-labelledby="registration__tab" hidden="">
	<!-- Registration -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Registration', 'magic-login' ); ?></span>
			<span class="sui-description">
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: 1: Documentation URL 2: 'Learn More' text */
						__( 'Activate registration feature. <a href="%1$s" target="_blank" rel="noopener">%2$s</a>', 'magic-login' ),
						get_doc_url( 'docs/magic-login-registration/' ),
						__( 'Learn More.', 'magic-login' )
					)
				);
				?>
			</span>								</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="registration_enable" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="registration_enable"
					       id="registration_enable"
					       aria-labelledby="registration_enable_label"
					       aria-controls="registration-details"
						<?php checked( 1, $settings['registration']['enable'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="registration_enable_label" class="sui-toggle-label"><?php esc_html_e( 'Enable registration.', 'magic-login' ); ?></span>
					<span class="sui-description"><?php esc_html_e( 'Allow users to register your site magically.', 'magic-login' ); ?></span>
				</label>
			</div>
		</div>
	</div>

	<!-- Registration Mode -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Registration Mode', 'magic-login' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Choose how you want to handle the registration process.', 'magic-login' ); ?></span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field" role="radiogroup">
				<label for="auto-registration" class="sui-radio sui-radio-stacked">
					<input
						type="radio"
						value="auto"
						name="registration_mode"
						id="auto-registration"
						value="auto"
						aria-controls="auto-registration-details"
						<?php checked( 'auto', $settings['registration']['mode'] ); ?>
					>
					<span aria-hidden="true"></span>
					<span class="sui-label-inline">
						<?php esc_html_e( 'Automatically register if the user enters an email address that has not registered.', 'magic-login' ); ?>
					</span>
				</label>

				<label for="standard-registration" class="sui-radio sui-radio-stacked">
					<input
						type="radio"
						name="registration_mode"
						id="standard-registration"
						value="standard"
						aria-controls="standard-registration-details"

						<?php checked( 'standard', $settings['registration']['mode'] ); ?>
					>
					<span aria-hidden="true"></span>
					<span class="sui-label-inline">
						<?php esc_html_e( 'Standard registration form.', 'magic-login' ); ?>
						<?php esc_html_e( 'The registration form will be displayed if the email address is not found during login.', 'magic-login' ); ?>
					</span>

				</label>

				<label for="shortcode-registration" class="sui-radio sui-radio-stacked">
					<input
						type="radio"
						name="registration_mode"
						id="shortcode-registration"
						value="shortcode"
						aria-controls="shortcode-registration-details"
						<?php checked( 'shortcode', $settings['registration']['mode'] ); ?>
					>
					<span aria-hidden="true"></span>
					<span class="sui-label-inline">
						<?php esc_html_e( 'Only with registration shortcode, do not integrate with magic login form.', 'magic-login' ); ?>
					</span>
				</label>
			</div>

			<div id="auto-registration-details" style="<?php echo( 'auto' === $settings['registration']['mode'] ? '' : 'display:none' ); ?>" tabindex="0">
				<div class="sui-form-field">
					<label for="fallback_email_field" class="sui-toggle">
						<input type="checkbox"
						       value="1"
						       name="fallback_email_field"
						       id="fallback_email_field"
						       aria-labelledby="fallback_email_field_label"
							<?php checked( 1, $settings['registration']['fallback_email_field'] ); ?>
						>
						<span class="sui-toggle-slider" aria-hidden="true"></span>
						<span id="fallback_email_field_label" class="sui-toggle-label">
							<?php esc_html_e( 'Show the email field if a username or invalid email is entered.', 'magic-login' ); ?>
						</span>
					</label>
				</div>
			</div>


			<div id="standard-registration-details" style="<?php echo( 'standard' === $settings['registration']['mode'] ? '' : 'display:none' ); ?>" tabindex="0">
				<div class="sui-form-field">
					<label for="registration_show_name_field" class="sui-toggle">
						<input type="checkbox"
						       value="1"
						       name="registration_show_name_field"
						       id="registration_show_name_field"
						       aria-labelledby="registration_show_name_field_label"
						       aria-controls="registration_name_field_details"
							<?php checked( 1, $settings['registration']['show_name_field'] ); ?>
						>
						<span class="sui-toggle-slider" aria-hidden="true"></span>
						<span id="registration_show_name_field_label" class="sui-toggle-label">
							<?php esc_html_e( 'Show first name and last name fields for registration.', 'magic-login' ); ?>
						</span>
					</label>
					<div id="registration_name_field_details"  style="<?php echo( $settings['registration']['show_name_field'] ? '' : 'display:none' ); ?>" >
						<label for="registration_require_name_field" class="sui-toggle">
							<input type="checkbox"
							       value="1"
							       name="registration_require_name_field"
							       id="registration_require_name_field"
							       aria-labelledby="registration_require_name_field_label"
								<?php checked( 1, $settings['registration']['require_name_field'] ); ?>
							>
							<span class="sui-toggle-slider" aria-hidden="true"></span>
							<span id="registration_require_name_field_label" class="sui-toggle-label">
								<?php esc_html_e( 'Mark as required.', 'magic-login' ); ?>
							</span>
						</label>
					</div>
				</div>

				<div class="sui-form-field">
					<label for="registration_show_terms_field" class="sui-toggle">
						<input type="checkbox"
						       value="1"
						       name="registration_show_terms_field"
						       id="registration_show_terms_field"
						       aria-labelledby="registration_show_terms_field_label"
						       aria-controls="registration_terms_field_details"
							<?php checked( 1, $settings['registration']['show_terms_field'] ); ?>
						>
						<span class="sui-toggle-slider" aria-hidden="true"></span>
						<span id="registration_show_terms_field_label" class="sui-toggle-label">
							<?php esc_html_e( 'Show terms field.', 'magic-login' ); ?>
						</span>

					</label>
					<div id="registration_terms_field_details"  style="<?php echo( $settings['registration']['show_terms_field'] ? '' : 'display:none' ); ?>">
						<label for="registration_require_terms_field" class="sui-toggle">
							<input type="checkbox"
							       value="1"
							       name="registration_require_terms_field"
							       id="registration_require_terms_field"
							       aria-labelledby="registration_require_terms_field_label"
								<?php checked( 1, $settings['registration']['require_terms_field'] ); ?>
							>
							<span class="sui-toggle-slider" aria-hidden="true"></span>
							<span id="registration_require_terms_field_label" class="sui-toggle-label">
								<?php esc_html_e( 'Mark as required.', 'magic-login' ); ?>
							</span>
						</label>
						<span class="sui-description">
							<?php esc_html_e( 'The visitors will required to accept following terms you stated in the textarea.', 'magic-login' ); ?>
						</span>
					</div>

				</div>
				<div class="sui-form-field">
					<?php
					\wp_editor(
						$settings['registration']['terms'],
						'registration_terms',
						[
							'media_buttons'    => false,
							'textarea_name'    => 'registration_terms',
							'editor_css'       => '',
							'editor_height'    => 5,
							'drag_drop_upload' => false,
							'tinymce'          => false,
							'teeny'            => false,
						]
					);
					?>
					<span id="registration-terms-description" class="sui-description">
						<?php esc_html_e( 'Enter the condition text you want the users to accept.', 'magic-login' ); ?>
					</span>
				</div>
			</div>

			<div id="shortcode-registration-details" style="<?php echo( 'shortcode' === $settings['registration']['mode'] ? '' : 'display:none' ); ?>" tabindex="0">
				<div class="sui-notice sui-notice-info" style="padding: 10px 20px 10px 0; margin:0;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p>
								<?php
								echo wp_kses_post(
									sprintf(
									/* translators: 1: Magic Login registration shortcode 2: Documentation URL 3: 'Learn More' text */
										__( 'To add a registration form to any page, use the shortcode <code>%1$s</code>. <a href="%2$s" target="_blank" rel="noopener">%3$s</a>', 'magic-login' ),
										'[magic_login_registration_form]',
										get_doc_url( 'docs/magic-login-registration/' ),
										__( 'Learn More.', 'magic-login' )
									)
								);
								?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Allowed Domain -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Domain Restriction', 'magic-login' ); ?>
			</span>
			<span id="domain-restriction-description" class="sui-description"><?php esc_html_e( 'Allow only listed domains for registration.', 'magic-login' ); ?></span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="enable_registration_domain_restriction" class="sui-toggle">
					<input
						type="checkbox"
						value="1"
						name="enable_registration_domain_restriction"
						id="enable_registration_domain_restriction"
						aria-describedby="enable-registration-domain-restriction-description"
						aria-controls="enable-registration-domain-restriction-controls"
						<?php checked( 1, $settings['registration']['enable_domain_restriction'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="domain-restriction-label" class="sui-toggle-label"><?php esc_html_e( 'Enable domain restriction', 'magic-login' ); ?></span>
					<span class="sui-description">
					</span>
				</label>

				<div style=" <?php echo( ! $settings['registration']['enable_domain_restriction'] ? 'display:none' : '' ); ?>" tabindex="0" id="enable-registration-domain-restriction-controls" class="sui-toggle-content sui-border-frame">
					<div class="sui-form-field">
																<textarea
																	placeholder="example.com"
																	id="allowed_registration_domains"
																	name="allowed_registration_domains"
																	class="sui-form-control"
																	aria-describedby="allowed-domains-description"
																	rows="7"
																><?php echo esc_textarea( $settings['registration']['allowed_domains'] ); ?></textarea>
						<span id="allowed-domains-description" class="sui-description"><?php esc_html_e( 'Enter allowed domains line by line.', 'magic-login' ); ?></span>
					</div>
				</div>
			</div>

		</div>
	</div>

	<!-- Registration Role -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label" id="registration_role_key"><?php esc_html_e( 'User Role', 'magic-login' ); ?></span>
		</div>

		<?php
		$roles = wp_roles()->get_names();
		?>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<select name="registration_role" id="select-magic-login-registration-role" class="sui-select">
					<option value=""><?php esc_html_e( 'Default role (from General Settings)', 'magic-login' ); ?></option>
					<?php foreach ( $roles as $role => $role_name ) : ?>
						<option <?php selected( $role, $settings['registration']['role'] ); ?> value="<?php echo esc_attr( $role ); ?>">
							<?php echo esc_attr( translate_user_role( $role_name ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<span class="sui-description"><?php esc_html_e( 'Choose the default role assigned to newly registered users. If no role is selected, WordPress will use the default role from General Settings.', 'magic-login' ); ?></span>
			</div>
		</div>
	</div>


	<!-- Registration Email -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label" id="registration_email_label"><?php esc_html_e( 'Registration Email', 'magic-login' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="registration_send_email" class="sui-toggle">
					<input type="checkbox"
					       value="1"
					       name="registration_send_email"
					       id="registration_send_email"
					       aria-labelledby="registration_send_email_label"
						<?php checked( 1, $settings['registration']['send_email'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="registration_send_email_label" class="sui-toggle-label"><?php esc_html_e( 'Send a registration email after signup.', 'magic-login' ); ?></span>
				</label>
			</div>

			<div class="sui-form-field">
				<span class="sui-settings-label" id="registration_email_subject_label"><?php esc_html_e( 'Email Subject', 'magic-login' ); ?></span>
				<input
					name="registration_email_subject"
					id="registration_email_subject"
					class="sui-form-control"
					aria-labelledby="registration_email_subject_label"
					type="text"
					value="<?php echo esc_attr( $settings['registration']['email_subject'] ); ?>"
				/>
			</div>

			<div class="sui-form-field">
				<span class="sui-settings-label" id="registration_email_label"><?php esc_html_e( 'Email Content', 'magic-login' ); ?></span>

				<?php
				wp_editor(
					$settings['registration']['email_content'],
					'registration_email_content',
					[
						'textarea_name'    => 'registration_email_content',
						'editor_css'       => '',
						'editor_height'    => 150,
						'drag_drop_upload' => false,
						'tinymce'          => false,
						'teeny'            => false,
						'media_buttons'    => false,
					]
				);
				?>
				<span class="sui-description"><?php esc_html_e( 'Supported placeholders: {{SITEURL}}, {{USERNAME}}, {{FIRST_NAME}}, {{LAST_NAME}}, {{FULL_NAME}}, {{DISPLAY_NAME}}, {{USER_EMAIL}, {{SITENAME}}, {{EXPIRES}}, {{MAGIC_LINK}}, {{MAGIC_LOGIN_QR}}, {{MAGIC_LOGIN_QR_IMG}}, {{EXPIRES_WITH_INTERVAL}}, {{TOKEN_VALIDITY_COUNT}}', 'magic-login' ); ?></span>
			</div>
		</div>

	</div>


	<!-- Registration Redirection -->
	<div class="sui-box-settings-row sui-disabled">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Redirection', 'magic-login' ); ?>
			</span>
			<span id="domain-restriction-description" class="sui-description">
				<?php esc_html_e( 'Redirect users to a specific page after successful registration.', 'magic-login' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="enable_registration_redirection" class="sui-toggle">
					<input
						type="checkbox"
						value="1"
						name="enable_registration_redirection"
						id="enable_registration_redirection"
						aria-describedby="enable-registration-redirection-description"
						aria-controls="enable-registration-redirection-controls"
						<?php checked( 1, $settings['registration']['enable_redirection'] ); ?>
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="domain-restriction-label" class="sui-toggle-label"><?php esc_html_e( 'Enable redirection', 'magic-login' ); ?></span>
					<span class="sui-description">
					</span>
				</label>

				<div style=" <?php echo( ! $settings['registration']['enable_redirection'] ? 'display:none' : '' ); ?>" tabindex="0" id="enable-registration-redirection-controls" class="sui-toggle-content sui-border-frame">
					<div class="sui-form-field">
						<label for="registration_redirection_url" class="sui-label">
							<?php esc_html_e( 'Redirection URL', 'magic-login' ); ?>
						</label>
						<input
							placeholder="https://example.com/welcome"
							id="registration_redirection_url"
							name="registration_redirection_url"
							class="sui-form-control"
							aria-labelledby="label-registration-redirection-url"
							aria-describedby="description-registration-redirection-url"
							value="<?php echo esc_url( $settings['registration']['redirection_url'] ); ?>"
						/>
						<span class="sui-description">
							<?php esc_html_e( 'Enter the URL to which users will be redirected after completing registration.', 'magic-login' ); ?>
						</span>
					</div>
				</div>
			</div>

		</div>
	</div>

	<!-- Upsell ads -->
	<div class="sui-box-settings-row sui-upsell-row">
		<div class="sui-upsell-notice" style="padding-left: 0;">
			<p><?php esc_html_e( 'With our pro version of magic login, you will unlock the registration features of the plugin and get access to the WP-CLI command along with our premium support.' ); ?><br>
				<a href="https://handyplugins.co/magic-login-pro/?utm_source=wp_admin&utm_medium=plugin&utm_campaign=settings_page_registration" rel="noopener noreferrer nofollow" target="_blank" class="sui-button sui-button-purple" style="margin-top: 10px;color:#fff;"><?php esc_html_e( 'Try Magic Login Pro Today', 'magic-login' ); ?></a>
			</p>
		</div>
	</div>


</div>
