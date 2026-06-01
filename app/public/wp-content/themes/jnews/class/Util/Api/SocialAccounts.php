<?php

/**
 * Plugin
 *
 * @author Jegtheme
 * @package JNews\Util\Api
 */

namespace JNews\Util\Api;

/**
 * Social Account API for Social Counter Elements
 */
class SocialAccounts {
	/**
	 * Instance of SocialAccount
	 *
	 * @var SocialAccounts
	 */
	private static $instance;

	/**
	 * @var $access_token
	 */
	protected $user_data;

	/**
	 * @var $access_token
	 */
	protected $tiktok_token_key = 'jnews_tiktok_token_expired_date';

	/**
	 * Singleton page of Instagram_Api class
	 *
	 * @return SocialAccounts
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Social Account API constructor.
	 */
	private function __construct() {
		$this->user_data = array(
			'instagram' => get_option( 'jnews_option[jnews_instagram]', array() ),
			'tiktok'    => get_option( 'jnews_option[jnews_tiktok]', array() ),
		);
		add_action( 'init', array( $this, 'jnews_instagram_page' ) );
	}

	/**
	 * Redirect page after getting access token from server.
	 */
	public function jnews_instagram_page() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! empty( $_GET['page'] ) && 'jnews-social' === $_GET['page'] && ! empty( $_GET['social'] ) ) {

			if ( empty( $_GET['customizer'] ) ) {

				$url    = 'admin.php?';
				$params = array(
					'page'          => 'jnews',
					'path'          => 'customizer',
					'activePanel'   => 'jnews_social_panel',
					'activeSection' => 'jnews_social_counter_section',
				);
			} else {
				$url    = 'customize.php?';
				$params = array(
					'autofocus[section]' => 'jnews_social_counter_section',
				);
			}

			$social = sanitize_text_field( $_GET['social'] );
			switch ( $social ) {
				case 'tiktok':
					if ( isset( $_GET['access_token'] ) && isset( $_GET['open_id'] ) ) {
						$tiktok_settings = $this->user_data['tiktok'];
						if ( empty( $tiktok_settings ) || ( ! empty( $tiktok_settings ) && $tiktok_settings['access_token'] !== sanitize_text_field( $_GET['access_token'] ) ) && true ) {

							$options = array(
								'access_token'       => sanitize_text_field( $_GET['access_token'] ),
								'open_id'            => sanitize_text_field( $_GET['open_id'] ),
								'expires_in'         => sanitize_text_field( $_GET['expires_in'] ),
								'refresh_token'      => sanitize_text_field( $_GET['refresh_token'] ),
								'refresh_expires_in' => sanitize_text_field( $_GET['refresh_expires_in'] ),
							);
							update_option( 'jnews_option[jnews_tiktok]', $options );
							$tiktok_data = $this->get_tiktok_data( sanitize_text_field( $_GET['access_token'] ), sanitize_text_field( $_GET['open_id'] ) );
							update_option( 'jnews_tiktok_display_name', $tiktok_data, 'no' );
						}
					}
					break;

				case 'instagram':
					if ( ! empty( $_GET['access_token'] ) ) {
						$user_data = $this->get_user_data( $_GET['access_token'] );
						$account   = array(
							'id'           => $user_data['user_id'],
							'username'     => $user_data['username'],
							'access_token' => $this->clean( sanitize_text_field( $_GET['access_token'] ) ),
							'expires_on'   => (int) $_GET['expires_on'] + time(),
						);
						update_option( 'jnews_option[jnews_instagram]', $account );
					} else {
						$params['alert']         = 'Failed to connect Instagram account';
						$params['alert_message'] = ( isset( $_GET['error'] ) && ! empty( $_GET['error'] ) ) ? sanitize_text_field( $_GET['error'] ) : '';
					}

					break;
			}

			wp_redirect( admin_url( $url . http_build_query( $params ) ) );
			exit;
		}
	}

		/**
		 * Get tiktok data from jeg tiktok server
		 *
		 * @param string $access_token Tiktok API access token.
		 * @param string $open_id Tiktok API open id.
		 *
		 * @return boolean|array
		 */
	public function get_tiktok_data( $access_token = '', $open_id = '' ) {
			$url = 'https://open.tiktokapis.com/v2/user/info/?fields=display_name';

			$user_info = $this->remote_get(
				$url,
				array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,

				)
			);
			return $user_info['data']['user']['display_name'];
	}


	/**
	 * Check if need to refresh the Access Token.
	 *
	 * @return bool
	 */
	private function time_passed_threshold() {

		$expiration_time   = $this->get( 'instagram', 'expires_on' );
		$refresh_threshold = $expiration_time - ( 30 * DAY_IN_SECONDS );

		return $refresh_threshold < time();
	}

	/**
	 * Refresh access token if needed.
	 * Valid for 60 days and refresh every 30 days
	 *
	 * @return bool|mixed|string|void|\WP_Error
	 */
	public function refresh_instagram_token() {
		if ( ! $this->is_active( 'instagram' ) ) {
			return false;
		}
		if ( ! $this->time_passed_threshold() ) {
			return true;
		}

		$url = add_query_arg(
			array(
				'grant_type'   => 'ig_refresh_token',
				'access_token' => $this->user_data['instagram']['access_token'],
			),
			'https://graph.instagram.com/refresh_access_token'
		);

		$data = $this->remote_get( $url );

		if ( is_wp_error( $data ) ) {
			return false;
		}
		if ( ! empty( $data['access_token'] ) ) {
			$access_token = $this->clean( sanitize_text_field( $data['access_token'] ) );
			$expires_on   = (int) $data['expires_in'] + time();

			$this->update( 'access_token', $access_token );
			$this->update( 'expires_on', $expires_on );
			return true;
		}
		return false;
	}

	/**
	 * Refresh tiktok token when fetch new data.
	 *
	 * @return bool
	 */
	public function refresh_tiktok_token() {
		if ( get_option( 'jnews_tiktok_token_expired_date', 0 ) > time() ) {
			return true;
		}
		$url      = 'https://support.jegtheme.com/wp-json/jeg-server-tiktok/v1/refresh-token';
		$settings = $this->user_data['tiktok'];
		if ( ! empty( $settings ) && isset( $settings['refresh_token'] ) ) {
			$args    = array(
				'body' => array_merge(
					$this->validation_data(),
					array(
						'access_token' => $settings['refresh_token'],
					)
				),
			);
			$request = json_decode( wp_remote_retrieve_body( wp_remote_post( $url, $args ) ) );

			if ( is_object( $request ) && property_exists( $request, 'access_token' ) ) {
				$settings = array_merge(
					$settings,
					array(
						'access_token'       => sanitize_text_field( $request->access_token ),
						'open_id'            => sanitize_text_field( $request->open_id ),
						'expires_in'         => sanitize_text_field( $request->expires_in ),
						'refresh_expires_in' => sanitize_text_field( $request->refresh_expires_in ),
						'refresh_token'      => sanitize_text_field( $request->refresh_token ),
					)
				);
				update_option( 'jnews_option[jnews_tiktok]', $settings );
				update_option( 'jnews_tiktok_token_expired_date', time() + (int) $request->expires_in );
				$this->user_data['tiktok'] = $settings;
				return true;
			} else {
				delete_option( 'jnews_option[jnews_tiktok]' );
			}
		}

		return false;
	}

	/**
	 * Get tiktok validation data
	 *
	 * @return array
	 */
	public function validation_data() {

		if ( function_exists( 'jnews_get_license' ) ) {
			$license = jnews_get_license();
			return array(
				'tiktok_callback' => get_site_url(),
				'code'            => $license['purchase_code'],
			);
		}
		return array();
	}

	/**
	 * JNews get user instagram data.
	 *
	 * @param string $token Instagram Acces Token.
	 */
	public function get_user_data( $token ) {
		$url      = sprintf(
			'https://graph.instagram.com/v20.0/me?fields=user_id,username,followers_count&access_token=%s',
			$token
		);
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
			)
		);
		if ( ! is_wp_error( $response ) && isset( $response['response'] ) && isset( $response['response']['code'] ) && $response['response']['code'] == '200' ) {
			$response_body = wp_remote_retrieve_body( $response );
			return json_decode( $response_body, true );
		}
		return array();
	}

	/**
	 * Get Instagram token option
	 *
	 * @param bool $key
	 *
	 * @return bool
	 */
	public function get( $social, $key = false ) {
		if ( ! empty( $this->user_data[ $social ][ $key ] ) ) {
			return $this->user_data[ $social ][ $key ];
		}

		return false;
	}



	/**
	 * Get Instagram token option
	 *
	 * @param bool $key
	 *
	 * @return bool
	 */
	public function set( $key = false ) {

		if ( empty( $instagram_token ) ) {
			return false;
		}
		if ( ! empty( $instagram_token[ $key ] ) ) {
			return $instagram_token[ $key ];
		}

		return false;
	}

	/**
	 * Update Instagram token option
	 *
	 * @param bool $key
	 * @param bool $value
	 */
	public function update( $key = false, $value = false ) {

		if ( empty( $key ) || empty( $value ) ) {
			return;
		}

		$account = get_option( 'jnews_option[jnews_instagram]', array() );

		$account[ $key ] = $value;

		update_option( 'jnews_option[jnews_instagram]', $account );
	}

	/**
	 * Check if smash ballon plugin active
	 *
	 * @return bool
	 */
	public function is_sb_activate() {
		return function_exists( 'sb_instagram_feed_init' );
	}

	/**
	 * Clean Access TokenClean Access Token
	 *
	 * @param $maybe_dirty
	 *
	 * @return string|string[]
	 */
	protected function clean( $maybe_dirty ) {

		if ( substr_count( $maybe_dirty, '.' ) < 3 ) {
			return str_replace( '634hgdf83hjdj2', '', $maybe_dirty );
		}

		$parts     = explode( '.', trim( $maybe_dirty ) );
		$last_part = $parts[2] . $parts[3];
		$cleaned   = $parts[0] . '.' . base64_decode( $parts[1] ) . '.' . base64_decode( $last_part );

		return $cleaned;
	}

	/**
	 * Check if there is a connected account
	 *
	 * @return bool
	 */
	public function is_active( $social ) {
		$is_active = false;
		if ( isset( $this->user_data[ $social ]['access_token'] ) && $this->user_data[ $social ]['access_token'] ) {
			$is_active = true;
		}

		return $is_active;
	}

	/**
	 * Check if the Access token is expired
	 *
	 * @return bool
	 */
	public function is_expired( $social ) {

		if ( ! $this->is_active( $social ) ) {
			return false;
		}

		$expires_on = $this->get( $social, 'expires_on' );

		return empty( $expires_on ) || ( ! empty( $expires_on ) && $expires_on < time() );
	}

	/**
	 * Make the connection to Instagram
	 *
	 * @param bool $url
	 *
	 * @return bool|mixed|string|WP_Error
	 */
	protected function remote_get( $url = false, $header = array() ) {
		if ( empty( $header ) ) {
			$args = array(
				'timeout'    => 30,
				'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36',
			);
		} else {
			$args = array(
				'headers'    => $header,
				'timeout'    => 30,
				'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36',
			);
		}
		$request = wp_remote_get( $url, $args );

		return $this->check_for_errors( $request );
	}

	/**
	 * Check if the reply has error
	 *
	 * @param bool $response
	 *
	 * @return bool|mixed|string|WP_Error
	 */
	protected function check_for_errors( $response = false ) {

		// Check Response for errors
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'http_error', $response->get_error_message() );
		}

		if ( ! empty( $response->errors ) && isset( $response->errors['http_request_failed'] ) ) {
			return new \WP_Error( 'http_error', esc_html( current( $response->errors['http_request_failed'] ) ) );
		}

		if ( 200 !== $response_code ) {

			// Get value of Error - contains more details
			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response, true );

			if ( ! empty( $response['error']['message'] ) ) {
				return new \WP_Error( $response_code, $response['error']['message'] );
			}

			if ( empty( $response_message ) ) {
				return new \WP_Error( $response_code, 'Connection Error' );
			} else {
				return new \WP_Error( $response_code, $response_message );
			}
		}
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$data = wp_remote_retrieve_body( $response );
		$data = json_decode( $data, true );
		return $data;
	}

	/**
	 * Get the Error Messages
	 *
	 * @param bool $error_id
	 *
	 * @return string
	 */
	public function get_error( $error_id = false ) {

		if ( ! empty( $error_id ) ) {

			switch ( $error_id ) {
				case 'inactive':
					return esc_html__( 'Go to the Customizer > JNews : Social, Like & View > Instagram Feed Setting, to connect your Instagram account.', 'jnews-instagram' );
					break;

				case 'expired':
					return esc_html__( 'The Instagram Access Token is expired, Go to the Customizer > JNews : Social, Like & View > Instagram Feed Setting, to refresh it.', 'jnews-instagram' );
					break;
			}
		}
	}

	/**
	 * Make the connection to Instagram
	 */
	public function get_followers( $key = 'instagram', $purge = false ) {
		$result = array();
		if ( ! $this->is_active( $key ) ) {
			return null;
		}
		switch ( $key ) {
			case 'instagram':
				if ( $this->refresh_instagram_token() ) {
					$args = array(
						'fields'       => 'followers_count',
						'access_token' => $this->user_data[ $key ]['access_token'],
					);
					$id   = $this->user_data[ $key ]['id'];
					$url  = add_query_arg( $args, "https://graph.instagram.com/$id" );
					$data = $this->remote_get( $url );
					if ( isset( $data['followers_count'] ) ) {
						$result['response']['code'] = 200;
						$result['counts']           = $data['followers_count'];
					}
					break;
				}

			case 'tiktok':
				if ( $this->refresh_tiktok_token() ) {
					$url  = 'https://open.tiktokapis.com/v2/user/info/?fields=follower_count';
					$data = $this->remote_get(
						$url,
						array(
							'Authorization' => 'Bearer ' . $this->user_data[ $key ]['access_token'],
							'Content-Type'  => 'application/json',
						)
					);
					if ( isset( $data['data'] ) && isset( $data['data']['user'] ) && isset( $data['data']['user']['follower_count'] ) ) {
						$result['response']['code'] = 200;
						$result['counts']           = $data['data']['user']['follower_count'];
					}
				}
				break;
		}
		return $result;
	}
}
