<?php
/**
 * Tools Tab
 *
 * @package   MagicLoginPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

?>
<div role="tabpanel" tabindex="0" id="tools__content" class="sui-tab-content magic-login-main-tab-content" aria-labelledby="tools__tab">
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Reset', 'magic-login' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Reset plugin settings or delete user tokens. This cannot be undone.', 'magic-login' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<button type="submit" name="magic_login_form_action" value="reset_settings" class="sui-button sui-button-ghost sui-button-red">
					<?php esc_html_e( 'Reset Settings', 'magic-login' ); ?>
				</button>

				<button type="submit" name="magic_login_form_action" value="reset_license" class="sui-button sui-button-ghost sui-button-red" style="display: none;" aria-hidden="true" tabindex="-1">
					<?php esc_html_e( 'Reset License', 'magic-login' ); ?>
				</button>

				<button type="submit" name="magic_login_form_action" value="reset_tokens" class="sui-button sui-button-ghost sui-button-red">
					<?php esc_html_e( 'Delete All Tokens', 'magic-login' ); ?>
				</button>
			</div>
		</div>
	</div>


	<!-- Export Settings -->
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Export', 'magic-login' ); ?></span>
			<span class="sui-description"><?php esc_html_e( 'Exports current plugin settings as a JSON file.', 'magic-login' ); ?></span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">

				<!-- Include Sensitive -->
				<label for="export_include_sensitive" class="sui-toggle" style="display: none;" aria-hidden="true" tabindex="-1">
					<input type="checkbox"
						   value="1"
						   name="export_include_sensitive"
						   id="export_include_sensitive"
						   aria-labelledby="include-sensitive-label"
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="include-sensitive-label" class="sui-toggle-label"><?php esc_html_e( 'Include sensitive data (e.g. API credentials)', 'magic-login' ); ?></span>
					<span class="sui-description" style="margin-top: 10px;"><?php esc_html_e( 'Encrypted values will be decrypted and exported in plain text. Use only if migrating to another site.', 'magic-login' ); ?></span>
				</label>

				<!-- Include License -->
				<label for="export_include_license" class="sui-toggle" style="display: none;" aria-hidden="true" tabindex="-1">
					<input type="checkbox"
						   value="1"
						   name="export_include_license"
						   id="export_include_license"
						   aria-labelledby="include-license-label"
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="include-license-label" class="sui-toggle-label"><?php esc_html_e( 'Include license key', 'magic-login' ); ?></span>
				</label>

				<!-- Submit -->
				<p style="margin-top: 15px;">
					<button type="submit" name="magic_login_form_action" value="export_settings" class="sui-button sui-button-ghost sui-button-green">
						<span class="sui-icon-download" aria-hidden="true"></span>

						<?php esc_html_e( 'Download Settings', 'magic-login' ); ?>
					</button>
				</p>

			</div>
		</div>
	</div>


	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Import', 'magic-login' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Choose a configuration file to import your Magic Login settings. This will overwrite your current settings.', 'magic-login' ); ?>
			</span>
		</div>

		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<!-- Activate License Toggle -->
				<label for="activate_imported_license" class="sui-toggle" style="display: none;" aria-hidden="true" tabindex="-1">
					<input type="checkbox"
						   value="1"
						   name="activate_imported_license"
						   id="activate_imported_license"
						   aria-labelledby="activate-imported-license-label"
					>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="activate-imported-license-label" class="sui-toggle-label">
						<?php esc_html_e( 'Activate license key after import', 'magic-login' ); ?>
					</span>
					<span class="sui-description" >
						<?php esc_html_e( 'If the file contains a license key, it will be activated automatically after import.', 'magic-login' ); ?>
					</span>
				</label>
				<br>


				<div class="sui-upload" id="magic-login-import-upload-wrap">
					<input id="magic-login-import-file-input" class="magic-login-file-input" name="import_file" type="file" value="" readonly="readonly" accept=".json">
					<label class="sui-upload-button" type="button" for="magic-login-import-file-input">
						<span class="sui-icon-upload-cloud" aria-hidden="true"></span>
						<?php esc_html_e( 'Upload file', 'magic-login' ); ?>
					</label>
					<div class="sui-upload-file">
						<span id="magic-login-import-file-name"></span>
						<button type="button" id="magic-login-import-remove-file" aria-label="Remove file">
							<span class="sui-icon-close" aria-hidden="true"></span>
						</button>
					</div>

					<button role="button" id="magic-login-import-btn" name="magic_login_form_action" value="import_settings" style="margin-left: 10px;" class="sui-button sui-button-ghost sui-button-blue" disabled>
						<?php esc_html_e( 'Upload and Import', 'magic-login' ); ?>
					</button>

				</div>
				<span class="sui-description" style="margin-top: 10px;"><?php esc_html_e( 'Choose a JSON(.json) file to import the configuration.', 'magic-login' ); ?></span>
			</div>

		</div>

	</div>
</div>
