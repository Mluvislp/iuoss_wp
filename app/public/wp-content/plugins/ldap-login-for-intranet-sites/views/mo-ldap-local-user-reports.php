<?php
/**
 * Display User Authentication Reports.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class="mo_ldap_local_auth_reports_outer mo_ldap_local_outer3">
	<div class="mo_ldap_local_imp_exp_headings">User Report</div>
	<div class="mo_ldap_local_auth_report_config">
		<div class="mo_ldap_local_log_requests">
			<form name="f" id="user_report_form" method="post" action="" style="height: 50px;">
				<?php wp_nonce_field( 'user_report_logs' ); ?>
				<input type="hidden" name="option" value="user_report_logs" />
				<input type="checkbox" id="mo_ldap_local_user_report_log" name="mo_ldap_local_user_report_log" class="mo_ldap_local_toggle_switch_hide" value="1" <?php checked( esc_attr( strcasecmp( get_option( 'mo_ldap_local_user_report_log' ), '1' ) === 0 ) ); ?> />
				<label for="mo_ldap_local_user_report_log" class="mo_ldap_local_toggle_switch"></label>
				<label for="mo_ldap_local_user_report_log" class="mo_ldap_local_d_inline mo_ldap_local_bold_label">
					Log Authentication Requests
				</label>
			</form>
		</div>
		</div>
		<div>
		<div style="display: flex; padding-top: 15px;">
			<div class="mo_ldap_local_input_label_container">
				<label for="mo_ldap_local_role_for_imported_users">Select Logs Cleanup frequency:</label>
			</div>
			<div id="mo_ldap_local_multidropdown_clearlog_div_box" class="mo_ldap_local_multidropdown_clearlog_div"  >
				<div id="mo_ldap_local_multidropdown_clearlog_button_box" class="mo_ldap_local_multidropdown_button" >
					<div id="clear-log-select-text">Never</div>
					<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'scroll-down.svg' ); ?>" height="20px" width="20px">
				</div>	
				<div id="mo_ldap_local_showfreq_box" class="mo_ldap_local_showroles" >
					<div class="mo_ldap_local_log_label">
						<div class="mo_ldap_local_log_label_inside">
							<input type="checkbox" id="clearLogCheckBox" class="ckkBox all" checked/>
							<span style="padding-left: 15px">Never</span>
						</div>
					</div>
					<div class="mo_ldap_local_log_label" style="cursor: not-allowed;">
						<div class="mo_ldap_local_log_label_inside">
							<input style="cursor: not-allowed;" type="checkbox" class="ckkBox all" onclick="return false;"/>
							<span style="padding-left: 15px">Weekly</span>
						</div>
						<img class="crown-img" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" >
					</div>
					<div class="mo_ldap_local_log_label" style="cursor: not-allowed;">
						<div class="mo_ldap_local_log_label_inside">
							<input style="cursor: not-allowed;" type="checkbox" class="ckkBox all" onclick="return false;"/>
							<span style="padding-left: 15px">Montly</span>
						</div>
						<img class="crown-img" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" >
					</div>
					<div class="mo_ldap_local_log_label" style="cursor: not-allowed;">
						<div class="mo_ldap_local_log_label_inside">
							<input style="cursor: not-allowed;" type="checkbox" class="ckkBox all" onclick="return false;"/>
							<span style="padding-left: 15px">Yearly</span>
						</div>
						<img class="crown-img" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" >
					</div>
				</div>
			</div>
		</div>
		<div>
		<div style="display: flex; padding-top: 15px;">
			<div class="mo_ldap_local_input_label_container">
				<label for="mo_ldap_local_role_for_imported_users">Select Authentication Logs:</label>
			</div>
			<div id="mo_ldap_local_multidropdown_div_box" class="mo_ldap_local_multidropdown_div">
				<div id="mo_ldap_local_multidropdown_button_box" class="mo_ldap_local_multidropdown_button" >
					<div id="select-text">Error</div>
					<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'scroll-down.svg' ); ?>" height="20px" width="20px">
				</div>	
				<div id="mo_ldap_local_showroles_box" class="mo_ldap_local_showroles" >
					<div class="mo_ldap_local_log_label" style="cursor: not-allowed;">
						<div class="mo_ldap_local_log_label_inside">
							<input style="cursor: not-allowed;" type="checkbox" class="ckkBox all" onclick="return false;"/>
							<span style="padding-left: 15px">Success</span>
						</div>
						<img class="crown-img" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" >
					</div>
					<div class="mo_ldap_local_log_label">
						<div class="mo_ldap_local_log_label_inside">
							<input type="checkbox" class="ckkBox all" id="errorLogCheckbox" checked/>
							<span style="padding-left: 15px">Error</span>
						</div>
					</div>
					<div class="mo_ldap_local_log_label" style="cursor: not-allowed;">
						<div class="mo_ldap_local_log_label_inside">
							<input style="cursor: not-allowed;" type="checkbox" class="ckkBox all" onclick="return false;"/>
							<span style="padding-left: 15px">Both</span>
						</div>
						<img class="crown-img" src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" >
					</div>
				</div>
			</div>
			<?php
			$log_user_reporting = get_option( 'mo_ldap_local_user_report_log' );
			$user_logs_empty    = $utils::mo_ldap_is_user_logs_empty();

			if ( strcasecmp( $log_user_reporting, '1' ) === 0 && ! $user_logs_empty ) {
				?>
			<div class="mo_ldap_local_auth_report_config mo_ldap_local_log_requests mo_ldap_local_auth_report_position">
				<form method="post" action="" name="mo_ldap_local_authentication_report">
					<?php wp_nonce_field( 'mo_ldap_authentication_report' ); ?>
					<input type="hidden" name="option" value="mo_ldap_authentication_report" />
					<input type="button" class="mo_ldap_save_user_mapping" onclick="document.forms['mo_ldap_local_authentication_report'].submit();" value="Export Report" />
				</form>
				<form method="post" action="" name="mo_ldap_local_clear_authentication_report">
					<?php wp_nonce_field( 'mo_ldap_clear_authentication_report' ); ?>
					<input type="hidden" name="option" value="mo_ldap_clear_authentication_report" />
					<input type="button" class="mo_ldap_save_user_mapping" onclick="document.forms['mo_ldap_local_clear_authentication_report'].submit();" value="Clear Logs" />
					<br>
				</form>
			</div>
		</div>
		<br><br>
		<div>
			<table id="authreports" class="display">
				<thead class="mo_ldap_user_report_table_header">
					<tr>
						<th>Sr No.</th>
						<th>Username</th>
						<th>Timestamp</th>
						<th>Status</th>
						<th>Additional Information</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$index = 1;
					foreach ( $auth_logs as $log ) {
						?>
						<tr>
							<td><?php echo esc_html( $index ); ?></td>
							<td><?php echo esc_html( $log['user_name'] ); ?></td>
							<td><?php echo esc_html( $log['time'] ); ?></td>
							<td>
								<div class="mo_ldap_local_ldap_status_error">
									<img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'round-error.png' ); ?>" height="20px" width="20px">
									<?php echo esc_html( $log['ldap_status'] ); ?>
								</div>
							</td>
							<td><?php echo wp_kses( $log['ldap_error'], MO_LDAP_LOCAL_ESC_ALLOWED ); ?></td>
						</tr>
						<?php
						$index++;
					}
					?>
				</tbody>
			</table>
		</div>
				<?php
			} elseif ( strcasecmp( $log_user_reporting, '1' ) === 0 && $user_logs_empty ) {
				?>
			</div>
				<?php
				echo '<p class="mo_ldap_local_normal_font"> No audit logs are available currently. <p>';
			}
			?>
	</div>
</div>
