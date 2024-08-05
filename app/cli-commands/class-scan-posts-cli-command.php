<?php
/**
 * WP-CLI Commands for WPMUDEV Plugin Test.
 *
 * @package WPMUDEV\PluginTest
 */

 namespace WPMUDEV\PluginTest\CLI;

// Abort if called directly.
defined( 'WPINC' ) || die;

use WP_CLI;
use WPMUDEV\PluginTest\App\Admin_Pages\Maintenance;

class Scan_Posts_CLI {
	/**
	 * Holds the class instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Singleton pattern to get the instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the WP-CLI commands.
	 */
	public function init() {
		WP_CLI::add_command( 'wpmudev scan-posts', [ $this, 'scan_posts' ] );
	}

	/**
	 * Scan Posts Command.
	 *
	 * Scans all public posts and pages and updates the wpmudev_test_last_scan post meta with the current timestamp.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpmudev scan-posts
	 *
	 * @when after_wp_load
	 */
	public function scan_posts() {
		$maintenance = Maintenance::instance();
		$maintenance->handle_scan_posts( 'cli' );
	}
}