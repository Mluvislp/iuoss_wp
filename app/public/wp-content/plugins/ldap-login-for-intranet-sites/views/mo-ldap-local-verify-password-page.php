<?php
/**
 * Display Login with miniOrange page.
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
		<div class="mo_ldap_local_popup_page_para mo_ldap_local_registration_heading">Login with miniOrange</div>
		<div>
			<form name="mo_ldap_verify_password" id="mo_ldap_verify_password" method="post" action="">
				<?php wp_nonce_field( 'mo_ldap_local_verify_customer' ); ?>
				<input type="hidden" name="option" value="mo_ldap_local_verify_customer"/>
				<div class="trial_page_input_email">
					<label class="mo_ldap_local_label mo_ldap_input_label_text" for="mo_ldap_local_register_email">Email</label>
					<input style="width:100%;" id="mo_ldap_local_register_email" class="mo_ldap_pop_up_input_field" type="email" name="email" required placeholder="person@example.com" value="<?php echo esc_attr( get_option( 'mo_ldap_local_admin_email' ) ); ?>"/>
				</div>
				<div class="trial_page_input_email">
					<label class="mo_ldap_local_label mo_ldap_input_label_text" for="mo_ldap_local_register_password">Password</label>
					<input style="width:100%;" id="mo_ldap_local_register_password" class="mo_ldap_pop_up_input_field" required type="password" name="password" placeholder="Choose your password (Min. length 6)" minlength="6" pattern="^[(\w)*(!@#$.%^&*-_)*]+$" title="Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*) should be present."/>
				</div>
				<div style="text-align: center;">
					<input type="submit" name="submit" style="margin-left: 5%;" value="Login" class="mo_ldap_local_reg_button mo_ldap_save_user_mapping mo_ldap_save_user_mapping_temp"/>
					<span><a style="text-decoration:none;" target="_blank" href="https://login.xecurify.com/moas/idp/resetpassword" rel="noopener">Click here if you forgot your password?</a></span>
					<hr style="width:80%"><br>
					<strong style="font-size: 16px;margin-left: 0; font-weight: 700;"><a style="cursor: pointer;" id="mo_ldap_goback">Create an Account</a></strong>
				</div>
			</form>
		</div>
		<form name="f" method="post" action="" id="mo_ldap_goback_form">
			<?php wp_nonce_field( 'mo_ldap_local_cancel' ); ?>
			<input type="hidden" name="option" value="mo_ldap_local_cancel"/>
		</form>
	</div>

</div>
