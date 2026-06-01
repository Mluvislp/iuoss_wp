<?php
/**
 * Display plugin header.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class="mo_ldap_local_main_head" >
	<div class="mo_ldap_title_container">
		<div class="mo_ldap_local_title">
			<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'logo.png' ); ?>" style="height:65px; width:65px;"><div class="mo_ldap_title_text"> LDAP/Active Directory Integration </div>
		</div>
	</div>
	<div class="mo_ldap_local_header_buttons_section">
		<div class="mo_ldap_local_column_flex_container mo_ldap_local_gap_20 mo_ldap_local_vertical_line">
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), htmlentities( $filtered_current_page_url ) ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_rounded_rectangular_buttons mo_ldap_local_horizontal_flex_container">Premium Pricing <span class="mo_ldap_free_trial"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'pricing.svg' ); ?>" height="20px" width="20px"></span></a>

			<div class="mo_ldap_local_rounded_rectangular_buttons mo_ldap_local_horizontal_flex_container">
				<div class="mo_ldap_cursor_pointer mo_ldap_local_custom_requirement_btn" data-id="mo_ldap_local_custom_requirements_box" onclick="mo_ldap_local_popup_card_clicked(this, '')" >Custom Requirements <span><img width="19px" height="21px" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'requirement.png' ); ?>" /></span></div>
			</div>
		</div>
		<div class="mo_ldap_local_help_links mo_ldap_local_help_links_div">
		<div><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'faqs' ), $request_uri ) ); ?>" class="mo_ldap_local_unset_link_affect">FAQs</a></div>
			<div id="mo_ldap_local_documentation_section" class="mo_ldap_position_relative">
				<div id="mo_ldap_local_documentation_dropdown" class="mo_ldap_local_horizontal_flex_container mo_ldap_local_content_start mo_ldap_cursor_pointer">
					<div>Documentation</div> 
					<svg id="mo_ldap_local_doc_dropdown" style="margin-left: 5%;" viewBox="0 0 448 512" height="15px" width="15px" fill="#fff" class="mo_ldap_local_reverse_rotate">
						<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/>
					</svg>
				</div>
				<div id="mo_ldap_local_absolute_documentation_box">
					<div class="mo_ldap_local_documentation_box">
						<div><a href="https://plugins.miniorange.com/step-by-step-guide-for-wordpress-ldap-login-plugin" target="_blank" class="mo_ldap_local_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'doc.svg' ); ?>" height="20px" width="20px"></span><div>Setup LDAP/AD Plugin</div></a></div>
						<div><a href="https://www.youtube.com/watch?v=jGhUxqkXgwc" target="_blank" class="mo_ldap_local_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'video.svg' ); ?>" height="20px" width="20px"></span><div>LDAP/AD Plugin Setup</div></a></div>
						<div><a href="https://www.miniorange.com/guide-to-setup-ldaps-on-windows-server" target="_blank" class="mo_ldap_local_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'doc.svg' ); ?>" height="20px" width="20px"></span><div>Setup LDAPS connection</div></a></div>
						<div><a href="https://youtu.be/r0pnB2d0QP8" target="_blank" class="mo_ldap_local_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'video.svg' ); ?>" height="20px" width="20px"></span><div>Premium Plugin Features</div></a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="mo_ldap_local_column_flex_container mo_ldap_local_gap_20">
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'account' ), $request_uri ) ); ?>" class="mo_ldap_local_my_account_styles mo_ldap_local_horizontal_flex_container mo_ldap_local_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'account.svg' ); ?>" height="18px" width="18px"></span> My Account</a>
			<div class="mo_ldap_local_support_icons_container">
				<div class="mo_ldap_local_support_icon mo_ldap_local_horizontal_flex_container" data-id="mo_ldap_local_contact_us_box" onclick="mo_ldap_local_popup_card_clicked(this, '')" >
				Contact Us
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'mail.svg' ); ?>" height="18px" width="18px">
				</span></div>
			</div>
		</div>
	</div>
</div>
<?php

if ( ! $utils::is_extension_installed( 'ldap' ) ) {
	?>
		<div style="padding:20px;border-radius: 8px;margin: 10px;" class="notice notice-error is-dismissible">
			<span style="color:#FF0000">Warning: PHP LDAP extension is not installed or disabled.</span>
			<div id="help_ldap_warning_title" class="mo_ldap_title_panel">
				<p><a target="_blank" style="cursor: pointer;">Click here for instructions to enable it.</a></p>
			</div>
			<div hidden="" style="padding: 2px 2px 2px 12px" id="help_ldap_warning_desc" class="mo_ldap_help_desc">
			<ul>
				<li style="font-size: large; font-weight: bold">Step 1 </li>
				<li style="font-size: medium; font-weight: bold">Loaded configuration file : <?php echo esc_attr( php_ini_loaded_file() ); ?></li>
				<li style="list-style-type:square;margin-left:20px">Open php.ini file from above file path</strong></li><br/>
				<li style="font-size: large; font-weight: bold">Step 2</li>
				<li style="font-weight: bold;color: #C31111">For Windows users using Apache Server</li>
				<li style="list-style-type:square;margin-left:20px">Search for <strong>"extension=php_ldap.dll"</strong> in php.ini file. Uncomment this line, if not present then add this line in the file and save the file.</li>
				<li style="font-weight: bold;color: #C31111">For Windows users using IIS server</li>
				<li style="list-style-type:square;margin-left:20px">Search for <strong>"ExtensionList"</strong> in the php.ini file. Uncomment the <strong>"extension=php_ldap.dll"</strong> line, if not present then add this line in the file and save the file.</li>
				<li style="font-weight: bold;color: #C31111">For Linux users</li>
				<ul style="list-style-type:square;margin-left: 20px">
				<li style="margin-top: 5px">Install php ldap extension (If not installed yet)
					<ul style="list-style-type:disc;margin-left: 15px;margin-top: 5px">
						<li>For Ubuntu/Debian, the installation command would be <strong>sudo apt-get -y install php-ldap</strong></li>
						<li>For RHEL based systems, the command would be <strong>yum install php-ldap</strong></li></ul></li>
				<li>Search for <strong>"extension=php_ldap.so"</strong> in php.ini file. Uncomment this line, if not present then add this line in the file and save the file.</li></ul><br/>
				<li style="margin-top: 5px;font-size: large; font-weight: bold">Step 3</li>
				<li style="list-style-type:square;margin-left:20px">Restart your server. After that refresh the "LDAP/AD" plugin configuration page.</li>
				</ul>
				<strong>For any further queries, please contact us.</strong>
			</div>
		<p style="color:black">If your site is hosted on <strong>Shared Hosting</strong> platforms like Bluehost, DreamHost, SiteGround, Flywheel etc and you are not able to enable the extension then you can use our <a href="https://wordpress.org/plugins/miniorange-wp-ldap-login/" target="_blank" rel="noopener" style="cursor: pointer;">Active Directory/LDAP Integration for Cloud & Shared Hosting Platforms</a> plugin.</p>
		</div>
	<?php
}
if ( ! $utils::is_extension_installed( 'openssl' ) ) {
	?>
		<div class="notice notice-error is-dismissible">
		<p style="color:#FF0000">(Warning: <a target="_blank" rel="noopener" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled)</p>
		</div>
	<?php
}
?>

<div id="mo_ldap_local_custom_requirements_box" class="mo_ldap_local_popup_box mo_ldap_local_trial_popup mo_ldap_d_none">
	<div class="mo_ldap_local_cross_button" type="button" data-id="mo_ldap_local_custom_requirements_box" onclick="mo_ldap_local_popup_card_cancel_remove(this)">+</div>
	<div class="mo_ldap_local_popup_page">
		<div class="mo_ldap_local_popup_page_details">
			<div class="mo_ldap_local_popup_page_para mo_ldap_local_small_font">
				Looking for some other features <br> or having Custom Requirements?
				<br>
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'LargeArrow.svg' ); ?>" width="150px"></span>
			</div>

			<p style="padding: 5%;">Reach out to us with your requirements and we will get back to you at the earliest.</p>

			<div class="mo_ldap_local_popup_page_note">
				<div style="width: fit-content;border-bottom: 2px solid #FF9F38;margin: auto;padding-bottom: 5px; color:#000;">WE ARE HAPPY TO HEAR FROM YOU</div>
			</div>
		</div>

		<div class="mo_ldap_local_popup_page_input">
			<form name="mo_ldap_custom_requirement_form" method="post" id="mo_ldap_custom_requirement_form">
				<?php wp_nonce_field( 'mo_ldap_local_custom_requirements_req_nonce' ); ?>	
				<input type="hidden" name="option" value="mo_ldap_local_custom_requirements_req"/>
				<div class="trial_page_input_email">
					<label for="mo_ldap_local_custom_requirements_email" style="display:block;" class="mo_ldap_input_label_text">Email</label>
					<input name="mo_ldap_local_custom_requirements_email" id="mo_ldap_local_custom_requirements_email" type="email" placeholder="Enter your email" class="trial_page_input_email_text mo_ldap_local_full_width_input mo_ldap_local_custom_reqirements_email_input mo_ldap_local_custom_requirements_input_upper_space" required/>
				</div>
				<br>
				<div class="trial_page_input_email">
					<label for="mo_ldap_local_custom_requirements_phone" class="mo_ldap_input_label_text">Phone</label>
					<input name="mo_ldap_local_custom_requirements_phone" id="mo_ldap_local_custom_requirements_phone" style="margin-top: 6px;" type="tel" class="trial_page_input_email_text mo_ldap_local_full_width_input mo_ldap_local_custom_reqirements_email_input"/>
				</div>
				<br>
				<div class="trial_page_input_description">
					<label for="mo_ldap_local_custom_requirements_query" class="mo_ldap_input_label_text">Query</label>
					<textarea name="mo_ldap_local_description" cols="40" rows="5" placeholder="Write your Custom requirement here" class="trial_page_input_email_text trial_page_input_email_text_tem mo_ldap_local_full_width_input mo_ldap_local_custom_requirements_input_upper_space"></textarea>
				</div>
				<input type="submit" class="mo_ldap_save_user_mapping mo_ldap_save_user_mapping_temp mo_ldap_local_full_width_input" value="Request Feature">
			</form>
		</div>
	</div>
</div>

<div id="mo_ldap_local_contact_us_box" class="mo_ldap_local_popup_box mo_ldap_local_contact_us_popup mo_ldap_d_none mo_ldap_local_contact_us_box_resize" style="padding: 1% 0% 1% 1%;">
	<div class="mo_ldap_local_cross_button" type="button" data-id="mo_ldap_local_contact_us_box" onclick="mo_ldap_local_popup_card_cancel_remove(this)">+</div>
	<div class="mo_ldap_local_contact_us_container"> 
		<div class="mo_ldap_local_popup_div">	
			<div class="mo_ldap_local_popup_title mo_ldap_local_contact_us_heading">
				Contact Us
			</div>
			<div class="mo_ldap_local_popup_description">
				<span>Need help with the plugin configuration? Just send us a query from below form.</span>
			</div>
			<div>
				<form name="mo_ldap_local_contact_us_form" method="post" action="">
					<input type="hidden" name="option" value="mo_ldap_login_send_query"/>
					<?php wp_nonce_field( 'mo_ldap_login_send_query' ); ?>
					<div>
						<input type="email" class="mo_ldap_pop_up_input_field mo_ldap_local_full_width_input" id="mo_ldap_local_query_email" style="margin-top: 10px" name="mo_ldap_local_query_email" value="<?php echo esc_attr( $admin_email ); ?>" placeholder="Enter your email" required>
						<div class="mo_ldap_local_phone_input_div">
							<input type="text" class="mo_ldap_pop_up_input_field mo_ldap_local_full_width_input mo_ldap_local_phone_input" name="mo_ldap_local_query_phone" id="mo_ldap_local_query_phone" value="<?php echo esc_attr( get_option( 'mo_ldap_local_admin_phone' ) ); ?>" placeholder="Enter your phone"/>
						</div>
						<textarea id="mo_ldap_local_query" name="mo_ldap_local_query" class="mo_ldap_local_contact_us_testarea" class="mo_ldap_pop_up_input_field mo_ldap_local_full_width_input" cols="52" rows="4"  maxlength="500" placeholder="Write your query here" required ></textarea>
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_send_config_toggle">
							<input type="checkbox" id="mo_ldap_local_send_config" name="mo_ldap_local_send_config" class="mo_ldap_local_toggle_switch_hide mo_ldap_local_full_width_input" onChange="mo_ldap_local_display_warning()"/>
							<label for="mo_ldap_local_send_config" class="mo_ldap_local_toggle_switch"></label>
							<label for="mo_ldap_local_send_config" class="mo_ldap_local_d_inline">
								Share configured settings of LDAP server
							</label>
						</div>
						<span id="mo_ldap_local_ldap_warning" style="color:red;display:none; margin-top: 10px"> * This will send the LDAP Configuration to our support team(No passwords are shared).</span>
						<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_send_config_toggle">
							<input type="checkbox" id="mo_ldap_local_setup_call" name="mo_ldap_local_setup_call" class="mo_ldap_local_toggle_switch_hide mo_ldap_local_full_width_input" onChange="mo_ldap_local_display_setup_call_details()"/>
							<label for="mo_ldap_local_setup_call" class="mo_ldap_local_toggle_switch"></label>
							<label for="mo_ldap_local_setup_call" class="mo_ldap_local_d_inline">
								Schedule a Call
							</label>
						</div>
						<div id="mo_ldap_local_setup_call_details_div" class="mo_ldap_d_none">
							<div class="mo_ldap_local_setup_call_timezone">
								<label class="mo_ldap_input_label_text">Timezone:<span style="color:red; margin-top: 10px">*</span></label>
								<select class="mo_ldap_pop_up_input_field mo_ldap_local_full_width_input mo_ldap_local_setup_call_timezone_dropdown" name="mo_ldap_setup_call_timezone">
									<option value="" selected disabled>---------Select your timezone--------</option>
									<?php
									foreach ( $zones as $zone => $value ) {
										if ( strcasecmp( $value, 'Etc/GMT' ) === 0 ) {
											?>
											<option value="<?php echo esc_attr( $zone ) . ' ' . esc_attr( $value ); ?>" selected><?php echo esc_html( $zone ); ?></option>
											<?php
										} else {
											?>
											<option value="<?php echo esc_attr( $zone ) . ' ' . esc_attr( $value ); ?>"><?php echo esc_html( $zone ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<div class="mo_ldap_local_setup_call_timezone mo_ldap_local_setup_call_date">
								<label class="mo_ldap_input_label_text">Date:<span style="color:red; margin-top: 10px">*</span></label>
								<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_setup_call_date_container">
									<input type="date" id="datepicker" placeholder="Select Meeting Date" autocomplete="off" name="mo_ldap_setup_call_date" required class="mo_ldap_pop_up_input_field">
									<input type="time" id="ldap-timepicker" value='now' placeholder="Select Meeting Time" autocomplete="off" name="mo_ldap_setup_call_time" required class="mo_ldap_pop_up_input_field">
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" value="<?php echo esc_attr( get_option( 'mo_ldap_local_server_url' ) ? $utils::decrypt( get_option( 'mo_ldap_local_server_url' ) ) : '' ); ?>" >
					<div class="mo_ldap_local_horizontal_flex_container" style="margin-top: 10px">
						<input type="submit" name="send_query" value="Submit Query" class="mo_ldap_save_user_mapping" />
						<button type="button" class="mo_ldap_cancel_button" data-id="mo_ldap_local_contact_us_box" onclick="mo_ldap_local_popup_card_cancel_remove(this)">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
</div>


<div class="mo_ldap_local_overlay_back mo_ldap_d_none" id="mo_ldap_local_overlay"></div>
