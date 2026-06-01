<?php
/**
 * Load Plugin dependencies.
 *
 * @package miniOrange_LDAP_AD_Integration
 */

namespace MO_LDAP;

require_once 'utils' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-utils.php';
require_once 'utils' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-addon-list-content.php';
require_once 'utils' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-data-store.php';

require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-save-options-handler.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-configuration-handler.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-customer-setup-handler.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-user-profile-handler.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-login-handler.php';

require_once 'helpers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-license-plans-pricing.php';
require_once 'helpers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-local-auth-response-helper.php';


use MO_LDAP\Utils\Mo_Ldap_Local_Utils;
use MO_LDAP\Utils\MO_LDAP_Local_Addon_List_Content;
use MO_LDAP\Utils\MO_LDAP_Local_Data_Store;

use MO_LDAP\Handlers\Mo_Ldap_Local_Save_Options_Handler;
use MO_LDAP\Handlers\Mo_Ldap_Local_User_Profile_Handler;
use MO_LDAP\Handlers\Mo_Ldap_Local_Login_Handler;
use MO_LDAP\Handlers\Mo_Ldap_Local_Configuration_Handler;

use MO_LDAP\Helpers\MO_LDAP_License_Plans_Pricing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Mo_Ldap_Local_Login' ) ) {
	/**
	 * Mo_Ldap_Local_Login : This is the main class of the plugin.
	 */
	final class Mo_Ldap_Local_Login {

		/**
		 * Utility object.
		 *
		 * @var [object]
		 */
		private $util;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			$this->util = new Mo_Ldap_Local_Utils();

			$this->mo_ldap_local_initialize_hooks();
			$this->mo_ldap_local_initialize_handlers();
			$this->mo_ldap_local_update_plugin_version();
		}

		/**
		 * Function mo_ldap_local_initialize_handlers : Returns URL links used in plugin menu
		 *
		 * @return void
		 */
		private function mo_ldap_local_initialize_handlers() {
			$save_options = new Mo_Ldap_Local_Save_Options_Handler();
			$user_profile = new Mo_Ldap_Local_User_Profile_Handler();

			if ( strcmp( get_option( 'mo_ldap_local_enable_login' ), '1' ) === 0 ) {
				$mo_ldap_local_login = new Mo_Ldap_Local_Login_Handler();
			}
		}

		/**
		 * Function mo_ldap_local_update_plugin_version : Returns URL links used in plugin menu
		 *
		 * @return void
		 */
		private function mo_ldap_local_update_plugin_version() {
			$version_in_db = ! empty( get_option( 'mo_ldap_local_current_plugin_version' ) ) ? get_option( 'mo_ldap_local_current_plugin_version' ) : '';

			if ( version_compare( $version_in_db, '5.2.3', '<=' ) ) {
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
				update_option( 'mo_ldap_local_search_filter', $this->util->encrypt( $ldap_search_filter ) );
			}

			if ( version_compare( $version_in_db, MO_LDAP_LOCAL_VERSION ) !== 0 ) {
				update_option( 'mo_ldap_local_current_plugin_version', MO_LDAP_LOCAL_VERSION );
			}
		}

		/**
		 * Function mo_ldap_local_update_plugin_version : Returns URL links used in plugin menu
		 *
		 * @return void
		 */
		private function mo_ldap_local_initialize_hooks() {
			add_action( 'admin_menu', array( $this, 'mo_ldap_local_login_widget_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'mo_ldap_local_settings_style' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'mo_ldap_local_settings_script' ) );
			register_activation_hook( MO_LDAP_LOCAL_PLUGIN_NAME, array( $this, 'mo_ldap_local_activate' ) );
			register_deactivation_hook( MO_LDAP_LOCAL_PLUGIN_NAME, array( $this, 'mo_ldap_local_deactivate' ) );
			add_action( 'admin_footer', array( $this, 'mo_ldap_local_feedback_request' ) );
			add_filter( 'plugin_action_links_' . MO_LDAP_LOCAL_PLUGIN_NAME, array( $this, 'mo_ldap_local_links' ) );
		}

		/**
		 * Function mo_ldap_local_links : Returns URL links used in plugin menu
		 *
		 * @param  array $links : Default Links present in plugin menu.
		 * @return array
		 */
		public function mo_ldap_local_links( $links ) {
			if ( is_plugin_active( MO_LDAP_LOCAL_PLUGIN_NAME ) ) {
				$links = array_merge(
					array(
						'<a href="' . esc_url( admin_url( '?page=mo_ldap_local_login' ) ) . '">' . __( 'Settings', 'ldap-login-for-intranet-sites' ) . '</a>',
						'<a href="' . esc_url( admin_url( '?page=mo_ldap_local_login&tab=pricing' ) ) . '">' . __( 'Upgrade to Premium', 'ldap-login-for-intranet-sites' ) . '</a>',
					),
					$links
				);
			}
			return $links;
		}

		/**
		 * Function mo_ldap_local_feedback_request : Return feedback form html invoked during deactivation.
		 *
		 * @return void
		 */
		public function mo_ldap_local_feedback_request() {
			if ( isset( $_SERVER['PHP_SELF'] ) && 'plugins.php' !== basename( esc_url( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) ) ) ) ) {
				return;
			}
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-feedback-form.php';
		}

		/**
		 * Function mo_ldap_local_activate : Called on plugin activation
		 *
		 * @return void
		 */
		public function mo_ldap_local_activate() {
			$mo_ldap_token_key = get_option( 'mo_ldap_local_customer_token' );
			$email_attr        = get_option( 'mo_ldap_local_email_attribute' );
			update_option( 'mo_ldap_local_register_user', 1 );
			if ( empty( $mo_ldap_token_key ) ) {
				update_option( 'mo_ldap_local_customer_token', $this->util->generate_random_string( 15 ) );
			}

			if ( empty( $email_attr ) ) {
				update_option( 'mo_ldap_local_email_attribute', 'mail' );
			}
			ob_clean();
		}

		/**
		 * Function mo_ldap_local_deactivate
		 *
		 * @return void
		 */
		public function mo_ldap_local_deactivate() {
			delete_option( 'mo_ldap_local_admin_email' );
			delete_option( 'mo_ldap_local_host_name' );
			delete_option( 'mo_ldap_local_password' );
			delete_option( 'mo_ldap_local_admin_phone' );
			delete_option( 'mo_ldap_local_admin_customer_key' );
			delete_option( 'mo_ldap_local_admin_api_key' );
			delete_option( 'mo_ldap_local_customer_token' );
			delete_option( 'mo_ldap_local_message' );
			delete_option( 'mo_ldap_local_service_account_status' );
			delete_option( 'mo_ldap_local_user_mapping_status' );
		}

		/**
		 * Function mo_ldap_local_update_plugin_version : Returns URL links used in plugin menu
		 *
		 * @return void
		 */
		public function mo_ldap_local_login_widget_menu() {
			add_menu_page( 'LDAP/AD Login for Intranet', 'LDAP/AD Login for Intranet', 'activate_plugins', 'mo_ldap_local_login', array( $this, 'mo_ldap_local_login_widget_options' ), MO_LDAP_LOCAL_URL . 'includes/images/miniorange_icon.png' );
			add_submenu_page( 'mo_ldap_local_login', 'LDAP/AD plugin', 'Licensing Plans', 'manage_options', 'mo_ldap_local_login&amp;tab=pricing', array( $this, 'mo_ldap_show_licensing_page' ) );
		}

		/**
		 * Function mo_ldap_local_update_plugin_version : Returns URL links used in plugin menu
		 *
		 * @return void
		 */
		public function mo_ldap_local_login_widget_options() {
			$utils          = $this->util;
			$addons         = new MO_LDAP_Local_Addon_List_Content();
			$pricing        = new MO_LDAP_License_Plans_Pricing();
			$timezones      = new MO_LDAP_Local_Data_Store();
			$mo_ldap_config = new Mo_Ldap_Local_Configuration_Handler();
			require_once MO_LDAP_LOCAL_DIR . 'controllers/mo-ldap-local-main-controller.php';
		}

		/**
		 * Function mo_ldap_local_update_plugin_version : Returns URL links used in plugin menu
		 *
		 * @param string $page : Current page.
		 * @return void
		 */
		public function mo_ldap_local_settings_style( $page ) {
			if ( strcasecmp( $page, 'toplevel_page_mo_ldap_local_login' ) !== 0 ) {
				return;
			}
			wp_enqueue_style( 'mo_ldap_local_admin_phone_style', MO_LDAP_LOCAL_INCLUDES . 'css/phone.min.css', array(), MO_LDAP_LOCAL_VERSION );
			wp_enqueue_style( 'mo_ldap_local_admin_plugin_style', MO_LDAP_LOCAL_INCLUDES . 'css/mo_ldap_local_plugin_style.min.css', array(), MO_LDAP_LOCAL_VERSION );
			wp_enqueue_style( 'mo_ldap_local_admin_datatable_style', MO_LDAP_LOCAL_INCLUDES . 'css/mo_ldap_local_datatable.min.css', array(), MO_LDAP_LOCAL_VERSION );
			wp_enqueue_style( 'mo_ldap_add_fonts', 'https://fonts.googleapis.com/css2?family=Inter&display=swap', false, MO_LDAP_LOCAL_VERSION );

			$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'default'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.

			if ( strcmp( $active_tab, 'pricing' ) === 0 ) {
				wp_enqueue_style( 'mo_ldap_local_admin_licensing_style', MO_LDAP_LOCAL_INCLUDES . 'css/mo_ldap_local_licensing_page.min.css', array(), MO_LDAP_LOCAL_VERSION );
			}
		}

		/**
		 * Function mo_ldap_local_settings_script : Enqueues required scripts.
		 *
		 * @return void
		 */
		public function mo_ldap_local_settings_script() {
			if ( isset( $_GET['page'] ) && strcasecmp( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'mo_ldap_local_login' ) === 0 ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- fetching GET parameter for changing table layout.
				wp_enqueue_script( 'mo_ldap_local_admin_phone_script', MO_LDAP_LOCAL_INCLUDES . 'js/phone.min.js', array(), MO_LDAP_LOCAL_VERSION, false );
				wp_enqueue_script( 'mo_ldap_local_admin_datatable_script', MO_LDAP_LOCAL_INCLUDES . 'js/mo_ldap_local_datatable.min.js', array(), MO_LDAP_LOCAL_VERSION, false );
				wp_register_script( 'mo_ldap_local_admin_plugin_script', MO_LDAP_LOCAL_INCLUDES . 'js/mo_ldap_local_plugin_script.min.js', array( 'jquery' ), MO_LDAP_LOCAL_VERSION, true );
				wp_enqueue_script( 'mo_ldap_local_admin_plugin_script' );
				$directory_server_value   = ! empty( get_option( 'mo_ldap_directory_server_value' ) ) ? get_option( 'mo_ldap_directory_server_value' ) : '';
				wp_localize_script(
					'mo_ldap_local_admin_plugin_script',
					'mo_ldap_local_object',
					array(
						'mo_ldap_directory_server_value'  => $directory_server_value,
					)
				);
			}
		}
	}
}
