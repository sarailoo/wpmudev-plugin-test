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
use WP_Error;

class Auth extends Endpoint {
	protected function __construct() {
		parent::__construct();

		if ( ! shortcode_exists( 'wpmudev_google_auth' ) ) {
			add_shortcode( 'wpmudev_google_auth', [ $this, 'wpmudev_google_auth_shortcode' ] );
		}
	}

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
					'args'    => array(
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

		// Register new route for Google oAuth confirmation.
		register_rest_route(
			$this->get_namespace(),
			'auth/confirm',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'handle_google_oauth' ),
					'permission_callback' => '__return_true',
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
			return new WP_REST_Response( array(
				'status'  => 'error',
				'message' => __( 'Invalid nonce.', 'wpmudev-plugin-test' ),
			), 403 );
		}

		$client_id     = sanitize_text_field( $request['client_id'] );
		$client_secret = sanitize_text_field( $request['client_secret'] );

		$options = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
		);

		try {
			update_option( 'wpmudev_plugin_test_settings', $options );

			return rest_ensure_response( array(
				'status'  => 'success',
				'message' => __( 'Settings saved successfully.', 'wpmudev-plugin-test' ),
			) );
		} catch ( Exception $e ) {
			return rest_ensure_response( array(
				'status'  => 'error',
				'message' => $e->getMessage(),
			), 500 );
		}
	}

	public function handle_google_oauth( $request ) {
		$code = $request->get_param('code');
	
		if ( !$code ) {
			return new WP_Error( 'missing_code', __( 'Missing authorization code.', 'wpmudev-plugin-test' ), array( 'status' => 400 ) );
		}

		// Exchange code for access token.
		$response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
			'body' => array(
				'code'          => $code,
				'client_id'     => get_option( 'wpmudev_plugin_test_settings' )['client_id'],
				'client_secret' => get_option( 'wpmudev_plugin_test_settings' )['client_secret'],
				'redirect_uri'  => home_url( '/wp-json/wpmudev/v1/auth/confirm' ),
				'grant_type'    => 'authorization_code',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			return new WP_Error( 'oauth_error', $data['error_description'], array( 'status' => 400 ) );
		}

		// Retrieve user info from Google.
		$access_token = $data['access_token'];
		$response = wp_remote_get( 'https://www.googleapis.com/oauth2/v2/userinfo', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$user_info = json_decode( $body, true );
	
		if ( ! isset( $user_info['email'] ) ) {
			return new WP_Error( 'missing_email', __( 'Failed to retrieve email from Google.', 'wpmudev-plugin-test' ), array( 'status' => 400 ) );
		}

		// Handle user login/registration.
		$email = sanitize_email( $user_info['email'] );
		$user = get_user_by( 'email', $email );

		if ( $user ) {
			// User exists, log them in.
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, true );
		} else {
			// Create new user.
			$random_password = wp_generate_password( 12, false );
			$user_id = wp_create_user( $email, $random_password, $email );
	
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id, true );
		}

		// Redirect to admin or home page.
		$redirect_url = is_user_logged_in() ? admin_url() : home_url();

		wp_redirect( $redirect_url );

		exit;
	}

	public function wpmudev_google_auth_shortcode() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return '<p>' . sprintf(
				/* translators: %s: user display name */
				__( 'Hello, %s!', 'wpmudev-plugin-test' ),
				esc_html( $current_user->display_name )
				) . '</p>';
		}

		$auth_url = 'https://accounts.google.com/o/oauth2/auth?client_id=' . urlencode( get_option('wpmudev_plugin_test_settings')['client_id'] ) . '&redirect_uri=' . urlencode( home_url('/wp-json/wpmudev/v1/auth/confirm') ) . '&response_type=code&scope=email';
		return '<a href="' . esc_url( $auth_url ) . '">' . __( 'Login with Google', 'wpmudev-plugin-test' ) . '</a>';
	}
}
