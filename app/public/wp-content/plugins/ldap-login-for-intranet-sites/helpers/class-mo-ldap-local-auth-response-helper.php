<?php
/**
 * This file contains Class used for all LDAP Auth response structure
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage helpers
 */

namespace MO_LDAP\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mo_Ldap_Local_Auth_Response_Helper' ) ) {
	/**
	 * Mo_Ldap_Local_Auth_Response_Helper : Standard used for all LDAP response
	 */
	class Mo_Ldap_Local_Auth_Response_Helper {

		/**
		 * Var status
		 *
		 * @var mixed
		 */
		public $status;

		/**
		 * Var status_message
		 *
		 * @var mixed
		 */
		public $status_message;

		/**
		 * Var user_dn
		 *
		 * @var mixed
		 */
		public $user_dn;

		/**
		 * Var attribute_list
		 *
		 * @var mixed
		 */
		public $attribute_list;

		/**
		 * Var profile_attributes_list
		 *
		 * @var mixed
		 */
		public $profile_attributes_list;
	}
}
