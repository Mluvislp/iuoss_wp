<?php
/**
 * Plugin Configuration Controller.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage controllers
 */

$active_subtab = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : 'ldap-config'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.
$active_step   = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : '1'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.

?>

<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_subtab_container">
	<div class="<?php echo strcmp( $active_subtab, 'ldap-config' ) === 0 ? 'mo_ldap_local_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'ldap-config' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">LDAP Configuration</a></div>
	<div class="<?php echo strcmp( $active_subtab, 'role-mapping' ) === 0 ? 'mo_ldap_local_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'role-mapping' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">Role Mapping</a></div>
	<div class="<?php echo strcmp( $active_subtab, 'attribute-mapping' ) === 0 ? 'mo_ldap_local_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'attribute-mapping' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">Attribute Mapping</a></div>
	<div class="<?php echo strcmp( $active_subtab, 'login-settings' ) === 0 ? 'mo_ldap_local_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'login-settings' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">Login Settings</a></div>
</div>
<hr class="mo_ldap_hr">

<?php

if ( strcmp( $active_subtab, 'ldap-config' ) === 0 ) {
	require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-ldap-config-page.php';
} elseif ( strcmp( $active_subtab, 'role-mapping' ) === 0 ) {
	require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-role-mapping-page.php';
} elseif ( strcmp( $active_subtab, 'attribute-mapping' ) === 0 ) {
	require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-attribute-mapping-page.php';
} elseif ( strcmp( $active_subtab, 'login-settings' ) === 0 ) {
	require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-login-settings-page.php';
}
