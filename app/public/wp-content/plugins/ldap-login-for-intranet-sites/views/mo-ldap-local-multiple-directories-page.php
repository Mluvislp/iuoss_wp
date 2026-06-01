<?php
/**
 * Display Multiple Directory Configuration Page
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class="mo_ldap_local_multiple_directories_outer">
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
				Add new LDAP server
			</div>
			<br>

			<div class="mo_ldap_local_multiple_directories_each_div">
				<div>LDAP Server*</div>
				<div><input type="text" id="phone_attribute" name="phone_attribute" placeholder="Enter LDAP Server" class="mo_ldap_local_disabled_input_field mo_ldap_local_disabled_input_field_md" disabled></div>
				&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;<small>eg: ldap://myldapserver.domain:389 , ldap://x.x.x.x:389.</small>
			</div>
			<br>
			<br>

			<div class="mo_ldap_local_multiple_directories_each_div">
				<div>Username*</div>
				<div><input type="text" id="phone_attribute" name="phone_attribute" placeholder="Enter Username" class="mo_ldap_local_disabled_input_field mo_ldap_local_disabled_input_field_md" disabled></div>
				&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;<small>e.g. cn=username,cn=group,dc=domain,dc=com</small>
			</div>
			<br>
			<br>

			<div class="mo_ldap_local_multiple_directories_each_div">
				<div>Password*</div>
				<div><input type="text" id="phone_attribute" name="phone_attribute" placeholder="Enter Password" class="mo_ldap_local_disabled_input_field mo_ldap_local_disabled_input_field_md" disabled></div>
			</div>
			<br>

			<div class="mo_ldap_local_multiple_directories_each_div">
				<div>Search Base(s)*</div>
				<div><input type="text" id="phone_attribute" name="phone_attribute" placeholder="Enter Search Base" class="mo_ldap_local_disabled_input_field mo_ldap_local_disabled_input_field_md" disabled></div>
				&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;<small>e.g. cn=Users,dc=domain,dc=com</small>
			</div>
			<br>
			<br>

			<div class="mo_ldap_local_multiple_directories_each_div">
				<div>User Attribute *</div>
				<div><input type="text" id="phone_attribute" name="phone_attribute" placeholder="Enter User Attribute " class="mo_ldap_local_disabled_input_field mo_ldap_local_disabled_input_field_md" disabled></div>
				&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;<small>e.g. sAMAccountName, userPrincipalName;mail</small>
			</div>

			<br>
			<br>
			<br>

			<div class="mo_ldap_local_md_headings ">
				Search Conditions
			</div>
			<br>

			<div class="mo_ldap_local_md_search_conditions">
				<div class="mo_ldap_local_md_dearch_conditions_box"></div>
				Enable Custom Search Filter
			</div>

			<br>

			<button class="mo_ldap_local_disabled_button mo_ldap_local_button_disabled">
				Test Connection and Save
			</button>
			<button class="mo_ldap_troubleshooting_btn mo_ldap_local_button_disabled mo_ldap_local_md_disabled_btn">
				Add New Configuration
			</button>
		</div>
	</div>
</div>
