<?php
/**
 * Posts maintenance.
 *
 * @link          https://wpmudev.com/
 * @since         1.0.0
 *
 * @author        WPMUDEV (https://wpmudev.com)
 * @package       WPMUDEV\PluginTest
 *
 * @copyright (c) 2023, Incsub (http://incsub.com)
 */

namespace WPMUDEV\PluginTest\App\Admin_Pages;

// Abort if called directly.
defined( 'WPINC' ) || die;

use WPMUDEV\PluginTest\Base;

class Maintenance extends Base {
	/**
	 * The page title.
	 *
	 * @var string
	 */
	private $page_title;

	/**
	 * The page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'wpmudev_plugintest_maintenance';

	/**
	 * Page Assets.
	 *
	 * @var array
	 */
	private $page_scripts = array();

	/**
	 * Assets version.
	 *
	 * @var string
	 */
	private $assets_version = '';

	/**
	 * A unique string id to be used in markup and jsx.
	 *
	 * @var string
	 */
	private $unique_id = '';

	/**
	 * Initializes the page.
	 *
	 * @return void
	 * @since NEXT
	 *
	 */
	public function init() {
		$this->page_title     = __( 'Posts Maintenance', 'wpmudev-plugin-test' );
		$this->assets_version = ! empty( $this->script_data( 'version' ) ) ? $this->script_data( 'version' ) : WPMUDEV_PLUGINTEST_VERSION;
		$this->unique_id      = "wpmudev_plugintest_auth_main_wrap-{$this->assets_version}";

		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		// Add body class to admin pages.
		add_filter( 'admin_body_class', array( $this, 'admin_body_classes' ) );

		add_action( 'wp_ajax_wpmudev_scan_posts', array( $this, 'handle_scan_posts_ajax' ) );
		add_action( 'init', array( $this, 'wpmudev_schedule_daily_scan' ) );
		add_action( 'wpmudev_daily_scan_event', array( $this, 'handle_scan_posts_cron' ) );
	}

	public function register_admin_page() {
		$page = add_menu_page(
			'Posts Maintenance',
			$this->page_title,
			'manage_options',
			$this->page_slug,
			array( $this, 'callback' ),
			'dashicons-search',
			6
		);

		add_action( 'load-' . $page, array( $this, 'prepare_assets' ) );
	}

	/**
	 * The admin page callback method.
	 *
	 * @return void
	 */
	public function callback() {
		$this->view();
	}

	/**
	 * Prepares assets.
	 *
	 * @return void
	 */
	public function prepare_assets() {
		if ( ! is_array( $this->page_scripts ) ) {
			$this->page_scripts = array();
		}

		$handle       = 'wpmudev_plugintest_maintenancepage';
		$src          = WPMUDEV_PLUGINTEST_ASSETS_URL . '/js/maintenancesettingspage.min.js';
		$style_src    = WPMUDEV_PLUGINTEST_ASSETS_URL . '/css/maintenancesettingspage.min.css';
		$dependencies = ! empty( $this->script_data( 'dependencies' ) )
			? $this->script_data( 'dependencies' )
			: array(
				'react',
				'wp-element',
				'wp-i18n',
				'wp-is-shallow-equal',
				'wp-polyfill',
			);

		$this->page_scripts[ $handle ] = array(
			'src'       => $src,
			'style_src' => $style_src,
			'deps'      => $dependencies,
			'ver'       => $this->assets_version,
			'strategy'  => true,
			'localize'  => array(
				'dom_element_id' => $this->unique_id,
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'wpmudevNonce'   => wp_create_nonce( 'wpmudev' ),
			),
		);
	}

	/**
	 * Gets assets data for given key.
	 *
	 * @param string $key
	 *
	 * @return string|array
	 */
	protected function script_data( string $key = '' ) {
		$raw_script_data = $this->raw_script_data();

		return ! empty( $key ) && ! empty( $raw_script_data[ $key ] ) ? $raw_script_data[ $key ] : '';
	}

	/**
	 * Gets the script data from assets php file.
	 *
	 * @return array
	 */
	protected function raw_script_data(): array {
		static $script_data = null;

		if ( is_null( $script_data ) && file_exists( WPMUDEV_PLUGINTEST_DIR . 'assets/js/authsettingspage.min.asset.php' ) ) {
			$script_data = include WPMUDEV_PLUGINTEST_DIR . 'assets/js/authsettingspage.min.asset.php';
		}

		return (array) $script_data;
	}

	/**
	 * Prepares assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! empty( $this->page_scripts ) ) {
			foreach ( $this->page_scripts as $handle => $page_script ) {
				wp_register_script(
					$handle,
					$page_script['src'],
					$page_script['deps'],
					$page_script['ver'],
					$page_script['strategy']
				);

				if ( ! empty( $page_script['localize'] ) ) {
					wp_localize_script( $handle, 'wpmudevPluginTest', $page_script['localize'] );
				}

				wp_enqueue_script( $handle );

				if ( ! empty( $page_script['style_src'] ) ) {
					wp_enqueue_style( $handle, $page_script['style_src'], array(), $this->assets_version );
				}
			}
		}
	}

	/**
	 * Prints the wrapper element which React will use as root.
	 *
	 * @return void
	 */
	protected function view() {
		echo '<div id="' . esc_attr( $this->unique_id ) . '" class="sui-wrap"></div>';
	}

	/**
	 * Adds the SUI class on markup body.
	 *
	 * @param string $classes
	 *
	 * @return string
	 */
	public function admin_body_classes( $classes = '' ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $classes;
		}

		$current_screen = get_current_screen();

		if ( empty( $current_screen->id ) || ! strpos( $current_screen->id, $this->page_slug ) ) {
			return $classes;
		}

		$classes .= ' sui-' . str_replace( '.', '-', WPMUDEV_PLUGINTEST_SUI_VERSION ) . ' ';

		return $classes;
	}

	/**
	 * Schedules a daily event for scanning posts.
	 *
	 * @since NEXT
	 * @return void
	 */
	public function wpmudev_schedule_daily_scan() {
		if ( ! wp_next_scheduled('wpmudev_daily_scan_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wpmudev_daily_scan_event' );
		}
	}

	/**
	 * Handles the post scanning process based on the context (ajax, cron, or cli).
	 *
	 * @since NEXT
	 * @param string $context The context in which the function is called. Defaults to 'ajax'.
	 * @return void
	 */
	public function handle_scan_posts( $context = 'ajax' ) {
		if ( 'ajax' === $context ) {
			check_ajax_referer( 'wpmudev', 'nonce' );
		}

		$post_types = apply_filters( 'wpmudev_scan_post_types', array( 'post', 'page' ) );

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				update_post_meta( get_the_ID(), 'wpmudev_test_last_scan', current_time( 'timestamp' ) );
			}

			wp_reset_postdata();

			if ( 'ajax' === $context ) {
				wp_send_json_success();
			} elseif ( 'cli' === $context ) {
				\WP_CLI::success( 'Posts scanned successfully.' );
			}
		} else {
			if ( 'ajax' === $context ) {
				wp_send_json_error();
			} elseif ( 'cli' === $context ) {
				\WP_CLI::error( 'No posts found to scan.' );
			}
		}
	}

	/**
	 * Handles the post scanning process for AJAX requests.
	 *
	 * @since NEXT
	 * @return void
	 */
	public function handle_scan_posts_ajax() {
		$this->handle_scan_posts();
	}

	/**
	 * Handles the post scanning process for cron jobs.
	 *
	 * @since NEXT
	 * @return void
	 */
	public function handle_scan_posts_cron() {
		$this->handle_scan_posts( 'cron' );
	}
}
