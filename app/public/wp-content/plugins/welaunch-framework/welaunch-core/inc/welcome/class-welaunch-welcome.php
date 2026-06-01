<?php
/**
 * weLaunch Welcome Class
 *
 * @class weLaunch_Core
 * @version 4.0.0
 * @package weLaunch Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'weLaunch_Welcome', false ) ) {

	/**
	 * Class weLaunch_Welcome
	 */
	class weLaunch_Welcome {

		/**
		 * Min capacity.
		 *
		 * @var string The capability users should have to view the page
		 */
		public $minimum_capability = 'manage_options';

		/**
		 * Display version.
		 *
		 * @var string
		 */
		public $display_version = '';

		/**
		 * Is loaded.
		 *
		 * @var bool
		 */
		public $welaunch_loaded = false;

		/**
		 * Get things started
		 *
		 * @since 1.4
		 */
		public function __construct() {
			// Load the welcome page even if a weLaunch panel isn't running.
			add_action( 'init', array( $this, 'init' ), 999 );
		}

		/**
		 * Class init.
		 */
		public function init() {
			if ( $this->welaunch_loaded ) {
				return;
			}

			if(isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'welaunch_add_license') {
				if(isset($_POST['license']) && !empty($_POST['license'])) {

					$license = trim($_POST['license']);

					$domain = parse_url( get_site_url() )['host'];
					// $adminEmail = sanitize_email( get_option( 'admin_email' ) );
					$current_user = wp_get_current_user();
					$adminEmail = $current_user->user_email;

					$url = 'https://www.welaunch.io/updates/paddle/activate.php?license=' . $license . '&domain=' . $domain . '&email=' .  $adminEmail;

					$ch = curl_init( $url );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					$result = json_decode( curl_exec($ch) );

					if (curl_errno($ch)) { 
					   print curl_error($ch); 
					   die();
					} 
					
					curl_close($ch);
					if(empty($result)) {
						wp_die('Empty result');
					}

					if(!$result->ok) {
						wp_die($result->message);
					}

					if(empty($result->item_name)) {
						wp_die('Empty item namme');
					}

					if($result->ok) {
						
						if(is_multisite()) {

							$existingLicenses = get_network_option(0, 'welaunch_licenses');

							if(!$existingLicenses) {
								$toSave = array(
									$result->item_name => $result->license
								);
							} else {
								$toSave = $existingLicenses;
								$toSave[$result->item_name] = $result->license;
							}

							update_network_option(0, 'welaunch_licenses', $toSave);
						} else {
							$existingLicenses = get_option('welaunch_licenses');

							if(!$existingLicenses) {
								$toSave = array(
									$result->item_name => $result->license
								);
							} else {
								$toSave = $existingLicenses;
								$toSave[$result->item_name] = $result->license;
							}
												
							update_option('welaunch_licenses', $toSave);
						}
						welaunch_validate_licenses_cron_handler(true);
						echo '<div class="notice notice-success"><p>License ' . $result->license . ' addded.</p></div>';
					}
				}
			}

			if(isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'welaunch_remove_license') {
				if(isset($_POST['item']) && !empty($_POST['item']) && isset($_POST['license']) && !empty($_POST['license'])) {

					$license = $_POST['license'];
					$url = 'https://www.welaunch.io/updates/account/remove.php?license=' . $license;

					$ch = curl_init( $url );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					$result = json_decode( curl_exec($ch) );

					if (curl_errno($ch)) { 
					   print curl_error($ch); 
					   die();
					} 
					
					curl_close($ch);
					
					if(empty($result)) {
						die('Could not remove license.');
					}

					if(!$result->status) {
						wp_die($result->msg);
					}
					
					if(is_multisite()) {

						$existingLicenses = get_network_option(0, 'welaunch_licenses');
						if(isset($existingLicenses[$_POST['item']])) {
							unset($existingLicenses[$_POST['item']]);
						}
						update_network_option(0, 'welaunch_licenses', $existingLicenses);

					} else {

						$existingLicenses = get_option('welaunch_licenses');
						if(isset($existingLicenses[$_POST['item']])) {
							unset($existingLicenses[$_POST['item']]);
						}
						update_option('welaunch_licenses', $existingLicenses);
					}

    				welaunch_validate_licenses_cron_handler(true);
    				echo '<div class="notice notice-success"><p>License ' . $license . ' removed.</p></div>';


				}
			}

			$this->welaunch_loaded = true;
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );

			if ( isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				if ( 'welaunch-' === substr( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 0, 9 ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$version               = explode( '.', weLaunch_Core::$version );
					$this->display_version = $version[0] . '.' . $version[1];
				}
			}
		}

		/**
		 * Do Redirect.
		 */
		public function do_redirect() {
			if ( ! defined( 'WP_CLI' ) ) {
				wp_safe_redirect( esc_url( admin_url( add_query_arg( array( 'page' => 'welaunch-framework' ), 'tools.php' ) ) ) );
				exit();
			}
		}


		/**
		 * Register the Dashboard Pages which are later hidden but these pages
		 * are used to render the What's weLaunch pages.
		 *
		 * @access public
		 * @since  1.4
		 * @return void
		 */
		public function admin_menus() {
			$page = 'add_management_page';

			// About Page.
			$page( esc_html__( 'What is weLaunch Framework?', 'welaunch-framework' ), esc_html__( 'weLaunch', 'welaunch-framework' ), $this->minimum_capability, 'welaunch-framework', array( $this, 'about_screen' ) );

			// Support Page.


			remove_submenu_page( 'tools.php', 'welaunch-status' );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( 'welaunch/pro/welcome/admin/menu', $page, $this );
		}

		/**
		 * Navigation tabs
		 *
		 * @access public
		 * @since  1.9
		 * @return void
		 */
		public function tabs() {
			$selected = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'welaunch-framework'; // phpcs:ignore WordPress.Security.NonceVerification
			$nonce    = wp_create_nonce( 'welaunch-support-hash' );

			?>
			<input type="hidden" id="welaunch_support_nonce" value="<?php echo esc_attr( $nonce ); ?>"/>
			<h2 class="nav-tab-wrapper">
				<a
					class="nav-tab <?php echo( 'welaunch-framework' === $selected ? 'nav-tab-active' : '' ); ?>"
					href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'welaunch-framework' ), 'tools.php' ) ) ); ?>">
					<?php esc_attr_e( 'What is weLaunch?', 'welaunch-framework' ); ?>
				</a>


				<?php // phpcs:ignore WordPress.NamingConventions.ValidHookName ?>
				<?php do_action( 'welaunch/pro/welcome/admin/tab', $selected ); ?>

			</h2>
			<?php
		}

		/**
		 * Render About Screen
		 *
		 * @access public
		 * @since  1.4
		 * @return void
		 */
		public function about_screen() {
			// Stupid hack for WordPress alerts and warnings.
			echo '<div class="wrap" style="height:0;overflow:hidden;"><h2></h2></div>';

			require_once 'views/about.php';
		}

		/**
		 * Render Get Support Screen
		 *
		 * @access public
		 * @since  1.9
		 * @return void
		 */
		public function get_support() {
			// Stupid hack for WordPress alerts and warnings.
			echo '<div class="wrap" style="height:0;overflow:hidden;"><h2></h2></div>';

			require_once 'views/support.php';
		}

		/**
		 * Action.
		 */
		public function actions() {
			?>
			<p class="welaunch-actions">
				<a href="http://docs.welaunch.io/" class="docs button button-primary">Docs</a>
				<a
					href="https://wordpress.org/support/view/plugin-reviews/welaunch-framework?filter=5#postform"
					class="review-us button button-primary"
					target="_blank">Review Us</a>
				<a
					href="https://www.paypal.me/welaunchframework"
					class="review-us button button-primary" target="_blank">Donate</a>
				<a
					href="https://twitter.com/share"
					class="twitter-share-button"
					data-url="https://welaunch.io"
					data-text="Supercharge your WordPress experience with weLaunch.io, the world's most powerful and widely used WordPress interface builder."
					data-via="weLaunchFramework" data-size="large" data-hashtags="weLaunch">Tweet</a>
				<?php
				$options = weLaunch_Helpers::get_plugin_options();
				$nonce   = wp_create_nonce( 'welaunch_framework_demo' );

				$query_args = array(
					'page'                   => 'welaunch-framework',
					'welaunch-framework-plugin' => 'demo',
					'nonce'                  => $nonce,
				);

				if ( $options['demo'] ) {
					?>
					<a
						href="<?php echo esc_url( admin_url( add_query_arg( $query_args, 'tools.php' ) ) ); ?>"
						class=" button-text button-demo"><?php echo esc_html__( 'Disable Panel Demo', 'welaunch-framework' ); ?></a>
					<?php
				} else {
					?>
					<a
						href="<?php echo esc_url( admin_url( add_query_arg( $query_args, 'tools.php' ) ) ); ?>"
						class=" button-text button-demo active"><?php echo esc_html__( 'Enable Panel Demo', 'welaunch-framework' ); ?></a>
					<?php
				}

				?>
				<script>
					!function( d, s, id ) {
						var js, fjs = d.getElementsByTagName( s )[0],
							p = /^http:/.test( d.location ) ? 'http' : 'https';
						if ( !d.getElementById( id ) ) {
							js = d.createElement( s );
							js.id = id;
							js.src = p + '://platform.twitter.com/widgets.js';
							fjs.parentNode.insertBefore( js, fjs );
						}
					}( document, 'script', 'twitter-wjs' );
				</script>
			</p>
			<?php
		}
	}
}
