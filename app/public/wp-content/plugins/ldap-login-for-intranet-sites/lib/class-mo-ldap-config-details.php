<?php
/**
 * This file contains constant used for User Account setup.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage lib
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Adding the required files.
require_once 'class-mo-ldap-basic-enum.php';

if ( ! class_exists( 'MO_LDAP_Config_Details' ) ) {
	/**
	 * MO_LDAP_Config_Details
	 */
	class MO_LDAP_Config_Details extends MO_LDAP_Basic_Enum {
		const LDAP_LOGIN_ENABLE             = 'mo_ldap_local_enable_login';
		const AUTH_ADMIN_BOTH_LDAP_WP       = 'mo_ldap_local_enable_admin_wp_login';
		const AUTO_REGISTERING              = 'mo_ldap_local_register_user';
		const DIRECTORY_SERVER_VALUE        = 'mo_ldap_directory_server_value';
		const CUSTOM_DIRECTORY_SERVER_VALUE = 'mo_ldap_directory_server_custom_value';
		const SERVER_URL                    = 'mo_ldap_local_server_url';
		const SERVER_DN                     = 'mo_ldap_local_server_dn';
		const SERVER_PORT                   = 'mo_ldap_local_ldap_port_number';
		const SERVER_PROTOCOL               = 'mo_ldap_local_ldap_protocol';
		const USER_ATTR                     = 'mo_ldap_local_username_attribute';
		const CUSTOM_USER_ATTR              = 'custom_ldap_username_attribute';
		const SERVER_DOMAIN_URL             = 'mo_ldap_local_ldap_server_address';
		const SERVER_PASSWORD               = 'mo_ldap_local_server_password';
		const SEARCH_BASE                   = 'mo_ldap_local_search_base';
		const SEARCH_FILTER                 = 'mo_ldap_local_search_filter';
		const USERNAME_ATTRIBUTE            = 'Filter_search';
		const DEFAULT_ROLE_VALUE            = 'mo_ldap_local_mapping_value_default';
		const ROLE_MAPPING_ENABLE           = 'mo_ldap_local_enable_role_mapping';
		const KEEP_EXSTING_ROLE             = 'mo_ldap_local_keep_existing_user_roles';
		const LOCAL_EMAIL_DOMAIN            = 'mo_ldap_local_email_domain';
		const MAIL                          = 'mo_ldap_local_email_attribute';
		const PLUGIN_VERSION                = 'mo_ldap_local_current_plugin_version';
	}
}
