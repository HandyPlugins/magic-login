<?php
/**
 * Settings page
 *
 * @package MagicLogin\Admin
 */

use function MagicLogin\Utils\get_ttl_with_interval;
use function MagicLogin\Utils\get_doc_url;
use function MagicLogin\Utils\get_allowed_intervals;
use function \MagicLogin\Utils\mask_string;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings = \MagicLogin\Utils\get_settings();

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

?>

<form method="post" action="">
	<?php wp_nonce_field( 'magic_login_settings', 'magic_login_settings' ); ?>
	<section class="sui-row-with-sidenav">

		<!-- TAB: Regular -->
		<div class="sui-box" data-tab="basic-options">

			<div class="sui-box-header">
				<h2 class="sui-box-title">
					<?php esc_html_e( 'Settings', 'magic-login' ); ?>
				</h2>
				<div class="sui-actions-right sui-hidden-important" style="display: none;">
					<button type="submit" class="sui-button sui-button-blue" id="magic-login-save-settings-top" data-msg="">
						<i class="sui-icon-save" aria-hidden="true"></i>
						<?php esc_html_e( 'Update settings', 'magic-login' ); ?>
					</button>
				</div>
			</div>

			<div class="sui-box-body sui-upsell-items">
				<div class="sui-tabs sui-padding-right sui-padding-left">
					<div role="tablist" class="sui-tabs-menu magic-login-main-tab-nav" style="border-top:none;">
						<button type="button" role="tab" id="login__tab" class="sui-tab-item magic-login-main-tab-item active" aria-controls="login_content" aria-selected="true">
							<?php esc_html_e( 'Login', 'magic-login' ); ?>
						</button>

						<button type="button" role="tab" id="registration__tab" class="sui-tab-item magic-login-main-tab-item" aria-controls="registration__content" aria-selected="false" tabindex="-1">
							<?php esc_html_e( 'Registration', 'magic-login' ); ?>
							<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
						</button>

						<button type="button" role="tab" id="spam_protection__tab" class="sui-tab-item magic-login-main-tab-item" aria-controls="spam_protection__content" aria-selected="false" tabindex="-1">
							<?php esc_html_e( 'Spam Protection', 'magic-login' ); ?>
							<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
						</button>
					</div>

					<div class="sui-tabs-content">
						<div role="tabpanel" tabindex="0" id="login_content" class="sui-tab-content magic-login-main-tab-content active" aria-labelledby="login__tab">
							<!-- Force Magic Login  -->
							<div class="sui-box-settings-row">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" for="force-magic-login"><?php esc_html_e( 'Force Magic Login', 'magic-login' ); ?></span>
									<span class="sui-description"><?php esc_html_e( 'Change default login behaviour and use magic login as default login method.', 'magic-login' ); ?></span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="is_default" class="sui-toggle">
											<input type="checkbox"
											       value="1"
											       name="is_default"
											       id="is_default"
											       aria-labelledby="force-magic-login-label"
												<?php checked( 1, $settings['is_default'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="force-magic-login-label" class="sui-toggle-label"><?php esc_html_e( 'Use magic login by default', 'magic-login' ); ?></span>
										</label>
										<span class="sui-description">
											<?php
											echo wp_kses_post(
												sprintf(
												/* translators: 1: Login URL with Magic Login flavour */
													__( 'Magic login form is accessible <a href="%1$s">%1$s</a>', 'magic-login' ),
													esc_url( site_url( 'wp-login.php?action=magic_login', 'login_post' ) )
												)
											);
											?>
										</span>
										<div class="sui-notice sui-notice-info" style="padding: 10px 20px 10px 0; margin:0;">
											<div class="sui-notice-content">
												<div class="sui-notice-message">
													<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
													<p>
														<?php
														echo wp_kses_post(
															sprintf(
															/* translators: 1: Magic Login shortcode 2: Documentation URL 3: 'Learn More' text */
																__( 'In order to add a login form to any page, you can use shortcode <code>%1$s</code> or block. <a href="%2$s" target="_blank" rel="noopener">%3$s</a>' ),
																'[magic_login_form]',
																get_doc_url( 'docs/add-magic-login-form-to-a-page/' ),
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

							<!-- Add to login form  -->
							<div class="sui-box-settings-row">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" for="force-magic-login"><?php esc_html_e( 'Show on Login Form', 'magic-login' ); ?></span>
									<span class="sui-description"><?php esc_html_e( 'Adds magic login button to WordPress login form.', 'magic-login' ); ?></span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="add_login_button" class="sui-toggle">
											<input type="checkbox"
											       value="1"
											       name="add_login_button"
											       id="add_login_button"
											       aria-labelledby="add-to-login-form-label"
												<?php checked( 1, $settings['add_login_button'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="add-to-login-form-label" class="sui-toggle-label"><?php esc_html_e( 'Add magic login button to standard login form', 'magic-login' ); ?></span>
										</label>
									</div>
								</div>
							</div>

							<!-- TTL -->
							<?php list( $token_ttl, $selected_interval ) = get_ttl_with_interval( $settings['token_ttl'] ); ?>
							<?php $allowed_intervals = get_allowed_intervals(); ?>
							<div class="sui-box-settings-row">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" id="token_ttl_label"><?php esc_html_e( 'Token Lifespan', 'magic-login' ); ?></span>
									<span class="sui-description">
										<?php esc_html_e( 'The TTL (time to live) of the login link. WP-Cron removes expired tokens.', 'magic-login' ); ?>
										<a href="<?php echo esc_url( get_doc_url( 'docs/magic-login-token-lifespan' ) ); ?>" target="_blank"><?php esc_html_e( 'Learn More.', 'magic-login' ); ?></a>
									</span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<input
											name="token_ttl"
											id="token_ttl"
											class="sui-form-control sui-field-has-suffix"
											aria-labelledby="token_ttl_label"
											type="number"
											value="<?php echo absint( $token_ttl ); ?>"
											min="0"
										/>
										<span class="sui-field-suffix">
											<select id="token_interval" name="token_interval" class="sui-form-control">
												<?php foreach ( $allowed_intervals as $val => $label ) : ?>
													<option <?php selected( $val, $selected_interval ); ?> value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
												<?php endforeach; ?>
											</select>
										</span>
									</div>
								</div>
							</div>

							<!-- Token Validity -->
							<div class="sui-box-settings-row">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" id="token_validity_label"><?php esc_html_e( 'Token Validity', 'magic-login' ); ?></span>
									<span class="sui-description">
										<?php esc_html_e( 'Specify how many times a token can be used.', 'magic-login' ); ?>
										<a href="<?php echo esc_url( get_doc_url( 'docs/magic-login-token-validity/' ) ); ?>" target="_blank"><?php esc_html_e( 'Learn More.', 'magic-login' ); ?></a>
									</span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<input
											name="token_validity"
											id="token_validity"
											class="sui-form-control sui-field-has-suffix"
											aria-labelledby="token_validity_label"
											type="number"
											value="<?php echo absint( $settings['token_validity'] ); ?>"
											min="0"
											max="10"
										/>
									</div>
								</div>
							</div>

							<!-- Auto Login links  -->
							<div class="sui-box-settings-row">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label"><?php esc_html_e( 'Auto Login Links', 'magic-login' ); ?></span>
									<span class="sui-description"><?php esc_html_e( 'If the recipient exists, automatically adds the login link to outgoing emails sent from WordPress. ', 'magic-login' ); ?></span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="auto_login_links" class="sui-toggle">
											<input type="checkbox"
											       value="1"
											       name="auto_login_links"
											       id="auto_login_links"
											       aria-labelledby="add-to-login-form-label"
												<?php checked( 1, $settings['auto_login_links'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="add-to-login-form-label" class="sui-toggle-label"><?php esc_html_e( 'Add magic login links to outgoing emails', 'magic-login' ); ?></span>
											<span class="sui-description">
												<?php esc_html_e( 'This could be useful when there is an action waiting for the user. (eg: reply comment, complete shopping etc...)', 'magic-login' ); ?>
											</span>
										</label>
									</div>
								</div>
							</div>

							<!-- Woo Integration  -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label">
										<?php esc_html_e( 'Enable Magic Login for WooCommerce', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
									<span class="sui-description">
										<?php esc_html_e( 'Integrates with WooCommerce login form.', 'magic-login' ); ?>
										<a href="<?php echo esc_url( get_doc_url( 'docs/magic-login-woocommerce-integration/' ) ); ?>" target="_blank"><?php esc_html_e( 'Learn More.', 'magic-login' ); ?></a>
									</span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="enable_woo_integration" class="sui-toggle">
											<input type="checkbox"
											       value="1"
											       name="enable_woo_integration"
											       id="enable_woo_integration"
											       aria-labelledby="integrate-to-woocommerce-form-label"
											       aria-controls="woo-detail-controls"
												<?php checked( 1, $settings['enable_woo_integration'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="integrate-to-woocommerce-form-label" class="sui-toggle-label"><?php esc_html_e( 'Integrate magic login with WooCommerce login form', 'magic-login' ); ?></span>
											<span class="sui-description">
												<?php esc_html_e( 'Streamline checkout experience for returning customers.', 'magic-login' ); ?>
											</span>
										</label>
									</div>
									<div style=" <?php echo( ! $settings['enable_woo_integration'] ? 'display:none' : '' ); ?>" tabindex="0" id="woo-detail-controls" class="sui-toggle-content sui-border-frame">
										<div class="sui-form-field" role="radiogroup">
											<span class="sui-label"><?php esc_html_e( 'Magic Login Form Position on WooCommerce', 'magic-login' ); ?></span>

											<label for="woo_position_before" class="sui-radio">
												<input
													type="radio"
													name="woo_position"
													id="woo_position_before"
													aria-labelledby="label-woo_position"
													value="before"
													<?php checked( 'before', $settings['woo_position'] ); ?>
												/>
												<span aria-hidden="true"></span>
												<span id="label-woo_position"><?php esc_html_e( 'Before WooCommerce Login Form', 'magic-login' ); ?></span>
											</label>
											<label for="woo_position_after" class="sui-radio">
												<input
													type="radio"
													name="woo_position"
													id="woo_position_after"
													aria-labelledby="label-woo_position"
													value="after"
													<?php checked( 'after', $settings['woo_position'] ); ?>
												/>
												<span aria-hidden="true"></span>
												<span id="label-woo_position"><?php esc_html_e( 'After WooCommerce Login Form', 'magic-login' ); ?></span>
											</label>
										</div>
									</div>
								</div>
							</div>

							<!-- Brute Force Protection -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label-with-tag">
										<?php esc_html_e( 'Brute Force Protection', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
									<span id="brute-force-protection-description" class="sui-description"><?php esc_html_e( 'Enable additional security mechanisms to limit login requests.', 'magic-login' ); ?></span>
								</div>
								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="enable_brute_force_protection" class="sui-toggle">
											<input type="checkbox"
											       value="1"
											       name="enable_brute_force_protection"
											       id="enable_brute_force_protection"
											       aria-describedby="brute-force-protection-description"
											       aria-controls="brute-force-protection-controls"
												<?php checked( 1, $settings['enable_brute_force_protection'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="brute-force-protection-label" class="sui-toggle-label"><?php esc_html_e( 'Enable brute force protection', 'magic-login' ); ?></span>
											<span class="sui-description">
											</span>
										</label>

										<div style=" <?php echo( ! $settings['enable_brute_force_protection'] ? 'display:none' : '' ); ?>" tabindex="0" id="brute-force-protection-controls" class="sui-toggle-content sui-border-frame">
											<div class="sui-form-field">
												<?php
												$brute_force_fields = array(
													'brute_force_bantime' => array(
														'min' => 1,
														'max' => 1440,
													),
													'brute_force_login_attempt' => array(
														'min' => 1,
														'max' => 100,
													),
													'brute_force_login_time' => array(
														'min' => 1,
														'max' => 600,
													),
												);
												foreach ( $brute_force_fields as $field => $args ) {
													${$field . '_input'} = sprintf(
														'<input
															id="%1$s"
															name="%1$s"
															min="%2$d"
															max="%3$d"
															type="number"
															class="sui-form-control sui-field-has-suffix"
															value="%4$d"
													>',
														esc_attr( $field ),
														absint( $args['min'] ),
														absint( $args['max'] ),
														absint( $settings[ $field ] )
													);
												}
												/* translators: 1: Ban duration input 2: Trial count input 3: Interval input */
												printf(
													__( 'Block the IP address for %1$s minutes when it fails to login %2$s times in %3$s minutes.', 'magic-login' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													$brute_force_bantime_input, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													$brute_force_login_attempt_input, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													$brute_force_login_time_input // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												?>
											</div>
										</div>
									</div>

								</div>
							</div>

							<!-- Throttle -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label-with-tag">
										<?php esc_html_e( 'Login Request Throttling', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
									<span id="magic-login-throttle-description" class="sui-description"><?php esc_html_e( 'Limit login URL generation for the given time span.', 'magic-login' ); ?></span>
								</div>
								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="enable_login_throttling" class="sui-toggle">
											<input
												type="checkbox"
												value="1"
												name="enable_login_throttling"
												id="enable_login_throttling"
												aria-describedby="magic-login-throttle-description"
												aria-controls="magic-login-throttle-controls"
												<?php checked( 1, $settings['enable_login_throttling'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="magic-login-throttle-label" class="sui-toggle-label"><?php esc_html_e( 'Enable throttling' ); ?></span>
											<span class="sui-description">
											</span>
										</label>

										<div style=" <?php echo( ! $settings['enable_login_throttling'] ? 'display:none' : '' ); ?>" tabindex="0" id="magic-login-throttle-controls" class="sui-toggle-content sui-border-frame">
											<div class="sui-form-field">
												<?php esc_html_e( 'Allow to create maximum' ); ?>
												<input
													id="login_throttling_limit"
													name="login_throttling_limit"
													min="1"
													max="30"
													type="number"
													class="sui-form-control sui-field-has-suffix"
													value="<?php echo absint( $settings['login_throttling_limit'] ); ?>"
												>
												<?php esc_html_e( 'login links from the same IP within' ); ?>
												<input
													id="login_throttling_time"
													name="login_throttling_time"
													min="1"
													max="1440"
													type="number"
													class="sui-form-control sui-field-has-suffix"
													value="<?php echo absint( $settings['login_throttling_time'] ); ?>"
												>
												<?php esc_html_e( 'minutes.' ); ?>
											</div>
										</div>
									</div>

								</div>
							</div>

							<!-- IP Address Check -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label">
										<?php esc_html_e( 'IP Check', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
									<span class="sui-description">
										<?php esc_html_e( 'The user should log in from the same IP that makes a login request. (except for the login links generated via CLI)', 'magic-login' ); ?>
									</span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="enable_ip_check" class="sui-toggle">
											<input
												type="checkbox"
												value="1"
												name="enable_ip_check"
												id="enable_ip_check"
												aria-labelledby="enable-ip-check-label"
												<?php checked( 1, $settings['enable_ip_check'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="enable-ip-check-label" class="sui-toggle-label"><?php esc_html_e( 'Enable IP address check', 'magic-login' ); ?></span>
										</label>
									</div>
								</div>
							</div>

							<!-- Allowed Domain -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label">
										<?php esc_html_e( 'Domain Restriction', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
									<span id="domain-restriction-description" class="sui-description"><?php esc_html_e( 'Allow only listed domains to login via magic links.', 'magic-login' ); ?></span>
								</div>
								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="enable_domain_restriction" class="sui-toggle">
											<input
												type="checkbox"
												value="1"
												name="enable_domain_restriction"
												id="enable_domain_restriction"
												aria-describedby="enable-domain-restriction-description"
												aria-controls="enable-domain-restriction-controls"
												<?php checked( 1, $settings['enable_domain_restriction'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="domain-restriction-label" class="sui-toggle-label"><?php esc_html_e( 'Enable domain restriction', 'magic-login' ); ?></span>
											<span class="sui-description">
											</span>
										</label>

										<div style=" <?php echo( ! $settings['enable_domain_restriction'] ? 'display:none' : '' ); ?>" tabindex="0" id="enable-domain-restriction-controls" class="sui-toggle-content sui-border-frame">
											<div class="sui-form-field">
																<textarea
																	placeholder="example.com"
																	id="allowed_domains"
																	name="allowed_domains"
																	class="sui-form-control"
																	aria-describedby="allowed-domains-description"
																	rows="7"
																><?php echo esc_textarea( $settings['allowed_domains'] ); ?></textarea>
												<span id="allowed-domains-description" class="sui-description"><?php esc_html_e( 'Enter allowed domains line by line.', 'magic-login' ); ?></span>
											</div>
										</div>
									</div>

								</div>
							</div>

							<!-- E-mail Subject -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" id="email_subject_label">
										<?php esc_html_e( 'Email Subject', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<input
											name="email_subject"
											id="email_subject"
											class="sui-form-control"
											aria-labelledby="email_subject_label"
											type="text"
											value="<?php echo esc_attr( $settings['email_subject'] ); ?>"
										/>
									</div>
								</div>
							</div>

							<!-- E-mail Content -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<!-- Email body -->
										<div class="sui-form-field">
											<label class="sui-settings-label sui-label sui-label-editor" for="login_email"><?php esc_html_e( 'Email Content', 'magic-login' ); ?>
												<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
												</span>
											</label>
											<?php
											\wp_editor(
												$settings['login_email'],
												'login_email',
												array(
													'media_buttons'    => false,
													'textarea_name'    => 'login_email',
													'editor_css'       => '',
													'editor_height'    => 192,
													'drag_drop_upload' => false,
													'tinymce'          => false,
												)
											);
											?>
											<span class="sui-description"><?php esc_html_e( 'Supported placeholders: {{SITEURL}}, {{USERNAME}}, {{FIRST_NAME}}, {{LAST_NAME}}, {{FULL_NAME}}, {{DISPLAY_NAME}}, {{USER_EMAIL}, {{SITENAME}}, {{EXPIRES}}, {{MAGIC_LINK}}, {{EXPIRES_WITH_INTERVAL}}, {{TOKEN_VALIDITY_COUNT}}', 'magic-login' ); ?></span>
										</div>
									</div>
								</div>
							</div>

							<!-- Login redirect settings -->
							<div class="sui-box-settings-row sui-disabled">

								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label"><?php esc_html_e( 'Login Redirect', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
									<span class="sui-description"><?php esc_html_e( 'Redirect users to custom URL after login.', 'magic-login' ); ?></span>
								</div>

								<div class="sui-box-settings-col-2">

									<div class="sui-form-field">
										<label for="enable_login_redirection" class="sui-toggle">
											<input
												type="checkbox"
												id="enable_login_redirection"
												name="enable_login_redirection"
												aria-labelledby="enable-login-redirection-label"
												aria-controls="login-redirection-controls"
												<?php checked( 1, $settings['enable_login_redirection'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="enable-login-redirection-label" class="sui-toggle-label"><?php esc_html_e( 'Enable custom login redirection', 'magic-login' ); ?></span>
										</label>

										<div style=" <?php echo( ! $settings['enable_login_redirection'] ? 'display:none' : '' ); ?>" tabindex="0" id="login-redirection-controls" class="sui-toggle-content sui-border-frame">

											<div class="sui-form-field">
												<label for="default_redirection_url" id="label-login-redirection-url" class="sui-label"><?php esc_html_e( 'Target URL:', 'magic-login' ); ?></label>
												<input
													placeholder="https://example.com/wp-admin"
													id="default_redirection_url"
													name="default_redirection_url"
													class="sui-form-control"
													aria-labelledby="label-login-redirection-url"
													aria-describedby="error-login-redirection-url description-login-redirection-url"
													value="<?php echo esc_url( $settings['default_redirection_url'] ); ?>"
												/>
												<span id="description-login-redirection-url" class="sui-description"><?php esc_html_e( 'By default it redirects to admin dashboard', 'magic-login' ); ?></span>
											</div>

											<div class="sui-form-field">
												<label for="enable_wp_login_redirection" class="sui-toggle">
													<input type="checkbox"
													       value="1"
													       id="enable_wp_login_redirection"
													       name="enable_wp_login_redirection"
													       aria-labelledby="enable-wp-login-redirection-label"
													       aria-describedby="enable-wp-login-redirection-description"
													       aria-controls="enable-wp-login-redirection-controls"
														<?php checked( 1, $settings['enable_wp_login_redirection'] ); ?>
													>
													<span class="sui-toggle-slider" aria-hidden="true"></span>
													<span id="enable-wp-login-redirection-label" class="sui-toggle-label"><?php esc_html_e( 'Apply redirection to normal WordPress login too.', 'magic-login' ); ?></span>
													<span class="sui-description"><?php esc_html_e( 'Enable this option to apply redirection rules for both password and passwordless logins, ensuring a seamless navigation experience post-login.', 'magic-login' ); ?></span>
												</label>
											</div>

											<div class="sui-form-field">
												<label for="enforce_redirection_rules" class="sui-toggle">
													<input type="checkbox"
													       value="1"
													       id="enforce_redirection_rules"
													       name="enforce_redirection_rules"
													       aria-labelledby="enforce-redirection-rules-label"
													       aria-describedby="enforce-redirection-rules-description"
														<?php checked( 1, $settings['enforce_redirection_rules'] ); ?>
													>
													<span class="sui-toggle-slider" aria-hidden="true"></span>
													<span id="enforce-redirection-rules-label" class="sui-toggle-label"><?php esc_html_e( 'Override block/shortcode specific redirections.', 'magic-login' ); ?></span>
													<span class="sui-description"><?php esc_html_e( 'When this option is enabled, block/shortcode-based redirections will be ignored.', 'magic-login' ); ?></span>
												</label>
											</div>

											<div class="sui-form-field">
												<label for="enable_role_based_redirection" class="sui-toggle">
													<input type="checkbox"
													       value="1"
													       id="enable_role_based_redirection"
													       name="enable_role_based_redirection"
													       aria-labelledby="enable-role-based-redirection-label"
													       aria-describedby="enable-role-based-redirection-description"
													       aria-controls="enable-role-based-redirection-controls"
														<?php checked( 1, $settings['enable_role_based_redirection'] ); ?>
													>
													<span class="sui-toggle-slider" aria-hidden="true"></span>
													<span id="enable-role-based-redirection-label" class="sui-toggle-label"><?php esc_html_e( 'Enable role-based redirection.', 'magic-login' ); ?></span>
													<span id="description-role-based-login-redirect" class="sui-description"><?php esc_html_e( 'Leave it blank to use default redirection rule.', 'magic-login' ); ?></span>
												</label>
											</div>

											<div style=" <?php echo( ! $settings['enable_role_based_redirection'] ? 'display:none' : '' ); ?>" tabindex="0" id="enable-role-based-redirection-controls" class="sui-toggle-content sui-border-frame">
												<?php
												$all_roles = wp_roles();
												$roles     = $all_roles->roles ? $all_roles->roles : [];
												?>
												<table class="sui-table">
													<thead>
													<tr>
														<th><?php esc_html_e( 'Role', 'magic-login' ); ?></th>
														<th><?php esc_html_e( 'Target URL', 'magic-login' ); ?></th>
													</tr>
													</thead>

													<tbody>

													<?php if ( $roles ) : ?>
														<?php foreach ( $roles as $role => $role_details ) : ?>
															<tr>
																<td class="sui-table-item-title"><?php echo esc_html( $role_details['name'] ); ?></td>
																<td><input
																		type="text"
																		name="redirect_role[<?php echo esc_attr( $role ); ?>]"
																		class="sui-form-control"
																		value="<?php echo( isset( $settings['role_based_redirection_rules'][ $role ] ) ? esc_url( $settings['role_based_redirection_rules'][ $role ] ) : '' ); ?>"
																	/></td>
															</tr>
														<?php endforeach; ?>
													<?php endif; ?>

													</tbody>

													<tfoot>
													</tfoot>

												</table>
											</div>

										</div>

									</div>
								</div>

							</div>

							<!-- Ajaxify magic login request  -->
							<div class="sui-box-settings-row">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" for="ajax-magic-login"><?php esc_html_e( 'Enable AJAX', 'magic-login' ); ?></span>
									<span class="sui-description"><?php esc_html_e( 'It will ajaxify the login requests on pages that use shortcode or block.', 'magic-login' ); ?></span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="enable_ajax" class="sui-toggle">
											<input type="checkbox"
											       value="1"
											       name="enable_ajax"
											       id="enable_ajax"
											       aria-labelledby="enable-ajax-form-label"
												<?php checked( 1, $settings['enable_ajax'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="enable-ajax-form-label" class="sui-toggle-label"><?php esc_html_e( 'Enable AJAX for requesting magic login links.', 'magic-login' ); ?></span>
											<span class="sui-description"><?php esc_html_e( 'It allows to send links without refreshing the current page.', 'magic-login' ); ?></span>
										</label>
									</div>
								</div>
							</div>

							<!-- Enable REST API -->
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" for="enable_rest_api">
										<?php esc_html_e( 'API Access', 'magic-login' ); ?>
										<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'magic-login' ); ?></span>
									</span>
									<span class="sui-description">
										<?php
										echo wp_kses_post(
											sprintf(
											/* translators: 1: Documentation URL 2: 'Learn More' text */
												__( 'Allows external systems to interact with your application programmatically by enabling REST API endpoints. <a href="%1$s" target="_blank" rel="noopener">%2$s</a>', 'magic-login' ),
												get_doc_url( 'docs/magic-login-rest-api/' ),
												__( 'Learn More.', 'magic-login' )
											)
										);
										?>
									</span>
								</div>

								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<label for="enable_rest_api" class="sui-toggle">
											<input type="checkbox"
												   value="1"
												   name="enable_rest_api"
												   id="enable_rest_api"
												   aria-labelledby="enable-ajax-form-label"
												<?php checked( 1, $settings['enable_rest_api'] ); ?>
											>
											<span class="sui-toggle-slider" aria-hidden="true"></span>
											<span id="enable-rest-api-form-label" class="sui-toggle-label"><?php esc_html_e( 'Enable REST API.', 'magic-login' ); ?></span>

										</label>
									</div>
								</div>
							</div>

							<!-- Reset Tokens -->
							<div class="sui-box-settings-row">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label"><?php esc_html_e( 'Reset Tokens', 'magic-login' ); ?></span>
									<span id="reset-tokens" class="sui-description"><?php esc_html_e( 'If you want to clean all tokens at once click to reset button.', 'magic-login' ); ?></span>
								</div>
								<div class="sui-box-settings-col-2">
									<div class="sui-form-field">
										<input type="submit" name="reset_tokens" id="reset_tokens" aria-describedby="reset-tokens" class="sui-button sui-button-ghost" value="<?php echo esc_attr( __( 'Reset', 'magic-login' ) ); ?>">
									</div>
								</div>
							</div>

							<!-- Upsell ads -->
							<div class="sui-box-settings-row sui-upsell-row">
								<div class="sui-upsell-notice" style="padding-left: 0;">
									<p><?php esc_html_e( 'With our pro version of magic login, you will unlock the advanced configurations of the plugin and get access to the WP-CLI command along with our premium support.' ); ?><br>
										<a href="https://handyplugins.co/magic-login-pro/?utm_source=wp_admin&utm_medium=plugin&utm_campaign=settings_page" rel="noopener noreferrer nofollow" target="_blank" class="sui-button sui-button-purple" style="margin-top: 10px;color:#fff;"><?php esc_html_e( 'Try Magic Login Pro Today', 'magic-login' ); ?></a>
									</p>
								</div>
							</div>


						</div>
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
												'textarea_name' => 'registration_email_content',
												'editor_css' => '',
												'editor_height' => 150,
												'drag_drop_upload' => false,
												'tinymce' => false,
												'teeny'   => false,
												'media_buttons' => false,
											]
										);
										?>
										<span class="sui-description"><?php esc_html_e( 'Supported placeholders: {{SITEURL}}, {{USERNAME}}, {{FIRST_NAME}}, {{LAST_NAME}}, {{FULL_NAME}}, {{DISPLAY_NAME}}, {{USER_EMAIL}, {{SITENAME}}, {{EXPIRES}}, {{MAGIC_LINK}}, {{EXPIRES_WITH_INTERVAL}}, {{TOKEN_VALIDITY_COUNT}}', 'magic-login' ); ?></span>
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
						<div role="tabpanel" tabindex="0" id="spam_protection__content" class="sui-tab-content magic-login-main-tab-content sui-disabled" aria-labelledby="spam_protection__tab">
							<div class="sui-box-settings-row sui-disabled">
								<div class="sui-box-settings-col-1">
									<span class="sui-settings-label" id="spam_protection_service_label"><?php esc_html_e( 'Spam Protection Service', 'easy-text-to-speech' ); ?></span>
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

					</div>

				</div>



			</div>

			<div class="sui-box-footer">
				<div class="sui-actions-left">
					<button type="submit" class="sui-button sui-button-blue" id="magic-login-save-settings" data-msg="">
						<i class="sui-icon-save" aria-hidden="true"></i>
						<?php esc_html_e( 'Update settings', 'magic-login' ); ?>
					</button>
				</div>
			</div>

		</div>
	</section>

</form>

