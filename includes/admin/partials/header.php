<?php
/**
 * Settings Page Header
 *
 * @package MagicLogin\Admin
 */

use const MagicLogin\Constants\DOCS_URL;

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
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

