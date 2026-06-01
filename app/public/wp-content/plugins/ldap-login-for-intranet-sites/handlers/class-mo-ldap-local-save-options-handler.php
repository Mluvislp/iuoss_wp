<?php
/**
 * Handle all the options.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage handlers
 */

namespace MO_LDAP\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __DIR__ ) . '/lib/class-mo-ldap-config-details.php';
require_once dirname( __DIR__ ) . '/lib/class-mo-ldap-account-details.php';

use MO_LDAP\Utils\Mo_Ldap_Local_Utils;

use MO_LDAP\Handlers\Mo_Ldap_Local_Customer_Setup_Handler;
use MO_LDAP\Handlers\Mo_Ldap_Local_Configuration_Handler;

if ( ! class_exists( 'Mo_Ldap_Local_Save_Options_Handler' ) ) {
	/**
	 * Save options handler class.
	 */
	class Mo_Ldap_Local_Save_Options_Handler {

		const LDAPFIELDS = 'All the fields are required. Please enter valid entries.';
		const LDAPCONN   = 'LDAP CONNECTION TEST';

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
			add_action( 'admin_init', array( $this, 'mo_ldap_local_save_options' ) );
			add_action( 'init', array( $this, 'test_attribute_configuration' ) );
			$this->utils = new Mo_Ldap_Local_Utils();
		}

		/**
		 * Function checkPasswordpattern
		 *
		 * @param  string $password : password pattern to be checked.
		 * @return string
		 */
		private static function check_password_pattern( $password ) {
			$pattern = '/^[(\w)*(\!\@\#\$\%\^\&\*\.\-\_)*]+$/';

			return ! preg_match( $pattern, $password );
		}

		/**
		 * Function test_attribute_configuration : Test LDAP attribute mapping
		 *
		 * @return void
		 */
		public function test_attribute_configuration() {
			if ( is_user_logged_in() && current_user_can( 'manage_options' ) && isset( $_REQUEST['option'] ) ) {
				if ( null !== $_REQUEST['option'] && strcasecmp( sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ), 'testattrconfig' ) === 0 && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'testattrconfig_nonce' ) ) {
					$username       = isset( $_REQUEST['user'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user'] ) ) : '';
					$mo_ldap_config = new Mo_Ldap_Local_Configuration_Handler();
					$mo_ldap_config->test_attribute_configuration( $username );
				} elseif ( strcasecmp( sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ), 'searchbaselist' ) === 0 && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'searchbaselist_nonce' ) ) {
					$mo_ldap_config = new Mo_Ldap_Local_Configuration_Handler();
					$mo_ldap_config->show_search_bases_list();
				} elseif ( null !== $_REQUEST['option'] && strcasecmp( sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ), 'testrolemapconfig' ) === 0 && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'testrolemapconfig_nonce' ) ) {
					$username       = isset( $_REQUEST['user'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user'] ) ) : '';
					$mo_ldap_config = new Mo_Ldap_Local_Configuration_Handler();
					$mo_ldap_config->test_role_mapping_configuration( $username );
				}
			}
		}

		/**
		 * Function mo_ldap_send_query : Send query to miniOrange Support Team
		 *
		 * @param  string $email : Email of the user asking for support.
		 * @param  string $phone : Phone Number of the user asking for support.
		 * @param  string $query : Query or Issues User Facing.
		 * @return void
		 */
		private function mo_ldap_send_query( $email, $phone, $query ) {
			$query = $query . '<br><br>[Current Version Installed] : ' . MO_LDAP_LOCAL_VERSION;

			if ( $this->utils::check_empty_or_null( $email ) || $this->utils::check_empty_or_null( $query ) ) {
				update_option( 'mo_ldap_local_message', 'Please submit your query along with email.' );
				$this->utils->show_error_message();
			} else {
				$contact_us = new Mo_Ldap_Local_Customer_Setup_Handler();
				$submited   = json_decode( $contact_us->submit_contact_us( $email, $phone, $query ), true );

				if ( isset( $submited['status'] ) && strcasecmp( $submited['status'], 'ERROR' ) === 0 ) {
					update_option( 'mo_ldap_local_message', 'There was an error in sending query. Please send us an email on <a rel="noopener" target="_blank" href=mailto:info@xecurify.com><strong>info@xecurify.com</strong></a>.' );
					$this->utils->show_error_message();
				} else {
					update_option( 'mo_ldap_local_message', 'Your query has been sent successfully. A miniOrange representative will soon reach out to you.<br>In case we dont get back to you, there might be email delivery failures. You can send us email on <a rel="noopener" target="_blank" href=mailto:info@xecurify.com><strong>info@xecurify.com</strong></a> in that case.' );
					$this->utils->show_success_message();
				}
			}
		}

		/**
		 * Function deactivate_error_message
		 *
		 * @return void
		 */
		private function deactivate_error_message() {
			$class       = 'error';
			$message     = get_option( 'mo_ldap_local_message' );
			$esc_allowed = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'b'      => array(),
				'h1'     => array(),
				'h2'     => array(),
				'h3'     => array(),
				'h4'     => array(),
				'h5'     => array(),
				'h6'     => array(),
				'i'      => array(),
			);
			echo "<div id='error' class='" . esc_attr( $class ) . "'> <p>" . wp_kses( $message, $esc_allowed ) . '</p></div>';
		}

		/**
		 * Function mo_ldap_clear_authentication_report : To delete all existing user authentication logs
		 *
		 * @return void
		 */
		private function mo_ldap_clear_authentication_report() {
			global $wpdb;
			$delete = $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}user_report" ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Changing a custom table.
			wp_cache_delete( 'mo_ldap_user_report_cache' );
			wp_cache_delete( 'mo_ldap_user_report_count_cache' );
			wp_cache_delete( 'wp_user_reports_pagination_cache' );
		}

		/**
		 * Function prefix_update_table
		 *
		 * @return void
		 */
		private function prefix_update_table() {
			global $prefix_my_db_version;
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE if not exists`{$wpdb->base_prefix}user_report` (
				  id int NOT NULL AUTO_INCREMENT,
				  user_name varchar(50) NOT NULL,
				  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				  ldap_status varchar(250) NOT NULL,
				  ldap_error varchar(250) ,
				  PRIMARY KEY  (id)
				) $charset_collate;";

			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			dbDelta( $sql );

			update_option( 'user_logs_table_exists', 1 );
		}

		/**
		 * Function auto_email_ldap_export : Returns plugin configuration to be sent in support email request after user consent taken
		 *
		 * @return array
		 */
		private function auto_email_ldap_export() {
			$directory_name = get_option( 'mo_ldap_local_directory_server' );
			$server_name    = get_option( 'mo_ldap_local_server_url' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_url' ) ) : '';
			$dn             = get_option( 'mo_ldap_local_server_dn' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_server_dn' ) ) : '';
			$search_base    = get_option( 'mo_ldap_local_search_base' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_base' ) ) : '';
			$search_filter  = get_option( 'mo_ldap_local_search_filter' ) ? $this->utils::decrypt( get_option( 'mo_ldap_local_search_filter' ) ) : '';
			return array(
				'LDAP Directory Name' => 'LDAP Directory Name:  ' . $directory_name,
				'LDAP Server'         => 'LDAP Server:  ' . $server_name,
				'Service Account DN'  => 'Service Account DN:  ' . $dn,
				'Search Base'         => 'Search Base:  ' . $search_base,
				'LDAP Search Filter'  => 'LDAP Search Filter:  ' . $search_filter,
			);
		}

		/**
		 * Function miniorange_ldap_authentication_report : Fetch users auth report
		 *
		 * @return void
		 */
		private function miniorange_ldap_authentication_report() {
			global $wpdb, $wp_filesystem;

			if ( ! function_exists( 'WP_Filesystem' ) || ! WP_Filesystem() ) {
				update_option( 'mo_ldap_local_message', 'Unable to initialize WP_Filesystem. This may be due to file permissions or server configuration. Check your WordPress file system settings and try again.' );
				$this->utils->show_error_message();
				return;
			}

			$wp_user_reports_cache = wp_cache_get( 'mo_ldap_user_report_cache' );
			if ( $wp_user_reports_cache ) {
				$user_reports = $wp_user_reports_cache;
			} else {
				$user_reports = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_report" ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Fetching data from a custom table.
				wp_cache_set( 'mo_ldap_user_report_cache', $user_reports );
			}

			$temp_file = wp_tempnam();
			if ( ! $temp_file ) {
				update_option( 'mo_ldap_local_message', 'Unable to create a temporary file for the report.' );
				$this->utils->show_error_message();
				return;
			}

			$csv_content = '';

			if ( ! empty( $user_reports ) ) {
				$fields       = array( 'ID', 'USERNAME', 'TIME', 'LDAP STATUS', 'LDAP ERROR' );
				$csv_content .= implode( ',', $fields ) . "\n";

				foreach ( $user_reports as $user_report ) {
					$line_data    = array(
						$user_report->id,
						$user_report->user_name,
						$user_report->time,
						$user_report->ldap_status,
						sanitize_text_field( $user_report->ldap_error ),
					);
					$csv_content .= implode( ',', $line_data ) . "\n";
				}
			} else {
				update_option( 'mo_ldap_local_message', 'No Logs Available' );
				$this->utils->show_error_message();
				return;
			}

			if ( ! $wp_filesystem->put_contents( $temp_file, $csv_content, FS_CHMOD_FILE ) ) {
				update_option( 'mo_ldap_local_message', 'Failed to write the report to the file.' );
				$this->utils->show_error_message();
				return;
			}

			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename=ldap-authentication-report.csv' );

			echo esc_html( $wp_filesystem->get_contents( $temp_file ) );

			$wp_filesystem->delete( $temp_file );

			exit;
		}

		/**
		 * Function mo_ldap_export_configuration : Export all configurations to JSON file
		 *
		 * @return void
		 */
		private function mo_ldap_export_configuration() {
			$tab_class_name = maybe_unserialize( TAB_LDAP_CLASS_NAMES );

			$configuration_array = array();
			foreach ( $tab_class_name as $key => $value ) {
				$configuration_array[ $key ] = $this->mo_get_configuration_array( $value );
			}

			header( 'Content-Disposition: attachment; filename=miniorange-ldap-config.json' );
			echo wp_json_encode( $configuration_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			exit;
		}

		/**
		 * Function mo_ldap_import_configuration : Import all configurations from JSON file
		 *
		 * @return void
		 */
		private function mo_ldap_import_configuration() {
			if ( ! check_admin_referer( 'mo_ldap_import' ) ) {
				return;
			}

			$file_name = isset( $_FILES['mo_ldap_import_file']['name'] ) ? $_FILES['mo_ldap_import_file']['name'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- no need to sanitize the file.
			$file_tmp  = isset( $_FILES['mo_ldap_import_file']['tmp_name'] ) ? $_FILES['mo_ldap_import_file']['tmp_name'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- no need to sanitize the file.	
			$file_ext  = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
			if ( 'json' === $file_ext ) {
				$file_json_content = '';
				if ( ! function_exists( 'WP_Filesystem' ) || ! WP_Filesystem() ) {
					update_option( 'mo_ldap_local_message', 'Unable to initialize WP_Filesystem. This may be due to file permissions or server configuration. Check your WordPress file system settings and try again.' );
					$this->utils->show_error_message();
					return;
				}
				global $wp_filesystem;

				if ( $wp_filesystem->is_readable( $file_tmp ) ) {
					$file_json_content = $wp_filesystem->get_contents( $file_tmp );

					if ( false === $file_json_content ) {
						update_option( 'mo_ldap_local_message', 'Error reading the uploaded file. Please try again.' );
						$this->utils->show_error_message();
						return;
					}
				} else {
					update_option( 'mo_ldap_local_message', 'The uploaded file is not readable.' );
					$this->utils->show_error_message();
					return;
				}

				$configuration_array = json_decode( $file_json_content, true );
				if ( array_key_exists( 'ldap_Login', $configuration_array ) || array_key_exists( 'ldap_config', $configuration_array ) ) {

					$class_object_options = call_user_func( 'MO_LDAP_Config_Details::get_constants' );
					foreach ( $class_object_options as $key => $option ) {
						if ( 'mo_ldap_local_enable_login' !== $option && 'mo_ldap_local_register_user' !== $option && 'mo_ldap_local_enable_role_mapping' !== $option && 'mo_ldap_local_keep_existing_user_roles' !== $option ) {
							delete_option( $option );
						}
					}

					update_option( 'mo_ldap_local_enable_login', 0 );
					update_option( 'mo_ldap_local_register_user', 0 );
					update_option( 'mo_ldap_local_enable_role_mapping', 0 );
					update_option( 'mo_ldap_local_keep_existing_user_roles', 0 );
					$tab_class_name = maybe_unserialize( TAB_LDAP_CLASS_NAMES );

					$plugin_version = '1.0.0';
					if ( isset( $configuration_array ) && isset( $configuration_array['ldap_config'] ) && isset( $configuration_array['ldap_config']['plugin_version'] ) ) {
						$plugin_version = $configuration_array['ldap_config']['plugin_version'];
					}
					if ( isset( $configuration_array ) && isset( $configuration_array['ldap_Login'] ) ) {
						$customer_token          = $this->utils->generate_random_string( 15 );
						$existing_customer_token = get_option( 'mo_ldap_local_customer_token' );

						if ( isset( $configuration_array['ldap_Login']['customer_token'] ) && ! empty( $configuration_array['ldap_Login']['customer_token'] ) ) {
							$customer_token = $configuration_array['ldap_Login']['customer_token'];
						} elseif ( ! empty( $existing_customer_token ) ) {
							$customer_token = $existing_customer_token;
						}
						update_option( 'mo_ldap_local_customer_token', $customer_token );
					}

					foreach ( $configuration_array as $class_key => $class_array ) {
						$class_object = call_user_func( $tab_class_name[ $class_key ] . '::get_constants' );
						foreach ( $class_object as $key => $option ) {
							$key = strtolower( $key );
							if ( array_key_exists( $key, $class_array ) ) {
								$value = ! empty( sanitize_text_field( $class_array[ $key ] ) ) ? sanitize_text_field( $class_array[ $key ] ) : $class_array[ $key ];
								if ( 'mo_ldap_local_server_url' === $option || 'mo_ldap_local_ldap_server_address' === $option || 'mo_ldap_local_server_dn' === $option || 'mo_ldap_local_search_base' === $option || 'mo_ldap_local_search_filter' === $option || 'mo_ldap_local_username_attribute' === $option ) {
									if ( 'mo_ldap_local_username_attribute' === $option ) {
										$predefine_usr_attr = array( 'samaccountname', 'mail', 'userprincipalname', 'uid', 'cn' );
										$extra_usr_attr     = '';
										if ( in_array( strtolower( $value ), $predefine_usr_attr ) ) {
											$value = strtolower( $value );
										} else {
											$extra_usr_attr = strtolower( $value );
										}
										if ( '' !== $extra_usr_attr ) {
											update_option( 'custom_ldap_username_attribute', $value );
											$value = 'custom_ldap_attribute';
										}
									} elseif ( 'mo_ldap_local_server_url' === $option && version_compare( $plugin_version, '5.2.0', '<' ) ) {
										$pattern = '/(\w+):\/\/([\d.]+):(\d+)/';
										if ( preg_match( $pattern, $value, $matches ) ) {
											$protocol   = $matches[1];
											$ip_address = $matches[2];
											$port       = $matches[3];
											update_option( 'mo_ldap_local_ldap_protocol', $protocol );
											update_option( 'mo_ldap_local_ldap_port_number', $port );
											update_option( 'mo_ldap_local_ldap_server_address', $this->utils::encrypt( $ip_address ) );
											$value = $this->utils::encrypt( $value );
										}
									} else {
										$value = $this->utils::encrypt( $value );
									}
								} elseif ( 'mo_ldap_local_directory_server' === $option ) {
									if ( 'other' === $value ) {
										update_option( 'mo_ldap_directory_server_value', 'other' );
									}
								} elseif ( 'mo_ldap_local_admin_customer_key' === $option || 'mo_ldap_local_admin_api_key' === $option ) {
									continue;
								}
								update_option( $option, $value );
							}
						}
					}
					$ldap_dn_password = get_option( 'mo_ldap_local_server_password' );
					if ( $this->utils->decrypt( $ldap_dn_password ) === false || $this->utils->decrypt( $ldap_dn_password ) === '' ) {
						delete_option( 'mo_ldap_local_server_password' );
					}

					$ldap_username_attribute = ! empty( get_option( 'mo_ldap_local_username_attribute' ) ) ? get_option( 'mo_ldap_local_username_attribute' ) : 'samaccountname';
					if ( 'custom_ldap_attribute' === $ldap_username_attribute ) {
						$ldap_username_attribute = get_option( 'custom_ldap_username_attribute' );
					}

					$directory_server_value = get_option( 'mo_ldap_directory_server_value' );
					if ( strcmp( $directory_server_value, 'freeipa' ) === 0 ) {
						$ldap_search_filter = '(&(' . $ldap_username_attribute . '=?)(|(objectClass=person)(objectClass=user)(objectClass=posixAccount)))';
					} else {
						$ldap_search_filter = '(&(' . $ldap_username_attribute . '=?)(|(objectClass=person)(objectClass=user)))';
					}
					update_option( 'mo_ldap_local_search_filter', $this->utils->encrypt( $ldap_search_filter ) );

					$mo_ldap_config = new Mo_Ldap_Local_Configuration_Handler();
					$content        = $mo_ldap_config->test_connection();
					$response       = json_decode( $content, true );

					if ( isset( $response['statusCode'] ) && strcasecmp( $response['statusCode'], 'BIND_SUCCESS' ) === 0 ) {
						$search_base             = get_option( 'mo_ldap_local_search_base' );
						$ldap_username_attribute = get_option( 'mo_ldap_local_username_attribute' );
						if ( ! empty( $search_base ) && ! empty( $ldap_username_attribute ) ) {
							update_option( 'mo_ldap_local_user_mapping_status', 'VALID', '', 'no' );
						}

						update_option( 'mo_ldap_local_save_config_status', 'VALID', '', 'no' );
					}

					update_option( 'mo_ldap_local_message', 'The file was imported successfully' );
					$this->utils->show_success_message();
				} else {
					update_option( 'mo_ldap_local_message', 'Incorrect file uploaded! Please upload the file that was exported from this plugin' );
					$this->utils->show_error_message();
				}
			} else {
				update_option( 'mo_ldap_local_message', 'Incorrect file uploaded! Please upload the file that was exported from this plugin' );
				$this->utils->show_error_message();
			}
		}

		/**
		 * Function mo_get_configuration_array
		 *
		 * @param  mixed $class_name : Sub Class required for config export.
		 * @return array
		 */
		private function mo_get_configuration_array( $class_name ) {
			$class_object = call_user_func( $class_name . '::get_constants' );
			$mo_array     = array();
			foreach ( $class_object as $key => $value ) {
				$key = strtolower( $key );
				if ( strcasecmp( $value, 'mo_ldap_local_ldap_server_address' ) === 0 || strcasecmp( $value, 'mo_ldap_local_server_url' ) === 0 || strcasecmp( $value, 'mo_ldap_local_server_dn' ) === 0 || strcasecmp( $value, 'mo_ldap_local_search_base' ) === 0 || strcasecmp( $value, 'mo_ldap_local_search_filter' ) === 0 || strcasecmp( $value, 'mo_ldap_local_Filter_Search' ) === 0 ) {
					$flag = 1;
				} else {
					$flag = 0;
				}
				$mo_option_exists = get_option( $value );

				if ( $mo_option_exists ) {
					if ( @maybe_unserialize( $mo_option_exists ) !== false ) {//phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Silencing errors to avoid error logs exported in JSON file with plugin configuration
						$mo_option_exists = maybe_unserialize( $mo_option_exists );
					}
					if ( 1 === $flag ) {
						$mo_array[ $key ] = $this->utils::decrypt( $mo_option_exists );
					} elseif ( strcasecmp( $value, 'mo_ldap_local_server_password' ) === 0 && ( empty( get_option( 'mo_ldap_export' ) ) || strcasecmp( get_option( 'mo_ldap_export' ), '0' ) === 0 ) ) {
						continue;
					} else {
						$mo_array[ $key ] = $mo_option_exists;
					}
				}
			}
			return $mo_array;
		}

		/**
		 * Function create_customer : To register customer with miniOrange
		 *
		 * @return array
		 */
		public function create_customer() {
			$customer     = new Mo_Ldap_Local_Customer_Setup_Handler();
			$customer_key = $customer->create_customer();

			$response = array();

			if ( ! empty( $customer_key ) ) {
				$customer_key = json_decode( $customer_key, true );

				if ( strcasecmp( $customer_key['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS' ) === 0 ) {
					$api_response = $this->get_current_customer();
					if ( $api_response ) {
						$response['status'] = 'SUCCESS';
					} else {
						$response['status'] = 'ERROR';
					}
				} elseif ( strcasecmp( $customer_key['status'], 'SUCCESS' ) === 0 && strpos( $customer_key['message'], 'Customer successfully registered.' ) !== false ) {
					$this->save_success_customer_config( $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], 'Thanks for registering with the miniOrange.' );
					$response['status'] = 'SUCCESS';
					return $response;
				}
				update_option( 'mo_ldap_local_password', '' );
				return $response;
			}
		}

		/**
		 * Function get_current_customer : Get current customer info.
		 *
		 * @return mixed
		 */
		public function get_current_customer() {
			$customer = new Mo_Ldap_Local_Customer_Setup_Handler();
			$content  = $customer->get_customer_key();

			$response = array();

			if ( ! empty( $content ) ) {
				$customer_key = json_decode( $content, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$this->save_success_customer_config( $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], 'Your account has been retrieved successfully.' );
					update_option( 'mo_ldap_local_password', '' );
					$response['status'] = 'SUCCESS';
				} else {
					update_option( 'mo_ldap_local_message', 'You already have an account with miniOrange. Please enter a valid password.' );
					$this->utils->show_error_message();
				}
			}
		}

		/**
		 * Function save_success_customer_config : Save customer information in DB after successful login
		 *
		 * @param  string $id : User ID.
		 * @param  string $api_key : User Unique API key.
		 * @param  string $token : User unique Token.
		 * @param  string $message : Success Message to be shown on UI.
		 * @return void
		 */
		private function save_success_customer_config( $id, $api_key, $token, $message ) {
			update_option( 'mo_ldap_local_admin_customer_key', $id );
			update_option( 'mo_ldap_local_admin_api_key', $api_key );
			update_option( 'mo_ldap_local_admin_token', $token );
			update_option( 'mo_ldap_local_password', '' );
			update_option( 'mo_ldap_local_message', $message );
			delete_option( 'mo_ldap_local_verify_customer' );
			delete_option( 'mo_ldap_local_new_registration' );
			delete_option( 'mo_ldap_local_registration_status' );
			$this->utils->show_success_message();
		}

		/**
		 * Function mo_ldap_local_save_options : Handler function for PHP forms.
		 *
		 * @return void
		 */
		public function mo_ldap_local_save_options() {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			if ( get_transient( 'mo_ldap_local_is_config_success' ) ) {
				$this->utils->show_success_message();
				delete_transient( 'mo_ldap_local_is_config_success' );
			}

			if ( ( ! empty( get_option( 'user_logs_table_exists' ) ) || strcasecmp( get_option( 'user_logs_table_exists' ), '1' ) === 0 ) && ( empty( get_option( 'mo_ldap_local_user_table_updated' ) ) || strcasecmp( get_option( 'mo_ldap_local_user_table_updated' ), 'true' ) !== 0 ) ) {
				$this->utils::update_user_auth_table_headers();
				update_option( 'mo_ldap_local_user_table_updated', 'true' );
			}

			if ( isset( $_POST['option'] ) && current_user_can( 'manage_options' ) ) {
				$post_option = sanitize_text_field( wp_unslash( $_POST['option'] ) );

				if ( strcmp( $post_option, 'mo_ldap_local_register_customer' ) === 0 && check_admin_referer( 'mo_ldap_local_register_customer' ) ) {

					$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
					if ( empty( $company ) ) {
						$company = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
					}
					$phone            = isset( $_POST['register_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['register_phone'] ) ) : '';
					$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
					$password         = isset( $_POST['password'] ) ? $_POST['password'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Should not be sanitised as Strong Passwords contains special characters
					$confirm_password = isset( $_POST['confirmPassword'] ) ? $_POST['confirmPassword'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Should not be sanitised as Strong Passwords contains special characters
					$use_case         = isset( $_POST['usecase'] ) ? sanitize_text_field( wp_unslash( $_POST['usecase'] ) ) : '';

					if ( empty( $email ) || empty( $password ) ) {
						update_option( 'mo_ldap_local_message', self::LDAPFIELDS );
						$this->utils->show_error_message();
						return;
					} elseif ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						update_option( 'mo_ldap_local_message', 'Please enter a valid email address.' );
						$this->utils->show_error_message();
						return;
					} elseif ( $this->check_password_pattern( wp_strip_all_tags( $password ) ) ) {
						update_option( 'mo_ldap_local_message', 'Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*-_) should be present.' );
						$this->utils->show_error_message();
						return;
					}

					update_option( 'mo_ldap_local_admin_company', $company );
					update_option( 'mo_ldap_local_admin_phone', $phone );
					update_option( 'mo_ldap_local_admin_email', $email );

					if ( strcmp( $password, $confirm_password ) === 0 ) {
						update_option( 'mo_ldap_local_password', $password );
						$customer = new Mo_Ldap_Local_Customer_Setup_Handler();
						$content  = $customer->check_customer();

						if ( ! empty( $content ) ) {
							$content = json_decode( $content, true );

							if ( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND' ) === 0 ) {
								$content = $this->create_customer();
								if ( is_array( $content ) && array_key_exists( 'status', $content ) && strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
									$pricing_url      = add_query_arg( array( 'tab' => 'pricing' ), $request_uri );
									$message          = 'Your account has been created successfully. <a rel="noopener" target="_blank" href="' . esc_url( $pricing_url ) . '">Click here to see our Premium Plans</a> ';
									$registered_email = get_option( 'mo_ldap_local_admin_email' );
									$query            = 'Phone Number :' . $phone . '<br><br>Query: A new LDAP customer has been registered with miniOrange. <br><br>Use Case: ' . $use_case;
									$subject          = 'WordPress LDAP Customer Registered - ' . $registered_email;
									$customer->send_email_alert( $subject, $registered_email, $query, $company );
									update_option( 'mo_ldap_local_message', $message );
									$this->utils->show_success_message();
									return;
								}
							} else {
								$response = $this->get_current_customer();
								if ( is_array( $response ) && array_key_exists( 'status', $response ) && strcasecmp( $response['status'], 'SUCCESS' ) === 0 ) {
									$pricing_url = add_query_arg( array( 'tab' => 'pricing' ), $request_uri );
									$message     = 'Your account has been retrieved successfully. <a rel="noopener" target="_blank" href="' . esc_url( $pricing_url ) . '">Click here to see our Premium Plans</a> ';
									update_option( 'mo_ldap_local_message', $message );
									$this->utils->show_success_message();
									return;
								}
							}
						}
					} else {
						update_option( 'mo_ldap_local_message', 'Password and Confirm password do not match.' );
						delete_option( 'mo_ldap_local_verify_customer' );
						$this->utils->show_error_message();
						return;
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_verify_customer' ) === 0 && check_admin_referer( 'mo_ldap_local_verify_customer' ) ) {
					$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
					$password = isset( $_POST['password'] ) ? $_POST['password'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Should not be sanitised as Strong Passwords contains special characters

					if ( empty( $email ) || empty( $_POST['password'] ) ) {
						update_option( 'mo_ldap_local_message', self::LDAPFIELDS );
						$this->utils->show_error_message();
						return;
					}

					update_option( 'mo_ldap_local_admin_email', $email );
					update_option( 'mo_ldap_local_password', $password );

					$customer = new Mo_Ldap_Local_Customer_Setup_Handler();
					$content  = $customer->get_customer_key();

					if ( ! is_null( $content ) ) {
						$customer_key = json_decode( $content, true );
						if ( json_last_error() === JSON_ERROR_NONE ) {
							$this->save_success_customer_config( $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], 'Your account has been retrieved successfully.' );
						} else {
							$message = 'Invalid username or password. Please try again.';
							update_option( 'mo_ldap_local_message', $message );
							$this->utils->show_error_message();
						}
						update_option( 'mo_ldap_local_password', '' );
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_enable' ) === 0 && check_admin_referer( 'mo_ldap_local_enable' ) ) {
					$enable_ldap_login = ( isset( $_POST['enable_ldap_login'] ) && strcmp( sanitize_text_field( wp_unslash( $_POST['enable_ldap_login'] ) ), 1 ) === 0 ) ? 1 : 0;

					update_option( 'mo_ldap_local_enable_login', $enable_ldap_login );
					update_option( 'mo_ldap_local_enable_admin_wp_login', $enable_ldap_login );

					if ( get_option( 'mo_ldap_local_enable_login' ) ) {
						update_option( 'mo_ldap_local_message', 'Login through your LDAP credentials has been enabled. To verify the LDAP configuration, you can <a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">Logout</a> from WordPress and login again with your LDAP credentials.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_local_message', 'Login through your LDAP credentials has been disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( strcmp( $post_option, 'user_report_logs' ) === 0 && check_admin_referer( 'user_report_logs' ) ) {

					$enable_user_report_logs = ( isset( $_POST['mo_ldap_local_user_report_log'] ) && strcmp( sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_user_report_log'] ) ), 1 ) === 0 ) ? 1 : 0;

					update_option( 'mo_ldap_local_user_report_log', $enable_user_report_logs );
					$user_logs_table_exists = get_option( 'user_logs_table_exists' );
					$user_reporting         = get_option( 'mo_ldap_local_user_report_log' );
					if ( strcasecmp( $user_reporting, '1' ) === 0 && strcasecmp( $user_logs_table_exists, '1' ) !== 0 ) {
						$this->prefix_update_table();
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_register_user' ) === 0 && check_admin_referer( 'mo_ldap_local_register_user' ) ) {

					$enable_user_auto_register = ( isset( $_POST['mo_ldap_local_register_user'] ) && strcmp( sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_register_user'] ) ), 1 ) === 0 ) ? 1 : 0;

					update_option( 'mo_ldap_local_register_user', $enable_user_auto_register );
					if ( get_option( 'mo_ldap_local_register_user' ) ) {
						update_option( 'mo_ldap_local_message', 'Auto Registering users has been enabled.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_local_message', 'Auto Registering users has been disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_save_config' ) === 0 && check_admin_referer( 'mo_ldap_local_save_config' ) ) {
					$server_name         = '';
					$dn                  = '';
					$admin_ldap_password = '';
					if ( empty( $_POST['ldap_server'] ) || empty( $_POST['dn'] ) || empty( $_POST['admin_password'] ) || empty( $_POST['mo_ldap_protocol'] ) || empty( $_POST['mo_ldap_server_port_no'] ) ) {
						update_option( 'mo_ldap_local_message', self::LDAPFIELDS );
						$this->utils->show_error_message();
						return;
					} else {
						$ldap_protocol       = isset( $_POST['mo_ldap_protocol'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_protocol'] ) ) : '';
						$port_number         = isset( $_POST['mo_ldap_server_port_no'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_server_port_no'] ) ) : '';
						$server_address      = isset( $_POST['ldap_server'] ) ? sanitize_text_field( wp_unslash( $_POST['ldap_server'] ) ) : '';
						$server_name         = $ldap_protocol . '://' . $server_address . ':' . $port_number;
						$dn                  = isset( $_POST['dn'] ) ? sanitize_text_field( wp_unslash( $_POST['dn'] ) ) : '';
						$admin_ldap_password = isset( $_POST['admin_password'] ) ? $_POST['admin_password'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Should not be sanitised as Strong Passwords contains special characters
					}

					if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
						update_option( 'mo_ldap_local_message', 'PHP openssl extension is not installed or disabled. Please enable it first.' );
						$this->utils->show_error_message();
					} else {
						$directory_server_value = isset( $_POST['mo_ldap_directory_server_value'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_directory_server_value'] ) ) : '';
						if ( strcasecmp( $directory_server_value, 'other' ) === 0 ) {
							$directory_server_custom_value = isset( $_POST['mo_ldap_directory_server_custom_value'] ) && ! empty( $_POST['mo_ldap_directory_server_custom_value'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_directory_server_custom_value'] ) ) : 'other';
							update_option( 'mo_ldap_directory_server_custom_value', $directory_server_custom_value );
						}
						update_option( 'mo_ldap_directory_server_value', $directory_server_value );

						if ( strcasecmp( $directory_server_value, 'msad' ) === 0 ) {
							$directory_server = 'Microsoft Active Directory';
						} elseif ( strcasecmp( $directory_server_value, 'openldap' ) === 0 ) {
										$directory_server = 'OpenLDAP';
						} elseif ( strcasecmp( $directory_server_value, 'freeipa' ) === 0 ) {
													$directory_server = 'FreeIPA';
						} elseif ( strcasecmp( $directory_server_value, 'jumpcloud' ) === 0 ) {
							$directory_server = 'JumpCloud';
						} elseif ( strcasecmp( $directory_server_value, 'other' ) === 0 ) {
							$directory_server = get_option( 'mo_ldap_directory_server_custom_value' );
						} else {
							$directory_server = 'Not Configured';
						}

						update_option( 'mo_ldap_local_directory_server', $directory_server );
						update_option( 'mo_ldap_local_ldap_protocol', $ldap_protocol );
						update_option( 'mo_ldap_local_ldap_server_address', $this->utils::encrypt( $server_address ) );
						if ( strcmp( $ldap_protocol, 'ldap' ) === 0 ) {
							update_option( 'mo_ldap_local_ldap_port_number', $port_number );
						} elseif ( strcmp( $ldap_protocol, 'ldaps' ) === 0 ) {
							update_option( 'mo_ldap_local_ldaps_port_number', $port_number );
						}

						update_option( 'mo_ldap_local_server_url', $this->utils::encrypt( $server_name ) );
						update_option( 'mo_ldap_local_server_dn', $this->utils::encrypt( $dn ) );
						update_option( 'mo_ldap_local_server_password', $this->utils::encrypt( $admin_ldap_password ) );

						delete_option( 'mo_ldap_local_message' );
						$mo_ldap_config = new Mo_Ldap_Local_Configuration_Handler();

						$content  = $mo_ldap_config->test_connection();
						$response = json_decode( $content, true );
						if ( isset( $response['statusCode'] ) && strcasecmp( $response['statusCode'], 'BIND_SUCCESS' ) === 0 ) {
							add_option( 'mo_ldap_local_save_config_status', 'VALID', '', 'no' );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							set_transient( 'mo_ldap_local_is_config_success', __( 'Configuration Successfull.', 'ldap-login-for-intranet-sites' ), 30 );
							wp_redirect( admin_url( 'admin.php?page=mo_ldap_local_login&step=2' ) );
						} elseif ( isset( $response['statusCode'] ) && strcasecmp( $response['statusCode'], 'BIND_ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( self::LDAPCONN, 'ERROR', '<strong>Test Connection Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( isset( $response['statusCode'] ) && strcasecmp( $response['statusCode'], 'PING_ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( self::LDAPCONN, 'ERROR', '<strong>Test Connection Error: </strong>Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address.' );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( isset( $response['statusCode'] ) && strcasecmp( $response['statusCode'], 'LDAP_ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( self::LDAPCONN, 'ERROR', '<strong>Test Connection Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( isset( $response['statusCode'] ) && strcasecmp( $response['statusCode'], 'OPENSSL_ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( self::LDAPCONN, 'ERROR', '<strong>Test Connection Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( isset( $response['statusCode'] ) && strcasecmp( $response['statusCode'], 'ERROR' ) === 0 ) {
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->mo_ldap_report_update( self::LDAPCONN, 'Error', '<strong>Test Connection Error: </strong>' . $response['statusMessage'] );
							$this->utils->show_error_message();
						}
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_save_user_mapping' ) === 0 && check_admin_referer( 'mo_ldap_local_save_user_mapping' ) ) {

					if ( ! $this->utils::is_extension_installed( 'ldap' ) ) {
						update_option( 'mo_ldap_local_message', "<a target='_blank' rel='noopener' href='http://php.net/manual/en/ldap.installation.php'>PHP LDAP extension</a> is not installed or disabled. Please enable it." );
						$this->utils->show_error_message();
						return;
					}
					delete_option( 'mo_ldap_local_user_mapping_status' );

					$server_url = ( get_option( 'mo_ldap_local_server_url' ) ? get_option( 'mo_ldap_local_server_url' ) : '' );
					if ( empty( $server_url ) ) {
						$message = 'Please save LDAP Connection Information before saving User Mapping Configuration.';
						update_option( 'mo_ldap_local_message', $message );
						$this->utils->show_error_message();
						return;
					}
					$search_base = isset( $_POST['search_base'] ) ? sanitize_text_field( wp_unslash( $_POST['search_base'] ) ) : '';

					if ( empty( $search_base ) ) {
						update_option( 'mo_ldap_local_message', self::LDAPFIELDS );
						add_option( 'mo_ldap_local_user_mapping_status', 'INVALID', '', 'no' );
						$this->utils->show_error_message();
						return;
					} elseif ( strpos( $search_base, ';' ) ) {
							$message = 'You have entered multiple search bases. Multiple Search Bases are supported in the <strong>Premium version</strong> of the plugin. <a rel="noopener" target="_blank" href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank">Click here to upgrade</a>.';
							update_option( 'mo_ldap_local_message', $message );
							$this->utils->show_error_message();
							return;
					}

					if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
						update_option( 'mo_ldap_local_message', 'PHP OpenSSL extension is not installed or disabled. Please enable it first.' );
						add_option( 'mo_ldap_local_user_mapping_status', 'INVALID', '', 'no' );
						$this->utils->show_error_message();
					} else {
						$ldap_username_attribute        = isset( $_POST['ldap_username_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['ldap_username_attribute'] ) ) : '';
						$custom_ldap_username_attribute = isset( $_POST['custom_ldap_username_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_ldap_username_attribute'] ) ) : '';
						$directory_server_value = get_option( 'mo_ldap_directory_server_value' );

						if ( ! $this->utils::check_empty_or_null( $ldap_username_attribute ) ) {
							update_option( 'mo_ldap_local_username_attribute', $ldap_username_attribute );
							if ( strcasecmp( $ldap_username_attribute, 'custom_ldap_attribute' ) === 0 ) {
								update_option( 'custom_ldap_username_attribute', $custom_ldap_username_attribute );
								if ( $this->utils::check_empty_or_null( $custom_ldap_username_attribute ) ) {
									if ( strcmp( $directory_server_value, 'openldap' ) === 0 || strcmp( $directory_server_value, 'freeipa' ) === 0 ) {
										$ldap_username_attribute = 'uid';
									} else {
										$ldap_username_attribute = 'samaccountname';
									}
								} else {
									$multiple_username_attributes = explode( ';', $custom_ldap_username_attribute );
									if ( count( $multiple_username_attributes ) > 1 ) {
										$message = 'You have entered multiple attributes for "Username Attribute" field. Logging in with multiple attributes are supported in the <strong>Premium version</strong> of the plugin. <a href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank" rel="noopener">Click here to upgrade</a> ';
										update_option( 'mo_ldap_local_message', $message );
										$this->utils->show_error_message();
										return;
									} else {
										$ldap_username_attribute = $custom_ldap_username_attribute;
									}
								}
							}
							if ( strcmp( $directory_server_value, 'freeipa' ) === 0 ) {
								$generated_search_filter = '(&(' . $ldap_username_attribute . '=?)(|(objectClass=person)(objectClass=user)(objectClass=posixAccount)))';
							} else {
								$generated_search_filter = '(&(' . $ldap_username_attribute . '=?)(|(objectClass=person)(objectClass=user)))';
							}
							update_option( 'Filter_search', $ldap_username_attribute );
							update_option( 'mo_ldap_local_search_filter', $this->utils::encrypt( $generated_search_filter ) );
						}

						if ( strcasecmp( $ldap_username_attribute, 'custom_ldap_attribute' ) !== 0 ) {
							update_option( 'custom_ldap_username_attribute', $ldap_username_attribute );
						}
						update_option( 'mo_ldap_local_search_base', $this->utils::encrypt( $search_base ) );
						delete_option( 'mo_ldap_local_message' );
						$message = 'LDAP User Mapping Configuration has been saved. Please proceed for Test Authentication to verify LDAP user authentication.';
						add_option( 'mo_ldap_local_message', $message, '', 'no' );
						add_option( 'mo_ldap_local_user_mapping_status', 'VALID', '', 'no' );
						update_option( 'import_flag', 1 );
						set_transient( 'mo_ldap_local_is_config_success', __( 'Configuration Successfull.', 'ldap-login-for-intranet-sites' ), 30 );
						wp_redirect( admin_url( 'admin.php?page=mo_ldap_local_login&step=3' ) );
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_save_attribute_config' ) === 0 && check_admin_referer( 'mo_ldap_save_attribute_config' ) ) {
					$email_attribute         = isset( $_POST['mo_ldap_email_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_email_attribute'] ) ) : '';
					$email_domain            = isset( $_POST['mo_ldap_email_domain'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_email_domain'] ) ) : '';
					$domain_validation_regex = '/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/';
					if ( ! preg_match( $domain_validation_regex, $email_domain ) && ! empty( $email_domain ) ) {
						update_option( 'mo_ldap_local_message', 'Please enter the domain name in valid format' );
						$this->utils->show_error_message();
					} else {
						update_option( 'mo_ldap_local_email_attribute', $email_attribute );
						update_option( 'mo_ldap_local_email_domain', $email_domain );
						update_option( 'mo_ldap_local_message', 'Successfully saved LDAP Attribute Configuration' );
						$this->utils->show_success_message();
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_enable_default_wp_role_mapping' ) === 0 && check_admin_referer( 'mo_ldap_local_enable_default_wp_role_mapping' ) ) {
					if ( isset( $_POST['mapping_value_default'] ) ) {
						update_option( 'mo_ldap_local_mapping_value_default', sanitize_text_field( wp_unslash( $_POST['mapping_value_default'] ) ) );
					}

					if ( ! get_option( 'mo_ldap_local_enable_role_mapping' ) ) {
						update_option( 'mo_ldap_local_message', 'Your default WordPress role has been saved. Please activate Enable Role Mapping to proceed further.' );
						$this->utils->show_success_message();
					} elseif ( ! get_option( 'mo_ldap_local_keep_existing_user_roles' ) ) {
								update_option( 'mo_ldap_local_message', 'Your role mapping configuration has been saved. Existing roles will be replaced with the selected default role.' );
								$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_local_message', 'Your role mapping configuration has been saved. New role will be added to the existing ones.' );
						$this->utils->show_success_message();
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_enable_role_mapping' ) === 0 && check_admin_referer( 'mo_ldap_local_enable_role_mapping' ) ) {
					$enable_role_mapping = ( isset( $_POST['enable_ldap_role_mapping'] ) && strcmp( sanitize_text_field( wp_unslash( $_POST['enable_ldap_role_mapping'] ) ), '1' ) === 0 ) ? 1 : 0;
					update_option( 'mo_ldap_local_enable_role_mapping', $enable_role_mapping );
					if ( ! get_option( 'mo_ldap_local_enable_role_mapping' ) ) {
						update_option( 'mo_ldap_local_message', 'Your default WordPress role has been saved. Please activate Enable Role Mapping to proceed further.' );
						$this->utils->show_success_message();
					} elseif ( ! get_option( 'mo_ldap_local_keep_existing_user_roles' ) ) {
						update_option( 'mo_ldap_local_message', 'Your role mapping configuration has been saved. Existing roles will be replaced with the selected default role.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_local_message', 'Your role mapping configuration has been saved. New role will be added to the existing ones.' );
						$this->utils->show_success_message();
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_keep_existing_user_role_mapping' ) === 0 && check_admin_referer( 'mo_ldap_local_keep_existing_user_role_mapping' ) ) {
					$keep_existing_roles = isset( $_POST['keep_existing_user_roles'] ) ? sanitize_text_field( wp_unslash( $_POST['keep_existing_user_roles'] ) ) : 0;
					update_option( 'mo_ldap_local_keep_existing_user_roles', $keep_existing_roles );
					if ( ! get_option( 'mo_ldap_local_enable_role_mapping' ) ) {
						update_option( 'mo_ldap_local_message', 'Your default WordPress role has been saved. Please activate Enable Role Mapping to proceed further.' );
						$this->utils->show_success_message();
					} elseif ( ! get_option( 'mo_ldap_local_keep_existing_user_roles' ) ) {
						update_option( 'mo_ldap_local_message', 'Your role mapping configuration has been saved. Existing roles will be replaced with the selected default role.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_local_message', 'Your role mapping configuration has been saved. New role will be added to the existing ones.' );
						$this->utils->show_success_message();
					}
				} elseif ( strcmp( $post_option, 'mo_ldap_local_test_auth' ) === 0 && check_admin_referer( 'mo_ldap_local_test_auth' ) ) {
					if ( ! $this->utils::is_extension_installed( 'ldap' ) ) {
						update_option( 'mo_ldap_local_message', "<a target='_blank' rel='noopener' href='http://php.net/manual/en/ldap.installation.php'>PHP LDAP extension</a> is not installed or disabled. Please enable it." );
						$this->utils->show_error_message();
						return;
					}

					$server_name         = ! empty( get_option( 'mo_ldap_local_server_url' ) ) ? get_option( 'mo_ldap_local_server_url' ) : '';
					$dn                  = ! empty( get_option( 'mo_ldap_local_server_dn' ) ) ? get_option( 'mo_ldap_local_server_dn' ) : '';
					$admin_ldap_password = ! empty( get_option( 'mo_ldap_local_server_password' ) ) ? get_option( 'mo_ldap_local_server_password' ) : '';
					$search_base         = ! empty( get_option( 'mo_ldap_local_search_base' ) ) ? get_option( 'mo_ldap_local_search_base' ) : '';
					$search_filter       = ! empty( get_option( 'mo_ldap_local_search_filter' ) ) ? get_option( 'mo_ldap_local_search_filter' ) : '';

					$test_username = isset( $_POST['test_username'] ) ? sanitize_text_field( wp_unslash( $_POST['test_username'] ) ) : '';
					$test_password = isset( $_POST['test_password'] ) ? $_POST['test_password'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Should not be sanitised as Strong Passwords contains special characters

					delete_option( 'mo_ldap_local_message' );

					if ( empty( $test_username ) || empty( $test_password ) ) {
						$this->utils->mo_ldap_report_update( 'Test Authentication ', 'ERROR', '<strong>ERROR</strong>: All the fields are required. Please enter valid entries.' );
						add_option( 'mo_ldap_local_message', self::LDAPFIELDS );
						$this->utils->show_error_message();
						return;
					} elseif ( empty( $server_name ) || empty( $dn ) || empty( $admin_ldap_password ) || empty( $search_base ) || empty( $search_filter ) ) {
						$this->utils->mo_ldap_report_update( 'Test authentication', 'ERROR', '<strong>Test Authentication Error</strong>: Please save LDAP Configuration to test authentication.' );
						add_option( 'mo_ldap_local_message', 'Please save LDAP Configuration to test authentication.', '', 'no' );
						$this->utils->show_error_message();
						return;
					}

					$mo_ldap_config = new Mo_Ldap_Local_Configuration_Handler();
					$content        = $mo_ldap_config->test_authentication( $test_username, $test_password );
					$response       = json_decode( $content, true );

					if ( isset( $response['statusCode'] ) ) {
						if ( strcasecmp( $response['statusCode'], 'LDAP_USER_BIND_SUCCESS' ) === 0 || strcasecmp( $response['statusCode'], 'LDAP_ERROR' ) === 0 ) {
							$message = 'You have successfully configured your LDAP settings.<br>
									You can set login via directory credentials by checking the Enable LDAP Login in the <strong>Login Settings Tab</strong> and then <a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">Logout</a> from WordPress and login again with your LDAP credentials.<br>';
							update_option( 'mo_ldap_local_message', $message );
							$this->utils->show_success_message();
						} elseif ( strcasecmp( $response['statusCode'], 'LDAP_USER_BIND_ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( $test_username, 'ERROR', '<strong>Test Authentication Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( strcasecmp( $response['statusCode'], 'LDAP_USER_SEARCH_ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( $test_username, 'ERROR', '<strong>Test Authentication Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( strcasecmp( $response['statusCode'], 'LDAP_USER_NOT_EXIST' ) === 0 ) {
							$respone_status_message = 'Cannot find user <b>' . $test_username . '</b> in the LDAP Server.';
							$this->utils->mo_ldap_report_update( $test_username, 'ERROR', '<strong>Test Authentication Error: </strong>' . $respone_status_message );
							update_option( 'mo_ldap_local_message', ( $response['statusMessage'] ) );
							$this->utils->show_error_message();
						} elseif ( strcasecmp( $response['statusCode'], 'ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( $test_username, 'ERROR', '<strong>Test Authentication Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( strcasecmp( $response['statusCode'], 'OPENSSL_ERROR' ) === 0 ) {
							$this->utils->mo_ldap_report_update( $test_username, 'ERROR', '<strong>Test Authentication Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						} elseif ( strcasecmp( $response['statusCode'], 'LDAP_LOCAL_SERVER_NOT_CONFIGURED' ) === 0 ) {
							$this->utils->mo_ldap_report_update( $test_username, 'ERROR', '<strong>Test Authentication Error: </strong>' . $response['statusMessage'] );
							update_option( 'mo_ldap_local_message', $response['statusMessage'] );
							$this->utils->show_error_message();
						}
					} else {
						$this->utils->mo_ldap_report_update( $test_username, 'ERROR', '<strong>Test Authentication Error: </strong> There was an error processing your request. Please verify the Search Base(s) and Username attribute. Your user should be present in the Search base defined.' );
						update_option( 'mo_ldap_local_message', 'There was an error processing your request. Please verify the Search Base(s) and Username attribute. Your user should be present in the Search base defined.' );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_pass' ) === 0 && check_admin_referer( 'mo_ldap_pass' ) ) {
					update_option( 'mo_ldap_export', isset( $_POST['enable_ldap_login'] ) ? 1 : 0 );

					if ( get_option( 'mo_ldap_export' ) ) {
						update_option( 'mo_ldap_local_message', 'Service account password will be exported in encrypted fashion' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_local_message', 'Service account password will not be exported.' );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_export' ) === 0 && check_admin_referer( 'mo_ldap_export' ) ) {
					$ldap_server_url = get_option( 'mo_ldap_local_server_url' );
					if ( ! empty( $ldap_server_url ) ) {
						$this->mo_ldap_export_configuration();
					} else {
						update_option( 'mo_ldap_local_message', 'LDAP Configuration not set. Please configure LDAP Connection settings.' );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_import' ) === 0 && check_admin_referer( 'mo_ldap_import' ) ) {
					$this->mo_ldap_import_configuration();
				} elseif ( strcasecmp( $post_option, 'mo_ldap_authentication_report' ) === 0 && check_admin_referer( 'mo_ldap_authentication_report' ) ) {

					$ldap_server_url = get_option( 'mo_ldap_local_server_url' );

					if ( ! empty( $ldap_server_url ) ) {
						$this->miniorange_ldap_authentication_report();
					} else {
						update_option( 'mo_ldap_local_message', 'LDAP Authentication report not found' );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_clear_authentication_report' ) === 0 && check_admin_referer( 'mo_ldap_clear_authentication_report' ) ) {
					$this->mo_ldap_clear_authentication_report();
				} elseif ( strcasecmp( $post_option, 'enable_config' ) === 0 && check_admin_referer( 'enable_config' ) ) {
					update_option( 'en_save_config', isset( $_POST['enable_save_config'] ) ? 1 : 0 );
					if ( get_option( 'en_save_config' ) ) {
						update_option( 'mo_ldap_local_message', 'Plugin configuration will be persisted upon uninstall.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_local_message', 'Plugin configuration will not be persisted upon uninstall' );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'reset_password' ) === 0 && check_admin_referer( 'reset_password' ) ) {
					$admin_email              = get_option( 'mo_ldap_local_admin_email' );
					$customer                 = new Mo_Ldap_Local_Customer_Setup_Handler();
					$forgot_password_response = $customer->mo_ldap_local_forgot_password( $admin_email );
					if ( ! empty( $forgot_password_response ) ) {
						$forgot_password_response = json_decode( $forgot_password_response, 'true' );
						if ( strcasecmp( $forgot_password_response->status, 'SUCCESS' ) === 0 ) {
								$message = 'You password has been reset successfully and sent to your registered email. Please check your mailbox.';
								update_option( 'mo_ldap_local_message', $message );
								$this->utils->show_success_message();
						}
					} else {
						update_option( 'mo_ldap_local_message', 'Error in request' );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_local_enable_admin_wp_login' ) === 0 && check_admin_referer( 'mo_ldap_local_enable_admin_wp_login' ) ) {
					$message = '';
					if ( ! get_option( 'mo_ldap_local_enable_login' ) ) {
						$message .= ' Kindly Enable Login Using LDAP to apply the changes';
					}
					update_option( 'mo_ldap_local_enable_admin_wp_login', ( isset( $_POST['mo_ldap_local_enable_admin_wp_login'] ) && strcmp( sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_enable_admin_wp_login'] ) ), '1' ) === 0 ) ? 1 : 0 );
					if ( get_option( 'mo_ldap_local_enable_admin_wp_login' ) ) {
						$message = 'Allow administrators to login with WordPress Credentials is enabled.' . $message;
						update_option( 'mo_ldap_local_message', $message );
						$this->utils->show_success_message();
					} else {
						$message = 'Allow administrators to login with WordPress Credentials is disabled.' . $message;
						update_option( 'mo_ldap_local_message', $message );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_local_cancel' ) === 0 && check_admin_referer( 'mo_ldap_local_cancel' ) ) {
					delete_option( 'mo_ldap_local_admin_email' );
					delete_option( 'mo_ldap_local_registration_status' );
					delete_option( 'mo_ldap_local_verify_customer' );
					delete_option( 'mo_ldap_local_email_count' );
					delete_option( 'mo_ldap_local_sms_count' );
				} elseif ( strcasecmp( $post_option, 'mo_ldap_goto_login' ) === 0 && check_admin_referer( 'mo_ldap_goto_login' ) ) {
					delete_option( 'mo_ldap_local_new_registration' );
					update_option( 'mo_ldap_local_verify_customer', 'true' );
				} elseif ( strcasecmp( $post_option, 'change_miniorange_account' ) === 0 && check_admin_referer( 'change_miniorange_account' ) ) {
					delete_option( 'mo_ldap_local_admin_customer_key' );
					delete_option( 'mo_ldap_local_admin_api_key' );
					delete_option( 'mo_ldap_local_password', '' );
					delete_option( 'mo_ldap_local_message' );
					delete_option( 'mo_ldap_local_verify_customer' );
					delete_option( 'mo_ldap_local_new_registration' );
					delete_option( 'mo_ldap_local_registration_status' );
				} elseif ( strcasecmp( $post_option, 'mo_ldap_login_send_query' ) === 0 && check_admin_referer( 'mo_ldap_login_send_query' ) ) {
					$email         = isset( $_POST['mo_ldap_local_query_email'] ) ? sanitize_email( wp_unslash( $_POST['mo_ldap_local_query_email'] ) ) : '';
					$phone         = isset( $_POST['mo_ldap_local_query_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_query_phone'] ) ) : '';
					$query         = isset( $_POST['mo_ldap_local_query'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_query'] ) ) : '';
					$is_setup_call = isset( $_POST['mo_ldap_local_setup_call'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_setup_call'] ) ) : '';
					$timezone      = isset( $_POST['mo_ldap_setup_call_timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_setup_call_timezone'] ) ) : '';
					$call_date     = isset( $_POST['mo_ldap_setup_call_date'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_setup_call_date'] ) ) : '';
					$call_time     = isset( $_POST['mo_ldap_setup_call_time'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_setup_call_time'] ) ) : '';
					$choice        = isset( $_POST['mo_ldap_local_send_config'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_send_config'] ) ) : 'off';
					if ( strcasecmp( $choice, 'on' ) === 0 ) {
						$configuration = $this->auto_email_ldap_export();
						$configuration = implode( ' <br>', $configuration );
						$query         = $query . ' ,<br><br>Plugin Configuration:<br> ' . $configuration;
					} elseif ( strcasecmp( $choice, 'off' ) === 0 ) {
						$configuration = 'Configuration was not uploaded by user';
						$query         = $query . ' ,<br><br>Plugin Configuration:<br> ' . $configuration;
					}

					$query = '[WP LDAP for Intranet (Free Plugin)]: ' . $query;

					if ( $is_setup_call ) {
						$query = $query . '<br><br> Time Zone: ' . $timezone . '<br> <br>Date: ' . $call_date . '<br> <br>Time: ' . $call_time;
					}

					$this->mo_ldap_send_query( $email, $phone, $query );
				} elseif ( strcasecmp( $post_option, 'mo_ldap_local_custom_requirements_req' ) === 0 && check_admin_referer( 'mo_ldap_local_custom_requirements_req_nonce' ) ) {
					$email = isset( $_POST['mo_ldap_local_custom_requirements_email'] ) ? sanitize_email( wp_unslash( $_POST['mo_ldap_local_custom_requirements_email'] ) ) : '';
					$phone = isset( $_POST['mo_ldap_local_custom_requirements_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_custom_requirements_phone'] ) ) : '';
					$query = isset( $_POST['mo_ldap_local_description'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_description'] ) ) : '';
					$query = '[WP LDAP for Intranet (Free Plugin)]: ' . $query;
					$this->mo_ldap_send_query( $email, $phone, $query );
				}
				if ( strcasecmp( $post_option, 'mo_ldap_trial_request' ) === 0 && check_admin_referer( 'mo_ldap_trial_request' ) ) {
					if ( isset( $_POST['mo_ldap_trial_email'] ) ) {
						$email = isset( $_POST['mo_ldap_trial_email'] ) ? sanitize_email( wp_unslash( $_POST['mo_ldap_trial_email'] ) ) : '';
					}

					if ( empty( $email ) ) {
						$email = get_option( 'mo_ldap_local_admin_email' );
					}

					if ( isset( $_POST['mo_ldap_trial_plan'] ) ) {
						$trial_plan = isset( $_POST['mo_ldap_trial_plan'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_trial_plan'] ) ) : '';
					}

					if ( isset( $_POST['mo_ldap_trial_description'] ) ) {
						$trial_requirements = isset( $_POST['mo_ldap_trial_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mo_ldap_trial_description'] ) ) : '';
					}

					$phone = '';

					$license_plans = array(
						'basic-plan'                 => 'Essential Authentication Plan',
						'kerbores-ntlm'              => 'Kerberos / NTLM SSO Plan',
						'standard-plan'              => 'Advanced Syncing & Authentication Plan',
						'enterprise-plan'            => 'All Inclusive Plan',
						'multisite-basic-plan'       => 'Multisite Essential Authentication Plan',
						'multisite-kerbores-ntlm'    => 'Multisite Kerberos / NTLM SSO Plan',
						'multisite-standard-plan'    => 'Multisite Advanced Syncing & Authentication Plan',
						'enterprise-enterprise-plan' => 'Multisite All Inclusive Plan',
					);
					if ( isset( $license_plans[ $trial_plan ] ) ) {
						$trial_plan = $license_plans[ $trial_plan ];
					}
					$addons = array(
						'directory-sync'          => 'Sync Users LDAP Directory',
						'buddypress-integration'  => 'Sync BuddyPress Extended Profiles',
						'password-sync'           => 'Password Sync with LDAP Server',
						'profile-picture-map'     => 'Profile Picture Sync for WordPress and BuddyPress',
						'ultimate-member-login'   => 'Ultimate Member Login Integration',
						'page-post-restriction'   => 'Page/Post Restriction',
						'search-staff'            => 'Search Staff from LDAP Directory',
						'profile-sync'            => 'Third Party Plugin User Profile Integration',
						'gravity-forms'           => 'Gravity Forms Integration',
						'buddypress-group'        => 'Sync BuddyPress Groups',
						'memberpress-integration' => 'MemberPress Plugin Integration',
						'emember-integration'     => 'eMember Plugin Integration',
						'buddyboss-integration'   => 'BuddyBoss Profile Integration',
						'directory-search'        => 'Directory Search',
						'paid-membership-pro'     => 'Paid Membership Pro Integrator',
						'wp-groups'               => 'WP Groups Plugin Integration',
						'custom-notifications'    => 'Custom Notifications on WordPress Login page',
					);

					$addons_selected = array();
					foreach ( $addons as $key => $value ) {
						if ( isset( $_POST[ $key ] ) && strcasecmp( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ), 'true' ) === 0 ) {
							$addons_selected[ $key ] = $value;
						}
					}
					$directory_access = '';
					$query            = '';
					if ( ! empty( $trial_plan ) ) {
						$query .= '<br><br>[Interested in plan] : ' . $trial_plan;
					}

					if ( ! empty( $addons_selected ) ) {
						$query .= '<br><br>[Interested in add-ons] : ';
						foreach ( $addons_selected as $key => $value ) {
							$query .= $value;
							if ( next( $addons_selected ) ) {
								$query .= ', ';
							}
						}
					}

					if ( ! empty( $trial_requirements ) ) {
						$query .= '<br><br>[Requirements] : ' . $trial_requirements;
					}

					if ( isset( $_POST['get_directory_access'] ) ) {
						$directory_access = sanitize_text_field( wp_unslash( $_POST['get_directory_access'] ) );
					}

					if ( strcasecmp( $directory_access, 'Yes' ) === 0 ) {
						$directory_access = 'Yes';
					} else {
						$directory_access = 'No';
					}
					$query .= '<br><br>[Is your LDAP server publicly accessible?] : ' . $directory_access . '';

					$query = ' [Trial: WordPress LDAP/AD Plugin]: ' . $query;
					$this->mo_ldap_send_query( $email, $phone, $query );
				}
				if ( strcasecmp( $post_option, 'mo_ldap_skip_feedback' ) === 0 && check_admin_referer( 'mo_ldap_skip_feedback' ) ) {
					deactivate_plugins( MO_LDAP_LOCAL_PLUGIN_NAME );
					update_option( 'mo_ldap_local_message', 'Plugin deactivated successfully.' );
					$this->deactivate_error_message();
					if ( isset( $_SERVER['PHP_SELF'] ) ) {
						Header( 'Location: ' . sanitize_url( wp_unslash( $_SERVER['PHP_SELF'] ) ) );
					}
				}
				if ( strcasecmp( $post_option, 'mo_ldap_hide_msg' ) === 0 && check_admin_referer( 'mo_ldap_hide_msg' ) ) {
					update_option( 'mo_ldap_local_multisite_message', 'true' );
				}
				if ( strcasecmp( $post_option, 'mo_ldap_feedback' ) === 0 && check_admin_referer( 'mo_ldap_feedback' ) ) {
					$user                        = wp_get_current_user();
					$message                     = 'Query :[WordPress LDAP/AD Plugin:] Plugin Deactivated: ';
					$deactivate_reason_message   = array_key_exists( 'mo_ldap_local_query_feedback', $_POST ) ? sanitize_textarea_field( wp_unslash( $_POST['mo_ldap_local_query_feedback'] ) ) : false;
					$deactivate_feedback_options = isset( $_POST['mo_ldap_local_feedback_options'] ) ? (array) array_map( 'sanitize_text_field', wp_unslash( $_POST['mo_ldap_local_feedback_options'] ) ) : array();
					wp_safe_redirect( admin_url( 'plugins.php' ) );
					$reply_required = '';
					if ( isset( $_POST['mo_ldap_local_get_reply'] ) ) {
						$reply_required = sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_get_reply'] ) );
					}
					if ( empty( $reply_required ) ) {
						$reply_required = 'NO';
						$message       .= '<strong><span style="color: red;">[Follow up Needed : ' . $reply_required . ']</strong></span><br> ';
					} else {
						$reply_required = 'YES';
						$message       .= '<strong><span style="color: green;">[Follow up Needed : ' . $reply_required . ']</strong></span><br>';
					}

					if ( ! empty( $deactivate_reason_message ) ) {
						$message .= '<br>Feedback : ' . $deactivate_reason_message . '<br>';
					}

					if ( ! empty( $deactivate_feedback_options ) ) {
						$message .= '<br>Deactivation Reason: ';
						$index    = 1;
						foreach ( $deactivate_feedback_options as $deactivate_feedback_option ) {
							$sanitized_feedback_option = sanitize_text_field( $deactivate_feedback_option );
							$message                  .= '(' . $index . ') ' . $sanitized_feedback_option . '. ';
							++$index;
						}
						$message .= '<br>';
					}

					$message .= '<br>Current Version Installed : ' . MO_LDAP_LOCAL_VERSION . '<br>';

					if ( isset( $_POST['mo_ldap_local_rate'] ) ) {
						$rate_value = sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_rate'] ) );
						$message   .= '<br>[Rating : ' . $rate_value . ']<br>';
					}

					$email   = isset( $_POST['mo_ldap_local_query_mail'] ) ? sanitize_email( wp_unslash( $_POST['mo_ldap_local_query_mail'] ) ) : '';
					$subject = 'WordPress LDAP/AD Plugin Feedback - ' . $email;

					if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						$email = get_option( 'mo_ldap_local_admin_email' );
						if ( empty( $email ) ) {
							$email = $user->user_email;
						}
					}
					$company          = isset( $_POST['mo_ldap_local_company'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_local_company'] ) ) : '';
					$feedback_reasons = new Mo_Ldap_Local_Customer_Setup_Handler();
					if ( ! is_null( $feedback_reasons ) ) {
						$submited = json_decode( $feedback_reasons->send_email_alert( $subject, $email, $message, $company ), true );
						if ( json_last_error() === JSON_ERROR_NONE ) {
							if ( is_array( $submited ) && array_key_exists( 'status', $submited ) && strcasecmp( $submited['status'], 'ERROR' ) === 0 ) {
										update_option( 'mo_ldap_local_message', $submited['message'] );
										$this->utils->show_error_message();
							} elseif ( ! $submited ) {
								update_option( 'mo_ldap_local_message', 'Error while submitting the query.' );
								$this->utils->show_error_message();
							}
						}

						deactivate_plugins( MO_LDAP_LOCAL_PLUGIN_NAME );
						update_option( 'mo_ldap_local_message', 'Thank you for the feedback.' );
						$this->utils->show_success_message();
						wp_safe_redirect( 'plugins.php' );
						exit;

					}
				}
			}
		}
	}
}
