<?php
/**
 * This file stores the configuration functions used all over the plugin.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage handlers
 */

namespace MO_LDAP\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MO_LDAP\Utils\Mo_Ldap_Local_Utils;
use MO_LDAP\Helpers\Mo_Ldap_Local_Auth_Response_Helper;

if ( ! class_exists( 'Mo_Ldap_Local_Configuration_Handler' ) ) {
	/**
	 * Mo_Ldap_Local_Configuration_Handler : Class for the all the plugin configuration functions.
	 */
	class Mo_Ldap_Local_Configuration_Handler {
		/**
		 * Utility object.
		 *
		 * @var [object]
		 */
		private $utils;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->utils = new Mo_Ldap_Local_Utils();
		}

		/**
		 * Function mo_ldap_local_authenticate : performs ldap authentication upon login.
		 *
		 * @param string $username Username.
		 * @param string $password Password.
		 * @return object
		 */
		public function mo_ldap_local_authenticate( $username, $password ) {
			$username = stripcslashes( $username );
			$password = stripcslashes( $password );

			if ( ! $this->utils::is_extension_installed( 'ldap' ) ) {
				$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
				$auth_response->status         = false;
				$auth_response->status_message = 'LDAP_ERROR';
				$auth_response->user_dn        = '';
				return $auth_response;

			}
			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
				$auth_response->status         = false;
				$auth_response->status_message = 'OPENSSL_ERROR';
				$auth_response->user_dn        = '';
				return $auth_response;
			}

			$ldapconn = $this->get_connection();
			if ( $ldapconn ) {
				$filter             = get_option( 'mo_ldap_local_search_filter' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_filter' ) ) : '';
				$search_base_string = get_option( 'mo_ldap_local_search_base' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_base' ) ) : '';
				$ldap_bind_dn       = get_option( 'mo_ldap_local_server_dn' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_dn' ) ) : '';
				$ldap_bind_password = get_option( 'mo_ldap_local_server_password' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_password' ) ) : '';

				$email_attribute         = strtolower( get_option( 'mo_ldap_local_email_attribute' ) );
				$search_filter_attribute = strtolower( get_option( 'Filter_search' ) );

				$attr = array();
				if ( isset( $email_attribute ) && ! empty( $email_attribute ) ) {
					array_push( $attr, $email_attribute );
				}
				if ( isset( $search_filter_attribute ) && ! empty( $search_filter_attribute ) ) {
					array_push( $attr, $search_filter_attribute );
				}
				$username = ldap_escape( $username, '', LDAP_ESCAPE_FILTER );

				$filter = str_replace( '?', $username, $filter );

				$user_search_result = null;
				$entry              = null;
				$info               = null;
				if ( get_option( 'mo_ldap_local_use_tls' ) ) {
					ldap_start_tls( $ldapconn );
				}
				@ldap_bind( $ldapconn, $ldap_bind_dn, $ldap_bind_password ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Used to silense LDAP error; hanlded them below using Mo_Ldap_Local_Auth_Response_Helper class
				$error_no = ldap_errno( $ldapconn );
				$err      = ldap_error( $ldapconn );
				if ( '' === $ldap_bind_password ) {
					$err = 'Invalid credentials';
				}

				if ( -1 === $error_no ) {
					$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
					$auth_response->status         = false;
					$auth_response->status_message = 'LDAP_PING_ERROR';
					$auth_response->user_dn        = '';
					return $auth_response;
				} elseif ( 0 !== strcasecmp( $err, 'success' ) ) {
					$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
					$auth_response->status         = false;
					$auth_response->status_message = 'LDAP_BIND_ERROR';
					$auth_response->user_dn        = '';
					return $auth_response;
				}

				if ( ( ! empty( $search_base_string ) && ! empty( $filter ) ) && ldap_search( $ldapconn, $search_base_string, $filter, $attr ) ) {
					$user_search_result = ldap_search( $ldapconn, $search_base_string, $filter, $attr );
				} else {
					$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
					$auth_response->status         = false;
					$auth_response->status_message = 'LDAP_USER_SEARCH_ERROR';
					$auth_response->user_dn        = '';
					return $auth_response;
				}
				$info  = ldap_first_entry( $ldapconn, $user_search_result );
				$entry = ldap_get_entries( $ldapconn, $user_search_result );

				if ( $info ) {
					$user_dn = ldap_get_dn( $ldapconn, $info );
				} else {
					$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
					$auth_response->status         = false;
					$auth_response->status_message = 'LDAP_USER_NOT_EXIST';
					$auth_response->user_dn        = '';
					return $auth_response;
				}
				$authentication_response = $this->authenticate( $user_dn, $password );
				if ( strcasecmp( $authentication_response->status_message, 'LDAP_USER_BIND_SUCCESS' ) === 0 ) {
					$attributes_array   = array();
					$profile_attributes = array();

					unset( $attr[0] );

					$authentication_response->attribute_list = $attributes_array;

					if ( ! empty( $email_attribute ) && isset( $entry[0][ $email_attribute ][0] ) ) {
						$profile_attributes['mail'] = $entry[0][ $email_attribute ][0];
					}

					$authentication_response->profile_attributes_list = $profile_attributes;
				}
				return $authentication_response;
			} else {
				$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
				$auth_response->status         = false;
				$auth_response->status_message = 'ERROR';
				$auth_response->user_dn        = '';
				return $auth_response;
			}
		}

		/**
		 * Function test_connection : Test connection with ldap.
		 *
		 * @return string
		 */
		public function test_connection() {

			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			if ( ! $this->utils::is_extension_installed( 'ldap' ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_ERROR',
						'statusMessage' => '<a target="_blank" rel="noopener" href="http://php.net/manual/en/ldap.installation.php">PHP LDAP extension</a> is not installed or disabled. Please enable it.',
					)
				);
			} elseif ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'OPENSSL_ERROR',
						'statusMessage' => '<a target="_blank" rel="noopener" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled. Please enable it.',
					)
				);
			}

			delete_option( 'mo_ldap_local_server_url_status' );
			delete_option( 'mo_ldap_local_service_account_status' );
			$server_name = get_option( 'mo_ldap_local_server_url' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_url' ) ) : '';

			$ldapconn = $this->get_connection();
			if ( $ldapconn ) {
				$ldap_bind_dn       = get_option( 'mo_ldap_local_server_dn' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_dn' ) ) : '';
				$ldap_bind_password = get_option( 'mo_ldap_local_server_password' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_password' ) ) : '';
				if ( get_option( 'mo_ldap_local_use_tls' ) ) {
					ldap_start_tls( $ldapconn );

				}
				@ldap_bind( $ldapconn, $ldap_bind_dn, $ldap_bind_password ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Used to silense LDAP error; hanlded them below using Mo_Ldap_Local_Auth_Response_Helper class
				$error_no = ldap_errno( $ldapconn );
				$err      = ldap_error( $ldapconn );
				if ( -1 === $error_no ) {
					add_option( 'mo_ldap_local_server_url_status', 'INVALID', '', 'no' );
					add_option( 'mo_ldap_local_service_account_status', 'INVALID', '', 'no' );
					$troubleshooting_url = add_query_arg( array( 'tab' => 'troubleshooting' ), $request_uri );
					if ( strpos( $server_name, 'ldaps' ) !== false ) {
						return wp_json_encode(
							array(
								'statusCode'    => 'PING_ERROR',
								'statusMessage' => 'Cannot connect to LDAP Server. It seems that you are trying <strong>ldaps</strong> connection. <br>1. Make sure you have gone go through the configuration steps mentioned in our <a href="https://www.miniorange.com/guide-to-setup-ldaps-on-windows-server" rel="noopener" target="_blank">LDAPS document</a> to connect with LDAP server over LDAPS (LDAP over SSL:636). <br>2. Make sure you have entered correct LDAP server hostname or IP address and if there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your WordPress site IP address and below specified port number.<br> You can also check our <a href=' . esc_url( $troubleshooting_url ) . '>Troubleshooting</a> steps. If you still face the same issue then contact us using the support form below.',
							)
						);
					} else {
						return wp_json_encode(
							array(
								'statusCode'    => 'PING_ERROR',
								'statusMessage' => 'Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address. <br>If there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your WordPress site IP address and below specified port number. <br>You can also check our <a href=' . esc_url( $troubleshooting_url ) . '>Troubleshooting</a> steps. If you still face the same issue then contact us using the support form below.',
							)
						);
					}
				} elseif ( strcasecmp( $err, 'success' ) !== 0 ) {
					add_option( 'mo_ldap_local_server_url_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_service_account_status', 'INVALID', '', 'no' );
					return wp_json_encode(
						array(
							'statusCode'    => 'BIND_ERROR',
							'statusMessage' => 'Connection to LDAP server is Successful but unable to make authenticated bind to LDAP server. Make sure you have provided correct username or password.',
						)
					);
				} else {
					add_option( 'mo_ldap_local_server_url_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_service_account_status', 'VALID', '', 'no' );
					return wp_json_encode(
						array(
							'statusCode'    => 'BIND_SUCCESS',
							'statusMessage' => 'Connection was established successfully and your configuration has been saved. Please configure LDAP User Mapping now.',
						)
					);
				}
			} else {
				add_option( 'mo_ldap_local_service_account_status', 'INVALID', '', 'no' );
				add_option( 'mo_ldap_local_server_url_status', 'INVALID', '', 'no' );
				$troubleshooting_url = add_query_arg( array( 'tab' => 'troubleshooting' ), $request_uri );
				return wp_json_encode(
					array(
						'statusCode'    => 'ERROR',
						'statusMessage' => 'There was an error in connecting to LDAP Server with the current settings. Make sure you have entered correct LDAP server hostname or IP address and if there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your WordPress site IP address and below specified port number. You can also check our <a href=' . esc_url( $troubleshooting_url ) . '>Troubleshooting</a> steps. If you still face the same issue then contact us using the support form below.',
					)
				);
			}
		}

		/**
		 * Function test_authentication : Test authentication for the ldap user.
		 *
		 * @param string $username Username.
		 * @param string $password Password.
		 * @return string
		 */
		public function test_authentication( $username, $password ) {
			if ( ! $this->utils::is_extension_installed( 'ldap' ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_ERROR',
						'statusMessage' => '<a target="_blank" rel="noopener" href="http://php.net/manual/en/ldap.installation.php">PHP LDAP extension</a> is not installed or disabled. Please enable it.',
					)
				);
			} elseif ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'OPENSSL_ERROR',
						'statusMessage' => '<a target="_blank" rel="noopener" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled. Please enable it.',
					)
				);
			}

			$local_server_url_status = get_option( 'mo_ldap_local_server_url_status' );
			if ( strcasecmp( $local_server_url_status, 'INVALID' ) === 0 ) {
				delete_option( 'mo_ldap_local_server_url_status' );
				delete_option( 'mo_ldap_local_service_account_status' );
				add_option( 'mo_ldap_local_server_url_status', 'INVALID', '', 'no' );
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_LOCAL_SERVER_NOT_CONFIGURED',
						'statusMessage' => 'Make sure you have successfully configured the <strong> LDAP connection information </strong>',
					)
				);
			}
			delete_option( 'mo_ldap_local_user_mapping_status' );
			delete_option( 'mo_ldap_local_username_status' );
			delete_option( 'mo_ldap_local_password_status' );
			$auth_response = $this->mo_ldap_local_authenticate( $username, $password );
			if ( strcasecmp( $auth_response->status_message, 'LDAP_USER_BIND_SUCCESS' ) === 0 ) {
					add_option( 'mo_ldap_local_server_url_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_service_account_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_user_mapping_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_username_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_password_status', 'VALID', '', 'no' );
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_USER_BIND_SUCCESS',
						'statusMessage' => 'You have successfully configured your LDAP settings.',
					)
				);
			} elseif ( strcasecmp( $auth_response->status_message, 'LDAP_USER_BIND_ERROR' ) === 0 ) {
					add_option( 'mo_ldap_local_user_mapping_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_username_status', 'VALID', '', 'no' );
					add_option( 'mo_ldap_local_password_status', 'INVALID', '', 'no' );
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_USER_BIND_ERROR',
						'statusMessage' => 'User found in the LDAP server but entered password is invalid. Please check your password.',
					)
				);
			} elseif ( strcasecmp( $auth_response->status_message, 'LDAP_USER_SEARCH_ERROR' ) === 0 ) {
					add_option( 'mo_ldap_local_user_mapping_status', 'INVALID', '', 'no' );
					add_option( 'mo_ldap_local_username_status', 'INVALID', '', 'no' );
					add_option( 'mo_ldap_local_password_status', 'INVALID', '', 'no' );
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_USER_SEARCH_ERROR',
						'statusMessage' => 'Error while searching user in LDAP server.',
					)
				);
			} elseif ( strcasecmp( $auth_response->status_message, 'LDAP_USER_NOT_EXIST' ) === 0 ) {
					add_option( 'mo_ldap_local_user_mapping_status', 'INVALID', '', 'no' );
					add_option( 'mo_ldap_local_username_status', 'INVALID', '', 'no' );
					add_option( 'mo_ldap_local_password_status', 'INVALID', '', 'no' );
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_USER_NOT_EXIST',
						'statusMessage' => 'Cannot find user <strong>' . esc_attr( $username ) . '</strong> in the directory.<br>Possible reasons:<br>1. The <strong>search base</strong> DN is typed incorrectly. Please verify if that search base is present.<br>2. User is not present in that search base. The user may be present in the directory but in some other <strong>Search Base DN</strong> and you may have entered a <strong>Search Base DN</strong> where this users is not present.<br>3. <strong>Username Attribute</strong> is incorrect - User is present in the search base but the username you are trying is mapped to a different attribute in the Username Attribute. <br>E.g. You may trying with <strong>email attribute</strong> value and you may have selected <strong>samaccountname attribute</strong> in the configuration. Please make sure that the right attribute is selected in the <strong>Username Attribute</strong> (with which you want the authentication to happen).<br> 4. User is actually not present in the search base. Please make sure that the user is present and test with the right user.',
					)
				);
			} elseif ( strcasecmp( $auth_response->status_message, 'ERROR' ) === 0 ) {
					add_option( 'mo_ldap_local_user_mapping_status', 'INVALID', '', 'no' );
					add_option( 'mo_ldap_local_username_status', 'INVALID', '', 'no' );
					add_option( 'mo_ldap_local_password_status', 'INVALID', '', 'no' );
				return wp_json_encode(
					array(
						'statusCode'    => 'LDAP_USER_SEARCH_ERROR',
						'statusMessage' => 'Error while authenticating user in LDAP server.',
					)
				);
			}
		}

		/**
		 * Function get_connection : Create a connection with the ldap server.
		 *
		 * @return object
		 */
		public function get_connection() {

			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return null;
			}

			$server_name = get_option( 'mo_ldap_local_server_url' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_url' ) ) : '';

			$ldapconn = ldap_connect( $server_name );
			if ( $ldapconn ) {
				if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
					ldap_set_option( $ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 5 );
				}

				ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3 );
				ldap_set_option( $ldapconn, LDAP_OPT_REFERRALS, 0 );
			}
			return $ldapconn;
		}

		/**
		 * Function authenticate : Performs authentication of a user.
		 *
		 * @param string $user_dn Distinguished name of the user.
		 * @param string $password Password.
		 * @return object
		 */
		public function authenticate( $user_dn, $password ) {

			$ldapconn = $this->get_connection();

			if ( get_option( 'mo_ldap_local_use_tls' ) ) {
				ldap_start_tls( $ldapconn );
			}
			@ldap_bind( $ldapconn, $user_dn, $password ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Used to silense LDAP error; hanlded them below using Mo_Ldap_Local_Auth_Response_Helper class
			$error_no = ldap_errno( $ldapconn );
			$err      = ldap_error( $ldapconn );

			if ( -1 === $error_no ) {
				$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
				$auth_response->status         = false;
				$auth_response->status_message = 'LDAP_PING_ERROR';
				$auth_response->user_dn        = '';
				return $auth_response;
			} elseif ( strcasecmp( $err, 'success' ) !== 0 ) {
				$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
				$auth_response->status         = false;
				$auth_response->status_message = 'LDAP_USER_BIND_ERROR';
				$auth_response->user_dn        = '';
				return $auth_response;
			} else {
				$auth_response                 = new Mo_Ldap_Local_Auth_Response_Helper();
				$auth_response->status         = true;
				$auth_response->status_message = 'LDAP_USER_BIND_SUCCESS';
				$auth_response->user_dn        = $user_dn;
				return $auth_response;
			}
		}

		/**
		 * Function show_search_bases_list : Display list of all the search bases.
		 *
		 * @return void
		 */
		public function show_search_bases_list() {
			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return;
			}

			$ldap_bind_dn       = get_option( 'mo_ldap_local_server_dn' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_dn' ) ) : '';
			$ldap_bind_password = get_option( 'mo_ldap_local_server_password' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_password' ) ) : '';

			if ( $this->utils::is_extension_installed( 'ldap' ) ) {

				$ldapconn = $this->get_connection();
				if ( $ldapconn ) {
					@ldap_bind( $ldapconn, $ldap_bind_dn, $ldap_bind_password ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Used to silense LDAP error; hanlded them below using Mo_Ldap_Local_Auth_Response_Helper class
					if ( '' === $ldap_bind_password ) {
						delete_option( 'mo_ldap_local_service_account_status' );
					}
					$check_ldap_conn = get_option( 'mo_ldap_local_service_account_status' );
					?>
				<style>
					.mo_ldap_local_search_bases_container {
						border-radius: 10px;
						/* width: 80%; */
						display: flex;
					}
					.mo_ldap_local_multiple_search_base_premium_box {
						text-align: center; 
						color: #fff; 
						padding: 18px; 
						font-weight: 500; 
						display: flex; 
						justify-content: center;
					}
					.mo_ldap_local_multiple_search_base_premium_box_inner {
						width: 85%; 
						border: 1px solid gray; 
						border-radius: 10px; 
						padding: 3px 5px; 
						background-color: #aa4dc8; 
						justify-content: center; 
						align-items: center; 
						display: flex; 
						margin-right: 17px;
					}
					.mo_ldap_local_search_field {
						opacity: 65%;
						background: #5b5b5b;
						width: 100%;
						padding: 15px;
						border: none;
						color: #fff;
						border-radius: 10px 0px 0px 10px;
						outline: none;
					}
					.mo_ldap_local_search_field::placeholder {
						color: #fff;
					}

					.mo_ldap_local_search_button {
						opacity: 65%;
						padding-right: 20px;
						background: #5b5b5b;
						border-radius: 0px 10px 10px 0px;
						border: none;
					}

					.mo_ldap_local_search_field_container {
						padding: 5% 10% 2% 10%;	
					}

					.mo_ldap_local_show_search_base_container {
						font-family: 'Inter' !important;
						font-style: normal;
						min-height: 100%;
						background: #9f9f9f;
						margin: -8px;
					}

					.mo_ldap_local_search_base_div {
						background-color: #565656;
						border-radius: 10px;
						padding: 15px 15px;
						width: 88%;
						font-size: 14px;
						color: #fff;
						margin-top: 10px;
						margin-right: auto;
						margin-left: auto;
					}
					.mo_ldap_local_not_found_div {
						background-color: #565656;
						border-radius: 10px;
						padding: 15px 15px;
						width: 80%;
						font-size: 14px;
						color: #fff;
						margin-top: 10px;
						margin-right: auto;
						margin-left: auto;
						text-align: justify;
					}
					.mo_ldap_local_overflow_container {
						height: 325px;
						display: flex;
						flex-direction: column;
						overflow-y: auto;
						width: 90%;
						margin: auto;
					}

					.mo_ldap_local_submit_button {
						font-size: 14px;
						width: 25%;
						cursor: pointer;
						background: #0076E1;
						color: white;
						border-radius: 8px;
						padding: 12px;
						margin: 10px;
						font-weight: 600;
						border: none;
					}

					.mo_ldap_local_close_button {
						font-size: 14px;
						width: 25%;
						cursor: pointer;
						background: #FFFFFF;
						/* border: 1px solid #0076E1; */
						color: #0076E1;
						border-radius: 8px;
						padding: 12px;
						margin: 10px;
						font-weight: 600;
						border: none;
					}

				</style>
				<script>
					function mo_ldap_local_search_search_base() {
						let input = document.getElementById('mo_ldap_local_search_bar').value
						input = input.toLowerCase();
						let x = document.getElementsByClassName('mo_ldap_local_search_base_div');

						for (i = 0; i < x.length; i++) { 
							if (!x[i].querySelector('input[name="select_ldap_search_bases[]"]').value.toLowerCase().includes(input)) {
								x[i].style.display="none";
							}
							else {
								x[i].style.display="block";                 
							}
						}
					}
				</script>
					<?php
					if ( 'VALID' === $check_ldap_conn ) {
						?>
						<div class="mo_ldap_local_show_search_base_container">
							<div class="mo_ldap_local_search_field_container">
								<div class="mo_ldap_local_search_bases_container">
										<input type="search" placeholder="Search" id="mo_ldap_local_search_bar" class="mo_ldap_local_search_field" onkeyup="mo_ldap_local_search_search_base()"/>
										<button type="submit" class="mo_ldap_local_search_button" >
										<svg width="25" height="25" viewBox="0 0 9 9" fill="#fff">
											<path d="M6.76163 6.23138L8.36775 7.83712L7.83712 8.36775L6.23138 6.76163C5.6339 7.24058 4.89075 7.50109 4.125 7.5C2.262 7.5 0.75 5.988 0.75 4.125C0.75 2.262 2.262 0.75 4.125 0.75C5.988 0.75 7.5 2.262 7.5 4.125C7.50109 4.89075 7.24058 5.6339 6.76163 6.23138ZM6.00937 5.95312C6.48529 5.46371 6.75108 4.80766 6.75 4.125C6.75 2.6745 5.57513 1.5 4.125 1.5C2.6745 1.5 1.5 2.6745 1.5 4.125C1.5 5.57513 2.6745 6.75 4.125 6.75C4.80766 6.75108 5.46371 6.48529 5.95312 6.00937L6.00937 5.95312Z" fill="#fff"/>
										</svg>
										</button>
								</div>
							</div>
							<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
								<span>Select your Search Base DN from the below Search bases list</span>
							</div>
							<form method="post" action="">
							<div class="mo_ldap_local_overflow_container">
								<?php
								$previous_search_bases     = $this->utils::decrypt( get_option( 'mo_ldap_local_search_base' ) );
								$search_base_list          = array();
								$result                    = ldap_read( $ldapconn, '', '(objectclass=*)', array( 'namingContexts' ) );
								$data                      = ldap_get_entries( $ldapconn, $result );
								$count                     = $data[0]['namingcontexts']['count'];
								$current_search_base_count = 0;
								for ( $i = 0; $i < $count; $i++ ) {
									if ( 0 === $i ) {
										$base_dn = $data[0]['namingcontexts'][ $i ];
									}
									$valuetext = $data[0]['namingcontexts'][ $i ];
									if ( strcasecmp( $valuetext, $previous_search_bases ) === 0 ) {
										echo "<div class='mo_ldap_local_search_base_div'><div><input type='radio' id='mo_ldap_local_searchbase_" . esc_attr( $i ) . "' class='select_search_bases' name='select_ldap_search_bases[]' value='" . esc_attr( $valuetext ) . "' checked><label for='mo_ldap_local_searchbase_" . esc_attr( $i ) . "'>" . esc_html( $valuetext ) . '</label></div></div>';
										array_push( $search_base_list, $data[0]['namingcontexts'][ $i ] );
									} else {
										echo "<div class='mo_ldap_local_search_base_div'><div><input type='radio' id='mo_ldap_local_searchbase_" . esc_attr( $i ) . "' class='select_search_bases' name='select_ldap_search_bases[]' value='" . esc_attr( $valuetext ) . "'><label for='mo_ldap_local_searchbase_" . esc_attr( $i ) . "'>" . esc_html( $valuetext ) . '</label></div></div>';
										array_push( $search_base_list, $data[0]['namingcontexts'][ $i ] );
									}
									$current_search_base_count++;
								}
								$filter      = '(|(objectclass=organizationalUnit)(&(objectClass=top)(cn=users)))';
								$search_attr = array( 'dn', 'ou' );
								$ldapsearch  = ldap_search( $ldapconn, $base_dn, $filter, $search_attr );
								$info        = ldap_get_entries( $ldapconn, $ldapsearch );
								for ( $i = 0; $i < $info['count']; $i++ ) {
									$textvalue = $info[ $i ]['dn'];
									if ( ( strcasecmp( $textvalue, $previous_search_bases ) ) === 0 ) {
										echo "<div class='mo_ldap_local_search_base_div'><div><input type='radio' id='mo_ldap_local_searchbase_" . esc_attr( $current_search_base_count ) . "' class='select_search_bases' name='select_ldap_search_bases[]' value='" . esc_attr( $textvalue ) . "' checked><label for='mo_ldap_local_searchbase_" . esc_attr( $current_search_base_count ) . "'>" . esc_html( $textvalue ) . '</label></div></div>';
										array_push( $search_base_list, $info[ $i ]['dn'] );
									} else {
										echo "<div class='mo_ldap_local_search_base_div'><div><input type='radio' id='mo_ldap_local_searchbase_" . esc_attr( $current_search_base_count ) . "' class='select_search_bases' name='select_ldap_search_bases[]' value='" . esc_attr( $textvalue ) . "'><label for='mo_ldap_local_searchbase_" . esc_attr( $current_search_base_count ) . "'>" . esc_html( $textvalue ) . '</label></div></div>';
										array_push( $search_base_list, $info[ $i ]['dn'] );
									}
									$current_search_base_count++;
								}
								?>
							</div>
							<div class="mo_ldap_local_multiple_search_base_premium_box">
								<div class="mo_ldap_local_multiple_search_base_premium_box_inner">
									<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="31px" width="25px">
									<p style="font-size: 15px; margin-left: 12px;">Multiple search bases are supported in the premium version of the plugin.</p>
								</div>
							</div>
							<div style="text-align: center;">
								<input class="mo_ldap_local_submit_button" id="submitbase" type="submit" value="Submit" name="submitbase">
								<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
							</div>
							</form>
						</div>
						<?php
					} else {
						?>
						<div class="mo_ldap_local_show_search_base_container">
							<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
								<span>No Search Base(s) Found</span>
								<div class="mo_ldap_local_not_found_div">
									<span>Please check :</span>
									<ul>
										<li>If your LDAP server configuration (LDAP server url, Username & Password) is correct.</li>
										<li>If you have successfully saved your LDAP Connection Information.</li>
									</ul>
								</div>
							</div>
							<div style="text-align: center;">
								<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
							</div>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				} else {
					?>
					<div class="mo_ldap_local_show_search_base_container">
						<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
							<span>No Search Base(s) Found</span>
							<div class="mo_ldap_local_not_found_div">
								<span>Please check :</span>
								<ul>
									<li>If your LDAP server configuration (LDAP server url, Username & Password) is correct.</li>
									<li>If you have successfully saved your LDAP Connection Information.</li>
								</ul>
							</div>
						</div>
						<div style="text-align: center;">
							<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
						</div>
					</div>
					<?php
				}
				?>
				</div>
				<?php
			} else {
				?>
				<div class="mo_ldap_local_show_search_base_container">
					<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
						<span>No Search Base(s) Found</span>
						<div class="mo_ldap_local_not_found_div">
							<span>Please check :</span>
							<ul>
								<li><span><a target="_blank" rel="noopener" href="http://php.net/manual/en/ldap.installation.php">PHP LDAP extension</a> is not installed or is disabled. Please enable it.</span></li>
							</ul>
						</div>
					</div>
					<div style="text-align: center;">
						<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
					</div>
				</div>
				<?php
			}
			if ( isset( $_POST['submitbase'] ) && ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'searchbaselist_nonce' ) ) ) {
				if ( ! empty( $_POST['select_ldap_search_bases'] ) ) {
					$search_bases = strtolower( isset( $_POST['select_ldap_search_bases'][0] ) ? sanitize_text_field( wp_unslash( $_POST['select_ldap_search_bases'][0] ) ) : '' );
					update_option( 'mo_ldap_local_search_base', $this->utils::encrypt( $search_bases ) );

					echo '<script>window.close();
               	window.onunload = function(){
               	window.opener.location.reload();
            	};
        		</script>';
				} else {
					echo '<span"><script> alert("You have not selected any Search Base.")</script></span>';
				}
			}
			exit();
		}

		/**
		 * Function test_attribute_configuration: Test attribute mapping.
		 *
		 * @param string $username Username.
		 * @return void
		 */
		public function test_attribute_configuration( $username ) {
			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return;
			}

			$username = ldap_escape( $username, '', LDAP_ESCAPE_FILTER );

			?>
				<style>
					.mo_ldap_local_attr_map_container {
						min-height: 100%;
						background: #9f9f9f;
						margin: -8px;
						font-family: 'Inter' !important;
						font-style: normal;
					}
					.mo_ldap_local_test_result {
						background-color: #565656;
						border-radius: 10px;
						padding: 15px 15px;
						width: 80%;
						font-size: 16px;
						color: #fff;
						margin-top: 10px;
						margin-right: auto;
						margin-left: auto;
					}
					.mo_ldap_local_attribute {
						min-width: 15%;
					}
					.mo_ldap_local_user_info {
						margin: 0 10% 0 10%;
						font-size: 15px;
						padding: 10px;
						gap: 10px;
						display: flex;
					}
					.mo_ldap_local_close_button {
						font-size: 14px;
						width: 25%;
						cursor: pointer;
						background: #FFFFFF;
						color: #0076E1;
						border-radius: 8px;
						padding: 12px;
						margin: 10px;
						font-weight: 600;
						border: none;
					}
				</style>
			<?php
			if ( $this->utils::is_extension_installed( 'ldap' ) ) {
				$ldap_bind_dn       = get_option( 'mo_ldap_local_server_dn' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_dn' ) ) : '';
				$ldap_bind_password = get_option( 'mo_ldap_local_server_password' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_password' ) ) : '';

				$search_base_string = get_option( 'mo_ldap_local_search_base' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_base' ) ) : '';
				$search_bases       = explode( ';', $search_base_string );
				$search_filter      = get_option( 'mo_ldap_local_search_filter' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_filter' ) ) : '';
				$search_filter      = str_replace( '?', $username, $search_filter );

				$email_attribute = strtolower( get_option( 'mo_ldap_local_email_attribute' ) );
				$attr            = array( $email_attribute );
				$ldapconn        = $this->get_connection();

				if( empty( $search_bases ) || empty( $search_filter ) ) {
					?>
					<div class="mo_ldap_local_attr_map_container">
						<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
							<span style="font-size: 1.5rem;">Attribute Mapping Test:</span>
						</div>
						<div class='mo_ldap_local_test_result'>
							<div style="justify-content: center;gap: 12px;display: flex;">
								<div style="align-self: center;">
									Status: Test Failed
								</div>
								<div>
									<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
										<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
									</svg>
								</div>
							</div>
							<hr style="width: 85%;">
							<div class="mo_ldap_local_user_info">
								<div>
									Please Check:
									<br>
									&bull; If you have successfully saved your LDAP User Mapping configuration.
								</div>
							</div>
						</div>
						<div style="text-align: center;">
							<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
						</div>
					</div>
					<?php
					exit();
				}

				if ( $ldapconn ) {

					if ( get_option( 'mo_ldap_local_use_tls' ) ) {
						ldap_start_tls( $ldapconn );
					}
					$count_search_bases = count( $search_bases );
					$bind               = @ldap_bind( $ldapconn, $ldap_bind_dn, $ldap_bind_password ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Used to silense LDAP error; hanlded them below using Mo_Ldap_Local_Auth_Response_Helper class
					if ( '' === $ldap_bind_password ) {
						$bind = false;
					}
					if ( $bind ) {

						for ( $i = 0; $i < $count_search_bases; $i++ ) {
							if ( ldap_search( $ldapconn, $search_bases[ $i ], $search_filter, $attr ) ) {
								$user_search_result = ldap_search( $ldapconn, $search_bases[ $i ], $search_filter, $attr );
								$info               = ldap_first_entry( $ldapconn, $user_search_result );
								$entry              = ldap_get_entries( $ldapconn, $user_search_result );
								if ( $info ) {
									$dn = ldap_get_dn( $ldapconn, $info );
									break;
								}
							}
						}
						if ( ! empty( $dn ) ) {
							?>
							<div class="mo_ldap_local_attr_map_container">
								<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
									<span style="font-size: 1.5rem;">Attribute Mapping Test:</span>
								</div>
								<div class='mo_ldap_local_test_result'>
									<div style="justify-content: center;gap: 12px;display: flex;">
										<div style="align-self: center;">
											Status: Test Successful
										</div>
										<div>
											<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
												<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/>
											</svg>
										</div>
									</div>
									<hr style="width: 85%;">
									<div class="mo_ldap_local_user_info">
										<div class="mo_ldap_local_attribute"><strong>User DN: </strong></div>
										<div>
											<?php
											if ( isset( $dn ) ) {
												echo esc_html( $dn );
											} else {
												echo 'User Not Found';
											}
											?>
										</div>
									</div>
									<hr style="width: 85%;">
									<div class="mo_ldap_local_user_info">
										<?php
										foreach ( $attr as $attribute ) {
											?>
											<div class="mo_ldap_local_attribute"><strong><?php echo esc_html( $attribute ); ?>: </strong></div>
											<div>
												<?php
												if ( isset( $entry[0][ $attribute ][0] ) ) {
													if ( isset( $entry[0][ $attribute ]['count'] ) ) {
														for ( $i = 0;$i < $entry[0][ $attribute ]['count'];$i++ ) {
															echo esc_attr( $entry[0][ $attribute ][ $i ] ) . '<br>';
														}
													} else {
														echo esc_attr( $entry[0][ $attribute ][0] );
													}
												} else {
													$mo_ldap_local_ldap_email_domain       = get_option( 'mo_ldap_local_email_domain' );
													$mo_ldap_local_ldap_username_attribute = strtolower( get_option( 'mo_ldap_local_username_attribute' ) );
													$custom_ldap_username_attribute        = strtolower( get_option( 'custom_ldap_username_attribute' ) );
													$username_list_array                   = array( 'samaccountname', 'uid' );

													if ( ! empty( $mo_ldap_local_ldap_email_domain ) ) {
														if ( in_array( $mo_ldap_local_ldap_username_attribute, $username_list_array, true ) || in_array( $custom_ldap_username_attribute, $username_list_array, true ) ) {
															$default_email_id = $username . '@' . $mo_ldap_local_ldap_email_domain;
															echo 'Mail attribute is not set in LDAP server.<br>As per configured default email domain <strong style="color:#60b6ff;">' . esc_html( $mo_ldap_local_ldap_email_domain ) . '</strong>, following email will be set to the user after successful login.<br><strong style="color:#60b6ff;">' . esc_html( $default_email_id ) . '</strong>';
														} else {
															echo 'Mail attribute is not set in LDAP server.';
														}
													} else {
														echo 'Mail attribute is not set in LDAP server.';
													}
												}
												?>
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<div style="text-align: center;">
									<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="mo_ldap_local_attr_map_container">
								<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
									<span style="font-size: 1.5rem;">Attribute Mapping Test:</span>
								</div>
								<div class='mo_ldap_local_test_result'>
									<div style="justify-content: center;gap: 12px;display: flex;">
										<div style="align-self: center;">
											Status: Test Failed
										</div>
										<div>
											<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
												<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
											</svg>
										</div>
									</div>
									<hr style="width: 85%;">
									<div class="mo_ldap_local_user_info">
										<strong>
										<?php
										if ( empty( $search_bases ) ) {
											echo 'ERROR: Please Check your LDAP User mapping configuration.';
										} else {
											echo 'ERROR: User is not found in LDAP server.';
										}
										?>
										</strong>
									</div>
								</div>
								<div style="text-align: center;">
									<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
								</div>
							</div>
							<?php
						}
					} else {
						?>
						<div class="mo_ldap_local_attr_map_container">
							<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
								<span style="font-size: 1.5rem;">Attribute Mapping Test:</span>
							</div>
							<div class='mo_ldap_local_test_result'>
								<div style="justify-content: center;gap: 12px;display: flex;">
									<div style="align-self: center;">
										Status: Test Failed
									</div>
									<div>
										<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
											<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
										</svg>
									</div>
								</div>
								<hr style="width: 85%;">
								<div class="mo_ldap_local_user_info">
									<div>
										Please Check:
										<br>
										&bull; If your LDAP server configuration (LDAP server url, Username & Password) is correct.
										<br>
										&bull; If you have successfully saved your LDAP Connection Information.
									</div>
								</div>
							</div>
							<div style="text-align: center;">
								<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
							</div>
						</div>
						<?php
					}
					exit();
				} else {
					$info = false;
				}
			} else {
				?>
				<div class="mo_ldap_local_attr_map_container">
					<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
						<span style="font-size: 1.5rem;">Attribute Mapping Test:</span>
					</div>
					<div class='mo_ldap_local_test_result'>
						<div style="justify-content: center;gap: 12px;display: flex;">
							<div style="align-self: center;">
								Status: Test Failed
							</div>
							<div>
								<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
									<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
								</svg>
							</div>
						</div>
						<hr style="width: 85%;">
						<div class="mo_ldap_local_user_info">
							<span><a target="_blank" style="color:#60b6ff;" rel="noopener" href="http://php.net/manual/en/ldap.installation.php">PHP LDAP extension</a> is not installed or is disabled. Please enable it.</span>
						</div>
					</div>
					<div style="text-align: center;">
						<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
					</div>
				</div>
				<?php
				exit();
			}
		}

		/**
		 * Function test_role_mapping_configuration: Test Role mapping.
		 *
		 * @param string $username Username.
		 * @return void
		 */
		public function test_role_mapping_configuration( $username ) {
			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return;
			}

			$username = ldap_escape( $username, '', LDAP_ESCAPE_FILTER );

			?>
				<style>
					.mo_ldap_local_attr_map_container {
						min-height: 100%;
						background: #9f9f9f;
						margin: -8px;
						font-family: 'Inter' !important;
						font-style: normal;
					}
					.mo_ldap_local_test_result {
						background-color: #565656;
						border-radius: 10px;
						padding: 15px 15px;
						width: 80%;
						font-size: 16px;
						color: #fff;
						margin-top: 10px;
						margin-right: auto;
						margin-left: auto;
					}
					.mo_ldap_local_attribute {
						min-width: 25%;
					}
					.mo_ldap_local_user_info {
						margin: 0 8% 0 8%;
						font-size: 15px;
						padding: 10px;
						gap: 10px;
						display: flex;
					}
					.mo_ldap_local_close_button {
						font-size: 14px;
						width: 25%;
						cursor: pointer;
						background: #FFFFFF;
						color: #0076E1;
						border-radius: 8px;
						padding: 12px;
						margin: 10px;
						font-weight: 600;
						border: none;
					}
				</style>
			<?php
			if ( $this->utils::is_extension_installed( 'ldap' ) ) {
				$ldap_bind_dn       = get_option( 'mo_ldap_local_server_dn' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_dn' ) ) : '';
				$ldap_bind_password = get_option( 'mo_ldap_local_server_password' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_password' ) ) : '';

				$search_base_string = get_option( 'mo_ldap_local_search_base' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_base' ) ) : '';
				$search_bases       = explode( ';', $search_base_string );
				$search_filter      = get_option( 'mo_ldap_local_search_filter' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_filter' ) ) : '';
				$search_filter      = str_replace( '?', $username, $search_filter );
				$default_role       = ! empty( get_option( 'mo_ldap_local_mapping_value_default' ) ) ? get_option( 'mo_ldap_local_mapping_value_default' ) : get_option( 'default_role' );

				if( empty( $search_bases ) || empty( $search_filter ) ) {
					?>
					<div class="mo_ldap_local_attr_map_container">
						<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
							<span style="font-size: 1.5rem;">Role Mapping Test:</span>
						</div>
						<div class='mo_ldap_local_test_result'>
							<div style="justify-content: center;gap: 12px;display: flex;">
								<div style="align-self: center;">
									Status: Test Failed
								</div>
								<div>
									<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
										<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
									</svg>
								</div>
							</div>
							<hr style="width: 85%;">
							<div class="mo_ldap_local_user_info">
								<div>
									Please Check:
									<br>
									&bull; If you have successfully saved your LDAP User Mapping configuration.
								</div>
							</div>
						</div>
						<div style="text-align: center;">
							<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
						</div>
					</div>
					<?php
					exit();
				}

				$ldapconn = $this->get_connection();

				if ( $ldapconn ) {

					if ( get_option( 'mo_ldap_local_use_tls' ) ) {
						ldap_start_tls( $ldapconn );
					}
					$count_search_bases = count( $search_bases );
					$bind               = @ldap_bind( $ldapconn, $ldap_bind_dn, $ldap_bind_password ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Used to silense LDAP error; hanlded them below using Mo_Ldap_Local_Auth_Response_Helper class

					if ( '' === $ldap_bind_password ) {
						$bind = false;
					}
					if ( $bind ) {

						for ( $i = 0; $i < $count_search_bases; $i++ ) {
							if ( ldap_search( $ldapconn, $search_bases[ $i ], $search_filter ) ) {
								$user_search_result = ldap_search( $ldapconn, $search_bases[ $i ], $search_filter );
								$info               = ldap_first_entry( $ldapconn, $user_search_result );
								$entry              = ldap_get_entries( $ldapconn, $user_search_result );
								if ( $info ) {
									$dn = ldap_get_dn( $ldapconn, $info );
									break;
								}
							}
						}
						if ( ! empty( $dn ) ) {
							?>
							<div class="mo_ldap_local_attr_map_container">
								<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
									<span style="font-size: 1.5rem;">Role Mapping Test:</span>
								</div>
								<div class='mo_ldap_local_test_result'>
									<div style="justify-content: center;gap: 12px;display: flex;">
										<div style="align-self: center;">
											Status: Test Successful
										</div>
										<div>
											<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
												<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/>
											</svg>
										</div>
									</div>
									<hr style="width: 85%;">
									<div class="mo_ldap_local_user_info">
										<div class="mo_ldap_local_attribute"><strong>User DN: </strong></div>
										<div>
											<?php
											if ( isset( $dn ) ) {
												echo esc_html( $dn );
											} else {
												echo 'User Not Found';
											}
											?>
										</div>
									</div>
									<hr style="width: 85%;">
									<div class="mo_ldap_local_user_info">
										<div class="mo_ldap_local_attribute"><strong>WordPress Role: </strong></div>
										<div><?php echo esc_html( $default_role ); ?></div>
									</div>
								</div>
								<div style="text-align: center;">
									<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="mo_ldap_local_attr_map_container">
								<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
									<span style="font-size: 1.5rem;">Role Mapping Test:</span>
								</div>
								<div class='mo_ldap_local_test_result'>
									<div style="justify-content: center;gap: 12px;display: flex;">
										<div style="align-self: center;">
											Status: Test Failed
										</div>
										<div>
											<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
												<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
											</svg>
										</div>
									</div>
									<hr style="width: 85%;">
									<div class="mo_ldap_local_user_info">
										<strong>
										<?php
										if ( empty( $search_bases ) ) {
											echo 'ERROR: Please Check your LDAP User mapping configuration.';
										} else {
											echo 'ERROR: User is not found in LDAP server.';
										}
										?>
										</strong>
									</div>
								</div>
								<div style="text-align: center;">
									<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
								</div>
							</div>
							<?php
						}
					} else {
						?>
						<div class="mo_ldap_local_attr_map_container">
							<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
								<span style="font-size: 1.5rem;">Role Mapping Test:</span>
							</div>
							<div class='mo_ldap_local_test_result'>
								<div style="justify-content: center;gap: 12px;display: flex;">
									<div style="align-self: center;">
										Status: Test Failed
									</div>
									<div>
										<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
											<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
										</svg>
									</div>
								</div>
								<hr style="width: 85%;">
								<div class="mo_ldap_local_user_info">
									<div>
										Please Check:
										<br>
										&bull; If your LDAP server configuration (LDAP server url, Username & Password) is correct.
										<br>
										&bull; If you have successfully saved your LDAP Connection Information.
									</div>
								</div>
							</div>
							<div style="text-align: center;">
								<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
							</div>
						</div>
						<?php
					}
					exit();
				} else {
					$info = false;
				}
			} else {
				?>
				<div class="mo_ldap_local_attr_map_container">
					<div style="text-align: center; color: #fff; padding: 18px;font-weight: 600;">
						<span style="font-size: 1.5rem;">Role Mapping Test:</span>
					</div>
					<div class='mo_ldap_local_test_result'>
						<div style="justify-content: center;gap: 12px;display: flex;">
							<div style="align-self: center;">
								Status: Test Failed
							</div>
							<div>
								<svg viewBox="0 0 512 512" height="25px" width="25px" fill="#fff">
									<path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"/>
								</svg>
							</div>
						</div>
						<hr style="width: 85%;">
						<div class="mo_ldap_local_user_info">
							<span><a target="_blank" style="color:#60b6ff;" rel="noopener" href="http://php.net/manual/en/ldap.installation.php">PHP LDAP extension</a> is not installed or is disabled. Please enable it.</span>
						</div>
					</div>
					<div style="text-align: center;">
						<input class="mo_ldap_local_close_button" type="button"  id ="searchbase" value="Close" onClick="self.close();"/>
					</div>
				</div>
				<?php
				exit();
			}
		}
	}
}
