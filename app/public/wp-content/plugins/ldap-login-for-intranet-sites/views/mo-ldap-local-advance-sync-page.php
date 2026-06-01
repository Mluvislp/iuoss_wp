<?php
/**
 * Display advance sync page.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

$current_subtab = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : 'directory_sync'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.
?>

<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_subtab_container">
	<div class="<?php echo strcmp( $current_subtab, 'directory_sync' ) === 0 ? 'mo_ldap_local_active_subtab' : ''; ?>">
		<a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'tab'    => 'advance-sync',
					'subtab' => 'directory_sync',
				),
				$filtered_current_page_url
			)
		);
		?>
		" class="mo_ldap_local_unset_link_affect">Sync Users LDAP Directory</a>
	</div>
	<div class="<?php echo strcmp( $current_subtab, 'password_sync' ) === 0 ? 'mo_ldap_local_active_subtab' : ''; ?>">
		<a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'tab'    => 'advance-sync',
					'subtab' => 'password_sync',
				),
				$filtered_current_page_url
			)
		);
		?>
		" class="mo_ldap_local_unset_link_affect">AD Self-Service Password Reset</a>
	</div>
	<div class="<?php echo strcmp( $current_subtab, 'profile_picture_sync' ) === 0 ? 'mo_ldap_local_active_subtab' : ''; ?>">
		<a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'tab'    => 'advance-sync',
					'subtab' => 'profile_picture_sync',
				),
				$filtered_current_page_url
			)
		);
		?>
		" class="mo_ldap_local_unset_link_affect">Profile Picture Sync</a>
	</div>
</div>
<hr class="mo_ldap_hr">

<?php
if ( strcasecmp( $current_subtab, 'directory_sync' ) === 0 ) {

	?>
	<div class="mo_ldap_local_outer mo_ldap_local_premium_box">
		<div style="top: 15%; height: 80%; right: 0;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
			<div><h1>Premium Plan</h1></div>
			<div style="font-size: 16px;">This is available in premium version of the plugin</div>
			<div class="">
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
				</a>
			</div>
		</div>

		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
			<div class="mo_ldap_local_premium_feature_btn">
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="20px" width="20px"></span> Premium Feature
			</div>
		</a>
		<div class="mo_ldap_local_premium_feature_box">
			<div class="mo_ldap_local_imp_exp_headings">
				LDAP to WordPress Sync
			</div>
			<br>
			<div class="mo_ldap_local_md_search_conditions">
				<input style="background: #f5f5f5; border: 1px solid #a3a3a3; border-radius: 4px; cursor: no-drop; width:20px; height: 20px !important" type="checkbox" disabled >
				Specify Search base and Search filter
			</div>
			<br>
			<div class="mo_ldap_local_input_field_container">
				<div>LDAP to WP Sync Frequency <span style="color:red;">*</span></div>
				<select style="background: #f5f5f5; cursor: no-drop;" class="mo_ldap_local_standerd_input mo_ldap_select_directory_server " disabled>
					<option class="mo_ldap_select_option" value="">Daily</option>
				</select>
			</div>
			<br>
			<div class="mo_ldap_local_input_field_container">
				<div>Select Timezone <span style="color:red;">*</span></div>
				<select style="background: #f5f5f5; cursor: no-drop;" class="mo_ldap_local_standerd_input mo_ldap_select_directory_server " disabled>
					<option class="mo_ldap_select_option" value="">Abidjan</option>
				</select>
			</div>

			<br>

			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Unsync WordPress Users not present in LDAP
			</div>
			<br>
			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Enable Schedule Sync
			</div>

			<br>

			<button style="cursor: no-drop;" class="mo_ldap_local_disabled_button">
				Save Configuration
			</button>

			<button style="cursor: no-drop;" class="mo_ldap_troubleshooting_btn mo_ldap_local_md_disabled_btn">
				Sync Users Now
			</button>

			<br>
			<br>
			<div class="mo_ldap_local_imp_exp_headings">
				WordPress to LDAP Sync
			</div>
			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Add new user in LDAP when registered in WordPress	
			</div>
			<br>

			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Delete user in LDAP when deleted in WordPress	
			</div>
			<br>

			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Update user profile in LDAP when updated in WordPress
			</div>
			<br>

			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Add/Remove user to/from groups in LDAP server when respective user role changed in WordPress
			</div>
			<br>

			<button style="cursor: no-drop;" class="mo_ldap_local_disabled_button">
				Sync WordPress Users
			</button>
		</div>
	</div>
	<?php
} elseif ( strcasecmp( $current_subtab, 'password_sync' ) === 0 ) {
	?>
	<div class="mo_ldap_local_outer mo_ldap_local_premium_box">

		<div style="top: 22%; height: 76%; right: 0;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
			<div><h1>Premium Plan</h1></div>
			<div style="font-size: 16px;">This is available in premium version of the plugin</div>
			<div class="">
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
				</a>
			</div>
		</div>

		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
			<div class="mo_ldap_local_premium_feature_btn">
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="20px" width="20px"></span> Premium Feature
			</div>
		</a>
		<div class="mo_ldap_local_premium_feature_box">
			<div class="mo_ldap_local_imp_exp_headings">
				LDAP Password Sync Configuration
			</div>
			<br>
			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Update user password in LDAP when reset in WordPress
			</div>
			<p>Enable the above option after successfully saving the LDAP Connection Information and User Mapping Configuration.</p>
			<br>

			<button style="cursor: no-drop;" class="mo_ldap_local_disabled_button">
				Password Sync
			</button>
		</div>
	</div>
	<?php
} elseif ( strcasecmp( $current_subtab, 'profile_picture_sync' ) === 0 ) {
	?>
	<div class="mo_ldap_local_outer mo_ldap_local_premium_box">

		<div style="top: 17%; height: 80%; right: 0;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
			<div><h1>Premium Plan</h1></div>
			<div style="font-size: 16px;">This is available in premium version of the plugin</div>
			<div class="">
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
				</a>
			</div>
		</div>

		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
			<div class="mo_ldap_local_premium_feature_btn">
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="20px" width="20px"></span> Premium Feature
			</div>
		</a>
		<div class="mo_ldap_local_premium_feature_box">
			<div class="mo_ldap_local_imp_exp_headings">
				LDAP Profile Picture Sync
			</div>
			<br>
			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Enable AD Profile Picture Mapping
			</div>
			<br> 
			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Enable BuddyPress Profile Picture Mapping
			</div>
			<br> 

			<div class="mo_ldap_local_input_field_container">
				<br>
				<div>Profile Photo Attribute <span style="color:red;">*</span></div>
				<input class="mo_ldap_local_disabled_input_field mo_ldap_local_disabled_input_field_md" placeholder="Enter Profile Picture Attribute" disabled/>
			</div>

			<button style="cursor: no-drop;" class="mo_ldap_local_disabled_button">
				Sync Profile Picture 
			</button>
		</div>
	</div> 
	<?php
}

