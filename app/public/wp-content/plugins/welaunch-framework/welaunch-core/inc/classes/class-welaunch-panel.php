<?php
/**
 * weLaunch Panel Class
 *
 * @class weLaunch_Panel
 * @version 3.0.0
 * @package weLaunch Framework/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'weLaunch_Panel', false ) ) {

	/**
	 * Class weLaunch_Panel
	 */
	class weLaunch_Panel {

		/**
		 * weLaunchFramwrok object pointer.
		 *
		 * @var object
		 */
		public $parent = null;

		/**
		 * Path to templates dir.
		 *
		 * @var null|string
		 */
		public $template_path = null;

		/**
		 * Original template path.
		 *
		 * @var null
		 */
		public $original_path = null;

		/**
		 * Sets the path from the arg or via filter. Also calls the panel template function.
		 *
		 * @param object $parent weLaunchFramework pointer.
		 */
		public function __construct( $parent ) {
			$this->parent        = $parent;
			$this->template_path = weLaunch_Core::$dir . 'templates/panel/';
			$this->original_path = weLaunch_Core::$dir . 'templates/panel/';

			if ( ! empty( $this->parent->args['templates_path'] ) ) {
				$this->template_path = trailingslashit( $this->parent->args['templates_path'] );
			}

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$this->template_path = trailingslashit( apply_filters( "welaunch/{$this->parent->args['opt_name']}/panel/templates_path", $this->template_path ) );
		}

		/**
		 * Class init.
		 */
		public function init() {
			$this->panel_template();
		}

		/**
		 * Loads the panel templates where needed and provides the container for weLaunch
		 */
		private function panel_template() {
			if ( $this->parent->args['dev_mode'] ) {
				$this->template_file_check_notice();
			}

			/**
			 * Action 'welaunch/{opt_name}/panel/before'
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/{$this->parent->args['opt_name']}/panel/before" );

			echo '<div class="wrap"><h2></h2></div>'; // Stupid hack for WordPress alerts and warnings.

			echo '<div class="clear"></div>';
			echo '<div class="wrap welaunch-wrap-div woocommerce_page_' . esc_attr( $this->parent->args['opt_name'] ) . '_options ' . esc_attr( $this->parent->args['opt_name'] ) . '" data-opt-name="' . esc_attr( $this->parent->args['opt_name'] ) . '">';

			// Do we support JS?
			echo '<noscript><div class="no-js">' . esc_html__( 'Warning- This options panel will not work properly without javascript!', 'welaunch-framework' ) . '</div></noscript>';

			// Security is vital!
			echo '<input type="hidden" class="welaunch-ajax-security" data-opt-name="' . esc_attr( $this->parent->args['opt_name'] ) . '" id="ajaxsecurity" name="security" value="' . esc_attr( wp_create_nonce( 'welaunch_ajax_nonce' . $this->parent->args['opt_name'] ) ) . '" />';

			/**
			 * Action 'welaunch/page/{opt_name}/form/before'
			 *
			 * @param object $this weLaunchFramework
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/page/{$this->parent->args['opt_name']}/form/before", $this );

			if ( is_rtl() ) {
				$this->parent->args['class'] = ' welaunch-rtl';
			}

			$this->get_template( 'container.tpl.php' );

			/**
			 * Action 'welaunch/page/{opt_name}/form/after'
			 *
			 * @param object $this weLaunchFramework
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/page/{$this->parent->args['opt_name']}/form/after", $this );

			echo '<div class="clear"></div>';
			echo '</div>';

			if ( true === $this->parent->args['dev_mode'] ) {
				echo '<br /><div class="welaunch-timer">' . esc_html( get_num_queries() ) . ' queries in ' . esc_html( timer_stop( 0 ) ) . ' seconds<br/>weLaunch is currently set to developer mode.</div>';
			}

			/**
			 * Action 'welaunch/{opt_name}/panel/after'
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/{$this->parent->args['opt_name']}/panel/after" );

			global $weLaunchLicenses;

		    $lics      = welaunch_get_licenses();              // ['assoc'=>[item=>key], 'flat'=>[keys...]]
		    $weLaunchLicenses     = is_array($lics['assoc']) ? $lics['assoc'] : [];
		    $statusOpt = wl_get_option(WELAUNCH_LIC_STATUS_OPTION, []);
		    $statusMap = (is_array($statusOpt) && !empty($statusOpt['licenses'])) ? (array)$statusOpt['licenses'] : [];
		    $lastCheck = wl_get_option(WELAUNCH_LIC_LAST_CHECK, 0);
		    $lastCheckStr = $lastCheck ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int)$lastCheck) : 'never';

			$plugin = str_replace( array('_', '-options'), array('-', ''), $this->parent->args['opt_name']);

			if(isset($weLaunchLicenses['woocommerce-product-catalog-mode'])) {
				$weLaunchLicenses['woocommerce-catalog-mode'] = $weLaunchLicenses['woocommerce-product-catalog-mode'];
			}

			if(isset($weLaunchLicenses['woocommerce-ultimate-pdf-invoices'])) {
				$weLaunchLicenses['woocommerce-pdf-invoices'] = $weLaunchLicenses['woocommerce-ultimate-pdf-invoices'];
				$weLaunchLicenses['woocommerce-packing-slips'] = $weLaunchLicenses['woocommerce-ultimate-pdf-invoices'];
			}
			

			$domainOk = false;
			if(isset($weLaunchLicenses[$plugin])) {
	            $licKey = trim((string)$weLaunchLicenses[$plugin]);
	            $st     = isset($statusMap[$licKey]) && is_array($statusMap[$licKey]) ? $statusMap[$licKey] : [];
	            $isValid   = !empty($st['valid']);
	            $isExpired = !empty($st['expired']);
	            $domainOk  = array_key_exists('domain_ok', $st) ? (bool)$st['domain_ok'] : null;
	            $validUntil= !empty($st['valid_until']) ? $st['valid_until'] : null;
	            $message   = !empty($st['message']) ? $st['message'] : ($isValid ? 'OK' : '—');
            }

            if(
	            isset($weLaunchLicenses['agency-license']) ||
	            isset($weLaunchLicenses['agency-bundle']) ||
	            isset($weLaunchLicenses['woocommerce-plugin-bundle']) ||
	            isset($weLaunchLicenses['wordpress-plugin-bundle']) ||
	            isset($weLaunchLicenses['welaunch-plugin-bundle'])
            ) {
            	return;
            }

			if (
			    (
			        isset($weLaunchLicenses[$plugin]) && $domainOk === false
			    ) || (
			        !isset($weLaunchLicenses[$plugin])
			    )
			) {
							



				?>
				<div class="welaunch-wrap-div"></div>
				<!-- Put this anywhere on that admin page (footer is fine) -->
				<style>
				  /* Overlay box */
				  .welaunch-license-mask{
				    position:absolute; inset:0; z-index:999999;
				    display:flex; align-items:center; justify-content:center;
				    background:rgba(255,255,255,.55);
				    -webkit-backdrop-filter: blur(6px) saturate(115%);
				    backdrop-filter: blur(6px) saturate(115%);
				    padding:24px;
				  }
				  .welaunch-license-mask__inner{
				    max-width:720px; width:min(92vw,720px);
				    background:rgba(255,255,255,.95);
				    border:1px solid #c3c4c7; border-radius:10px;
				    box-shadow:0 10px 30px rgba(0,0,0,.08);
				    text-align:center; padding:28px 24px; font:inherit;
				  }
				  .welaunch-license-mask__title{
				    font-size:18px; margin:0 0 .5rem; line-height:1.4;
				  }
				   .welaunch-license-mask__buttons{ display:flex; gap:8px; justify-content:center; flex-wrap:wrap; }
				  .welaunch-license-mask__desc{ margin:.25rem 0 1rem; }
				  .welaunch-license-mask .button-hero{ font-size:14px; padding:8px 16px; }
				  /* Make wrapper a positioning context */
				  .welaunch-wrap-div{ position:relative; }
				  .welaunch-button {
				  	background: #3171ee !important;
				    border-radius: 50px !important;
				    color: #fff;
				    border: none;
				    padding: 0 15px !important;
				  }
				  .welaunch-button.button-secondary {
				  	background: #fff !important;
				  	color: #3171ee !important;
				  }
				</style>

				<script>
				(function () {
				  // 1) Find the panel wrapper from your markup
				  var wrap = document.querySelector('.welaunch-container'); // page root
				  if (!wrap) return;

				  // 2) Build the tools.php URL using WP’s ajaxurl when available
				  function buildToolsUrl(){
				    try{
				      if (typeof ajaxurl === 'string'){
				        var u = new URL(ajaxurl, window.location.origin);
				        u.pathname = u.pathname.replace(/admin-ajax\.php$/, 'tools.php');
				        u.search = 'page=welaunch-framework';
				        return u.toString();
				      }
				    }catch(e){}
				    // Fallback that works on most WP installs
				    try { return new URL('tools.php?page=welaunch-framework', window.location.origin + '/wp-admin/').toString(); }
				    catch(e){ return '/wp-admin/tools.php?page=welaunch-framework'; }
				  }
				  var toolsUrl = buildToolsUrl();

				// Buy URL uses the PHP $plugin variable
				  var buyUrl = '<?php
				    $base = "https://welaunch.io/product/";
				    $slug = isset($plugin) ? rawurlencode($plugin) : "";
				    $url  = $base . $slug . ($slug ? "/" : "");
				    echo esc_url( $url );
				  ?>';

				  // 3) Create overlay
				  var overlay = document.createElement('div');
				  overlay.className = 'welaunch-license-mask';
				  overlay.innerHTML = '' +
				    '<div class="welaunch-license-mask__inner" role="dialog" aria-live="polite" aria-label="License required">' +
				      '<p class="welaunch-license-mask__title"><strong>This plugin is not registered.</strong></p>' +
				      '<p class="welaunch-license-mask__desc">Please activate your license now.</p>' +
				      '<div class="welaunch-license-mask__buttons">' +
				        '<a class="welaunch-button button button-primary button-hero" href="' + toolsUrl + '">Activate license</a>' +
				        '<a class="welaunch-button button button-secondary button-hero" href="' + buyUrl + '" target="_blank" rel="noopener">Buy license</a>' +
				      '</div>' +

				    '</div>';

				  // 4) Append and wire up interactions
				  wrap.appendChild(overlay);
				  overlay.addEventListener('click', function(e){
				    // clicking outside the box also goes to activation
				    if (!e.target.closest('.welaunch-license-mask__inner')) {
				      window.location.href = toolsUrl;
				    }
				  });

				  // 5) Fallback blur for browsers without backdrop-filter
				  var supportsBackdrop = window.CSS && (CSS.supports('backdrop-filter','blur(4px)') || CSS.supports('-webkit-backdrop-filter','blur(4px)'));
				  if (!supportsBackdrop){
				    // Blur the main options area behind the overlay
				    var main = wrap.querySelector('.welaunch-container') || wrap;
				    main.style.filter = 'blur(4px)';
				    main.style.pointerEvents = 'none';
				  }
				})();
				</script>

				<?php
			}
		}

		/**
		 * Calls the various notification bars and sets the appropriate templates.
		 */
		public function notification_bar() {
			if ( isset( $this->parent->transients['last_save_mode'] ) ) {

				if ( 'import' === $this->parent->transients['last_save_mode'] ) {
					/**
					 * Action 'welaunch/options/{opt_name}/import'
					 *
					 * @param object $this weLaunchFramework
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					do_action( "welaunch/options/{$this->parent->args['opt_name']}/import", $this, $this->parent->transients['changed_values'] );

					echo '<div class="admin-notice notice-blue saved_notice">';

					/**
					 * Filter 'welaunch-imported-text-{opt_name}'
					 *
					 * @param string  translated "settings imported" text
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					echo '<strong>' . esc_html( apply_filters( "welaunch-imported-text-{$this->parent->args['opt_name']}", esc_html__( 'Settings Imported!', 'welaunch-framework' ) ) ) . '</strong>';
					echo '</div>';
				} elseif ( 'defaults' === $this->parent->transients['last_save_mode'] ) {
					/**
					 * Action 'welaunch/options/{opt_name}/reset'
					 *
					 * @param object $this weLaunchFramework
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					do_action( "welaunch/options/{$this->parent->args['opt_name']}/reset", $this );

					echo '<div class="saved_notice admin-notice notice-yellow">';

					/**
					 * Filter 'welaunch-defaults-text-{opt_name}'
					 *
					 * @param string  translated "settings imported" text
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					echo '<strong>' . esc_html( apply_filters( "welaunch-defaults-text-{$this->parent->args['opt_name']}", esc_html__( 'All Defaults Restored!', 'welaunch-framework' ) ) ) . '</strong>';
					echo '</div>';
				} elseif ( 'defaults_section' === $this->parent->transients['last_save_mode'] ) {
					/**
					 * Action 'welaunch/options/{opt_name}/section/reset'
					 *
					 * @param object $this weLaunchFramework
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					do_action( "welaunch/options/{$this->parent->args['opt_name']}/section/reset", $this );

					echo '<div class="saved_notice admin-notice notice-yellow">';

					/**
					 * Filter 'welaunch-defaults-section-text-{opt_name}'
					 *
					 * @param string  translated "settings imported" text
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					echo '<strong>' . esc_html( apply_filters( "welaunch-defaults-section-text-{$this->parent->args['opt_name']}", esc_html__( 'Section Defaults Restored!', 'welaunch-framework' ) ) ) . '</strong>';
					echo '</div>';
				} elseif ( 'normal' === $this->parent->transients['last_save_mode'] ) {
					/**
					 * Action 'welaunch/options/{opt_name}/saved'
					 *
					 * @param mixed $value set/saved option value
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					do_action( "welaunch/options/{$this->parent->args['opt_name']}/saved", $this->parent->options, $this->parent->transients['changed_values'] );

					echo '<div class="saved_notice admin-notice notice-green">';

					/**
					 * Filter 'welaunch-saved-text-{opt_name}'
					 *
					 * @param string translated "settings saved" text
					 */

					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					echo '<strong>' . esc_html( apply_filters( "welaunch-saved-text-{$this->parent->args['opt_name']}", esc_html__( 'Settings Saved!', 'welaunch-framework' ) ) ) . '</strong>';
					echo '</div>';
				}

				unset( $this->parent->transients['last_save_mode'] );

				$this->parent->transient_class->set();
			}

			/**
			 * Action 'welaunch/options/{opt_name}/settings/changes'
			 *
			 * @param mixed $value set/saved option value
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/options/{$this->parent->args['opt_name']}/settings/change", $this->parent->options, $this->parent->transients['changed_values'] );

			echo '<div class="welaunch-save-warn notice-yellow">';

			/**
			 * Filter 'welaunch-changed-text-{opt_name}'
			 *
			 * @param string translated "settings have changed" text
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			echo '<strong>' . esc_html( apply_filters( "welaunch-changed-text-{$this->parent->args['opt_name']}", esc_html__( 'Settings have changed, you should save them!', 'welaunch-framework' ) ) ) . '</strong>';
			echo '</div>';

			/**
			 * Action 'welaunch/options/{opt_name}/errors'
			 *
			 * @param array $this ->errors error information
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/options/{$this->parent->args['opt_name']}/errors", $this->parent->errors );

			echo '<div class="welaunch-field-errors notice-red">';
			echo '<strong>';
			echo '<span></span> ' . esc_html__( 'error(s) were found!', 'welaunch-framework' );
			echo '</strong>';
			echo '</div>';

			/**
			 * Action 'welaunch/options/{opt_name}/warnings'
			 *
			 * @param array $this ->warnings warning information
			 */

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/options/{$this->parent->args['opt_name']}/warnings", $this->parent->warnings );

			echo '<div class="welaunch-field-warnings notice-yellow">';
			echo '<strong>';
			echo '<span></span> ' . esc_html__( 'warning(s) were found!', 'welaunch-framework' );
			echo '</strong>';
			echo '</div>';
		}

		/**
		 * Used to intitialize the settings fields for this panel. Required for saving and redirect.
		 */
		private function init_settings_fields() {
			// Must run or the page won't redirect properly.
			settings_fields( "{$this->parent->args['opt_name']}_group" );
		}

		/**
		 * Enable file deprecate warning from core.  This is necessary because the function is considered private.
		 *
		 * @return bool
		 */
		public function tick_file_deprecate_warning() {
			return true;
		}

		/**
		 * Used to select the proper template. If it doesn't exist in the path, then the original template file is used.
		 *
		 * @param string $file Path to template file.
		 */
		public function get_template( $file ) {
			if ( empty( $file ) ) {
				return;
			}

			if ( file_exists( $this->template_path . $file ) ) {
				$path = $this->template_path . $file;
			} else {
				$path = $this->original_path . $file;
			}

			// Shim for v3 templates.
			if ( ! file_exists( $path ) ) {
				$old_file = $file;

				add_filter( 'deprecated_file_trigger_error', array( $this, 'tick_file_deprecate_warning' ) );

				$file = str_replace( '-', '_', $file );

				_deprecated_file( esc_html( $file ), '4.0', esc_html( $old_file ), 'Please replace this outdated template with the current one from the weLaunch core.' );

				if ( file_exists( $this->template_path . $file ) ) {
					$path = $this->template_path . $file;
				} else {
					$path = $this->original_path . $file;
				}
			}

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/{$this->parent->args['opt_name']}/panel/template/" . $file . '/before' );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$path = apply_filters( "welaunch/{$this->parent->args['opt_name']}/panel/template/" . $file, $path );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/{$this->parent->args['opt_name']}/panel/template/" . $file . '/after' );

			require $path;
		}

		/**
		 * Scan the template files.
		 *
		 * @param string $template_path Path to template file.
		 *
		 * @return array
		 */
		public function scan_template_files( $template_path ) {
			$files  = scandir( $template_path );
			$result = array();
			if ( $files ) {
				foreach ( $files as $key => $value ) {
					if ( ! in_array( $value, array( '.', '..' ), true ) ) {
						if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
							$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
							foreach ( $sub_files as $sub_file ) {
								$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
							}
						} else {
							$result[] = $value;
						}
					}
				}
			}

			return $result;
		}

		/**
		 * Show a notice highlighting bad template files
		 */
		public function template_file_check_notice() {
			if ( $this->original_path === $this->template_path ) {
				return;
			}

			$core_templates = $this->scan_template_files( $this->original_path );

			foreach ( $core_templates as $file ) {
				$developer_theme_file = false;

				if ( file_exists( $this->template_path . $file ) ) {
					$developer_theme_file = $this->template_path . $file;
				}

				if ( $developer_theme_file ) {
					$core_version      = weLaunch_Helpers::get_template_version( $this->original_path . $file );
					$developer_version = weLaunch_Helpers::get_template_version( $developer_theme_file );

					if ( $core_version && $developer_version && version_compare( $developer_version, $core_version, '<' ) && isset( $this->parent->args['dev_mode'] ) && ! empty( $this->parent->args['dev_mode'] ) ) {
						?>
						<div id="message" class="error welaunch-message">
							<p>
								<strong><?php esc_html_e( 'Your panel has bundled copies of weLaunch Framework template files that are outdated!', 'welaunch-framework' ); ?></strong>&nbsp;&nbsp;<?php esc_html_e( 'Please update them now as functionality issues could arise.', 'welaunch-framework' ); ?></a></strong>
							</p>
						</div>
						<?php

						return;
					}
				}
			}
		}

		/**
		 * Outputs the HTML for a given section using the WordPress settings API.
		 *
		 * @param mixed $k Section number of settings panel to display.
		 */
		private function output_section( $k ) {
			do_settings_sections( $this->parent->args['opt_name'] . $k . '_section_group' );
		}
	}
}

if ( ! class_exists( 'welaunchCorePanel' ) ) {
	class_alias( 'weLaunch_Panel', 'welaunchCorePanel' );
}


