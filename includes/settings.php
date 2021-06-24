<?php
/**
 * Settings Page
 *
 * @package MagicLogin
 */

namespace MagicLogin\Settings;

use const MagicLogin\Constants\BLOG_URL;
use const MagicLogin\Constants\DOCS_URL;
use const MagicLogin\Constants\FAQ_URL;
use const MagicLogin\Constants\GITHUB_URL;
use const MagicLogin\Constants\SUPPORT_URL;
use const MagicLogin\Constants\SETTING_OPTION;
use const MagicLogin\Constants\TWITTER_URL;
use function MagicLogin\Utils\delete_all_tokens;

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	if ( MAGIC_LOGIN_IS_NETWORK ) {
		add_action( 'network_admin_menu', $n( 'admin_menu' ) );
	} else {
		add_action( 'admin_menu', $n( 'admin_menu' ) );
	}

	add_action( 'admin_init', $n( 'save_settings' ) );
	add_filter( 'admin_body_class', $n( 'add_sui_admin_body_class' ) );
}

/**
 * Add required class for shared UI
 *
 * @param string $classes css classes for admin area
 *
 * @return string
 * @see https://wpmudev.github.io/shared-ui/installation/
 */
function add_sui_admin_body_class( $classes ) {
	$classes .= ' sui-2-10-9 ';

	return $classes;
}


/**
 * Add menu item
 */
function admin_menu() {
	$parent = MAGIC_LOGIN_IS_NETWORK ? 'settings.php' : 'options-general.php';

	add_submenu_page(
		$parent,
		esc_html__( 'Magic Login', 'magic-login' ),
		esc_html__( 'Magic Login', 'magic-login' ),
		apply_filters( 'magic_login_admin_menu_cap', 'manage_options' ),
		'magic-login',
		__NAMESPACE__ . '\settings_page'
	);
}

/**
 * Settings page
 */
