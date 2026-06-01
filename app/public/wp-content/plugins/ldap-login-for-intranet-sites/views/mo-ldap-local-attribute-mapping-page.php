<?php
/**
 * Display Attribute Mapping Page.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class="mo_ldap_local_attribute_mapping_outer">	
		<div class="mo_ldap_role_local_outer">
			<form name="f" method="post" id="attribute_config_form">
				<?php wp_nonce_field( 'mo_ldap_save_attribute_config' ); ?>	
				<input type="hidden" name="option" value="mo_ldap_save_attribute_config"/>	
				<div>
					<div class="mo_ldap_heading_container mo_ldap_local_heading_right">
						<div class="mo_ldap_local_footer_btns_container mo_ldap_local_btns_upr_space_remove">
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
								<button type="button" class="mo_ldap_back_btn">Back</button>
							</a>
							<a
								<?php
								echo 'href="' . esc_url(
									add_query_arg(
										array(
											'subtab' => 'login-settings',
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

					<div class="mo_ldap_local_attribute_mapping_premium_feature_all_feature">
						<div class="mo_ldap_premium_feature_each_feature">
							<div class="mo_ldap_premium_freature_input_common">
								<label for="ldap_intranet_attribute_mail_name" class="mo_ldap_input_label_text">Email Attribute <span style="color:red;">*</span></label>
							</div>
							<div class="mo_ldap_premium_freature_input_common mo_ldap_premium_feature_input">
								<input type="text" id="ldap_intranet_attribute_mail_name" name="mo_ldap_email_attribute" placeholder="Enter Email Attribute" class="mo_ldap_local_input_field1 mo_ldap_local_attribute_mapping_input" required value="<?php echo esc_attr( get_option( 'mo_ldap_local_email_attribute' ) ); ?>">
							</div>
						</div>

						<div class="mo_ldap_premium_feature_each_feature">
							<div class="mo_ldap_premium_freature_input_common">
								<label for="mo_ldap_email_domain" class="mo_ldap_input_label_text">Email Domain</label>
							</div>
							<div class="mo_ldap_premium_freature_input_common mo_ldap_premium_feature_input">
								<input type="text" pattern="[a-z0-9.-]+\.[a-z]{2,}$" title="Please Enter Valid Domain Name. Ex. miniorange.com" id="mo_ldap_email_domain" name="mo_ldap_email_domain" placeholder="example.com" class="mo_ldap_local_input_field1 mo_ldap_local_attribute_mapping_input" value="<?php echo esc_attr( get_option( 'mo_ldap_local_email_domain' ) ); ?>" >
							</div>
						</div>
						<div class="mo_ldap_local_attribute_mapping_email_para">
							<p class="mo_ldap_local_input_paragraph">Set user email to <span style="font-weight: bold;">username@email_domain</span> in WordPress, if the "mail" attribute is not set in  LDAP directory.</p>
						</div>

					</div>
				</div>

				<div id="mo_ldap_local_attribute_mapping_premium_box">
					<div style="top: 20%;height: 50%;right: 5%;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
						<div><h1>Premium Plan</h1></div>
						<div style="font-size: 16px;padding: 10px;">This is available in premium version of the plugin</div>
						<div class="">
							<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
								<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
							</a>
						</div>
					</div>

					<div id="mo_ldap_local_premium_attribute_mapping" class="mo_ldap_local_attribute_mapping_premium_feature_all_feature mo_ldap_local_premium_feature_box">
						<div class="mo_ldap_premium_feature_each_feature">
							<div class="mo_ldap_premium_freature_input_common mo_ldap_local_attribute_mapping_fields"><label for="phone_attribute" class="mo_ldap_input_label_text">Phone Attribute</label></div>
							<div class="mo_ldap_premium_freature_input_common mo_ldap_premium_feature_input mo_ldap_local_tooltip"><input type="text" id="phone_attribute" name="phone_attribute" placeholder="Enter Phone Attribute" class="mo_ldap_local_disabled_input_field" disabled><span class="mo_ldap_local_tooltiptext"> <img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"> Premium Feature</span></div>
						</div>
						<div class="mo_ldap_premium_feature_each_feature">
							<div class="mo_ldap_premium_freature_input_common mo_ldap_local_attribute_mapping_fields"><label for="first_name_attribute" class="mo_ldap_input_label_text">First Name Attribute</label></div>
							<div class="mo_ldap_premium_freature_input_common mo_ldap_premium_feature_input mo_ldap_local_tooltip"><input type="text" id="first_name_attribute" name="first_name_attribute" placeholder="Enter First Name Attribute" class="mo_ldap_local_disabled_input_field" disabled> <span class="mo_ldap_local_tooltiptext"> <img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"> Premium Feature</span> </div>
						</div>
						<div class="mo_ldap_premium_feature_each_feature">
							<div class="mo_ldap_premium_freature_input_common mo_ldap_local_attribute_mapping_fields"><label for="last_name_attribute" class="mo_ldap_input_label_text">Last Name Attribute</label></div>
							<div class="mo_ldap_premium_freature_input_common mo_ldap_premium_feature_input mo_ldap_local_tooltip"><input type="text" id="last_name_attribute" name="last_name_attribute" placeholder="Enter Last Name Attribute" class="mo_ldap_local_disabled_input_field" disabled><span class="mo_ldap_local_tooltiptext"> <img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"> Premium Feature</span></div>
						</div>
						<div class="mo_ldap_premium_feature_each_feature">
							<div class="mo_ldap_premium_freature_input_common mo_ldap_local_attribute_mapping_fields"><label for="display_name_attribute" class="mo_ldap_input_label_text">Display Name Attribute</label></div>
							<div class="mo_ldap_premium_freature_input_common mo_ldap_premium_feature_input mo_ldap_local_tooltip"><input type="text" id="display_name_attribute" name="display_name_attribute" placeholder="Enter Display Name Attribute" class="mo_ldap_local_disabled_input_field" disabled><span class="mo_ldap_local_tooltiptext"> <img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"> Premium Feature</span></div>
						</div>
						<div class="mo_ldap_premium_feature_each_feature">
							<div class="mo_ldap_premium_freature_input_common mo_ldap_local_attribute_mapping_fields"><label for="nickname_attribute" class="mo_ldap_input_label_text">Nickname Attribute</label></div>
							<div class="mo_ldap_premium_freature_input_common mo_ldap_premium_feature_input mo_ldap_local_tooltip"><input type="text" id="nickname_attribute" name="nickname_attribute" placeholder="Enter Nickname Attribute" class="mo_ldap_local_disabled_input_field" disabled><span class="mo_ldap_local_tooltiptext"> <img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"> Premium Feature</span></div>
						</div>

						<br></br>
						<h3>Add custom Attributes</h3>
						<p class="mo_ldap_local_custom_attri_msg">Enter custom LDAP attributes you wish to be included in the user profile</p>

						<div class="mo_ldap_local_adding_custom_attributes">
							<div class="mo_ldap_local_attribute_input mo_ldap_local_tooltip">
								Custom Attribute Name
								<span class="mo_ldap_local_tooltiptext mo_ldap_local_custom_attribute_tooltiptext"> <img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="15px" width="15px"> Premium Feature</span>
							</div>
							<div class="mo_ldap_local_iod">+</div>
							<div class="mo_ldap_local_iod">-</div>
						</div>
						<br><br>
					</div>
				</div>
				<div class="mo_ldap_local_attr_mapping_left_align"> 
					<input type="submit" class="mo_ldap_save_user_mapping " value="Save Configuration" />
				</div>
			</form>
		</div>

		<div class="mo_ldap_local_outer">
			<h3>Test Attribute Configuration</h3>
			<form method="post" id="attribiteconfigtest">
				<input type="hidden" name="option" value="mo_ldap_test_attribute_configuration" />
				<br>
				<div class="mo_ldap_display_flex_elements mo_ldap_local_test_attr_username_box">
					<label for="username" class="mo_ldap_input_label_text mo_ldap_local_config_label">Username <span style="color:red;">*</span></label>
					<input type="text" id="mo_ldap_username" name="mo_ldap_username" placeholder="Enter Username" class="mo_ldap_enter_username"required>
				</div>
				<p class="mo_ldap_local_input_paragraph mo_ldap_local_test_mapping_para">Enter LDAP username to test attribute configuration</p>
				<br>
				<?php
				$search_base_string = get_option( 'mo_ldap_local_search_base' ) ? $utils::decrypt( get_option( 'mo_ldap_local_search_base' ) ) : '';
				$search_bases       = explode( ';', $search_base_string );
				?>
				<div class="mo_ldap_local_attr_mapping_left_align"> 
				<input type="submit" class="mo_ldap_save_user_mapping" value="Test Configuration" 
					<?php
					if ( empty( $search_bases[0] ) ) {
						echo 'disabled';
					}
					?>
				>
				</div>
			</form>
		</div>
		<script>
			function testConfiguration(){

				var nonce = "<?php echo esc_attr( wp_create_nonce( 'testattrconfig_nonce' ) ); ?>";

				var username = jQuery("#mo_ldap_username").val();
				var myWindow = window.open('<?php echo esc_url( site_url() ); ?>' + '/?option=testattrconfig&user='+username + '&_wpnonce='+nonce, "Test Attribute Configuration", "width=700, height=600");
			}
		</script>
</div>
