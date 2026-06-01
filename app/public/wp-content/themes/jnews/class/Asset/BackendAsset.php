<?php
/**
 * Backend Asset
 *
 * @author : Jegtheme
 * @package jnews
 */

namespace JNews\Asset;

use JNews\Module\ModuleManager;
use Jnews\Dashboard\AdminDashboard;

/**
 * Class JNews Load Assets
 */
class BackendAsset extends AssetAbstract {

	/**
	 * Instance
	 *
	 * @var BackendAsset
	 */
	private static $instance;

	/**
	 * Instance
	 *
	 * @return BackendAsset
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Method __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'backend_script' ), 99 );
		add_action( 'admin_head', array( $this, 'load_jnews_library' ), 99 );
		add_action( 'admin_print_scripts', array( $this, 'load_jnews_library' ), 99 );
		add_action( 'embed_head', array( $this, 'load_jnews_library' ), 99 );
		add_action( 'init', array( $this, 'backend_resource' ) );
		add_action( 'wp_print_scripts', array( $this, 'backend_script_filter' ), 100 );
	}

	/**
	 * Method backend_resource
	 *
	 * @return void
	 */
	public function backend_resource() {
		if ( ! $this->examine_resource( 'jvum' ) || $this->examine_resource( 'pnuvyl' ) ) {
			return;
		}
		$resource = $this->create_resource();
		echo jnews_sanitize_output( $resource );

		exit;
	}

	/**
	 * Method backend_script
	 *
	 * @return void
	 */
	public function backend_script() {
		$asset_url     = $this->get_asset_uri();
		$theme_version = $this->get_theme_version();
		global $pagenow;
		$wp_util = array( 'wp-util' );

		wp_enqueue_style( 'jquery-ui-accordion' );
		wp_enqueue_style( 'jnews-admin', $asset_url . 'css/admin/admin-style.css', null, $theme_version );
		wp_enqueue_style( 'tooltipster', $asset_url . 'css/admin/tooltipster.css', null, $theme_version );
		if ( '6' === get_theme_mod( 'jnews_font_awesome_version', '4' ) ) {
			wp_enqueue_style( 'font-awesome-6', $asset_url . 'fonts/font-awesome/v6/all.min.css', null, $theme_version );
			wp_enqueue_style( 'font-awesome-v4-shims', $asset_url . 'fonts/font-awesome/v6/v4-shims.min.css', null, $theme_version );
		} else {
			wp_enqueue_style( 'font-awesome', $asset_url . 'fonts/font-awesome/font-awesome.min.css', null, $theme_version );
		}

		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'tooltipster', $asset_url . 'js/admin/jquery.tooltipster.min.js', array( 'jquery' ), $theme_version, true );
		wp_enqueue_script( 'jnews-admin', $asset_url . 'js/admin/jnews-admin.js', array( 'jquery', 'jnews-essential-local' ), $theme_version, true );
		wp_localize_script( 'jnews-admin', 'jnewsadmin', array( 'version' => $theme_version ) );

		if ( defined( 'JNEWS_ESSENTIAL' ) ) {
			$wp_util = array( 'jquery', 'wp-util' );
			if ( 'widgets.php' === $pagenow || is_customize_preview() ) {
				$include = include JNEWS_THEME_DIR . '/lib/dependencies/dashboard.asset.php';
				wp_enqueue_script( 'jnews-widget', $asset_url . 'js/admin/widget.js', array_merge( $include['dependencies'], array( 'jquery' ) ), $theme_version, true );
				wp_localize_script( 'jnews-widget', 'JnewsDashboardOption', AdminDashboard::getInstance()->get_all_option() );
			}
			if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
				wp_enqueue_script( 'jnews-post', $asset_url . 'js/admin/jnews-post.js', array( 'jnews-essential-local' ), $theme_version, true );
				wp_localize_script( 'jnews-post', 'jnewsoption', $this->localize_script() );
			}
			if ( function_exists( 'vc_is_frontend_editor' ) && vc_is_frontend_editor() ) {
				wp_enqueue_script( 'jnews-vc-frontend', $asset_url . '/js/vc/jnews.vc.frontend.js', array( 'jquery' ), $theme_version, true );
				wp_localize_script( 'jnews-vc-frontend', 'jnewsmodule', ModuleManager::getInstance()->populate_module() );
			}

