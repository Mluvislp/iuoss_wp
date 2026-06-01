<?php
/**
 * Display Licensing Page
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

$addon_array_recommended = maybe_unserialize( MO_LDAP_RECOMMENDED_ADDONS );
$addon_array_third_party = maybe_unserialize( MO_LDAP_THIRD_PARTY_INTEGRATION_ADDONS );
$request_uri             = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
$pricing_list            = $pricing;
$site_type               = isset( $_GET['sitetype'] ) ? sanitize_key( wp_unslash( $_GET['sitetype'] ) ) : 'singlesite'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.

?>
<script>
	var selectArray = JSON.parse('<?php echo wp_json_encode( $pricing_list ); ?>');
	function createSelectOpt(elemId) {
		var selectPricingArray = selectArray[elemId];
		var selectElem = '<span id="mo_ldap_local_price_' + elemId + '" class="mo_ldap_local_price">$ ' + selectArray[elemId]["1"] + '</span></div><div class="mo_ldap_local_licensing_plan_instances"><div><b>No. of Intances</b></div>';
		selectElem += '<select class="mo_ldap_local_standerd_input mo_ldap_select_directory_server mo_ldap_licensing_dropdown" onchange="changePricing(this)" id="' + elemId + '">'
		jQuery.each(selectPricingArray, function (instances, price) {
			selectElem = selectElem + '<option value="' + instances + '" data-value="' + instances + '">' + instances + ' </option>';
		})
		selectElem = selectElem + '</select></div>';
		return document.write(selectElem);
	}
	function createSelectWithSubsitesOpt(elemId) {
		var selectPricingArray = selectArray[elemId];
		var selectElem = '<span id="mo_ldap_local_price_' + elemId + '" class="mo_ldap_local_price">$ ' + ( parseInt(selectArray[elemId]["1"]) + 60 ) + '</span></div><br><div class="mo_ldap_local_licensing_plan_instances"><div><b>No. of Intances</b></div>';
		selectElem += '<select class="mo_ldap_local_standerd_input mo_ldap_select_directory_server mo_ldap_licensing_dropdown" onchange="changePricing(this)" id="' + elemId + '">'
		jQuery.each(selectPricingArray, function (instances, price) {
			selectElem = selectElem + '<option value="' + instances + '" data-value="' + instances + '">' + instances + ' </option>';
		})
		selectElem = selectElem + '</select></div>';

		selectElem += '<br><div class="mo_ldap_local_licensing_plan_instances"><div><b>No. of Subsites</b></div>'
		selectElem += '<select style="padding-right: 23px !important;" class="mo_ldap_local_standerd_input mo_ldap_select_directory_server mo_ldap_licensing_dropdown" onchange="changePricing(this)" id="' + elemId + '" name="' + elemId + '_subsites">'
		let count = 0;
		var selectSubsitePricingArray = selectArray['subsite_intances'];
		jQuery.each(selectSubsitePricingArray, function (instances, price) {
			let selected = "";
			if(count == 0) {
				selected = "selected";
			}
			selectElem = selectElem + '<option value="' + instances + '" data-value="' + instances + '" '+ selected +'>' + instances + ' </option>';
			count++;
		})
		selectElem = selectElem + '</select></div>';

		return document.write(selectElem);
	}
</script>
<div class="mo_ldap_local_licensing_main_body">

	<div id="mo_ldap_local_licesing_nav" class="mo_ldap_local_licensing_nav_bar">
		<div style="font-weight:700;" class="mo_ldap_local_horizontal_flex_container">
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect mo_ldap_local_horizontal_flex_container">
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'LeftArrow.svg' ); ?>" height="20px" width="15px"></span>Plugin Config
			</a>
		</div>
		<div id="mo_ldap_local_nav_plans" class="mo_ldap_local_active_nav_block">
			<a href="#mo_ldap_local_plans" class="mo_ldap_local_unset_link_affect mo_ldap_local_nav_elements">
			Plans
			</a>
		</div>
		<div id="mo_ldap_local_nav_feature_comparison">
			<a href="#mo_ldap_local_feature_comparison" class="mo_ldap_local_unset_link_affect mo_ldap_local_nav_elements">
			Feature Comparison
			</a>
		</div>
		<div id="mo_ldap_local_nav_upgrade_steps">
			<a href="#mo_ldap_local_upgrade_steps" class="mo_ldap_local_unset_link_affect mo_ldap_local_nav_elements">
			Upgrade Steps
			</a>
		</div>
		</div>
	</div>

	<div id="mo_ldap_local_pricing_div" class="mo_ldap_local_licensing_body">

		<div class="mo_ldap_local_pricing_div_header">
			<h2>Licensing Page</h2>
			<input type="button" class="mo_ldap_local_addons_button" value="Premium Addons" onclick="window.open('https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin#Add-Ons','_blank');">
		</div>

		<div class="mo_ldap_local_licensing_page_toogle_switch_outer_div">
			<div class="mo_ldap_local_licensing_page_toogle_switch" onclick="mo_ldap_local_license_switch()">

				<div id="mo_ldap_local_single_site" class="mo_ldap_local_licensing_page_site <?php echo strcasecmp( $site_type, 'multisite' ) !== 0 ? 'mo_ldap_local_toogle_switch_highlighted' : ''; ?>" >
					SINGLE SITE
				</div>
				<div id="mo_ldap_local_multi_site" class="mo_ldap_local_licensing_page_site <?php echo strcasecmp( $site_type, 'multisite' ) === 0 ? 'mo_ldap_local_toogle_switch_highlighted' : ''; ?>" >
					MULTI SITE
				</div>

			</div>
		</div>

		<div id="mo_ldap_local_plans">
			<div id="mo_ldap_single_site_plans" class="mo_ldap_local_licensing_all_plans <?php echo strcasecmp( $site_type, 'multisite' ) === 0 ? 'mo_ldap_d_none' : ''; ?>">
				<div class="mo_ldap_local_licensing_plan_container mo_ldap_local_licensing_plan_container2">
					<div class="mo_ldap_local_licensing_plan_name">
						<div class="mo_ldap_local_each_plan_heading">
							AD Authentication & Kerberos SSO Plan


						</div>
						<script>
							createSelectOpt('pricing_kerberos');
						</script>
						<hr>
					<div class="mo_ldap_local_licensing_details_about_plan">
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Login using AD / any LDAP directory credentials</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Supports Kerberos / NTLM SSO</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Role mapping based on LDAP group/OU</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Attribute mapping</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">LDAP active directory forest support</div>
						</div>
					</div>
					<button class="mo_ldap_next_btn mo_ldap_local_licensing_buy_now_btn mo_ldap_upgrade_now_btn" onclick="upgradeform('wp_ldap_intranet_premium_plan')">
						Upgrade Now
					</button>
				</div>

				<div class="mo_ldap_local_licensing_plan_container mo_ldap_local_licensing_plan_container3">
					<div class="mo_ldap_local_licensing_plan_name">
						<div class="mo_ldap_local_each_plan_heading">
							Advanced Syncing &  Authentication Plan
						</div>
						<script>
							createSelectOpt('pricing_standard');
						</script>
						<hr>
					<div class="mo_ldap_local_licensing_details_about_plan">
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">All Features from the AD Authentication & Kerberos SSO Plan</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">User Profile Directory Sync</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Import AD Profile Picture</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Real-time User Provisioning in AD</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Group Provisioning</div>
						</div>
					</div>

					<button class="mo_ldap_next_btn mo_ldap_local_licensing_buy_now_btn mo_ldap_upgrade_now_btn" onclick="upgradeform('wp_ldap_standard_bundled_plan')">
						Upgrade Now
					</button>
				</div>

				<div class="mo_ldap_local_licensing_plan_container mo_ldap_local_licensing_plan_container4">
					<div class="mo_ldap_recommended_tag">Recommended</div>
					<div class="mo_ldap_local_licensing_plan_name">
						<div class="mo_ldap_local_each_plan_heading">
							All Inclusive Plan

						</div>
						<script>
							createSelectOpt('pricing_enterprise');
						</script>
						<hr>
					<div class="mo_ldap_local_licensing_details_about_plan">
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">All Features from the Advanced Syncing & Authentication Plan</div>
						</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">AD Self-Service Password Reset</div>
					</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">All Third Party App Integrations</div>
					</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">Page/Post Restriction Add-On</div>
					</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">WP User & Login Management</div>
					</div>
					</div>

					<button class="mo_ldap_next_btn mo_ldap_local_licensing_buy_now_btn mo_ldap_upgrade_now_btn mo_ldap_recommended_plan" onclick="upgradeform('wp_ldap_all_inclusive_bundled_plan')">
						Upgrade Now
					</button>
				</div>
			</div>

			<!-- Multisite  -->
			<div id="mo_ldap_multi_site_plans" class="mo_ldap_local_licensing_all_plans <?php echo strcasecmp( $site_type, 'multisite' ) !== 0 ? 'mo_ldap_d_none' : ''; ?>" >
				<div class="mo_ldap_local_licensing_plan_container mo_ldap_local_licensing_plan_container2">
					<div class="mo_ldap_local_licensing_plan_name">
						<div class="mo_ldap_local_each_plan_heading">
							Multisite AD Authentication & Kerberos SSO Plan

						</div>
						<br><br>
						<script>
							createSelectWithSubsitesOpt('mulpricing_kerberos');
						</script>
						<hr>
					<div class="mo_ldap_local_licensing_details_about_plan">
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Login using AD / any LDAP directory credentials</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Supports Kerberos / NTLM SSO</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Role mapping based on LDAP group / OU</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Attribute mapping</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">LDAP active directory forest support</div>
						</div>
					</div>
					<button class="mo_ldap_next_btn mo_ldap_local_licensing_buy_now_btn mo_ldap_upgrade_now_btn" onclick="upgradeform('wp_ldap_ntlm_sso_multisite_bundled_plan')">
						Upgrade Now
					</button>
				</div>

				<div class="mo_ldap_local_licensing_plan_container mo_ldap_local_licensing_plan_container3">
					<div class="mo_ldap_local_licensing_plan_name">
						<div class="mo_ldap_local_each_plan_heading">
							Multisite Advanced Syncing & Authentication Plan
						</div>
						<br>
						<br>
						<script>
							createSelectWithSubsitesOpt('mulpricing_standard');
						</script>
						<hr>
					<div class="mo_ldap_local_licensing_details_about_plan">
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">All Features from the AD Authentication & Kerberos SSO Plan</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">User Profile Directory Sync</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Import AD Profile Picture</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Real-time User Provisioning in AD</div>
						</div>
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">Group Provisioning</div>
						</div>
					</div>
					<button class="mo_ldap_next_btn mo_ldap_local_licensing_buy_now_btn mo_ldap_upgrade_now_btn" onclick="upgradeform('wp_ldap_standard_multisite_bundled_plan')">
						Upgrade Now
					</button>
				</div>

				<div class="mo_ldap_local_licensing_plan_container mo_ldap_local_licensing_plan_container4">
				<div class="mo_ldap_recommended_tag">Recommended</div>
					<div class="mo_ldap_local_licensing_plan_name">
						<div class="mo_ldap_local_each_plan_heading">
							Multisite All Inclusive Plan
						</div>
						<br>
						<br>
						<script>
							createSelectWithSubsitesOpt('mulpricing_enterprise');
						</script>
						<hr>
					<div class="mo_ldap_local_licensing_details_about_plan">
						<div>
							<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
							<div class="mo_ldap_local_licensing_plan_feature_container">All Features from the Advanced Syncing & Authentication Plan</div>
						</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">AD Self-Service Password Reset</div>
					</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">All Third Party App Integrations</div>
					</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">Page/Post Restriction Add-On</div>
					</div>
					<div>
						<span class="mo_ldap_local_licensing_bullets"><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'tick.svg' ); ?>" height="17px" width="17px"></span>
						<div class="mo_ldap_local_licensing_plan_feature_container">WP User & Login Management</div>
					</div>
					</div>
					<button class="mo_ldap_next_btn mo_ldap_local_licensing_buy_now_btn mo_ldap_upgrade_now_btn mo_ldap_recommended_plan" onclick="upgradeform('wp_ldap_all_inclusive_multisite_bundled_plan')">
						Upgrade Now
					</button>
				</div>
			</div>

		</div>
		<div class="mo_ldap_local_more_details_link"><a href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin" target="_blank">More Details</a></div>
		<form style="display:none;" id="loginform" action="<?php echo esc_url( MO_LDAP_LOCAL_HOST_NAME ) . '/moas/login'; ?>" target="_blank" method="post">
			<input type="hidden" value="<?php echo esc_attr( $utils::is_customer_registered() ); ?>" id="mo_customer_registered">
			<input type="email" name="username" value="<?php echo esc_attr( get_option( 'mo_ldap_local_admin_email' ) ); ?>" />
			<input type="text" name="redirectUrl" value="<?php echo esc_attr( MO_LDAP_LOCAL_HOST_NAME ) . '/moas/initializepayment'; ?>" />
			<input type="text" name="requestOrigin" id="requestOrigin" /> 
		</form> 
		<a id="mo_backto_ldap_accountsetup_tab" style="display:none;" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'account' ), htmlentities( $request_uri ) ) ); ?>">Back</a> 
	<div class="mo_ldap_local_contact_us">
		<div class="mo_ldap_local_contact_us_1">
			<p class="mo_ldap_local_contact_us_1_para"> Are you not able to choose your plan? </p>

			<button class="mo_ldap_save_user_mapping mo_ldap_licensing_page_contac_us" data-id="mo_ldap_local_contact_us_box" onclick="mo_ldap_local_popup_card_clicked(this, '')">
				<b>Contact Us</b>
			</button>
		</div>
		<div class = "mo_ldap_local_contact_us_vertical_line"></div>
		<div class="mo_ldap_local_contact_us_2">
			<p class="mo_ldap_local_contact_us_2_para"> Know more about WordPress instance, Subsites & Multisites Network </p>
			<br>
			<a target="_blank" rel="noopener" href="https://faq.miniorange.com/knowledgebase/what-is-an-instance" ><b style="color:#0076E1; font-size:15px;">Click Here</b></a>
		</div>
		<div class = "mo_ldap_local_contact_us_vertical_line"></div>
		<div class="mo_ldap_local_contact_us_3">
			<div class="mo_ldap_local_contact_us_3_text">
				Watch Premium Features Video
				<br>
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'LargeArrow.svg' ); ?>" height="30px" width="80px"></span>
			</div>

			<div class="mo_ldap_local_contact_us_3_link">
				<a target="_blank" rel="noopener" href="https://www.youtube.com/embed/r0pnB2d0QP8" >
					<img width="230" height="110" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'thumbnail.jpg' ); ?>" alt="Video Thumbnail">
				</a>
			</div>
		</div>
	</div>

	<div id="mo_ldap_local_feature_comparison" class="mo_ldap_local_licensing_feature_comparison">
		<div class="mo_ldap_local_licensing_feature_comparison_heading">
			FEATURES COMPARISON
		</div>

		<div class="mo_ldap_local_licensing_feature_comparison_table">
			<div class="mo_ldap_local_licensing_feature_comparison_table_header">
				<div class="mo_ldap_local_licensing_feature_comparison_col1">Add-Ons List</div>
				<div class="mo_ldap_local_licensing_feature_comparison_col2">AD Authentication & Kerberos SSO Plan</div>
				<div class="mo_ldap_local_licensing_feature_comparison_col2">Advanced Syncing & Authentication Plan</div>
				<div class="mo_ldap_local_licensing_feature_comparison_col2">All Inclusive Plan</div>
			</div>

			<div class="mo_ldap_local_license_features_row1" onclick="displayFeatures(this)">
				<div class="mo_ldap_local_licensing_feature_comparison_table_row">
					<div class="mo_ldap_local_licensing_feature_comparison_col1"><span><img class="mo_ldap_local_dropdown_arrow mo_ldap_local_reverse_rotate" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'down.svg' ); ?>" height="12px" width="12px"></span>Kerberos / NTLM SSO</div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
				</div>
				<div class="mo_ldap_local_feature_details">
					<ul>
						<li>&bull; Kerberos/NTLM SSO Add-on</li>
					</ul>
				</div>
			</div>
			<div class="mo_ldap_local_license_features_row2" onclick="displayFeatures(this)">
				<div class="mo_ldap_local_licensing_feature_comparison_table_row">
					<div class="mo_ldap_local_licensing_feature_comparison_col1"><span><img class="mo_ldap_local_dropdown_arrow mo_ldap_local_reverse_rotate" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'down.svg' ); ?>" height="12px" width="12px"></span>LDAP/Active Directory to WordPress Data Sync</div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'no.svg' ); ?>" height="12px" width="12px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
				</div>
				<div class="mo_ldap_local_feature_details ">
					<ul>
						<li>&bull; Sync Users LDAP Directory Add-on</li>
						<li>&bull; Profile Picture Sync Add-on</li>
						<li>&bull; Password Sync Add-on</li>
					</ul>
				</div>
			</div>
			<div class="mo_ldap_local_license_features_row1" onclick="displayFeatures(this)">
				<div class="mo_ldap_local_licensing_feature_comparison_table_row">
					<div class="mo_ldap_local_licensing_feature_comparison_col1"><span><img class="mo_ldap_local_dropdown_arrow mo_ldap_local_reverse_rotate" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'down.svg' ); ?>" height="12px" width="12px"></span>Restrict Pages/Posts</div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'no.svg' ); ?>" height="12px" width="12px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'no.svg' ); ?>" height="12px" width="12px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
				</div>
				<div class="mo_ldap_local_feature_details">
					<ul>
						<li>&bull; Page/Post Restriction Add-on</li>
					</ul>
				</div>
			</div>
			<div class="mo_ldap_local_license_features_row2" onclick="displayFeatures(this)">
				<div class="mo_ldap_local_licensing_feature_comparison_table_row">
					<div class="mo_ldap_local_licensing_feature_comparison_col1"><span><img class="mo_ldap_local_dropdown_arrow mo_ldap_local_reverse_rotate" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'down.svg' ); ?>" height="12px" width="12px"></span>Third-Party Plugin Integrations</div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'no.svg' ); ?>" height="12px" width="12px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'no.svg' ); ?>" height="12px" width="12px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
				</div>
				<div class="mo_ldap_local_feature_details">
					<ul>
						<li>&bull; Ultimate Member Profile Integration Add-on</li>
						<li>&bull; BuddyPress Integration Add-on</li>
						<li>&bull; Gravity forms Integration Add-on</li>
						<li>&bull; WP Groups Plugin Integration Add-on</li>
						<li>&bull; BuddyBoss Integration Add-on</li>
					</ul>
				</div>
			</div>
			<div class="mo_ldap_local_license_features_row1" onclick="displayFeatures(this)">
				<div class="mo_ldap_local_licensing_feature_comparison_table_row">
					<div class="mo_ldap_local_licensing_feature_comparison_col1"><span><img class="mo_ldap_local_dropdown_arrow mo_ldap_local_reverse_rotate" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'down.svg' ); ?>" height="12px" width="12px"></span>LMS Integrations</div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'no.svg' ); ?>" height="12px" width="12px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'no.svg' ); ?>" height="12px" width="12px"></span></div>
					<div class="mo_ldap_local_licensing_feature_comparison_col2"><span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'yes.svg' ); ?>" height="14px" width="14px"></span></div>
				</div>
				<div class="mo_ldap_local_feature_details">
					<ul>
						<li>&bull; Tutor LMS</li>
						<li>&bull; Sensei LMS</li>
						<li>&bull; LearnDash</li>
						<li>&bull; Lifter LMS</li>
						<li>&bull; LearnPress</li>
						<li>&bull; MemberPress</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="mo_ldap_local_more_details_link"><a href="https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites?utm_source=ldap%20intranet%20plugin&utm_medium=ldap%20free%20plugin&utm_campaign=ldap%20intranet%20free%20plugin#PlanComparison" target="_blank">View Detailed Comparison</a></div>

	<div id="mo_ldap_local_upgrade_steps" class="mo_ldap_local_how_to_upgrade">
		<div class="mo_ldap_local_how_to_upgrade_heading">
			HOW TO UPGRADE TO PREMIUM
		</div>
		<hr>
		<div class="mo_ldap_local_how_to_upgrade_container">			
			<div class="mo_ldap_local_upgrade_steps">
				<div>
					<div class="mo_ldap_local_upgrade_step">1 </div>
					<div class="mo_ldap_local_upgrade_step_description">Click on Upgrade Now button for required premium plan and you will be redirected to miniOrange login console.</div>
				</div>
				<div>
					<div class="mo_ldap_local_upgrade_step">5 </div>
					<div class="mo_ldap_local_upgrade_step_description">From the WordPress admin dashboard, delete the free plugin currently installed.</div>
				</div>
				<div class="mo_ldap_vertical_line_sepertor"></div>
				<div class="mo_ldap_vertical_line_sepertor"></div>

				<div>
					<div class="mo_ldap_local_upgrade_step">2 </div>
					<div class="mo_ldap_local_upgrade_step_description">Enter your username and password with which you have created an account with us. After that you will be redirected to payment page.</div>
				</div>
				<div>
					<div class="mo_ldap_local_upgrade_step">6 </div>
					<div class="mo_ldap_local_upgrade_step_description">Unzip the downloaded premium plugin and extract the files.</div>
				</div>

				<div class="mo_ldap_vertical_line_sepertor"></div>
				<div class="mo_ldap_vertical_line_sepertor"></div>

				<div>
					<div class="mo_ldap_local_upgrade_step">3 </div>
					<div class="mo_ldap_local_upgrade_step_description">Enter your card details and proceed for payment. On successful payment completion, the premium plugin(s) and add-on(s) will be available to download.</div>
				</div>
				<div>
					<div class="mo_ldap_local_upgrade_step">7 </div>
					<div class="mo_ldap_local_upgrade_step_description">Upload the extracted files using FTP to path /wp-content/plugins/. Alternately, go to Add New → Upload Plugin in the plugin's section to install the .zip file directly.</div>
				</div>

				<div class="mo_ldap_vertical_line_sepertor"></div>
				<div class="mo_ldap_vertical_line_sepertor"></div>

				<div>
					<div class="mo_ldap_local_upgrade_step">4 </div>
					<div class="mo_ldap_local_upgrade_step_description">Download the premium plugin(s) and add-on(s) from Plugin Releases and Downloads section.</div>
				</div>
				<div>
					<div class="mo_ldap_local_upgrade_step">8 </div>
					<div class="mo_ldap_local_upgrade_step_description">After activating the premium plugin, login using the account you have registered with us.</div>
				</div>
			</div>


			<div class="mo_ldap_local_upgrade_steps_note">
				<b>Note:</b> The premium plans are available in the miniOrange dashboard. Please don't update the premium plugin from the WordPress Marketplace. 
				We'll notify you via email whenever a newer version of the plugin is available in the miniOrange dashboard.
			</div>
		</div>
	</div>

	<div class="mo_ldap_local_supported_payment_methods">
		<div class="mo_ldap_local_supported_payment_methods_heading">
			Supported Payment Methods 	
		</div>
		<hr>
		<div class="mo_ldap_local_supported_payment_methods_cards">

			<div class="mo_ldap_local_supported_payment_methods_card">
				<div class="mo_ldap_local_supported_payment_methods_card_header">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'cards.png' ); ?>" width="85%" height="40px"></span>
				</div>
				<div class="mo_ldap_local_supported_payment_methods_card_discription">
					If the payment is made through Credit Card/International Debit Card, the license will be created automatically once the payment is completed.
				</div>
			</div>
			<div class="mo_ldap_local_supported_payment_methods_card">
				<div class="mo_ldap_local_supported_payment_methods_card_header">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'paypal.svg' ); ?>" width="100%" height="40px"></span>
				</div>
				<div class="mo_ldap_local_supported_payment_methods_card_discription">
				Use the following PayPal ID info@xecurify.com for making the payment via PayPal.				</div>
			</div>
			<div class="mo_ldap_local_supported_payment_methods_card">
				<div class="mo_ldap_local_supported_payment_methods_card_header">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'bankTransfer.svg' ); ?>" width="100%" height="40px"></span>
				</div>
				<div class="mo_ldap_local_supported_payment_methods_card_discription">
				If you want to use bank transfer for the payment then contact us at ldapsupport@xecurify.com so that we can provide you the bank details.				</div>
			</div>

		</div>

		<div class="mo_ldap_local_upgrade_steps_note mo_ldap_local_payment_methods_note">
			Note: Once you have paid through PayPal/Net Banking, please inform us so that we can confirm and update your license.
		</div>
	</div>


	<div class="mo_ldap_local_customer_reviews">
		<div class="mo_ldap_local_customer_reviews_header">
			WHAT OUR CUSTOMERS SAY
		</div>
		<hr>
		<div class="mo_ldap_local_review_cards">
			<div class="mo_ldap_local_review_card">
				<div class="mo_ldap_local_review_card_header">
					Great plugin!
				</div>
				<br>
				<div class="mo_ldap_local_review_card_stars">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
				</div>
				<br>
				<div class="mo_ldap_local_review_card_description">
					This plugin solved a lot of functionalities we had to sort out (and more). The premium version is totally worth it. And great support from the team.
				</div>

				<a href="https://wordpress.org/support/topic/great-plugin-36192/" target="_blank" rel="noopener" class="mo_ldap_local_unset_link_affect mo_ldap_local_see_full_review">
					See Full Review
				</a>
			</div>
			<div class="mo_ldap_local_review_card">
				<div class="mo_ldap_local_review_card_header">
					Perfect Intranet Solution
				</div>
				<br>
				<div class="mo_ldap_local_review_card_stars">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
				</div>
				<br>
				<div class="mo_ldap_local_review_card_description">
					We've been using MiniOrange for several years now. On the two occasions we reached out to them for support, they were very expedient in resolving the issues we had. We use...
				</div>

				<a href="https://wordpress.org/support/topic/perfect-intranet-solution/" target="_blank" rel="noopener" class="mo_ldap_local_unset_link_affect mo_ldap_local_see_full_review">
					See Full Review
				</a>
			</div>
			<div class="mo_ldap_local_review_card">
				<div class="mo_ldap_local_review_card_header">
					Great Product Great Support
				</div>
				<br>
				<div class="mo_ldap_local_review_card_stars">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'star.svg' ); ?>" height="20px" width="20px"></span>
				</div>
				<br>
				<div class="mo_ldap_local_review_card_description">
					We've been using this plugin for over a year now and we are very happy with it. Their support team is always there to help.
				</div>

				<a href="https://wordpress.org/support/topic/great-product-great-support-163/" target="_blank" rel="noopener" class="mo_ldap_local_unset_link_affect mo_ldap_local_see_full_review">
					See Full Review
				</a>
			</div>
		</div>
	</div>


	<div class="mo_ldap_local_return_policy">
		<div class="return_policy_header">
			RETURN POLICY
		</div>
		<hr>	
		<div class="mo_ldap_local_return_pol_content_div">
			<div class="return_policy_box">
				If the premium plugin you purchased is not working as advertised and you’ve attempted to resolve any feature issues with our support team, which couldn't get resolved, we will refund the whole amount within 10 days of the purchase.
			</div>
			<br>
			<div class="return_policy_description">
				<b>Note that this policy does not cover the following cases:</b>
					<br><br>
					1. Change of mind or change in requirements after purchase.
					<br><br>
					2. Infrastructure issues not allowing the functionality to work.
				</div>
				<div class="mo_ldap_local_upgrade_steps_note return_policy_note">
					Please email us at info@xecurify.com for any queries regarding the return policy.
				</div>
			</div>
		</div>

		<div class="mo_ldap_local_go_top_div">
			<a class="mo_ldap_save_user_mapping mo_ldap_local_go_top_btn" href="#">Top &#8593; </a>
		</div>

	</div>


	<div id="mo_ldap_local_contact_us_box" class="mo_ldap_local_popup_box mo_ldap_local_contact_us_popup mo_ldap_d_none mo_ldap_local_contact_us_box_resize">
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
						<div style="margin-top: 8px">
							<input type="text" style="height:38px;" class="mo_ldap_pop_up_input_field mo_ldap_local_full_width_input" name="mo_ldap_local_query_phone" id="mo_ldap_local_query_phone" value="<?php echo esc_attr( get_option( 'mo_ldap_local_admin_phone' ) ); ?>" placeholder="Enter your phone"/>
						</div>
						<textarea id="mo_ldap_local_query" name="mo_ldap_local_query" class="mo_ldap_pop_up_input_field mo_ldap_local_full_width_input mo_ldap_local_contact_us_testarea" cols="52" rows="4"  placeholder="Write your query here" required ></textarea>
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
								<select class="mo_ldap_pop_up_input_field mo_ldap_local_full_width_input" name="mo_ldap_setup_call_timezone" style="margin-top: 10px; min-width: 70%; width: 70% !important;">
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
<div class="mo_ldap_local_overlay_back mo_ldap_d_none" id="mo_ldap_local_overlay"></div>
