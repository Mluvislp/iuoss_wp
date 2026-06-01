<?php
/**
 * Display Customer Details Page.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class="mo_ldap_local_account_detail_page">
	<div class="mo_ldap_local_account_detail_header">
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_horizontal_flex_container">
			<span>
				<svg id="mo_ldap_local_dropdown" style="margin-top: 3%;margin-left: 5%;transform: rotate(90deg);" viewBox="0 0 448 512" height="15px" width="15px" fill="#fff" class="mo_ldap_local_reverse_rotate">
					<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/>
				</svg>
			</span>
		</a>
		<span style="margin-left: 20px">Account Details</span>
	</div>
	<div>
		<div style="padding:25px;">
			<div class="mo_ldap_local_account_detail_table">
				<div class="mo_ldap_local_account_info_field">miniOrange Account Email</div>
				<div><?php echo esc_html( get_option( 'mo_ldap_local_admin_email' ) ); ?></div>
			</div>
			<div class="mo_ldap_local_account_detail_table">
				<div class="mo_ldap_local_account_info_field">Customer ID</div>
				<div><?php echo esc_html( get_option( 'mo_ldap_local_admin_customer_key' ) ); ?></div>
			</div>
			<div class="mo_ldap_local_account_detail_table">
				<div class="mo_ldap_local_account_info_field">Telephone Number</div>
				<div><?php echo esc_html( get_option( 'mo_ldap_local_admin_phone' ) ? get_option( 'mo_ldap_local_admin_phone' ) : '-' ); ?></div>
			</div>
		</div>
		<div style="display:flex;padding: 10px;">
			<div>
				<form name="mo_ldap_change_account_form" method="post" action="" id="mo_ldap_change_account_form">
					<?php wp_nonce_field( 'change_miniorange_account' ); ?>
					<input type="hidden" name="option" value="change_miniorange_account"/>
					<input style="margin-left: 35px;" class="mo_ldap_save_user_mapping" type="submit" value="Change Account" class="button button-primary-ldap button-large"/>
				</form>
			</div>
			<div style="display:flex;">
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), htmlentities( $filtered_current_page_url ) ) ); ?>" style="text-decoration:none; margin-left: 35px;" type="button" class="mo_ldap_troubleshooting_btn mo_ldap_wireframe_btn">Check Licensing Plans</a>
			</div>
		</div>
	</div>
</div>
