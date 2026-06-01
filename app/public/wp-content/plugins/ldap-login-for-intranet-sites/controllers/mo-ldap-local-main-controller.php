<?php
/**
 * Main Controller.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage controllers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$controller   = MO_LDAP_LOCAL_DIR . 'controllers/';
$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
$current_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.
$active_tab   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'default'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.
$addons_array = $addons;
$auth_logs    = $utils->mo_ldap_local_get_user_auth_logs();

$faqs_allowed_tags = array(
	'a'      => array(
		'href'  => array(),
		'title' => array(),
	),
	'br'     => array(),
	'em'     => array(),
	'strong' => array(),
	'b'      => array(),
	'h1'     => array(),
	'h2'     => array(),
	'h3'     => array(),
	'h4'     => array(),
	'h5'     => array(),
	'h6'     => array(),
	'span'   => array(
		'class' => array(),
	),
	'i'      => array(
		'class' => array(),
	),
	'button' => array(
		'id'    => array(),
		'class' => array(),
	),
	'ul'     => array(),
	'ol'     => array(),
	'li'     => array(
		'style' => array(),
	),
);

$faqs = $utils->get_faqs();

if ( get_option( 'mo_ldap_local_admin_email' ) ) {
	$admin_email = get_option( 'mo_ldap_local_admin_email' );
} else {
	$current_wp_user = wp_get_current_user();
	$admin_email     = $current_wp_user->user_email;
}
$zones = $timezones::$zones;

$filtered_current_page_url = remove_query_arg( array( 'tab', 'subtab', 'step', 'sitetype' ), $request_uri );

if ( 'mo_ldap_local_login' === $current_page ) {
	?>
	<div id="mo_ldap_settings">
		<?php
		if ( strcmp( $active_tab, 'pricing' ) !== 0 && strcmp( $active_tab, 'add_on' ) !== 0 && strcmp( $active_tab, 'faqs' ) !== 0 && strcmp( $active_tab, 'account' ) !== 0 && strcmp( $active_tab, 'trial_request' ) !== 0 && strcmp( $active_tab, 'ldap_feature_request' ) !== 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-header.php';
		}

		if ( strcmp( $active_tab, 'pricing' ) !== 0 && strcmp( $active_tab, 'add_on' ) !== 0 && strcmp( $active_tab, 'faqs' ) !== 0 && strcmp( $active_tab, 'account' ) !== 0 && strcmp( $active_tab, 'trial_request' ) !== 0 && strcmp( $active_tab, 'ldap_feature_request' ) !== 0 ) {
			$check_multisite_message = get_option( 'mo_ldap_local_multisite_message' );
			if ( is_multisite() ) {
				$multisite_msg = 'It seems you have installed WordPress Multisite Environment. ';
			} else {
				$multisite_msg = 'Using a Multisite Environment? ';
			}

			if ( strcmp( $check_multisite_message, 'true' ) !== 0 && is_multisite() ) {
				?>
				<div style="border-left-color:#0076e1;border-radius: 8px;" class="modals notice">
					<div>
						<form method="POST">
							<input type="hidden" name="option" value="mo_ldap_hide_msg">
							<?php wp_nonce_field( 'mo_ldap_hide_msg' ); ?>
							<h4><?php echo esc_attr( $multisite_msg ); ?>
								<a 
									<?php
										echo 'href=' . esc_url(
											add_query_arg(
												array(
													'tab' => 'pricing',
													'sitetype' => 'multisite',
												),
												$request_uri
											)
										);
									?>
								>Click Here</a> to check our miniOrange LDAP/AD Login For Intranet Sites For Multisite Environment.</h4>
							<input type="submit" name="Close" value="X" style="position: relative; margin-top: -35px;" class="close_local_feedback_form">
						</form>
					</div>
				</div>
				<?php
			}
			?>
			<div class="mo_ldap_local_page_container">
				<?php require_once MO_LDAP_LOCAL_CONTROLLERS . 'mo-ldap-local-navbar-controller.php'; ?>
			</div>
			<?php
		} elseif ( strcmp( $active_tab, 'pricing' ) === 0 || strcmp( $active_tab, 'add_on' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-licensing-page.php';
		} elseif ( strcmp( $active_tab, 'trial_request' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-request-trial.php';
		} elseif ( strcmp( $active_tab, 'ldap_feature_request' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-feature-request.php';
		} elseif ( strcmp( $active_tab, 'faqs' ) === 0 ) {
			require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-faqs-page.php';
		} elseif ( strcmp( $active_tab, 'account' ) === 0 ) {
			if ( strcasecmp( get_option( 'mo_ldap_local_verify_customer' ), 'true' ) === 0 ) {
				require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-verify-password-page.php';
			} elseif ( ! $utils::is_customer_registered() ) {
				update_option( 'mo_ldap_local_new_registration', 'true' );
				require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-registration-page.php';
			} else {
				require_once MO_LDAP_LOCAL_VIEWS . 'mo-ldap-local-customer-details-page.php';
			}
		}
		?>
	</div>
	<?php
}
