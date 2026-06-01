<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * Active Directory Integration for Intranet Sites Plugin
 *
 * This plugin enables to integrate LDAP/AD Authentication and Sync with WordPress site.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage Main
 */

/**
 * Plugin Name: Active Directory Integration for Intranet Sites
 * Plugin URI: https://miniorange.com
 * Description: Active Directory Integration for Intranet Sites plugin provides login to WordPress using credentials stored in your Active Directory / other LDAP Directory.
 * Author: miniOrange
 * Version: 5.2.5
 * Author URI: https://miniorange.com
 * License: Expat
 * License URI: https://plugins.miniorange.com/mit-license
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-ldap-local-login.php';

define( 'MO_LDAP_LOCAL_PLUGIN_FILE_URL', __FILE__ );

use MO_LDAP\Mo_Ldap_Local_Login;

define( 'MO_LDAP_LOCAL_PLUGIN_NAME', plugin_basename( __FILE__ ) );

$dir_name = substr( MO_LDAP_LOCAL_PLUGIN_NAME, 0, strpos( MO_LDAP_LOCAL_PLUGIN_NAME, '/' ) );
define( 'MO_LDAP_LOCAL_NAME', $dir_name );

require_once 'mo-ldap-local-autoload-plugin.php';

new Mo_Ldap_Local_Login();
