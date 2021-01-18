<?php
namespace MagicLogin\Core;

/**
 * This is a very basic test case to get things started. You should probably rename this and make
 * it work for your project. You can use all the tools provided by WP Mock and Mockery to create
 * your tests. Coverage is calculated against your includes/ folder, so try to keep all of your
 * functional code self contained in there.
 *
 * References:
 *   - http://phpunit.de/manual/current/en/index.html
 *   - https://github.com/padraic/mockery
 *   - https://github.com/10up/wp_mock
 */

use MagicLogin as Base;

class Core_Tests extends Base\TestCase {

	protected $testFiles = [
		'core.php'
	];

	/**
	 * Test load method.
	 */
	public function test_setup() {
		// Setup
		\WP_Mock::expectActionAdded( 'init', 'MagicLogin\Core\i18n' );
		\WP_Mock::expectActionAdded( 'init', 'MagicLogin\Core\init' );
		\WP_Mock::expectAction( 'magic_login_loaded' );

		// Act
		setup();

		// Verify
		$this->assertConditionsMet();
	}

	/**
	 * Test internationalization integration.
	 */
	public function test_i18n() {
		// Setup
		\WP_Mock::userFunction( 'get_locale', array(
			'times' => 1,
			'args' => array(),
			'return' => 'en_US',
		) );
		\WP_Mock::onFilter( 'plugin_locale' )->with( 'en_US', 'magic-login' )->reply( 'en_US' );
		\WP_Mock::userFunction( 'load_textdomain', array(
			'times' => 1,
			'args' => array( 'magic-login', 'lang_dir/magic-login/magic-login-en_US.mo' ),
		) );
		\WP_Mock::userFunction( 'plugin_basename', array(
			'times' => 1,
			'args' => array( 'path' ),
			'return' => 'path',
		) );
		\WP_Mock::userFunction( 'load_plugin_textdomain', array(
			'times' => 1,
			'args' => array( 'magic-login', false, 'path/languages/' ),
		) );

		// Act
		i18n();

		// Verify
		$this->assertConditionsMet();
	}

	/**
	 * Test initialization method.
	 */
	public function test_init() {
		// Setup
		\WP_Mock::expectAction( 'magic_login_init' );

		// Act
		init();

		// Verify
		$this->assertConditionsMet();
	}

}
