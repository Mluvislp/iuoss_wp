<?php
/**
 * User Profile handler.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage handlers
 */

namespace MO_LDAP\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MO_LDAP\Utils\Mo_Ldap_Local_Utils;

if ( ! class_exists( 'Mo_Ldap_Local_User_Profile_Handler' ) ) {
	/**
	 * Mo_Ldap_Local_User_Profile_Handler
	 */
	class Mo_Ldap_Local_User_Profile_Handler {

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
			add_action( 'show_user_profile', array( $this, 'mo_ldap_local_show_user_profile' ) );
		}

		/**
		 * Function show_user_profile : Show User's LDAP Profile Attribute
		 *
		 * @param  mixed $user : WordPress User Object.
		 * @return void
		 */
		public function mo_ldap_local_show_user_profile( $user ) {
			if ( $this->utils->is_administrator_user( $user ) ) {
				?>
				<h3>Extra profile information</h3>

				<table class="form-table" aria-hidden="true">

					<tr>
						<td><strong><label for="user_dn">User DN</label></strong></td>

						<td>
							<strong><?php echo esc_html( get_the_author_meta( 'mo_ldap_user_dn', $user->ID ) ); ?></strong></td>
					</tr>
				</table>

				<?php
			}
		}
	}
}
