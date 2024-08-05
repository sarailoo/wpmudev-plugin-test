<?php
/**
 * Class Scan_Posts_Test
 *
 * @package WPMUDEV_PluginTest
 */

use WPMUDEV\PluginTest\App\Admin_Pages\Maintenance;

/**
 * Scan Posts Test.
 */
class Scan_Posts_Test extends WP_UnitTestCase {
	/**
	 * The Maintenance instance.
	 *
	 * @var Maintenance
	 */
	protected $maintenance;

	/**
	 * Set up the test environment.
	 */
	public function set_up(): void {
		parent::set_up();
		$this->maintenance = Maintenance::instance();
	}

	/**
	 * Test the handle_scan_posts method.
	 */
	public function test_handle_scan_posts() {
		// Create sample posts and pages.
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		// Simulate cron request.
		$this->maintenance->handle_scan_posts( 'cron' );

		// Check if the post meta has been updated.
		$post_meta = get_post_meta( $post_id, 'wpmudev_test_last_scan', true );
		$page_meta = get_post_meta( $page_id, 'wpmudev_test_last_scan', true );

		$this->assertNotEmpty( $post_meta, 'The post scan timestamp should be updated.' );
		$this->assertNotEmpty( $page_meta, 'The page scan timestamp should be updated.' );
	}
}