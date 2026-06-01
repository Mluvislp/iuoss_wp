<?php
/**
 * Display Customer Registration page.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>

<div class="mo_ldap_local_account_box">
	<div style="padding-left: 30px;">
		<a style="width: fit-content;" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_back_btn mo_ldap_local_plugin_config_back_btn mo_ldap_local_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'back.svg' ); ?>" height="10px" width="15px"></span> Plugin Config</a>
	</div>
	<div class="mo_ldap_local_registration_info">
		<div>
			<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'registration.svg' ); ?>" width="70%">
		</div>
		<div class="mo_ldap_local_popup_page_para" style="margin-top:0%;">Why should I register?</div>
		<div class="mo_ldap_local_registration_para">
			You should register so that in case you need help, we can help you with step by step
			instructions. We support all known directory systems like Active Directory, OpenLDAP, JumpCloud etc.
			<strong>You will also need a miniOrange account to upgrade to the premium version of the plugins.</strong> We do not store any information except the email that you will use to register with us.
		</div>
	</div>

	<div class="mo_ldap_local_register_box">
		<div class="mo_ldap_local_popup_page_para mo_ldap_local_registration_heading">Register with miniOrange</div>
		<div>
			<form name="mo_ldap_registration_page" id="mo_ldap_registration_page" method="post" action="">
				<?php wp_nonce_field( 'mo_ldap_local_register_customer' ); ?>
				<input type="hidden" name="option" value="mo_ldap_local_register_customer"/>
				<div class="mo_ldap_local_inline_fields">
					<div class="trial_page_input_email" style="width: 50%">
						<label class="mo_ldap_local_label mo_ldap_input_label_text" for="mo_ldap_local_company">Website/Company</label>
						<input style="width:100%;" class="mo_ldap_pop_up_input_field" id="mo_ldap_local_company" type="text" name="company" required placeholder="Company Name" value="<?php echo isset( $_SERVER['SERVER_NAME'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) ) : ''; ?>"/>
					</div>
					<div class="trial_page_input_email" style="width: 50%">
						<label class="mo_ldap_local_label mo_ldap_input_label_text" for="mo_ldap_local_register_phone">Telephone Number</label>
						<input style="width:100%; height: 38px;" class="mo_ldap_pop_up_input_field" type="text" name="register_phone" id="mo_ldap_local_register_phone" placeholder="Enter your phone number" value="<?php echo esc_attr( get_option( 'mo_ldap_local_admin_phone' ) ); ?>"/>
					</div>
				</div>
				<div class="trial_page_input_email">
					<label class="mo_ldap_local_label mo_ldap_input_label_text" for="mo_ldap_local_register_email">Email</label>
					<input style="width:100%;" id="mo_ldap_local_register_email" class="mo_ldap_pop_up_input_field" type="email" name="email" required placeholder="person@example.com" value="<?php echo esc_attr( $admin_email ); ?>"/>
				</div>
				<div class="trial_page_input_email">
					<label class="mo_ldap_local_label mo_ldap_input_label_text" for="mo_ldap_local_register_password">Password</label>
					<input style="width:100%;" id="mo_ldap_local_register_password" class="mo_ldap_pop_up_input_field" required type="password" name="password" placeholder="Choose your password (Min. length 6)" minlength="6" pattern="^[(\w)*(!@#$.%^&*-_)*]+$" title="Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*) should be present."/>
				</div>
				<div class="trial_page_input_email">
					<label class="mo_ldap_local_label mo_ldap_input_label_text" for="mo_ldap_local_register_confirmpassword">Confirm Password</label>
					<input style="width:100%;" id="mo_ldap_local_register_confirmpassword" class="mo_ldap_pop_up_input_field" required type="password" name="confirmPassword" placeholder="Choose your password (Min. length 6)" minlength="6" pattern="^[(\w)*(!@#$.%^&*-_)*]+$" title="Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*) should be present."/>
				</div>
				<div class="trial_page_input_description">
					<p class="mo_ldap_local_label mo_ldap_input_label_text">Use case</p>
					<textarea style="width:100%; height: 100px;" cols="40" rows="5" name="usecase" placeholder="Write about your usecase." class="mo_ldap_pop_up_input_field trial_page_input_email_text_tem"></textarea>
				</div>
				<div style="text-align: center;">
					<input type="submit" name="submit" value="Register" class="mo_ldap_local_reg_button mo_ldap_save_user_mapping mo_ldap_save_user_mapping_temp"/>
					<span style="margin-left: 0;">Trouble in registering account? click <a style="cursor: pointer; font-weight:700;" href="https://www.miniorange.com/businessfreetrial" target="_blank">here</a> for more info.</span>
					<hr style="width:80%"><br>
					<strong style="font-size: 16px;margin-left: 0; font-weight: 700;">Already have an account? <a style="cursor: pointer;" id="mo_ldap_goto_login">Login</a></strong>
				</div>
			</form>
		</div>
		<form name="f1" method="post" action="" id="mo_ldap_goto_login_form">
			<?php wp_nonce_field( 'mo_ldap_goto_login' ); ?>
			<input type="hidden" name="option" value="mo_ldap_goto_login"/>
		</form>
	</div>

</div>
