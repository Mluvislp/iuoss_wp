<?php
/**
 * Autoload all the plugin dependencies.
 *
 * @package miniOrange_LDAP_AD_Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MO_LDAP_LOCAL_HOST_NAME', 'https://login.xecurify.com' );
define( 'MO_LDAP_LOCAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'MO_LDAP_LOCAL_URL', plugin_dir_url( __FILE__ ) );
define( 'MO_LDAP_LOCAL_VIEWS', MO_LDAP_LOCAL_DIR . 'views/' );
define( 'MO_LDAP_LOCAL_IMAGES', MO_LDAP_LOCAL_URL . 'includes/images/' );
define( 'MO_LDAP_LOCAL_CONTROLLERS', MO_LDAP_LOCAL_DIR . 'controllers/' );
define( 'MO_LDAP_LOCAL_LIB', MO_LDAP_LOCAL_DIR . 'lib/' );
define( 'MO_LDAP_LOCAL_LOGO_URL', MO_LDAP_LOCAL_URL . 'includes/images/logo.png' );
define( 'MO_LDAP_LOCAL_INCLUDES', MO_LDAP_LOCAL_URL . 'includes/' );
define( 'MO_LDAP_LOCAL_VERSION', '5.2.5' );
define(
	'TAB_LDAP_CLASS_NAMES',
	maybe_serialize(
		array(
			'ldap_Login'  => 'MO_LDAP_Account_Details',
			'ldap_config' => 'MO_LDAP_Config_Details',
		)
	)
);
define(
	'MO_LDAP_LOCAL_ESC_ALLOWED',
	array(
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
		'span'   => array(
			'class' => array(),
		),
		'i'      => array(
			'class' => array(),
		),
		'button' => array(
			'id'    => array(),
			'class' => array(),
		),
	)
);
