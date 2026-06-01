<?php
/**
 * Display Import Export Configuration
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class="mo_ldap_local_imp_exp_outer">

	<div class="mo_ldap_local_outer">
		<div id="mo_import" style="background: white;position: relative;border-radius: 10px;">
			<form method="post" action="" name="mo_import" enctype="multipart/form-data">
			<?php wp_nonce_field( 'mo_ldap_import' ); ?>
				<input type="hidden" name="option" value="mo_ldap_import"/>
				<div class="mo_ldap_local_imp_exp_headings">
					Import Configuration
				</div>
				<div>
					<div class="mo_ldap_test_authentication_heading">
						<div class="mo_ldap_local_note_inner">
							<b>This feature will allow you to import your plugin configuration from a previously exported JSON file.</b>
						</div>
					</div>

					<div class="mo_ldap_local_drop_zone">
						<div>
							<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'export.svg' ); ?>" height="20px" width="20px"></span>
						</div>
						<span class="mo_ldap_local_drop_zone_prompt">Drag & Drop or Choose file to upload</span>
						<input type="file" name="mo_ldap_import_file" id="mo_ldap_import_file" class="mo_ldap_local_drop_zone_input" required>
					</div>
					<br>
					<div>
						<div>
							<input type="submit" class="mo_ldap_save_user_mapping" value="Import Configuration" name="import_file" >
						</div>
					</div> 
				</div>
			</form>
		</div>
	</div>
	<div class="mo_ldap_local_outer">
		<div class="mo_ldap_local_imp_exp_headings">Export Configuration</div>
		<form method="post" action="" id="mo_ldap_local_save_config" name="mo_ldap_local_save_config">
			<?php wp_nonce_field( 'enable_config' ); ?>
			<input type="hidden" name="option" value="enable_config" />
			<div>
				<input type="checkbox" id="enable_save_config" name="enable_save_config" class="mo_ldap_local_toggle_switch_hide" value="1" onchange="this.form.submit()" <?php checked( esc_attr( strcasecmp( get_option( 'en_save_config' ), '1' ) === 0 ) ); ?> />
				<label for="enable_save_config" class="mo_ldap_local_toggle_switch"></label>
				<label for="enable_save_config" class="mo_ldap_local_d_inline mo_ldap_local_bold_label">
					Keep configuration upon deactivation
				</label>
			</div>
		</form>
		<br>
		<form method="post" action="" name="mo_export_pass" id="mo_export_pass">
			<?php wp_nonce_field( 'mo_ldap_pass' ); ?>
			<input type="hidden" name="option" value="mo_ldap_pass" />
			<div>
				<input type="checkbox" id="enable_ldap_login" name="enable_ldap_login" class="mo_ldap_local_toggle_switch_hide" value="1" onchange="this.form.submit()" <?php checked( esc_attr( strcasecmp( get_option( 'mo_ldap_export' ), '1' ) === 0 ) ); ?> />
				<label for="enable_ldap_login" class="mo_ldap_local_toggle_switch"></label>
				<label for="enable_ldap_login" class="mo_ldap_local_d_inline mo_ldap_local_bold_label">
					Securely Export Service Account password
				</label>
			</div>
		</form>

		<div class="mo_ldap_test_authentication_heading mo_ldap_import_conf_notice_container">
			<div class="mo_ldap_local_note_inner">
				<b>Your service account password will be exported in an encrypted fashion
				<br>
				Enable this only when server password is needed.</b>
			</div>
		</div>
		<br>
		<form method="post" action="" name="mo_export">
			<?php wp_nonce_field( 'mo_ldap_export' ); ?>
			<input type="hidden" name="option" value="mo_ldap_export"/>
			<input type="button" class="mo_ldap_save_user_mapping" onclick="document.forms['mo_export'].submit();" value= "Export configuration" />
		</form>
	</div>
</div>
