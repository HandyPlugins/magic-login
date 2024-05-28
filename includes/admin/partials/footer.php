<?php
/**
 * Footer for the settings page
 *
 * @package MagicLogin\Admin
 */

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
use const MagicLogin\Constants\BLOG_URL;
use const MagicLogin\Constants\DOCS_URL;
use const MagicLogin\Constants\FAQ_URL;
use const MagicLogin\Constants\GITHUB_URL;
use const MagicLogin\Constants\SUPPORT_URL;
use const MagicLogin\Constants\TWITTER_URL;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!-- ELEMENT: The Brand -->
<div class="sui-footer">
	<?php
	echo wp_kses_post(
		sprintf(
		/* translators: %s: HandyPlugins URL */
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