function settings_page() {
	$settings = \MagicLogin\Utils\get_settings();
	?>
	<?php if ( is_network_admin() ) : ?>
		<?php settings_errors(); ?>
	<?php endif; ?>

	<main class="sui-wrap">
		<div class="sui-header">
			<h1 class="sui-header-title">
				<?php esc_html_e( 'Magic Login', 'magic-login' ); ?>
			</h1>

			<!-- Float element to Right -->
			<div class="sui-actions-right">
				<a href="<?php echo esc_url( DOCS_URL ); ?>" class="sui-button sui-button-blue" target="_blank">
					<i class="sui-icon-academy" aria-hidden="true"></i>
					<?php esc_html_e( 'Documentation', 'magic-login' ); ?>
				</a>
			</div>
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'magic_login_settings', 'magic_login_settings' ); ?>
			<section class="sui-row-with-sidenav">

				<!-- TAB: Regular -->
				<div class="sui-box" data-tab="basic-options">

					<div class="sui-box-header">
						<h2 class="sui-box-title">
							<?php esc_html_e( 'Settings', 'magic-login' ); ?>
						</h2>
					</div>

					<div class="sui-box-body sui-upsell-items">

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
												__( 'Magic login form is accessible <a href="%1$s">%1$s</a>' ),
												esc_url( site_url( 'wp-login.php?action=magic_login', 'login_post' ) )
											)
										);
										?>
									</span>
								</div>
							</div>
						</div>

						<!-- TTL -->
						<div class="sui-box-settings-row">
							<div class="sui-box-settings-col-1">
								<span class="sui-settings-label" id="token_ttl_label"><?php esc_html_e( 'Token Lifespan', 'magic-login' ); ?></span>
								<span class="sui-description"><?php esc_html_e( 'The TTL (time to live) of the login link. Expired tokens remove with WP Cron. Enter between 1-60.', 'magic-login' ); ?></span>
							</div>

							<div class="sui-box-settings-col-2">

								<div class="sui-form-field">
									<input
											name="token_ttl"
											id="token_ttl"
											class="sui-form-control sui-field-has-suffix"
											aria-labelledby="token_ttl_label"
											min="1"
											max="60"
											type="number"
											value="<?php echo absint( $settings['token_ttl'] ); ?>"
									/>
									<span class="sui-field-suffix">
										<?php esc_html_e( 'Minutes', 'magic-login' ); ?>
									</span>
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
											<?php esc_html_e( 'Block the IP address for', 'magic-login' ); ?>
											<input
													id="brute_force_bantime"
													name="brute_force_bantime"
													min="1"
													max="1440"
													type="number"
													class="sui-form-control sui-field-has-suffix"
													value="<?php echo absint( $settings['brute_force_bantime'] ); ?>"
											>
											<?php esc_html_e( 'minutes when it fails to login ', 'magic-login' ); ?>
											<input
													id="brute_force_login_attempt"
													name="brute_force_login_attempt"
													min="1"
													max="100"
													type="number"
													class="sui-form-control sui-field-has-suffix"
													value="<?php echo absint( $settings['brute_force_login_attempt'] ); ?>"
											>
											<?php esc_html_e( 'times in', 'magic-login' ); ?>
											<input
													id="brute_force_login_time"
													name="brute_force_login_time"
													min="1"
													max="600"
													type="number"
													class="sui-form-control sui-field-has-suffix"
													value="<?php echo absint( $settings['brute_force_login_time'] ); ?>"
											>
											<?php esc_html_e( 'minutes.', 'magic-login' ); ?>
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
										<span id="enable-ip-check-label" class="sui-toggle-label"><?php esc_html_e( 'Enable IP address check' ); ?></span>
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

						<!-- E-mail -->
						<div class="sui-box-settings-row sui-disabled">
							<div class="sui-box-settings-col-2">
								<div class="sui-form-field">
									<!-- Email body -->
									<div class="sui-form-field">
										<label class="sui-settings-label sui-label sui-label-editor" for="emailmessage"><?php esc_html_e( 'Email Content', 'magic-login' ); ?>
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
											)
										);
										?>
										<span class="sui-description"><?php esc_html_e( 'Supported placeholders: {{SITEURL}}, {{USERNAME}}, {{SITENAME}}, {{EXPIRES}}, {{MAGIC_LINK}}', 'magic-login' ); ?></span>
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
											</label>
										</div>

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
															<td class="sui-table-item-title"><?php echo esc_attr( $role_details['name'] ); ?></td>
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

						<!-- Reset Tokens -->
						<div class="sui-box-settings-row">
							<div class="sui-box-settings-col-1">
								<span class="sui-settings-label"><?php esc_html_e( 'Reset Tokens', 'magic-login' ); ?></span>
								<span id="reset-tokens" class="sui-description"><?php esc_html_e( 'If you want to clean all tokens at once click to reset button.', 'magic-login' ); ?></span>
							</div>
							<div class="sui-box-settings-col-2">
								<div class="sui-form-field">
									<input type="submit" name="reset_tokens" id="reset_tokens" aria-describedby="reset-tokens" class="sui-button sui-button-ghost" value="<?php esc_html_e( 'Reset', 'magic-login' ); ?>">
								</div>
							</div>
						</div>

						<!-- Upsell ads -->
						<div class="sui-box-settings-row sui-upsell-row">
							<div class="sui-upsell-notice" style="padding-left: 0;">
								<p><?php esc_html_e( 'With our pro version of magic login, you will unlock the advanced configurations of the plugin and get access to the WP-CLI command along with our premium support.' ); ?><br>
									<a href="https://handyplugins.co/magic-login-pro/" rel="noopener noreferrer nofollow" target="_blank" class="sui-button sui-button-purple" style="margin-top: 10px;color:#fff;"><?php esc_html_e( 'Try Magic Login Pro Today', 'magic-login' ); ?></a>
								</p>
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

		<!-- ELEMENT: The Brand -->
		<div class="sui-footer">
			<?php
			echo wp_kses_post(
				sprintf(
					__( 'Made with <i class="sui-icon-heart"></i> by <a href="%s" rel="noopener" target="_blank">HandyPlugins</a>', 'magic-login' ),
					'https://handyplugins.co/'
				)
			);
			?>
		</div>

		<footer>
			<!-- ELEMENT: Navigation -->
			<ul class="sui-footer-nav">
				<li><a href="<?php echo esc_url_raw( FAQ_URL ); ?>" target="_blank"><?php esc_html_e( 'FAQ', 'magic-login' ); ?></a></li>
				<li><a href="<?php echo esc_url( BLOG_URL ); ?>" target="_blank"><?php esc_html_e( 'Blog', 'magic-login' ); ?></a></li>
				<li><a href="<?php echo esc_url( SUPPORT_URL ); ?>" target="_blank"><?php esc_html_e( 'Support', 'magic-login' ); ?></a></li>
				<li><a href="<?php echo esc_url( DOCS_URL ); ?>" target="_blank"><?php esc_html_e( 'Docs', 'magic-login' ); ?></a></li>
			</ul>

			<!-- ELEMENT: Social Media -->
			<ul class="sui-footer-social">
				<li><a href="<?php echo esc_url( GITHUB_URL ); ?>" target="_blank">
						<i class="sui-icon-social-github" aria-hidden="true"></i>
						<span class="sui-screen-reader-text">GitHub</span>
					</a></li>
				<li><a href="<?php echo esc_url( TWITTER_URL ); ?>" target="_blank">
						<i class="sui-icon-social-twitter" aria-hidden="true"></i></a>
					<span class="sui-screen-reader-text">Twitter</span>
				</li>
			</ul>
		</footer>

	</main>

	<?php
}

/**
 * Save settings
 */
function save_settings() {
	$nonce = filter_input( INPUT_POST, 'magic_login_settings', FILTER_SANITIZE_STRING );
	if ( wp_verify_nonce( $nonce, 'magic_login_settings' ) ) {

		if ( isset( $_POST['reset_tokens'] ) ) {
			if ( false !== delete_all_tokens() ) {
				add_settings_error( SETTING_OPTION, 'magic-login', esc_html__( 'Tokens has been removed.', 'magic-login' ), 'success' );
			} else {
				add_settings_error( SETTING_OPTION, 'magic-login', esc_html__( 'Tokens could not be removed.', 'magic-login' ), 'error' );
			}

			return;
		}

		$settings               = [];
		$settings['is_default'] = boolval( filter_input( INPUT_POST, 'is_default' ) );
		$settings['token_ttl']  = absint( filter_input( INPUT_POST, 'token_ttl' ) );

		if ( MAGIC_LOGIN_IS_NETWORK ) {
			update_site_option( SETTING_OPTION, $settings );
		} else {
			update_option( SETTING_OPTION, $settings );
		}

		add_settings_error( SETTING_OPTION, 'magic-login', esc_html__( 'Settings saved.', 'magic-login' ), 'success' );

		return;
	}

}
