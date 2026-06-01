<?php
/**
 * LDAP Configuration Page.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

$directory_server_value   = ! empty( get_option( 'mo_ldap_directory_server_value' ) ) ? get_option( 'mo_ldap_directory_server_value' ) : '';
$server_url               = ( get_option( 'mo_ldap_local_server_url' ) ? $utils::decrypt( get_option( 'mo_ldap_local_server_url' ) ) : '' );
$ldap_server_protocol     = ( get_option( 'mo_ldap_local_ldap_protocol' ) ? get_option( 'mo_ldap_local_ldap_protocol' ) : 'ldap' );
$ldap_server_address      = get_option( 'mo_ldap_local_ldap_server_address' ) ? $utils::decrypt( get_option( 'mo_ldap_local_ldap_server_address' ) ) : '';
$ldap_server_port_number  = ( get_option( 'mo_ldap_local_ldap_port_number' ) ? get_option( 'mo_ldap_local_ldap_port_number' ) : '389' );
$ldaps_server_port_number = ( get_option( 'mo_ldap_local_ldaps_port_number' ) ? get_option( 'mo_ldap_local_ldaps_port_number' ) : '636' );

$dn          = ( get_option( 'mo_ldap_local_server_dn' ) ? $utils::decrypt( get_option( 'mo_ldap_local_server_dn' ) ) : '' );
$search_base = ( get_option( 'mo_ldap_local_search_base' ) ? $utils::decrypt( get_option( 'mo_ldap_local_search_base' ) ) : '' );

$mo_ldap_local_server_url_status = get_option( 'mo_ldap_local_server_url_status' ) ? get_option( 'mo_ldap_local_server_url_status' ) : '';
if ( ! empty( $server_url ) ) {
	if ( strcasecmp( $mo_ldap_local_server_url_status, 'VALID' ) === 0 ) {
		$mo_ldap_local_server_url_status = 'mo_ldap_local_valid_value';
	} elseif ( strcasecmp( $mo_ldap_local_server_url_status, 'INVALID' ) === 0 ) {
		$mo_ldap_local_server_url_status = 'mo_ldap_local_invalid_value';
	}
}

$mo_ldap_local_service_account_status = get_option( 'mo_ldap_local_service_account_status' ) ? get_option( 'mo_ldap_local_service_account_status' ) : '';
if ( strcasecmp( $mo_ldap_local_service_account_status, 'VALID' ) === 0 ) {
	$mo_ldap_local_service_account_status = 'mo_ldap_local_valid_value';
} elseif ( strcasecmp( $mo_ldap_local_service_account_status, 'INVALID' ) === 0 ) {
	$mo_ldap_local_service_account_status = 'mo_ldap_local_invalid_value';
}

$mo_ldap_local_user_mapping_status = get_option( 'mo_ldap_local_user_mapping_status' ) ? get_option( 'mo_ldap_local_user_mapping_status' ) : '';
if ( strcasecmp( $mo_ldap_local_user_mapping_status, 'VALID' ) === 0 ) {
	$mo_ldap_local_user_mapping_status = 'mo_ldap_local_valid_value';
} elseif ( strcasecmp( $mo_ldap_local_user_mapping_status, 'INVALID' ) === 0 ) {
	$mo_ldap_local_user_mapping_status = 'mo_ldap_local_invalid_value';
}

$mo_ldap_local_username_status = get_option( 'mo_ldap_local_username_status' ) ? get_option( 'mo_ldap_local_username_status' ) : '';
if ( strcasecmp( $mo_ldap_local_username_status, 'VALID' ) === 0 ) {
	$mo_ldap_local_username_status = 'mo_ldap_local_valid_value';
} elseif ( strcasecmp( $mo_ldap_local_username_status, 'INVALID' ) === 0 ) {
	$mo_ldap_local_username_status = 'mo_ldap_local_invalid_value';
}
delete_option( 'mo_ldap_local_username_status' );

$mo_ldap_local_pass_status = get_option( 'mo_ldap_local_password_status' ) ? get_option( 'mo_ldap_local_password_status' ) : '';
if ( strcasecmp( $mo_ldap_local_pass_status, 'VALID' ) === 0 ) {
	$mo_ldap_local_pass_status = 'mo_ldap_local_valid_value';
} elseif ( strcasecmp( $mo_ldap_local_pass_status, 'INVALID' ) === 0 ) {
	$mo_ldap_local_pass_status = 'mo_ldap_local_invalid_value';
}
delete_option( 'mo_ldap_local_password_status' );

$mo_ldap_local_ldap_username_attribute        = ! empty( get_option( 'mo_ldap_local_username_attribute' ) ) ? get_option( 'mo_ldap_local_username_attribute' ) : 'samaccountname';
$mo_ldap_local_custom_ldap_username_attribute = ! empty( get_option( 'custom_ldap_username_attribute' ) ) ? get_option( 'custom_ldap_username_attribute' ) : '';
$mo_ldap_local_custom_directory               = ! empty( get_option( 'mo_ldap_directory_server_custom_value' ) ) ? get_option( 'mo_ldap_directory_server_custom_value' ) : '';
?>
<div class="mo_ldap_outer" >
	<div class="mo_ldap_all_tabs">
		<div class="mo_ldap_tabs <?php echo strcasecmp( $active_step, '1' ) !== 0 ? 'mo_ldap_arrow_between_tabs_disabled' : ''; ?>">
			<a href="<?php echo esc_url( add_query_arg( array( 'step' => '1' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
				<?php
				if ( strcasecmp( $active_step, '1' ) !== 0 && strcasecmp( $mo_ldap_local_server_url_status, 'mo_ldap_local_valid_value' ) === 0 && strcasecmp( $mo_ldap_local_service_account_status, 'mo_ldap_local_valid_value' ) === 0 ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_success mo_ldap_local_circle_border_remove">
						<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'valid.svg' ); ?>" height="20px" width="20px" class="mo_ldap_config_success_img_light">
					</div>
					<?php
				} elseif ( strcasecmp( $active_step, '1' ) === 0 && strcasecmp( $mo_ldap_local_server_url_status, 'mo_ldap_local_valid_value' ) === 0 && strcasecmp( $mo_ldap_local_service_account_status, 'mo_ldap_local_valid_value' ) === 0 ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_success" >
						<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'valid.svg' ); ?>" height="20px" width="20px">
					</div>
					<?php
				} elseif ( strcasecmp( $active_step, '1' ) !== 0 && ! empty( $server_url ) ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_warn">
						<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'warn.svg' ); ?>" height="20px" width="20px">
					</div>
					<?php
				} elseif ( strcasecmp( $active_step, '1' ) === 0 ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_current">
						1						
					</div>
					<?php
				} else {
					?>
					<div class="mo_ldap_local_circle">
						1						
					</div>
					<?php
				}
				?>

				<div 
				<?php
				if ( strcasecmp( $active_step, '1' ) === 0 ) {
					?>
					class="mo_ldap_config_subtab_bold" 
					<?php
				} else {
					?>
					class="mo_ldap_config_subtag_normal" <?php } ?> >LDAP Connection Configuration</div>

			</a>
		</div>

		<div class="mo_ldap_arrow_between_tabs">
			<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'fwdarrow.svg' ); ?>" height="20px" width="20px"></span>
		</div>
		<div class="mo_ldap_tabs <?php echo strcasecmp( $active_step, '2' ) !== 0 ? 'mo_ldap_arrow_between_tabs_disabled' : ''; ?>">
			<a href="<?php echo esc_url( add_query_arg( array( 'step' => '2' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
				<?php
				if ( strcasecmp( $active_step, '2' ) !== 0 && strcasecmp( $mo_ldap_local_user_mapping_status, 'mo_ldap_local_valid_value' ) === 0 ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_success mo_ldap_local_circle_border_remove">
						<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'valid.svg' ); ?>" height="20px" width="20px" class="mo_ldap_config_success_img_light">
					</div>
					<?php
				} elseif ( strcasecmp( $active_step, '2' ) === 0 && strcasecmp( $mo_ldap_local_user_mapping_status, 'mo_ldap_local_valid_value' ) === 0 ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_success" >
						<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'valid.svg' ); ?>" height="20px" width="20px">
					</div>
					<?php
				} elseif ( strcasecmp( $active_step, '2' ) !== 0 && ! empty( $search_base ) ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_warn">
						<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'warn.svg' ); ?>" height="20px" width="20px">
					</div>
					<?php
				} elseif ( strcasecmp( $active_step, '2' ) === 0 ) {
					?>
					<div class="mo_ldap_local_circle mo_ldap_local_circle_current" style="width: 23px; height: 23px;">
						2
					</div>
					<?php
				} else {
					?>
					<div class="mo_ldap_local_circle">
						2
					</div>
					<?php
				}
				?>
				<div 
				<?php
				if ( strcasecmp( $active_step, '2' ) === 0 ) {
					?>
					class="mo_ldap_config_subtab_bold" 
					<?php
				} else {
					?>
					class="mo_ldap_config_subtag_normal" <?php } ?> >LDAP User Mapping Configuration</div>
			</a>
		</div>
		<div class="mo_ldap_arrow_between_tabs">
			<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'fwdarrow.svg' ); ?>" height="20px" width="20px"></span>
		</div>
		<div class="mo_ldap_tabs <?php echo strcasecmp( $active_step, '3' ) !== 0 ? 'mo_ldap_arrow_between_tabs_disabled' : ''; ?>">
			<a href="<?php echo esc_url( add_query_arg( array( 'step' => '3' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
				<div class="mo_ldap_local_circle <?php echo strcasecmp( $active_step, '3' ) === 0 ? 'mo_ldap_local_circle_current' : ''; ?>">
					3
				</div>
				<div 
				<?php
				if ( strcasecmp( $active_step, '3' ) === 0 ) {
					?>
					class="mo_ldap_config_subtab_bold" 
					<?php
				} else {
					?>
					class="mo_ldap_config_subtag_normal" <?php } ?> >Test Authentication</div>
			</a>
		</div>
	</div>
</div>
<?php
if ( strcasecmp( $active_step, '1' ) === 0 ) {
	$directory_server_value = ! empty( $directory_server_value ) ? $directory_server_value : 'Select';
	?>
	<div class="mo_ldap_outer mo_ldap_outer_box">
		<div class="mo_ldap_all_configurations">
			<form id="mo_ldap_connection_info_form" class="mo_ldap_form1" method="post" action="">
				<hr class="mo_ldap_local_hr_margin_remove">
				<?php wp_nonce_field( 'mo_ldap_local_save_config' ); ?>
				<input id="mo_ldap_local_ldap_server_port_no" type="hidden" name="mo_ldap_local_ldap_server_port_no" value="<?php echo esc_attr( $ldap_server_port_number ); ?>" />
				<input id="mo_ldap_local_ldaps_server_port_no" type="hidden" name="mo_ldap_local_ldaps_server_port_no" value="<?php echo esc_attr( $ldaps_server_port_number ); ?>" />

				<input id="mo_ldap_local_connection_configuration_form_action" type="hidden" name="option" value="mo_ldap_local_save_config" />

				<div class="mo_ldap_heading_container">
					<div class="mo_ldap_local_footer_btns_container mo_ldap_local_btns_upr_space_remove">
						<a 
							<?php
							echo 'href="' . esc_url(
								add_query_arg(
									array(
										'subtab' => 'ldap-config',
										'tab'    => 'default',
										'step'   => '2',
									),
									$filtered_current_page_url
								)
							)
							. '"';
							?>
							class="mo_ldap_local_unset_link_affect">
							<button type="button" class="mo_ldap_next_btn">
								Next
							</button>
						</a>
					</div>
				</div>
				<div> 
				<div class="mo_ldap_local_input_field_container mo_ldap_local_input_field_row" style=" margin-top: 15px;">
				<label for="mo_ldap_directory_server_value" class="mo_ldap_input_label_text mo_ldap_local_config_label">Directory Server <span style="color:red;">*</span></label>
					<select name="mo_ldap_directory_server_value" id="mo_ldap_directory_server_value" onchange="showCustomDirectoryInputField()" class="mo_ldap_local_standerd_input mo_ldap_select_directory_server mo_ldap_local_select_directory_server_max_width" required>
						<option class="mo_ldap_select_option" value="">Select</option>
						<option value="msad"
						<?php
						if ( strcmp( $directory_server_value, 'msad' ) === 0 ) {
							echo 'selected';}
						?>
						>Microsoft Active Directory</option>
						<option class="mo_ldap_select_option" value="openldap" 
						<?php
						if ( strcmp( $directory_server_value, 'openldap' ) === 0 ) {
							echo 'selected';}
						?>
						>OpenLDAP</option>
						<option class="mo_ldap_select_option" value="freeipa" 
						<?php
						if ( strcmp( $directory_server_value, 'freeipa' ) === 0 ) {
							echo 'selected';}
						?>
						>FreeIPA</option>
						<option class="mo_ldap_select_option" value="jumpcloud" 
						<?php
						if ( strcmp( $directory_server_value, 'jumpcloud' ) === 0 ) {
							echo 'selected';}
						?>
						>JumpCloud</option>
						<option class="mo_ldap_select_option" value="other" 
						<?php
						if ( strcmp( $directory_server_value, 'other' ) === 0 ) {
							echo 'selected';}
						?>
						>Other</option>
					</select>
				</div>
				<br>

				<div class="mo_ldap_conection_heading_font">
					<input class="mo_ldap_local_standerd_input mo_ldap_user_credentials" name="mo_ldap_directory_server_custom_value" value="<?php echo esc_attr( $mo_ldap_local_custom_directory ); ?>" placeholder="Microsoft Active Directory" type="text" id="mo_ldap_local_show_custom_directory" style="max-width: 17rem; <?php echo 'other' === $directory_server_value ? 'display:block;' : 'display:none;'; ?>"/>
				</div>

				<br>

				<div class="mo_ldap_local_input_field_container mo_ldap_local_input_field_row">
					<label for="mo_ldap_server" class="mo_ldap_input_label_text mo_ldap_local_config_label">LDAP Server Domain/IP<span style="color:red;">*</span></label>
					<div class="mo_ldap_server_align">
						<select name="mo_ldap_protocol" id="mo_ldap_protocol" class="  mo_ldap_select_server" >
							<?php
							if ( strcmp( $ldap_server_protocol, 'ldap' ) === 0 ) {
								?>
							<option value="ldap" selected>ldap</option>
							<option value="ldaps">ldaps</option>
								<?php
							} elseif ( strcmp( $ldap_server_protocol, 'ldaps' ) === 0 ) {
								?>
							<option value="ldap">ldap</option>
							<option value="ldaps" selected>ldaps</option>
								<?php
							}
							?>
						</select>	
						<input type="text" id="mo_ldap_server" name="ldap_server" placeholder="LDAP Server hostname or IP address" class="  mo_ldap_input_ip <?php echo esc_attr( $mo_ldap_local_server_url_status ); ?>" value="<?php echo esc_attr( $ldap_server_address ); ?>" required />
						<input type="text" id="mo_ldap_server_port_no" name="mo_ldap_server_port_no" placeholder="port number" class="  mo_ldap_input_port" value="<?php echo strcmp( $ldap_server_protocol, 'ldaps' ) === 0 ? esc_attr( $ldaps_server_port_number ) : esc_attr( $ldap_server_port_number ); ?>" required/>
					</div>
				</div>
				<div class="mo_ldap_local_input_paragraph_div"> 
					<p class="mo_ldap_local_input_paragraph" style="margin-top: 9px;"><span id="ldap_server_url"></span></p>
				</div>
				<div class="mo_ldap_local_server_details">
						<p>Select ldap or ldaps from the above dropdown list. Specify the host name for the LDAP server in the above text field. Edit the port number if you have custom port number.	
						</p>
				</div>
				<br>
				<div class="mo_ldap_local_input_field_container mo_ldap_local_input_field_row">
					<label for="dn" class="mo_ldap_input_label_text mo_ldap_local_config_label">Service Account Username <span style="color:red; width: 15%" >*</span></label>
					<input type="text" id="dn" name="dn" class="mo_ldap_local_standerd_input mo_ldap_user_credentials <?php echo esc_attr( $mo_ldap_local_service_account_status ); ?>" placeholder="Enter Username" value="<?php echo esc_attr( $dn ); ?>" />
				</div>
				<div class="mo_ldap_local_input_paragraph_div"> 
					<p class="mo_ldap_local_input_paragraph">Please enter the Distinguished Name (DN) or userPrincipalName of any user present in your LDAP server</p>
				</div>
				<br>
				<div class="mo_ldap_local_input_field_container mo_ldap_local_input_field_row">
					<label for="ldap_server_password_field" class="mo_ldap_input_label_text mo_ldap_local_config_label">Service Account Password <span style="color:red; width: 15%">*</span></label>
					<input type="password" id="ldap_server_password_field" name="admin_password" class="mo_ldap_local_standerd_input mo_ldap_user_credentials" placeholder="Enter Password" required/>
				</div>
				<div class="mo_ldap_local_input_paragraph_div"> 
					<p class="mo_ldap_local_input_paragraph">The above username and password will be used to establish the connection to your LDAP server</p>
				</div>

				<br>
				<div class="mo_ldap_position_relative mo_ldap_centerlized_btn">
					<input type="submit" class="mo_ldap_save_user_mapping" value="Test Connection & Save"/>
					<button type="button" id="mo_ldap_troubleshooting_btn1" class="mo_ldap_troubleshooting_btn">Troubleshooting</button>
				</div>
				<div id="mo_ldap_troubleshooting1" class="mo_ldap_local_hover_container mo_ldap_local_more_info_container3 mo_ldap_d_none">
					Are you having trouble connecting to your LDAP server from this plugin?
					<ol>
						<li>
							Please make sure that all the values entered are correct.
						</li>
						<li>
							If you are having firewall, open the firewall to allow incoming requests to your LDAP from your WordPress Server IP and port 389.
						</li>
					</ol>
					</div>
				<br>

				<div class="mo_ldap_local_add_more_server_centerlized">
					<div class="mo_ldap_local_add_more_server_btn" id="add_ldap_server_Btn" onclick="display_ldap_server_premium_box()"> Add Additional LDAP Server 
						<span class="mo_ldap_local_add_more_server_plus">+</span>  
					</div> 
				</div>

				<div class="mo_ldap_premium_version mo_ldap_d_none" id="mo_ldap_add_more_server_premium_box">
					<p class="mo_ldap_premium_version_p">Adding more LDAP server(s) is supported in premium version of the plugin</p>
					<div class="">
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now mo_ldap_local_unset_link_affect">
							<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Now
						</a>
					</div>
				</div>

				</div>
			</form>
		</div>
	</div>
	<?php
} elseif ( strcasecmp( $active_step, '2' ) === 0 ) {
	?>
	<div class="mo_ldap_outer mo_ldap_outer_box">
		<div class="mo_ldap_user_mapping_configs">
			<hr class="mo_ldap_local_hr_margin_remove">
			<div class="mo_ldap_heading_container"> 
				<div class="mo_ldap_local_footer_btns_container mo_ldap_local_btns_upr_space_remove"> 
					<a 
						<?php
						echo 'href="' . esc_url(
							add_query_arg(
								array(
									'subtab' => 'ldap-config',
									'tab'    => 'default',
									'step'   => '1',
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
									'subtab' => 'ldap-config',
									'tab'    => 'default',
									'step'   => '3',
								),
								$filtered_current_page_url
							)
						)
						. '"';
						?>
						class="mo_ldap_local_unset_link_affect">
						<button type="button" class="mo_ldap_next_btn">Next</button></a>
				</div>
			</div>
			<form id="mo_ldap_user_mapping_form" name="f" method="post" action="">
				<?php wp_nonce_field( 'mo_ldap_local_save_user_mapping' ); ?>
				<input id="mo_ldap_local_user_mapping_configuration_form_action" type="hidden" name="option" value="mo_ldap_local_save_user_mapping" />
				<div class="mo_ldap_user_mapping_search_base">
					<div class="mo_ldap_local_input_field_container mo_ldap_local_searh_base_container" >
						<div class="mo_ldap_input_label_text mo_ldap_local_config_label" style="display: flex;">Search Base:<span style="color:red;">*</span></div>
						<div class="mo_ldap_search_base_details" >
							<div class="mo_ldap_search_base_details_inner">
								<input type="text" id="search_base" name="search_base" placeholder="dc=domain,dc=com" class="mo_ldap_local_standerd_input mo_ldap_local_input_field1 <?php echo esc_attr( $mo_ldap_local_user_mapping_status ); ?>" style="width:58%;" value="<?php echo esc_attr( $search_base ); ?>" required/>
								<div id="searchbases" class="mo_ldap_select_search_base mo_ldap_search_base mo_ldap_user_mapping_tem">
									Select Search Base   
									<svg style="margin-left: 10px" fill="#0076E1" height="20px" width="20px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 490.4 490.4" xml:space="preserve" stroke="#0076E1"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M484.1,454.796l-110.5-110.6c29.8-36.3,47.6-82.8,47.6-133.4c0-116.3-94.3-210.6-210.6-210.6S0,94.496,0,210.796 s94.3,210.6,210.6,210.6c50.8,0,97.4-18,133.8-48l110.5,110.5c12.9,11.8,25,4.2,29.2,0C492.5,475.596,492.5,463.096,484.1,454.796z M41.1,210.796c0-93.6,75.9-169.5,169.5-169.5s169.6,75.9,169.6,169.5s-75.9,169.5-169.5,169.5S41.1,304.396,41.1,210.796z"></path> </g> </g></svg>
								</div>
							</div>
						</div>
					</div>
					<div id="mo_ldap_local_user_mapping_notice" class="mo_ldap_local_user_mapping_notice"> 
						<p>Enter the distinguished name (DN) of the search base where users are present.</p>
						<div id="mo_ldap_local_info" class="mo_ldap_notice_dropdown_btn">
							Show More
							<svg id="mo_ldap_local_doc_dropdown" style="margin-left: 5%;" viewBox="0 0 448 512" height="15px" width="15px" class="mo_ldap_local_reverse_rotate">
								<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"></path>
							</svg>
						</div>			

					</div>

					<div id="search_base_info_div" class="mo_ldap_local_more_info_div mo_ldap_d_none">
						<div class="mo_ldap_local_user_mapping_notice_more_info_img">
							<div id="mo_ldap_local_info_reverse" class="mo_ldap_notice_dropdown_btn">
								Show Less
								<svg id="mo_ldap_local_doc_dropdown" style="margin-left: 5%; transform: rotate(180deg);" viewBox="0 0 448 512" height="15px" width="15px" class="mo_ldap_local_reverse_rotate">
									<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"></path>
								</svg>
							</div>	
						</div>
						<ul style="margin-top: 0px;">
							<li>&bull; This is the LDAP Tree under which we will search for the users for authentication. If we are not able to find a user in LDAP it means they are not present in this search base or any of its sub trees. They may be present in some other search base.</li>
							<li>&bull; Provide the distinguished name of the Search Base object. <strong>eg. cn=Users,dc=domain,dc=com.</strong></li>
						</ul>
						<div class="mo_ldap_premium_version">
							<p class="mo_ldap_premium_version_p">Multiple search bases are supported in premium version of the plugin.</p>
							<div class="">
								<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now mo_ldap_local_unset_link_affect">
									<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Now
								</a>
							</div>
						</div>
					</div>
				</div>

				<div class="mo_ldap_local_custom_search_filter_box"> 
					<div class="mo_ldap_local_input_container mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start mo_ldap_local_custom_search_filter_box_inner">
						<input type="checkbox" class="mo_ldap_local_toggle_switch_hide" id="enable_custome_search_filter" name="enable_custome_search_filter" onclick="show_custom_search_filter()" value="0"/>
						<label for="enable_custome_search_filter" class="mo_ldap_local_toggle_switch"></label>
						<label for="enable_custome_search_filter" class="mo_ldap_local_d_inline mo_ldap_local_bold_label" style="font-size: 15px;">
							Enable custom search filter 
						</label>
					</div>
				</div>

				<div id="mo_ldap_multiple_attr_toggle" class="mo_ldap_d_none mo_ldap_local_tooltip" style="display: flex; align-items: center; margin: 2%;">
					<div class="mo_ldap_input_label_text" style="font-size: 15px; margin-left: 10px; width: 28.5%;">Custom Search Filter:</div>
					<input id="mo_ldap_custom_search_filter_input_id" class="mo_ldap_custom_search_filter_input" value="" disabled/>
					<span class="mo_ldap_local_tooltiptext" style="bottom: 2.7rem; font-size: 13px; margin-left: -7%"> <img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"> Premium Feature</span> 
				</div>

				<div id="mo_ldap_username_attr_container" class="mo_ldap_custom_search_filter_parent" style="position:relative;">
					<div class="mo_ldap_input_label_text mo_ldap_local_username_attribute_label" style="">Username Attribute: <span style="color:red;">*</span></div>
					<div class="mo_ldap_username_attr_option_container">
					<div id="mo_ldap_search_filter_ldap" class="mo_ldap_username_attr_option_container_inner">	   
						<div class="mo_ldap_multiselect_user">
							<div class="mo_ldap_position_relative" onclick="showCheckboxes('user')">
								<div class="mo_ldap_overSelect">
								</div>
							</div>
							<select id="ldap_username_attribute" name="ldap_username_attribute" class="mo_ldap_local_standerd_input mo_ldap_local_select_username_att " onchange="showCustomAttributeInputField()">
								<option value="samaccountname" <?php echo 'samaccountname' === $mo_ldap_local_ldap_username_attribute ? 'selected' : ''; ?>>sAMAccountName</option>
								<option value="mail" <?php echo 'mail' === $mo_ldap_local_ldap_username_attribute ? 'selected' : ''; ?>>mail</option>
								<option value="userprincipalname" <?php echo 'userprincipalname' === $mo_ldap_local_ldap_username_attribute ? 'selected' : ''; ?>>userPrincipalName</option>
								<option value="uid" <?php echo 'uid' === $mo_ldap_local_ldap_username_attribute ? 'selected' : ''; ?>>uid</option>
								<option value="cn" <?php echo 'cn' === $mo_ldap_local_ldap_username_attribute ? 'selected' : ''; ?>>cn</option>
								<option value="custom_ldap_attribute" <?php echo 'custom_ldap_attribute' === $mo_ldap_local_ldap_username_attribute ? 'selected' : ''; ?>>Custom attribute</option>
							</select>
							<div class="mo_ldap_custom_attribute_input" id="mo_ldap_local_show_custom_attr" style=" <?php echo 'custom_ldap_attribute' === $mo_ldap_local_ldap_username_attribute ? 'display:inline-flex;' : 'display:none;'; ?>">
								<span class="mo_ldap_custom_attr_label">Custom Attribute:</span><input class="mo_ldap_local_input_field1" name="custom_ldap_username_attribute" value="<?php echo esc_attr( $mo_ldap_local_custom_ldap_username_attribute ); ?>" placeholder="sAMAccountName" class="mo_ldap_no_checkbox_user" type="text" id="mo_ldap_local_show_custom_field" style="width:47%;" />
							</div>
						</div>
					</div>
				</div>
				</div>

				<div id="mo_ldap_local_user_mapping_notice2" class="mo_ldap_local_user_mapping_notice"> 
					<p>Select the LDAP attribute to be used as WordPress username.</p>			
					<div id="mo_ldap_local_info2" class="mo_ldap_notice_dropdown_btn">
						Show More
						<svg id="mo_ldap_local_doc_dropdown" style="margin-left: 5%;" viewBox="0 0 448 512" height="15px" width="15px" class="mo_ldap_local_reverse_rotate">
							<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"></path>
						</svg>
					</div>		 		
				</div>

				<div id="search_base_info_div2" class="mo_ldap_local_more_info_div mo_ldap_d_none">
					<div class="mo_ldap_local_user_mapping_notice_more_info_img">
						<div id="mo_ldap_local_info_reverse2" class="mo_ldap_notice_dropdown_btn">
							Show Less
							<svg id="mo_ldap_local_doc_dropdown" style="margin-left: 5%; transform: rotate(180deg);" viewBox="0 0 448 512" height="15px" width="15px" class="mo_ldap_local_reverse_rotate">
								<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"></path>
							</svg>
						</div>		 		
					</div>
					This field is important for two reasons.<br>
					1. While searching for users, this is the attribute that is going to be matched to see if the user exists.<br>
					2. Users in WordPress will be created using the selected username attribute value.<br>
					<br>
					If you want your users to login with their username or firstname.lastname or email - you need to specify those options in this field. e.g. <strong> LDAP_ATTRIBUTE</strong>. Replace <strong>&lt;LDAP_ATTRIBUTE&gt;</strong> with the attribute where your username is stored. <br>
					Some common attributes are:
					<ol>
					<table aria-hidden="true">
						<tr><td>logon name</td><td><strong>sAMAccountName, userPrincipalName</strong><br/></td></tr>
						<tr><td>email</td><td><strong>mail</strong></td></tr>
						<tr><td style="width:50%">common name</td><td><strong>cn</strong></td></tr>
						<tr><td>Custom attribute where you store your WordPress usernames</td> <td><strong>Custom attribute</strong></td></tr>
					</table><br>
				</div>

				<div>
					<div class="mo_ldap_local_username_attribute_container">
				</div>

				<div class="mo_ldap_premium_version ">
					<p class="mo_ldap_premium_version_p">Logging in with <span style="font-weight: bold;font-style: italic;">multiple attributes</span> and <span style="font-weight: bold;font-style: italic;">using Custom search filters</span> are supported in the Premium Version of the Plugin</p>
					<div>
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now mo_ldap_local_unset_link_affect">
							<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Now
						</a>
					</div>
				</div>

				<div class="mo_ldap_position_relative mo_ldap_centerlized_btn">
					<input type="submit" class="mo_ldap_user_mapping_btn1" value="Save User Mapping" />
					<button type="button" id="mo_ldap_troubleshooting_btn2" class="mo_ldap_user_mapping_btn2">Troubleshooting</button>
				</div>
				<div id="mo_ldap_troubleshooting2" class="mo_ldap_local_hover_container mo_ldap_local_more_info_container3 mo_ldap_d_none mo_ldap_local_font_weight_normal">
					Are you having trouble connecting to your LDAP server from this plugin?
					<ol>
						<li>
							The search base URL is typed incorrectly. Please verify if that search base is present.
						</li>
						<li>
							User is not present in that search base. The user may be present in the directory but in some other tree and you may have entered a tree where this users is not present.
						</li>
						<li>
							Search filter is incorrect - User is present in the search base but the username is mapped to a different attribute in the search filter. E.g. you may be logging in with username and may have mapped it to the email attribute. So this wont work. Please make sure that the right attribute is mentioned in the search filter (with which you want the mapping to happen)
						</li>
						<li>
							Please make sure that the user is present and test with the right user.
						</li>
					</ol>
				</div>
			</form>
		</div>

		<script>
			jQuery("#searchbases").click(function (){
				showsearchbaselist();
			});
			jQuery('#mo_ldap_troubleshooting_btn2').click(function(){
				jQuery('#mo_ldap_troubleshooting2').toggleClass("mo_ldap_d_none");
			});
			function showsearchbaselist() {
				var nonce = "<?php echo esc_js( wp_create_nonce( 'searchbaselist_nonce' ) ); ?>";
				var myWindow =   window.open('<?php echo esc_js( site_url() ); ?>' + '/?option=searchbaselist' + '&_wpnonce=' + nonce, "Search Base Lists", "width=600, height=600");
			}
			jQuery('#mo_ldap_local_info').click(function(){
				jQuery('#mo_ldap_local_dropdown').toggleClass("mo_ldap_local_rotate");
				jQuery('#mo_ldap_local_dropdown').toggleClass("mo_ldap_local_reverse_rotate");
				jQuery('#search_base_info_div').toggleClass("mo_ldap_d_none");
				jQuery('#mo_ldap_local_user_mapping_notice').toggleClass("mo_ldap_d_none");
			});

			jQuery('#mo_ldap_local_info_reverse').click(function() {
				jQuery('#mo_ldap_local_user_mapping_notice').toggleClass("mo_ldap_d_none");
				jQuery('#search_base_info_div').toggleClass("mo_ldap_d_none");
			})

			jQuery('#mo_ldap_local_info_reverse2').click(function() {
				jQuery('#mo_ldap_local_user_mapping_notice2').toggleClass("mo_ldap_d_none");
				jQuery('#search_base_info_div2').toggleClass("mo_ldap_d_none");
			})

			jQuery('#mo_ldap_local_info2').click(function(){
				jQuery('#mo_ldap_local_dropdown2').toggleClass("mo_ldap_local_rotate");
				jQuery('#mo_ldap_local_dropdown2').toggleClass("mo_ldap_local_reverse_rotate");
				jQuery('#search_base_info_div2').toggleClass("mo_ldap_d_none");
				jQuery('#mo_ldap_local_user_mapping_notice2').toggleClass("mo_ldap_d_none");
			});
		</script>
	</div>
	<?php
} elseif ( strcasecmp( $active_step, '3' ) === 0 ) {
	?>
	<!-- Third Page -->
	<div class="mo_ldap_outer mo_ldap_outer_box">
		<div class="mo_ldap_test_authentication">
			<hr class="mo_ldap_local_hr_margin_remove">
			<div class="mo_ldap_heading_container">
				<div class="mo_ldap_local_footer_btns_container mo_ldap_local_btns_upr_space_remove">
					<a 
						<?php
						echo 'href="' . esc_url(
							add_query_arg(
								array(
									'subtab' => 'ldap-config',
									'tab'    => 'default',
									'step'   => '2',
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
									'subtab' => 'role-mapping',
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
			<form name="mo_ldap_test_auth_form" class="mo_ldap_test_authentication_form" method="post" action="">
				<?php wp_nonce_field( 'mo_ldap_local_test_auth' ); ?>
				<input type="hidden" name="option" value="mo_ldap_local_test_auth" />
				<div class="mo_ldap_local_test_auth_username_div"> 
					<label for="test_username" class="mo_ldap_input_label_text mo_ldap_local_config_label">Username: <span style="color:red; font-size: 15px;">*</span></label>
					<input type="text" name="test_username" class="mo_ldap_local_standerd_input mo_ldap_test_authentication_details_input <?php echo esc_attr( $mo_ldap_local_username_status ); ?>"
						<?php
						if ( isset( $_POST['test_username'] ) && check_admin_referer( 'mo_ldap_local_test_auth' ) ) {
							echo 'value=' . esc_attr( sanitize_text_field( wp_unslash( $_POST['test_username'] ) ) );
						}
						?>
						placeholder="Enter Username" required >
				</div>
				<br>
				<div class="mo_ldap_local_test_auth_username_div"> 
					<label for="test_password" class="mo_ldap_input_label_text mo_ldap_local_config_label">Password: <span style="color:red; font-size: 15px">*</span></label>
					<input type="password" name="test_password" class="mo_ldap_local_standerd_input  mo_ldap_test_authentication_details_input <?php echo esc_attr( $mo_ldap_local_pass_status ); ?>" placeholder="Enter Password" required>
				</div>
				<br>
				<div class="mo_ldap_position_relative mo_ldap_centerlized_btn" >
					<input type="submit" class="mo_ldap_test_authentication_btn1" value="Test Authentication"/>
					<button type="button" id="mo_ldap_troubleshooting_btn3" class="mo_ldap_test_authentication_btn2 mo_ldap_wireframe_btn">Troubleshooting</button>
				</div>
				<div id="mo_ldap_troubleshooting3" class="mo_ldap_local_hover_container mo_ldap_local_more_info_container3 mo_ldap_d_none">
					User is not getting authenticated? Check the following:
					<ol>
						<li>
							The username-password you are entering is correct.
						</li>
						<li>
							The user is not present in the search bases you have specified against SearchBase(s) above.
						</li>
						<li>
							Your Search Filter may be incorrect and the username mapping may be to an LDAP attribute other than the ones provided in the Search Filter
						</li>
					</ol>
				</div>
			</form>

		</div>
	</div>
	<script>
		jQuery('#mo_ldap_troubleshooting_btn3').click(function(){
			jQuery('#mo_ldap_troubleshooting3').toggleClass("mo_ldap_d_none");
		});
	</script>
	<?php
}
?>
