<?php
/**
 * Display Feedback form page.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

wp_enqueue_script( 'utils' );
wp_enqueue_style( 'mo_ldap_admin_plugins_page_style', MO_LDAP_LOCAL_INCLUDES . 'css/mo_ldap_local_plugin_style.min.css', array(), MO_LDAP_LOCAL_VERSION );
?>

</head>
<body>

<div id="ldapModal" class="mo_ldap_modal_feedback">
	<div class="mo_ldap_modal_container_feedback"></div>
		<div class="mo_ldap_modal_content_feedback">
			<form name="f" method="post" action="" id="mo_ldap_feedback">
				<?php wp_nonce_field( 'mo_ldap_feedback' ); ?>
				<input type="hidden" name="option" value="mo_ldap_feedback"/>
				<div class="mo_ldap_local_cross_button" onclick="getElementById('ldapModal').style.display = 'none'">+</div>
				<div class="mo_ldap_feedback_header mo_ldap_local_registration_heading">Your Feedback</div>
				<div id="mo_ldap_local_smi_rate" class="mo_ldap_local_rating">
					<input type="radio" name="mo_ldap_local_rate" id="mo_ldap_local_angry" value="1"/>
					<label for="mo_ldap_local_angry"><img class="mo_ldap_local_sm" alt="Image not found" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'angry.png' ); ?>" />
					</label>

					<input type="radio" name="mo_ldap_local_rate" id="mo_ldap_local_sad" value="2"/>
					<label for="mo_ldap_local_sad"><img class="mo_ldap_local_sm" alt="Image not found" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'sad.png' ); ?>" />
					</label>

					<input type="radio" name="mo_ldap_local_rate" id="mo_ldap_local_neutral" value="3"/>
					<label for="mo_ldap_local_neutral"><img class="mo_ldap_local_sm" alt="Image not found" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'normal.png' ); ?>" />
					</label>

					<input type="radio" name="mo_ldap_local_rate" id="mo_ldap_local_smile" value="4"/>
					<label for="mo_ldap_local_smile">
						<img class="mo_ldap_local_sm" alt="Image not found" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'smile.png' ); ?>" />
					</label>

					<input type="radio" name="mo_ldap_local_rate" id="mo_ldap_local_happy" value="5" checked/>
					<label for="mo_ldap_local_happy"><img class="mo_ldap_local_sm" alt="Image not found" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'happy.png' ); ?>" />
					</label>
				</div>
				<hr class="mo_ldap_local_horizontal_line">
				<div class="mo_ldap_local_feedback_contanier">
					<?php
						$email = get_option( 'mo_ldap_local_admin_email' );
					if ( empty( $email ) ) {
						$user  = wp_get_current_user();
						$email = $user->user_email;
					}
					?>
					<div class="mo_ldap_feedback_email_div">
						<label for="mo_ldap_local_query_mail" class="mo_ldap_local_d_inline mo_ldap_local_bold_label">Email Address:</label>
						<input type="email" id="mo_ldap_local_query_mail" name="mo_ldap_local_query_mail" class="mo_ldap_pop_up_input_field mo_ldap_feedback_email_field" placeholder="your email address" required value="<?php echo esc_attr( $email ); ?>" readonly="readonly"/>

						<input type="radio" name="mo_ldap_local_edit" id="mo_ldap_local_edit" onclick="editEmailAddress()" value=""/>
						<label for="mo_ldap_local_edit"><img class="mo_ldap_local_editable" alt="Image not found" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . '61456.png' ); ?>" />
						</label>
					</div>
					<div class="mo_ldap_local_feedback_options_container">
						<h4 style="font-size: 14px">Please help us to improve our plugin by giving your opinion<span style="color:red;">*</span></h4>
						<div class="mo_ldap_local_feedback_options">
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="I'll reactivate it later" class="mo_ldap_local_feedback_checkbox">
								<label class="mo_ldap_local_dactivation_form_label">I'll reactivate it later</label>
							</div>
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="plugin is not working" class="mo_ldap_local_feedback_checkbox"/>
								<label class="mo_ldap_local_dactivation_form_label">The Plugin is not working</label>
							</div>
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="Upgrading to premium version" class="mo_ldap_local_feedback_checkbox"/>
								<label class="mo_ldap_local_dactivation_form_label">Upgrading to premium plugin</label>
							</div>
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="Could not understand how to use" class="mo_ldap_local_feedback_checkbox"/>
								<label class="mo_ldap_local_dactivation_form_label"> I could not understand how to use it</label>
							</div>
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="Looking for specific feature" class="mo_ldap_local_feedback_checkbox"/>
								<label class="mo_ldap_local_dactivation_form_label"> Looking for specific feature</label>
							</div>
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="LDAP server connection failed" class="mo_ldap_local_feedback_checkbox"/>
								<label class="mo_ldap_local_dactivation_form_label">LDAP server connection failed</label>
							</div>
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="LDAP login not working" class="mo_ldap_local_feedback_checkbox"/>
								<label class="mo_ldap_local_dactivation_form_label">LDAP login is not working</label>
							</div>
							<div>
								<input type="checkbox" name="mo_ldap_local_feedback_options[]" value="other" class="mo_ldap_local_feedback_checkbox"/>
								<label class="mo_ldap_local_dactivation_form_label">Other</label>
							</div>
						</div>

						<div>					
							<div class="trial_page_input_description mo_ldap_local_textarea_div mo_ldap_local_margin_top">
								<textarea cols="40" rows="5" id="mo_ldap_local_query_feedback" name="mo_ldap_local_query_feedback" placeholder="Tell us what happened!" class="mo_ldap_pop_up_input_field trial_page_input_email_text_tem mo_ldap_local_feedback_textarea"></textarea>
							</div>
							<div class="mo_ldap_local_horizontal_flex_container mo_ldap_local_feedback_toggle_div mo_ldap_local_margin_top">
								<input type="checkbox" id="mo_ldap_local_send_config" name="mo_ldap_local_get_reply" class="mo_ldap_local_toggle_switch_hide" value="YES" checked />
								<label for="mo_ldap_local_send_config" class="mo_ldap_local_toggle_switch"></label>
								<label for="mo_ldap_local_send_config" class="mo_ldap_local_d_inline">
									I want to get in touch with your technical team for more assistance.
								</label>
							</div>
							<div class="mo_ldap_local_feedback_notice mo_ldap_local_margin_top">
								On submitting the feedback, your email address will be shared with the miniOrange team.
							</div>
						</div>
						<div class="mo_ldap_modal-footer">
							<input type="submit" class="mo_ldap_save_user_mapping" name="miniorange_ldap_local_feedback_submit" id="miniorange_ldap_local_feedback_submit"
								class="button button-primary-ldap button-large" value="Submit"/>
							<span width="30%">&nbsp;&nbsp;</span>
							<input type="button" name="miniorange_skip_feedback"
								class="mo_ldap_test_authentication_btn2 mo_ldap_wireframe_btn" style="font-weight:500;" value="Skip feedback & deactivate"
								onclick="document.getElementById('mo_ldap_feedback_form_close').submit();"/>
						</div>
					</div>
				</div>
				<script>
					let submitButton = document.getElementById('miniorange_ldap_local_feedback_submit');
					submitButton.addEventListener('click', function(e) {
						let checkedCheckboxes = document.querySelectorAll('.mo_ldap_local_feedback_checkbox:checked').length;
						if (checkedCheckboxes < 1) {
							alert('Please select atleast one reason for deactivation.');
							e.preventDefault();
						}
					});

					document.querySelectorAll('.mo_ldap_local_feedback_checkbox').forEach(function(checkbox) {
						checkbox.addEventListener('change', function() {
							let checkedCheckboxes = document.querySelectorAll('.mo_ldap_local_feedback_checkbox:checked').length;
							if (checkedCheckboxes >= 3) {
								document.querySelectorAll('.mo_ldap_local_feedback_checkbox').forEach(function(otherCheckbox) {
									if (!otherCheckbox.checked) {
										otherCheckbox.disabled = true;
									}
								});
							} else {
								document.querySelectorAll('.mo_ldap_local_feedback_checkbox').forEach(function(otherCheckbox) {
									otherCheckbox.disabled = false;
								});
							}
						});
					});

					function editEmailAddress(){
						document.querySelector('#mo_ldap_local_query_mail').removeAttribute('readonly');
						document.querySelector('#mo_ldap_local_query_mail').focus();
						return false;
					}
				</script>
				<style>
					.mo_ldap_local_editable{
						text-align:center;
						width:1em;
						height:1em;
						cursor: pointer;
					}
					.mo_ldap_local_sm {
						text-align:center;
						width: 24px;
						height: 24px;
						padding: 1vw;
					}

					input[type=radio] {
						display: none;
					}

					.mo_ldap_local_sm:hover {
						opacity:0.6;
						cursor: pointer;
					}

					.mo_ldap_local_sm:active {
						opacity:0.4;
						cursor: pointer;
					}

					input[type=radio]:checked + label > .mo_ldap_local_sm {
						border: 2px solid #21ecdc;
					}
				</style>
			</form>
			<form name="mo_ldap_feedback_form_close" method="post" action="" id="mo_ldap_feedback_form_close">
				<?php wp_nonce_field( 'mo_ldap_skip_feedback' ); ?>
				<input type="hidden" name="option" value="mo_ldap_skip_feedback"/>
			</form>
		</div>
	</div>

	<script>
		var active_plugins = document.getElementsByClassName('deactivate');
		for (i = 0; i<active_plugins.length;i++) {
			var plugin_deactivate_link = active_plugins.item(i).getElementsByTagName('a').item(0);
			var plugin_name = plugin_deactivate_link.href;
			if (plugin_name.includes('plugin=ldap-login-for-intranet-sites')) {
				jQuery(plugin_deactivate_link).click(function () {

				var mo_ldap_modal = document.getElementById('ldapModal');
				var span = document.getElementsByClassName("mo_ldap_close")[0];
				mo_ldap_modal.style.display = "block";
				window.onclick = function (event) {
					if (event.target == mo_ldap_modal) {
						mo_ldap_modal.style.display = "none";
					}
				}
				return false;
				});
				break;
			}
		}
	</script>
	</body>