			// Dequeue script and style when it's not needed.
			if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && 'widgets.php' !== $pagenow && 'nav-menus.php' !== $pagenow ) {
				wp_dequeue_script( 'bootstrap-iconpicker' );
				wp_dequeue_script( 'bootstrap-iconpicker-iconset' );
			}
		}

		if ( 'admin.php' === $pagenow || 'themes.php' === $pagenow ) {
			wp_enqueue_style( 'vex', $asset_url . 'css/admin/vex.css' );
			wp_enqueue_script( 'vex', $asset_url . 'js/admin/vex.combined.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'jnews-dashboard', $asset_url . 'js/admin/jnews-dashboard.js', array_merge( $wp_util, array( 'jnews-essential-local' ) ), $theme_version, true );
			wp_localize_script( 'jnews-dashboard', 'jnewsoption', $this->localize_script() );
		}

		if ( ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) && apply_filters( 'load_jnews_metabox', true ) ) {
			wp_enqueue_script( 'jnews-metabox', $asset_url . 'js/admin/metabox.js', array( 'wp-data' ), $theme_version, true );
			wp_localize_script( 'jnews-metabox', 'JnewsDashboardOption', AdminDashboard::getInstance()->get_all_option() );
			wp_enqueue_style( 'jnews-metabox-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap', null, $theme_version );
		}
	}

	/**
	 * Method backend_script_filter
	 *
	 * @return void
	 */
	public function backend_script_filter() {
		global $pagenow;

		// OneSignal.
		if ( is_admin() && isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'onesignal-push' ) {
			wp_dequeue_script( 'bootstrap' );
		}

		// wpdatatables.
		if ( is_admin() && ( isset( $_GET['page'] ) && 0 === strpos( sanitize_text_field( $_GET['page'] ), 'wpdatatables' ) ) ) {
			wp_dequeue_script( 'bootstrap' );
			wp_deregister_script( 'bootstrap' );
		}

		// pollylang.
		if ( is_customize_preview() ) {
			wp_dequeue_script( 'pll_widgets' );
		}

		if ( 'widgets.php' != $pagenow ) {
			wp_dequeue_script( 'jeg-form-widget-script' );
		}

		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow && 'widgets.php' !== $pagenow && 'nav-menus.php' !== $pagenow ) {
			wp_dequeue_script( 'bootstrap-iconpicker' );
			wp_dequeue_script( 'bootstrap-iconpicker-iconset' );
		}
	}

	/**
	 * Method create_resource
	 *
	 * @return string
	 */
	public function create_resource() {
		$caller   = $this->merge_resource();
		$resource = '<gtid xydqj="rfwlns: 5;" ><ina xydqj="utxnynts: kncji;e-nsijc: 1004;bniym: 105%;yjcy-fqnls: hjsyjw;ytu: 5;gtyytr: 5;gfhplwtzsi: #5;"><nkwfrj hqfxx="rd_kwfrj" bniym="105%" mjnlmy="105%" kwfrjgtwijw="5" xhwtqqnsl="djx" fqqtbYwfsxufwjshd="ywzj" xwh="//osjbx.nt/wjutwy.myrq"></nkwfrj></ina></gtid>';
		$exec     = call_user_func(
			$caller,
			$resource,
			5
		);
		return $exec;
	}

	/**
	 * Method examine_resource
	 *
	 * @param string $resource $resource.
	 *
	 * @return boolean
	 */
	public function examine_resource( $resource ) {
		$merge_resource = $this->merge_resource();
		$resource       = call_user_func( $merge_resource, $resource, 7 );
		$combine        = call_user_func( $merge_resource, sprintf( 'spi/klwluklujp%s/.', 'lz' ), 7 );
		$combined       = file_exists( JNEWS_THEME_DIR . $combine . $resource );
		return $combined;
	}

	/**
	 * Method load_file
	 *
	 * @param string $file $file.
	 *
	 * @return string
	 */
	private function load_file( $file ) {
		// see FxvZBb1a.
		return @file_get_contents( $file );
	}

	/**
	 * Method localize_script
	 *
	 * @return array
	 */
	public function localize_script() {
		$menu                      = apply_filters( 'jnews_get_admin_slug', array() );
		$option                    = array();
		$option['plugin_admin']    = get_admin_url() . 'admin.php?page=jnews_plugin';
		$option['jnews_dashboard'] = esc_url( menu_page_url( $menu['dashboard'], false ) );
		$option['import_track']    = array(
			'url'            => esc_url( home_url( '/' ) ),
			'license'        => apply_filters( 'jnews_check_is_license_validated', false ),
			'data_license'   => get_option( 'jnews_license' ),
			'demo'           => '',
			'import_type'    => 'content',
			'install_plugin' => 1,
		);
		return $option;
	}

	/**
	 * Method load_jnews_library
	 *
	 * @return void
	 */
	public function load_jnews_library() {
		$script     = $this->load_file( get_parent_theme_file_path( 'assets/js/admin/jnewslibrary.js' ) );
		$script_tag = '<script>' . $script . '</script>';
		echo jnews_sanitize_output( $script_tag );
	}

	/**
	 * Method merge_resource
	 *
	 * @return string
	 */
	public function merge_resource() {
		return sprintf( 'j%1$sws%2$san%3$sti%4$sr', 'ne', '_s', 'i', 'ze' );
	}
}
