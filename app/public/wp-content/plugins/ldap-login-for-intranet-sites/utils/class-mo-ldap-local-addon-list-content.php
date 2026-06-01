<?php
/**
 * Plugin utilities.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage utils
 */

namespace MO_LDAP\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MO_LDAP_Local_Addon_List_Content' ) ) {
	/**
	 * MO_LDAP_Local_Addon_List_Content : Class to store the details of addons.
	 */
	class MO_LDAP_Local_Addon_List_Content {

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			define(
				'MO_LDAP_RECOMMENDED_ADDONS',
				maybe_serialize(
					array(

						'ADVANCED_SYNC'           => array(
							'addonName'     => 'Advanced Sync',
							'addonFeatures' => array(
								'Sync Users From Active Directory To WordPress',
								'Sync Users From WordPress To Active Directory',
								'Self Service Password Reset In AD From WordPress Profile Page',
								'Map User Profile Pictures From AD To WordPress',
							),
							'addonLicense'  => 'ContactUs',
							'addonGuide'    => 'https://plugins.miniorange.com/setup-guide-to-sync-ldap-active-directory-users-with-wordpress',
							'addonVideo'    => '',
							'addonPage'     => '',
						),
						'PAGE_POST_RESTRICTION'   => array(
							'addonName'     => 'Page/Post Restriction',
							'addonFeatures' => array(
								'Restrict access based on LDAP groups',
								'Restrict access based on WordPress roles',
								'Control page and post visibility',
							),
							'addonLicense'  => 'ContactUs',
							'addonGuide'    => 'https://plugins.miniorange.com/wordpress-page-restriction',
							'addonVideo'    => '',
							'addonPage'     => '',
						),
						'USER_&_LOGIN_MANAGEMENT' => array(
							'addonName'     => 'User and Login Management Plugin',
							'addonFeatures' => array(
								'Bulk Users Role Management',
								'Bulk User Modification',
								'Advanced login management options',
								'Manage WordPress users Profile picture',
							),
							'addonLicense'  => 'ContactUs',
							'addonGuide'    => 'https://plugins.miniorange.com/wordpress-user-session-login-management',
							'addonVideo'    => '',
							'addonPage'     => 'https://plugins.miniorange.com/wordpress-login-and-user-management-plugin',
						),
					)
				)
			);

			define(
				'MO_LDAP_THIRD_PARTY_INTEGRATION_ADDONS',
				maybe_serialize(
					array(

						'BUDDYPRESS_PROFILE_SYNC'    => array(
							'addonName'     => 'BuddyPress/BuddyBoss Profile Integration',
							'addonLicense'  => 'ContactUs',
							'addonFeatures' => array(
								'Sync BuddyPress/BuddyBoss profiles with LDAP attributes',
								'Assign BuddyPress/BuddyBoss groups based on LDAP groups',
							),
							'addonGuide'    => 'https://plugins.miniorange.com/guide-to-setup-miniorange-ldap-buddypress-integration-add-on',
							'addonVideo'    => 'https://www.youtube.com/embed/7itUoIINyTw',
						),
						'ULTIMATE_MEMBER_PROFILE_INTEGRATION' => array(
							'addonName'     => 'Ultimate Member Integration',
							'addonFeatures' => array(
								'Login to Ultimate Member with LDAP credentials',
								'Sync Ultimate Member profiles with LDAP attributes',
							),
							'addonLicense'  => 'ContactUs',
							'addonGuide'    => 'https://plugins.miniorange.com/guide-to-setup-ultimate-member-login-integration-with-ldap-credentials',
							'addonVideo'    => 'https://www.youtube.com/embed/-d2B_0rDFi0',
						),
						'LDAP_WP_GROUPS_INTEGRATION' => array(
							'addonName'     => 'WP Groups Plugin Integration',
							'addonFeatures' => array(
								'Assign WordPress groups based on LDAP group membership',
								'Map multiple WordPress groups to LDAP groups',
							),
							'addonLicense'  => 'ContactUs',
							'addonGuide'    => 'https://plugins.miniorange.com/ldap-active-directory-groups-plugin-integration',
							'addonVideo'    => 'https://www.youtube.com/watch?v=2GuUXGLGDFo',
						),
						'LEARNDASH_ADDON'            => array(
							'addonName'     => 'LearnDash Integration',
							'addonFeatures' => array(
								'Assign LearnDash groups based on LDAP group memberships',
								'Map multiple LearnDash groups to LDAP groups',
							),
							'addonLicense'  => 'ContactUs',
							'addonGuide'    => '',
							'addonVideo'    => '',
						),
						'WOOCOMMERCE_INTEGRATION'    => array(
							'addonName'     => 'WooCommerce Integration',
							'addonFeatures' => array(
								'Login to WooCommerce with LDAP credentials',
								'Sync WooCommerce profiles with LDAP attributes',
							),
							'addonLicense'  => 'ContactUs',
							'addonGuide'    => '',
							'addonVideo'    => '',
						),
					)
				)
			);
		}
	}
}
