<?php
/**
 * Display Role Mapping Page.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class=" mo_ldap_outer_box" >
	<div class="mo_ldap_role_local_outer">
		<div class="mo_ldap_heading_container mo_ldap_local_heading_right">
			<div class="mo_ldap_local_footer_btns_container mo_ldap_local_btns_upr_space_remove">
				<a 
					<?php
					echo 'href="' . esc_url(
						add_query_arg(
							array(
								'subtab' => 'ldap-config',
								'tab'    => 'default',
							),
							$filtered_current_page_url
						)
					)
					. '"';
					?>
					class="mo_ldap_local_unset_link_affect">
					<button type="button" class="mo_ldap_back_btn">Back</button>
				</a>
				<a
					<?php
					echo 'href="' . esc_url(
						add_query_arg(
							array(
								'subtab' => 'attribute-mapping',
								'tab'    => 'default',
							),
							$filtered_current_page_url
						)
					)
					. '"';
					?>
					class="mo_ldap_local_unset_link_affect">
					<button type="button" class="mo_ldap_next_btn">Next</button>
				</a>
			</div>
		</div>

		<br>
		<form name="enable_role_mapping_form" id="enable_default_wp_role_mapping_form" method="post" action="">
			<?php wp_nonce_field( 'mo_ldap_local_enable_default_wp_role_mapping' ); ?>
			<input type="hidden" name="option" value="mo_ldap_local_enable_default_wp_role_mapping"/>
			<div class="mo_ldap_display_flex_elements">
				<label for="default_group_mapping" class="mo_ldap_local_d_block mo_ldap_input_label_text" style="width: 48%;">Select the default role for all the LDAP users:</label>
				<select id="default_group_mapping" name="mapping_value_default" class="mo_ldap_select_wp_role">
					<?php
					if ( get_option( 'mo_ldap_local_mapping_value_default' ) ) {
						$default_role = get_option( 'mo_ldap_local_mapping_value_default' );
					} else {
						$default_role = get_option( 'default_role' );
					}
					wp_dropdown_roles( $default_role );
					?>
				</select>
			</div>
		</form>
		<br>
		<form name="enable_role_mapping_form" id="enable_role_mapping_form" method="post" action="">
			<?php wp_nonce_field( 'mo_ldap_local_enable_role_mapping' ); ?>
			<input type="hidden" name="option" value="mo_ldap_local_enable_role_mapping"/>
			<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start">
				<input type="checkbox" class="mo_ldap_local_toggle_switch_hide" id="enable_ldap_role_mapping" name="enable_ldap_role_mapping"  value="1" <?php checked( esc_attr( strcasecmp( get_option( 'mo_ldap_local_enable_role_mapping' ), '1' ) === 0 ) ); ?>/>
				<label for="enable_ldap_role_mapping" class="mo_ldap_local_toggle_switch"></label>
				<label for="enable_ldap_role_mapping" class="mo_ldap_local_d_inline mo_ldap_input_label_text">
					Enable Role Mapping
				</label>
			</div>
		</form>
		<br>
		<form name="enable_role_mapping_form" id="keep_existing_user_role_mapping_form" method="post" action="">
			<?php wp_nonce_field( 'mo_ldap_local_keep_existing_user_role_mapping' ); ?>
			<input type="hidden" name="option" value="mo_ldap_local_keep_existing_user_role_mapping"/>
			<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start">
				<input type="checkbox" id="keep_existing_user_roles" class="mo_ldap_local_toggle_switch_hide" name="keep_existing_user_roles" value="1" <?php checked( esc_attr( strcasecmp( get_option( 'mo_ldap_local_keep_existing_user_roles' ), '1' ) === 0 ) ); ?>/>
				<label for="keep_existing_user_roles" class="mo_ldap_local_toggle_switch"></label>
				<label for="keep_existing_user_roles" class="mo_ldap_local_d_inline mo_ldap_input_label_text">
					Keep existing roles of users (New roles will still be added).
				</label>
			</div>
		</form>
	</div>
	<div class="mo_ldap_local_outer mo_ldap_local_premium_box">
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
			<div class="mo_ldap_local_premium_feature_btn"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"></span>Premium feature</div>
		</a>

		<h3 style="padding-left:4%;">Role Mapping based on Security groups, LDAP OU and LDAP attributes</h3>
		<br>

		<div class="mo_ldap_local_role_mapping_premium_nav">
			<div data-role-mapping-type="group" id="mo_ldap_local_role_mapping_type_1" class="mo_ldap_local_active_role_mapping_subnav">
				Assign WordPress Roles Based On LDAP Security Groups
			</div>
			<div data-role-mapping-type="ou" id="mo_ldap_local_role_mapping_type_2">
				Assign WordPress Roles Based On LDAP OU
			</div>
			<div data-role-mapping-type="attribute" id="mo_ldap_local_role_mapping_type_3">
				Assign WordPress Roles Based On LDAP Attributes
			</div>
		</div>

		<div id="mo_ldap_local_advanced_role_mapping_box1" class="mo_ldap_local_outer mo_ldap_local_outer2 mo_ldap_d_none">
			<div style="right:0;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
				<div><h1>Premium Plan</h1></div>
				<div style="font-size: 16px;">This is available in premium version of the plugin</div>
				<div class="">
					<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
						<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
					</a>
				</div>
			</div>

			<div class="mo_ldap_local_advanced_role_mapping_box">
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start">
					<input type="checkbox" disabled>
					<label class="mo_ldap_local_d_inline mo_ldap_local_bold_label">Enable Role Mapping Based On LDAP Security Groups</label>
				</div>
				<br>
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start">
					<input type="text" class="mo_ldap_local_input mo_ldap_local_searchbase_input" name="" id="" placeholder="cn=group,dc=domain,dc=com" disabled>
					<input type="button" value="Show Groups" class="mo_ldap_local_wired_button mo_ldap_local_groups_btn" disabled>
				</div>
				<br>
				<div class="mo_ldap_local_horizontal_flex_container">
					<div class="mo_ldap_local_group_role_div">
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_table_head">
							<div>Group Name</div>
							<div>Distinguished Name</div>
							<div>WordPress Role</div>
							<div>Add/Remove</div>
						</div>
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_table_example">
							<div style="margin-left:5%;" >Group</div>
							<div style="margin-left:5%;" >CN=Group,DC=domain,DC=com</div>
							<div style="margin-right:12%" >Contributor</div>
							<div style="margin-right:7%" >+</div>
						</div>
					</div>
					<div class="mo_ldap_local_wired_button mo_ldap_local_horizontal_flex_container" style="margin-bottom: 5%;">
						Filters <span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'filters.svg' ); ?>" height="14px" width="15px"></span>
					</div>
				</div>
				<br>
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start mo_ldap_local_flex_gap">
					<label class="mo_ldap_local_d_inline mo_ldap_local_bold_label">LDAP Group Attributes Name</label>
					<input type="text" class="mo_ldap_local_input" style="width:40%" name="" id="" placeholder="Group attributes Name" disabled>
				</div>
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start mo_ldap_local_flex_gap">
					<input type="button" class="button1 mo_ldap_local_disabled_button" value="Save Mapping" disabled>
				</div>
			</div>

		</div>


		<div id="mo_ldap_local_advanced_role_mapping_box2" class="mo_ldap_local_outer mo_ldap_local_outer2 mo_ldap_d_none">
			<div style="right:0;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
				<div><h1>Premium Plan</h1></div>
				<div style="font-size: 16px;">This is available in premium version of the plugin</div>
				<div class="">
					<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
						<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
					</a>
				</div>
			</div>

			<div class="mo_ldap_local_advanced_role_mapping_box">
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start">
					<input type="checkbox" disabled>
					<label class="mo_ldap_local_d_inline mo_ldap_local_bold_label">Enable Role Mapping Based On LDAP OU</label>
				</div>
				<br>
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start">
					<input type="text" class="mo_ldap_local_input mo_ldap_local_searchbase_input" name="" id="" placeholder="cn=group,dc=domain,dc=com" disabled>
					<input type="button" value="Show OUs" class="mo_ldap_local_wired_button mo_ldap_local_groups_btn" disabled>
				</div>
				<br>
				<div class="mo_ldap_local_horizontal_flex_container">
					<div class="mo_ldap_local_group_role_div">
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_table_head">
							<div>Organizational Unit</div>
							<div>WordPress Role</div>
							<div>Add/Remove</div>
						</div>
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_table_example">
							<div style="margin-left:0%;" >OU=TestOU,DC=domain,DC=com</div>
							<div style="margin-right:13%" >Contributor</div>
							<div style="margin-right:15%" >+</div>
						</div>
					</div>
					<div class="mo_ldap_local_wired_button mo_ldap_local_horizontal_flex_container" style="margin-bottom: 5%;">
						Filters <span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'filters.svg' ); ?>" height="14px" width="15px"></span>
					</div>
				</div>
				<br>
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start mo_ldap_local_flex_gap">
					<input type="button" class="button1 mo_ldap_local_disabled_button" value="Save Mapping" disabled>
				</div>
			</div>
		</div>

		<div id="mo_ldap_local_advanced_role_mapping_box3" class="mo_ldap_local_outer mo_ldap_local_outer2 mo_ldap_d_none">
			<div style="right:0;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
				<div><h1>Premium Plan</h1></div>
				<div style="font-size: 16px;">This is available in premium version of the plugin</div>
				<div class="">
					<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
						<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
					</a>
				</div>
			</div>

			<div class="mo_ldap_local_advanced_role_mapping_box">
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start">
					<input type="checkbox" disabled>
					<label class="mo_ldap_local_d_inline mo_ldap_local_bold_label">Enable Role Mapping Based On LDAP Attributes</label>
				</div>
				<br>
				<div class="mo_ldap_local_horizontal_flex_container">
					<div class="mo_ldap_local_group_role_div">
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_table_head">
							<div>LDAP Attribute</div>
							<div>Attribute Value</div>
							<div>WordPress Role</div>
							<div>Add/Remove</div>
						</div>
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_table_example">
							<div style="margin-left:5%;" >Department</div>
							<div style="margin-left:5%;" >IT admin</div>
							<div style="margin-right:12%" >Administrator</div>
							<div style="margin-right:7%" >+</div>
						</div>
					</div>
				</div>
				<br>
				<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start mo_ldap_local_flex_gap">
					<input type="button" class="button1 mo_ldap_local_disabled_button" value="Save Mapping" disabled>
				</div>
			</div>
		</div>

	</div>

	<br>
	<br>

	<div class="mo_ldap_local_outer">
		<h3>Test Role Mapping Configuration</h3>
		<form method="post" id="rolemapconfigtest">
			<input type="hidden" name="option" value="mo_ldap_test_rolemap_configuration" />
			<br>
			<div class="mo_ldap_display_flex_elements" style="margin-left: 7%;">
				<label for="username" class="mo_ldap_input_label_text mo_ldap_local_config_label">Username <span style="color:red;">*</span></label>
				<input type="text" id="mo_ldap_username" name="mo_ldap_username" placeholder="Enter Username" class="mo_ldap_enter_username" required>
			</div>
			<p class="mo_ldap_local_input_paragraph mo_ldap_local_test_mapping_para">Enter LDAP username to test role mapping configuration</p>
			<br>
			<?php
				$search_base_string = get_option( 'mo_ldap_local_search_base' ) ? $utils::decrypt( get_option( 'mo_ldap_local_search_base' ) ) : '';
				$search_bases       = explode( ';', $search_base_string );
			?>
			<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start mo_ldap_local_flex_gap mo_ldap_local_test_config_btn"> 
			<input type="submit" class="mo_ldap_local_test_configuration_button" value="Test Configuration" 
				<?php
				if ( empty( $search_bases[0] ) ) {
					echo 'disabled';
				}
				?>
			>
			</div>
		</form>
	</div>
</div>


<script>
	jQuery( document ).ready(function() {
		jQuery("#default_group_mapping option[value='administrator']").remove();
	});

	function testRoleMappingConfiguration(){

		var nonce = "<?php echo esc_attr( wp_create_nonce( 'testrolemapconfig_nonce' ) ); ?>";

		var username = jQuery("#mo_ldap_username").val();
		var myWindow = window.open('<?php echo esc_url( site_url() ); ?>' + '/?option=testrolemapconfig&user='+username + '&_wpnonce='+nonce, "Test Role Mapping Configuration", "width=700, height=600");
	}
</script>
