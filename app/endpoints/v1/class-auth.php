<?php
/**
 * Google Auth Shortcode.
 *
 * @link          https://wpmudev.com/
 * @since         1.0.0
 *
 * @author        WPMUDEV (https://wpmudev.com)
 * @package       WPMUDEV\PluginTest
 *
 * @copyright (c) 2023, Incsub (http://incsub.com)
 */

namespace WPMUDEV\PluginTest\Endpoints\V1;

// Abort if called directly.
defined( 'WPINC' ) || die;

use WPMUDEV\PluginTest\Endpoint;
use WP_REST_Server;
use WP_REST_Response;

class Auth extends Endpoint {
	/**
	 * API endpoint for the current endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @var string $endpoint
	 */
	protected $endpoint = 'auth/auth-url';

	/**
	 * Register the routes for handling auth functionality.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function register_routes() {
		// TODO
		// Add a new Route to logout.

		// Route to get auth url.
		register_rest_route(
			$this->get_namespace(),
			$this->get_endpoint(),
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_credentials' ),
					'permission_callback' => array( $this, 'edit_permission' ),
					'args'                => array(
						'client_id'     => array(
							'required'    => true,
							'description' => __( 'The client ID from Google API project.', 'wpmudev-plugin-test' ),
							'type'        => 'string',
						),
						'client_secret' => array(
							'required'    => true,
							'description' => __( 'The client secret from Google API project.', 'wpmudev-plugin-test' ),
							'type'        => 'string',
						),
					),
				),
			)
		);
	}

	/**
	 * Save the client id and secret.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object on success, or an error array on failure.
	 */
	public function save_credentials( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_REST_Response(
				array(
					'status'  => 'error',
					'message' => __( 'Invalid nonce.', 'wpmudev-plugin-test' ),
				),
				403,
			);
		}

		$client_id     = sanitize_text_field( $request['client_id'] );
		$client_secret = sanitize_text_field( $request['client_secret'] );

		$options = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
		);

		try {
			update_option( 'wpmudev_plugin_test_settings', $options );

			return rest_ensure_response(
				array(
					'status'  => 'success',
					'message' => __( 'Settings saved successfully.', 'wpmudev-plugin-test' ),
				)
			);
		} catch ( Exception $e ) {
			return rest_ensure_response(
				array(
					'status'  => 'error',
					'message' => $e->getMessage(),
				),
				500
			);
		}
	}
}
