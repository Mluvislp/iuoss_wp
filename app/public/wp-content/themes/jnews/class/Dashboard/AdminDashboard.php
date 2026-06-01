<?php
/**
 * Dashboard Class
 *
 * @author Jegtheme
 */

namespace JNews\Dashboard;

use JNews\Importer;
use JNews\Util\Api\Plugin;
use JNews\Util\RestAPI;
use JNews\Util\ValidateLicense;
use Jeg\Customizer\Customizer;

use JNews\Customizer\AdsOption;
use JNews\Customizer\ArchiveOption;
use JNews\Customizer\BlockOption;
use JNews\Customizer\CategoryOption;
use JNews\Customizer\CodeOption;
use JNews\Customizer\FontOption;
use JNews\Customizer\FooterOption;
use JNews\Customizer\HeaderOption;
use JNews\Customizer\ImageOption;
use JNews\Customizer\LayoutOption;
use JNews\Customizer\OtherOption;
use JNews\Customizer\SchemeStyleOption;
use JNews\Customizer\SearchOption;
use JNews\Customizer\SingleOption;
use JNews\Customizer\SocialOption;

/**
 * Class Init
 *
 * @package JNews\Dashboard
 */
class AdminDashboard {
	/**
	 * Type
	 *
	 * @var string
	 */
	const TYPE = 'jnews-panel';

	/**
	 * Instance of Dashboard class
	 *
	 * @var Dashboard
	 */
	private static $instance;

	/**
	 * @var \JNews\Template
	 */
	private $template;

	private $register_location = array(
		'toplevel_page_jnews',
		'appearance_page_jnews',
	);

	/**
	 * Instance of Dashboard
	 *
	 * @return Dashboard
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Init constructor.
	 */
	private function __construct() {
		if ( is_admin() ) {
			$this->setup_init();
			$this->setup_hook();
		}
		$this->load_hook();
	}

	/**
	 * Setup Init
	 */
	private function setup_init() {
		$this->template = new \JNews\Template( JNEWS_THEME_DIR . 'class/Dashboard/template/' );
	}

	/**
	 * Setup Hook
	 */
	private function setup_hook() {
		add_filter( 'jnews_get_admin_slug', array( &$this, 'admin_slug' ) );
		add_action( 'after_switch_theme', array( &$this, 'switch_themes' ), 99 );
		add_filter( 'jnews_get_admin_menu', array( &$this, 'get_admin_menu' ) );
		add_action( 'in_admin_header', array( $this, 'remove_notice' ), 1000 );
		add_filter( 'admin_footer_text', '__return_empty_string', 11 );
		add_filter( 'update_footer', '__return_empty_string', 11 );
		add_action( 'admin_notices', array( $this, 'plugin_notice' ) );
		add_action( 'wp_ajax_dismiss_plugin_notice', array( $this, 'dismiss_plugin_notice' ) );
		add_action( 'wp_ajax_nopriv_dismiss_plugin_notice', array( $this, 'dismiss_plugin_notice' ) );
		if ( ! get_option( 'jnews_remove_old_theme_mods' ) ) {
			$this->delete_old_jnews_theme_mods();
		}
	}

	/**
	 * Method dismiss_plugin_notice
	 *
	 * @return void
	 */
	public function dismiss_plugin_notice() {
		update_option( 'jnews_dismiss_plugin_notice', true );
	}

	/**
	 * Plugin update notice
	 */
	public function plugin_notice() {
		if ( ( defined( 'JNEWS_FRONT_TRANSLATION_VERSION' ) && version_compare( JNEWS_FRONT_TRANSLATION_VERSION, '11.0.2', '<' ) ||
		defined( 'JNEWS_MIGRATION_JANNAH_VERSION' ) && version_compare( JNEWS_MIGRATION_JANNAH_VERSION, '11.0.1', '<' ) ||
		defined( 'JNEWS_MIGRATION_JMAGZ_VERSION' ) && version_compare( JNEWS_MIGRATION_JMAGZ_VERSION, '11.0.1', '<' ) ||
		defined( 'JNEWS_MIGRATION_NEWSPAPER_VERSION' ) && version_compare( JNEWS_MIGRATION_NEWSPAPER_VERSION, '11.0.1', '<' ) ||
		defined( 'JNEWS_MIGRATION_PUBLISHER_VERSION' ) && version_compare( JNEWS_MIGRATION_PUBLISHER_VERSION, '11.0.1', '<' ) ||
		defined( 'JNEWS_MIGRATION_SAHIFA_VERSION' ) && version_compare( JNEWS_MIGRATION_SAHIFA_VERSION, '11.0.1', '<' ) ||
		defined( 'JNEWS_MIGRATION_SOLEDAD_VERSION' ) && version_compare( JNEWS_MIGRATION_SOLEDAD_VERSION, '11.0.1', '<' ) ||
		defined( 'JNEWS_MIGRATION_NEWSMAG_VERSION' ) && version_compare( JNEWS_MIGRATION_NEWSMAG_VERSION, '11.0.1', '<' ) ) &&
		! get_option( 'jnews_dismiss_plugin_notice', false ) ) {
			?>
				<div class="notice notice-error">
					<p>
						<?php
						printf(
							wp_kses(
								__(
									'<span class="jnews-notice-heading">Update Required for JNews Plugin</span>
									<span style="display: block;">We recommend updating the JNews plugin to access the latest features and enhancements. Click the button below to navigate to the plugin dashboard:</span>
									<span class="jnews-notice-button">
										<a href="%s" class="button-primary">Go to Plugin Dashboard</a>
									</span>
									',
									'jnews'
								),
								array(
									'strong' => array(),
									'span'   => array(
										'style' => true,
										'class' => true,
									),
									'a'      => array(
										'href'  => true,
										'class' => true,
									),
								)
							),
							esc_url( get_admin_url() . 'admin.php?page=jnews&path=plugin&section=require-update' )
						);
						?>
					</p>
					<span class="close-button plugin"><i class="fa fa-times"></i></span>
				</div>
			<?php
		}

		$metabox_updated_plugins = array(
			'JNEWS_ESSENTIAL'           => array(
				'plugin_name' => 'JNews - Essential',
				'version'     => 'JNEWS_ESSENTIAL_VERSION',
			),
			'JNEWS_SOCIAL_SHARE'        => array(
				'plugin_name' => 'JNews - Social Share',
				'version'     => 'JNEWS_SOCIAL_SHARE_VERSION',
			),
			'JNEWS_META_HEADER'         => array(
				'plugin_name' => 'JNews - Meta Header',
				'version'     => 'JNEWS_META_HEADER_VERSION',
			),
			'JNEWS_BOOKMARK'            => array(
				'plugin_name' => 'JNews - Bookmark',
				'version'     => 'JNEWS_BOOKMARK_VERSION',
			),
			'JNEWS_FOOD_RECIPE'         => array(
				'plugin_name' => 'JNews - Food Recipe',
				'version'     => 'JNEWS_FOOD_RECIPE_VERSION',
			),
			'JNEWS_PAYWALL'             => array(
				'plugin_name' => 'JNews - Paywall',
				'version'     => 'JNEWS_PAYWALL_VERSION',
			),
			'JNEWS_REVIEW'              => array(
				'plugin_name' => 'JNews - Review',
				'version'     => 'JNEWS_REVIEW_VERSION',
			),
			'JNEWS_SPLIT'               => array(
				'plugin_name' => 'JNews - Split',
				'version'     => 'JNEWS_SPLIT_VERSION',
			),
			'JNEWS_VIDEO'               => array(
				'plugin_name' => 'JNews - Video',
				'version'     => 'JNEWS_VIDEO_VERSION',
			),
			'JNEWS_JSONLD'              => array(
				'plugin_name' => 'JNews - JSON-LD',
				'version'     => 'JNEWS_JSONLD_VERSION',
			),
			'JNEWS_PAY_WRITER'          => array(
				'plugin_name' => 'JNews - Pay Writer',
				'version'     => 'JNEWS_PAY_WRITER_VERSION',
			),
			'JNEWS_MIGRATION_NEWSPAPER' => array(
				'plugin_name' => 'JNews - Migration Newspaper',
				'version'     => 'JNEWS_MIGRATION_NEWSPAPER_VERSION',
			),
			'JNEWS_OPTION_CUSTOMIZER'   => array(
				'plugin_name' => 'JNews - Extended Category Option',
				'version'     => 'JNEWS_OPTION_CUSTOMIZER_VERSION',
			),
			'JNEWS_PODCAST'             => array(
				'plugin_name' => 'JNews - Podcast',
				'version'     => 'JNEWS_PODCAST_VERSION',
			),
		);

		$plugin_list = '';

		foreach ( $metabox_updated_plugins as $slug => $plugin_data ) {
			if ( ( defined( $slug ) && ! defined( $plugin_data['version'] ) ) || ( defined( $plugin_data['version'] ) && version_compare( constant( $plugin_data['version'] ), '11.6.0', '<' ) ) ) {
				$plugin_list .= '<li>' . $plugin_data['plugin_name'] . '</li>';
			}
		}

		if ( ! empty( $plugin_list ) ) {
			echo wp_kses(
				' <div class="notice notice-error jnews-update-plugin-notice">
					<span class="jnews-notice-heading">' . __( 'JNews Plugin Updates Required', 'jnews' ) . '</span>
					<span>' . __( 'To ensure compatibility with your current JNews theme version, please update the following plugins:', 'jnews' ) . '</span><ul>' .
					$plugin_list .
					'</ul>
					<span class="jnews-notice-button">
						<a href="' . esc_url( get_admin_url() . 'admin.php?page=jnews&path=plugin&section=require-update' ) . '" class="button-primary">' . __( 'Update Plugins Now', 'jnews' ) . '</a>
					</span>
				</div>',
				array(
					'div'    => array(
						'class' => true,
					),
					'i'      => array(
						'class' => true,
					),
					'ul'     => array(),
					'li'     => array(),
					'strong' => array(),
					'span'   => array(
						'style' => true,
						'class' => true,
					),
					'a'      => array(
						'href'  => true,
						'class' => true,
					),
				)
			);
		}
	}

	/**
	 * Switch Themes
	 */
	public function switch_themes() {
		$slug = $this->admin_slug();
		global $pagenow;

		if ( is_admin() && 'themes.php' === $pagenow && isset( $_GET['activated'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . $slug['dashboard'] ) );
			exit;
		}
	}

	/**
	 * Admin slug
	 *
	 * @return array
	 */
	public function admin_slug() {
		$admin_slug = array(
			'dashboard'     => 'jnews',
			'plugin'        => 'jnews_plugin',
			'import'        => 'jnews_import',
			'documentation' => 'jnews_documentation',
			'system'        => 'jnews_system',
			'option'        => 'jnews_option',
		);

		return apply_filters( 'jnews_admin_slug', $admin_slug );
	}

	/**
	 * Get admin menu
	 *
	 * @return array
	 */
	public function get_admin_menu() {
		$slug      = $this->admin_slug();
		$admin_url = 'themes.php';
		$menu      = array();

		if ( defined( 'JNEWS_ESSENTIAL' ) ) {
			$admin_url = 'admin.php';
			$menu[]    = array(
				'title'        => esc_html__( 'Customize Style', 'jnews' ),
				'menu'         => esc_html__( 'Customize Style', 'jnews' ),
				'slug'         => 'customize.php',
				'action'       => false,
				'priority'     => 55,
				'show_on_menu' => true,
			);
		}

		$menu = array_merge(
			array(
				array(
					'title'        => esc_html__( 'Dashboard', 'jnews' ),
					'menu'         => esc_html__( 'JNews Dashboard', 'jnews' ),
					'slug'         => $slug['dashboard'],
					'action'       => array( &$this, 'load_jnews_dashboard' ),
					'priority'     => 51,
					'show_on_menu' => true,
				),
				array(
					'title'        => esc_html__( 'Import Demo & Style', 'jnews' ),
					'menu'         => esc_html__( 'Import Demo & Style', 'jnews' ),
					'slug'         => $slug['dashboard'] . '&path=' . $slug['import'],
					'action'       => array( &$this, 'load_jnews_dashboard' ),
					'priority'     => 53,
					'show_on_menu' => true,
				),
				array(
					'title'        => esc_html__( 'Install Plugin', 'jnews' ),
					'menu'         => esc_html__( 'Install Plugin', 'jnews' ),
					'slug'         => $slug['dashboard'] . '&path=' . $slug['plugin'],
					'action'       => array( &$this, 'load_jnews_dashboard' ),
					'priority'     => 52,
					'show_on_menu' => true,
				),
				array(
					'title'        => esc_html__( 'System Status', 'jnews' ),
					'menu'         => esc_html__( 'System Status', 'jnews' ),
					'slug'         => $slug['dashboard'] . '&path=' . $slug['system'],
					'action'       => array( &$this, 'load_jnews_dashboard' ),
					'priority'     => 57,
					'show_on_menu' => true,
				),
				array(
					'title'        => esc_html__( 'Video Documentation', 'jnews' ),
					'menu'         => esc_html__( 'Video Documentation', 'jnews' ),
					'slug'         => $slug['dashboard'] . '&path=' . $slug['documentation'],
					'action'       => array( &$this, 'load_jnews_dashboard' ),
					'priority'     => 56,
					'show_on_menu' => true,
				),
			),
			$menu,
		);

		return apply_filters( 'jnews_admin_menu', $menu );
	}

	/**
	 * Remove notice only on JNews dashboard
	 */
	public function remove_notice() {
		if ( in_array( get_current_screen()->id, $this->register_location, true ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/**
	 * Get admin dashboard menu
	 *
	 * @return array
	 */
	public static function get_dashboard_menu() {
		$allmenu = apply_filters( 'jnews_get_admin_menu', array() );
		$menus   = array();
		foreach ( $allmenu as $menu ) {
			$plugin  = isset( $menu['plugin'] ) ? $menu['plugin'] : false;
			$pageurl = menu_page_url( $menu['slug'], false );
			if ( 'customize.php' === $menu['slug'] ) {
				$pageurl = admin_url() . 'customize.php';
			}
			if ( 'jnews' === $menu['slug'] ) {
				$pageurl = '';
			}
			$menus[] = array(
				'name'   => $menu['slug'],
				'title'  => $menu['title'],
				'url'    => $pageurl,
				'plugin' => $plugin,
			);
		}
		return $menus;
	}

	/**
	 * Get theme detail information
	 *
	 * @return array
	 */
	public static function get_theme_info() {
		$theme = wp_get_theme();
		$data  = array(
			'name'    => $theme->get( 'Name' ),
			'version' => $theme->get( 'Version' ),
		);
		if ( $theme->parent() && null !== $theme->parent() ) {
			$data['parentName']    = $theme->parent()->get( 'Name' );
			$data['parentVersion'] = $theme->parent()->get( 'Version' );
		}
		return $data;
	}

	/**
	 * Method jnews_dashboard_config
	 *
	 * @return array
	 */
	public static function demo_data() {
		$data                      = array();
		$importer                  = get_option( Importer::$option );
		$data['installed_style']   = isset( $importer['style'] ) ? $importer['style'] : false;
		$data['installed_content'] = isset( $importer['content'] ) ? $importer['content'] : false;
		$data['content']           = array();

		return $data;
	}

	/**
	 * JNews Dashboard Config
	 *
	 * @return array
	 */
	public static function jnews_dashboard() {
		$config = array();
		// Theme data.
		$config['demoData']    = self::demo_data();
		$config['themeInfo']   = self::get_theme_info();
		$config['menus']       = self::get_dashboard_menu();
		$config['licenseData'] = ValidateLicense::getInstance()->jnews_dashboard_config();

		// Theme additional data.
		$config['userId']      = get_current_user_id();
		$config['currentTime'] = ( new \DateTime() )->getTimestamp();

		// Theme URL.
		$config['nonceAPI']    = wp_create_nonce( 'wp_rest' );
		$config['endpointAPI'] = '/wp-json/' . RestAPI::ENDPOINT;
		$config['themeURL']    = JNEWS_THEME_URL;

		// Plugin.
		$config['pluginData'] = Plugin::jnews_dashboard_config();

		// Site URL.
		$config['adminURL']  = untrailingslashit( get_admin_url() );
		$config['domainURL'] = home_url();

		// External URL.
		$config['JegthemeServerURL'] = JEGTHEME_SERVER;
		$config['JNewsServerURL']    = JNEWS_THEME_SERVER;

		// Directory Status
		$wp_upload_dir             = wp_upload_dir();
		$config['UploadDirStatus'] = wp_is_writable( $wp_upload_dir['basedir'] ) ? '1' : '0';
		$config['PluginDirStatus'] = wp_is_writable( WP_PLUGIN_DIR ) ? '1' : '0';

		return $config;
	}

	/**
	 * Load JNews Dashboard Page
	 */
	public function load_jnews_dashboard() {
		?>
		<div id="jnews-dashboard"></div>
		<?php
	}

	/**
	 * START New React Dashboard
	 */

	/**
	 * Load Hook
	 */
	private function load_hook() {
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', array( $this, 'setup_parent_page' ) );
			add_action( 'admin_menu', array( $this, 'setup_child_page' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_react' ) );
		}
	}

	/**
	 * Type
	 *
	 * @var string
	 */
	const JNEWS_DASHBOARD = 'jnews';

	/**
	 * Menu for dashboard
	 *
	 * @var array
	 */
	public static $dashboard_menu = array();

	/**
	 * Initialize Menu
	 */
	public function define_menu() {
		self::$dashboard_menu['dashboard'] = array(
			'name'     => esc_html__( 'Dashboard', 'jnews' ),
			'priority' => 40,
			'type'     => 'menu',
		);

		self::$dashboard_menu['demos'] = array(
			'name'     => esc_html__( 'Import Demo & Style', 'jnews' ),
			'priority' => 50,
			'type'     => 'menu',
		);

		self::$dashboard_menu['plugin'] = array(
			'name'     => esc_html__( 'Plugin', 'jnews' ),
			'priority' => 60,
			'type'     => 'menu',
		);

		self::$dashboard_menu['install-plugin'] = array(
			'name'     => esc_html__( 'Install Plugin', 'jnews' ),
			'priority' => 60.5,
			'type'     => 'menu',
			'parent'   => 'plugin',
			'alias'    => 'plugin',
		);

		if ( defined( 'JNEWS_ESSENTIAL' ) ) {
			self::$dashboard_menu['customizer'] = array(
				'name'     => esc_html__( 'Customize Style', 'jnews' ),
				'priority' => 70,
				'type'     => 'menu',
			);
		}

		self::$dashboard_menu['system-status'] = array(
			'name'     => esc_html__( 'System Status', 'jnews' ),
			'priority' => 80,
			'type'     => 'menu',
		);

		self::$dashboard_menu['documentation'] = array(
			'name'     => esc_html__( 'Documentation', 'jnews' ),
			'priority' => 90,
			'type'     => 'menu',
		);

		self::$dashboard_menu = apply_filters( 'jnews_dashboard_menu_item', self::$dashboard_menu );
	}

	/**
	 * Setup parent page menu
	 */
	public function setup_parent_page() {
		$this->define_menu();
		add_menu_page( esc_html__( 'JNews', 'jnews' ), esc_html__( 'JNews', 'jnews' ), 'edit_theme_options', self::JNEWS_DASHBOARD, null, 'none', 3.001 );
	}

		/**
		 * Get child pages
		 *
		 * @return void
		 */
	public function setup_child_page() {
		$path    = admin_url( 'admin.php?page=' . self::JNEWS_DASHBOARD . '&path=' );
		$subpath = '&subpath=';
		$pages   = array();

		foreach ( self::$dashboard_menu as $key => $menu ) {
			if ( $menu ) {
				$pages[] = array(
					'title'    => $menu['name'],
					'menu'     => $menu['name'],
					'slug'     => 'url' === $menu['type'] ? $menu['url'] : $path . $key,
					'position' => $menu['priority'],
				);
				if ( 1 === count( $pages ) ) {
					$pages[ count( $pages ) - 1 ]['slug']     = self::JNEWS_DASHBOARD;
					$pages[ count( $pages ) - 1 ]['callback'] = array( $this, 'dashboard_page' );
				}
				if ( isset( $menu['parent'] ) ) {
					$pages[ count( $pages ) - 1 ]['class'] = self::JNEWS_DASHBOARD . '-child-menu';
					$pages[ count( $pages ) - 1 ]['slug']  = 'url' === $menu['type'] ? $menu['url'] : $path . $menu['parent'] . $subpath . $key;
				}
				if ( isset( $menu['alias'] ) ) {
					$pages[ count( $pages ) - 1 ]['slug'] = 'url' === self::$dashboard_menu[ $menu['alias'] ]['type'] ? self::$dashboard_menu[ $menu['alias'] ]['url'] : $path . $menu['alias'];
				}
			}
		}

		/** Sorting Page menus by Positions */
		usort(
			$pages,
			function ( $a, $b ) {
				$menu_a = floatval( $a['position'] );
				$menu_b = floatval( $b['position'] );

				if ( $menu_a < $menu_b ) {
					return -1;
				} elseif ( $menu_a > $menu_b ) {
					return 1;
				} else {
					return 0;
				}
			}
		);

		foreach ( $pages as $key => $page ) {
			add_submenu_page(
				self::JNEWS_DASHBOARD,
				$page['title'],
				$page['menu'],
				'edit_theme_options',
				$page['slug'],
				isset( $page['callback'] ) ? $page['callback'] : ''
			);
			$this->add_child_menu_class( $key, $page );
		}
	}

	/**
	 * Add Class Selector to Child Menu
	 *
	 * @param int   $key Menu offset.
	 * @param array $menu List of menu.
	 */
	private function add_child_menu_class( $key, $menu ) {
		global $submenu;

		if ( isset( $menu['class'] ) ) {
			// @codingStandardsIgnoreStart
			$submenu[self::JNEWS_DASHBOARD][ $key ][4] = $menu['class'];
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook .
	 */
	public function enqueue_scripts_react( $hook ) {
		$register_location = array(
			'post.php',
			'toplevel_page_' . self::JNEWS_DASHBOARD,
		);

		wp_enqueue_style(
			self::JNEWS_DASHBOARD . '-dashboard',
			JNEWS_THEME_URL . '/assets/css/admin/dashboard.css',
			array(),
			JNEWS_THEME_VERSION
		);

		if ( in_array( $hook, $register_location, true ) ) {
			$include            = include JNEWS_THEME_DIR . '/lib/dependencies/dashboard.asset.php';
			$include            = apply_filters( 'jnews_include_dashboard', $include );
			$name_option_object = $this->get_object_name( self::JNEWS_DASHBOARD . '-dashboard-option', '-' );
			$option_object      = $this->get_all_option();

			wp_enqueue_media();

			wp_enqueue_script(
				self::JNEWS_DASHBOARD . '-dashboard',
				JNEWS_THEME_URL . '/assets/js/admin/dashboard.js',
				array_merge( $include['dependencies'], array( 'media-upload' ) ),
				JNEWS_THEME_VERSION,
				true
			);

			wp_localize_script( self::JNEWS_DASHBOARD . '-dashboard', $name_option_object, $option_object );

			wp_set_script_translations( self::JNEWS_DASHBOARD . '-dashboard', 'jnews' );
		}
	}

	/**
	 * Get all option
	 *
	 * @return array
	 */
	public function get_all_option() {
		$wp_upload_dir = wp_upload_dir();
		$fonts         = array();
		$customizer    = array();

		if ( method_exists( 'Jeg\Customizer\Customizer', 'load_all_font' ) ) {
			if ( class_exists( '\JNews\Customizer' ) ) {
				add_filter( 'jeg_font_typography', array( \JNews\Customizer::getInstance(), 'load_custom_font' ), 80 );
				add_filter( 'jeg_font_typography', array( \JNews\Customizer::getInstance(), 'load_typekit' ), 81 );
			}
			$fonts      = Customizer::get_instance()->load_all_font();
			$customizer = self::get_customizer();
		}
		$home_url   = home_url();
		$parsed_url = parse_url( $home_url );
		$scheme     = $parsed_url['scheme'];
		$host       = $parsed_url['host'];
		$domain_url = $scheme . '://' . $host; /* see 9wKrx9UJ */

		return array(
			'homeSlug'          => self::JNEWS_DASHBOARD,
			'menus'             => self::$dashboard_menu,
			'root'              => self::JNEWS_DASHBOARD,
			'system'            => self::system_status_info(),
			'themeVersion'      => JNEWS_THEME_VERSION,
			'demoData'          => self::demo_data(),
			'themeInfo'         => self::get_theme_info(),
			'licenseData'       => ValidateLicense::getInstance()->jnews_dashboard_config(),
			'endpointAPI'       => '/wp-json/' . RestAPI::ENDPOINT,
			'adminURL'          => untrailingslashit( get_admin_url() ),
			'domainURL'         => $domain_url,
			'JegthemeServerURL' => JEGTHEME_SERVER,
			'JNewsServerURL'    => JNEWS_THEME_SERVER,
			'nonceAPI'          => wp_create_nonce( 'wp_rest' ),
			'nonceMedia'        => wp_create_nonce( 'media-form' ),
			'UploadDirStatus'   => wp_is_writable( $wp_upload_dir['basedir'] ) ? '1' : '0',
			'PluginDirStatus'   => wp_is_writable( WP_PLUGIN_DIR ) ? '1' : '0',
			'themeURL'          => JNEWS_THEME_URL,
			'pluginData'        => Plugin::jnews_dashboard_config(),
			'customizer'        => $customizer,
			'fonts'             => $fonts,
			'rtl'               => is_rtl(),
			'imgURL'            => JNEWS_THEME_URL . '/assets/img/',
			'activate'          => add_query_arg(
				array(
					'siteurl'  => admin_url(),
					'callback' => '/admin.php?page=jnews', /* see 9wKrx9UJ */
					'item_id'  => JNEWS_THEME_ID,
				),
				JEGTHEME_SERVER . '/activate/'
			),
			'callback_url'      => apply_filters(
				'jnews_callback_url',
				array(
					'social_accounts' => array(
						'callback'   => admin_url( 'admin.php?page=jnews-social' ),
						'client_url' => get_site_url(),
					),
				)
			),
		);
	}


	/**
	 * Get Customizer.
	 *
	 * @return void
	 */
	public function get_customizer() {
		$customizer = jnews_customizer();

		new LayoutOption( $customizer, 171 );
		new HeaderOption( $customizer, 172 );
		new FooterOption( $customizer, 173 );
		new SingleOption( $customizer, 174 );
		new ImageOption( $customizer, 175 );
		new SocialOption( $customizer, 176 );
		new SearchOption( $customizer, 177 );
		new CategoryOption( $customizer, 180 );
		new ArchiveOption( $customizer, 185 );
		new FontOption( $customizer, 191 );
		new OtherOption( $customizer, 192 );
		new AdsOption( $customizer, 193 );
		new BlockOption( $customizer, 193 );
		new CodeOption( $customizer, 195 );
		new SchemeStyleOption( $customizer, 199 );

		$customizer->register_customizer();

		add_filter( 'jeg_register_lazy_section', array( \JNews\Customizer::getInstance(), 'register_lazy_category' ) );
		$fields = $customizer->get_all_fields();
		remove_filter( 'jeg_register_lazy_section', array( \JNews\Customizer::getInstance(), 'register_lazy_category' ) );

		if ( method_exists( 'Jeg\Customizer\Customizer', 'get_all_fields' ) ) {
			$fields = Customizer::get_instance()->get_all_fields();
		}
		foreach ( $fields as $key => &$item ) { // remove unused key.
			if ( isset( $item['postvar'] ) ) {
				unset( $item['postvar'] );
			}

			if ( isset( $item['output'] ) ) {
				unset( $item['output'] );
			}
		}

		$value = get_theme_mods();

		$value = array_merge(
			$value,
			array(
				'jnews_options' => get_option( 'jnews_option', array() ),
			)
		);

		// add extra fields
		$fields   = $this->extra_field( $fields );
		$sections = $this->extra_section( $customizer->get_sections() );
		$panels   = $this->extra_panels( $customizer->get_panels() );

		return array(
			'panels'   => $panels,
			'sections' => $sections,
			'fields'   => $fields,
			'value'    => $value,
		);
	}

	/**
	 * Add extra panel
	 *
	 * @param array $panels option.
	 */
	public function extra_panels( $panels ) {
		if ( isset( $panels['jnews_code_panel'] ) ) {
			$code                       = $panels['jnews_code_panel'];
			$code['title']              = esc_html__( 'Additonal Code', 'jnews' );
			$code['description']        = esc_html__( 'Additonal Code', 'jnews' );
			$panels['jnews_code_panel'] = $code;
		}

		return $panels;
	}

	/**
	 * Add extra section
	 *
	 * @param array $sections option.
	 */
	public function extra_section( $sections ) {
		$sections['jnews_css_code_section'] = array(
			'id'       => 'jnews_css_code_section',
			'title'    => esc_html__( 'Additional CSS', 'jnews' ),
			'panel'    => 'jnews_code_panel',
			'priority' => 251,
		);

		return $sections;
	}

	/**
	 * Add extra field
	 *
	 * @param array $fields option.
	 */
	public function extra_field( $fields ) {
		$fields['jnews_additional_header_js'] = array(
			'id'          => 'jnews_additional_header_js',
			'default'     => '',
			'type'        => 'jnews-code-js',
			'section'     => 'jnews_header_code_section',
			'label'       => esc_html__( 'Additional Javascript on Header', 'jnews' ),
			'description' => esc_html__( 'Put your additional javascript code right here. This code will be placed on header', 'jnews' ),
		);

		$fields['jnews_additional_js'] = array(
			'id'          => 'jnews_additional_js',
			'default'     => '',
			'type'        => 'jnews-code-js',
			'section'     => 'jnews_footer_code_section',
			'label'       => esc_html__( 'Additional Javascript on Footer', 'jnews' ),
			'description' => esc_html__( 'Put your additional javascript code right here. This code will be placed on footer', 'jnews' ),
		);

		$fields['jnews_additional_css'] = array(
			'id'          => 'jnews_additional_css',
			'default'     => wp_get_custom_css(),
			'type'        => 'jnews-code-css',
			'section'     => 'jnews_css_code_section',
			'label'       => esc_html__( 'Additional CSS', 'jnews' ),
			'description' => esc_html__( 'Put your additional CSS code right here.', 'jnews' ),
		);

		return $fields;
	}

	/**
	 * System Status.
	 *
	 * @return array
	 */
	public function system_status_info() {
		$status = array();

		/** Themes */
		$theme                    = wp_get_theme();
		$parent                   = wp_get_theme( get_template() );
		$status['theme_name']     = $theme->get( 'Name' );
		$status['theme_version']  = $theme->get( 'Version' );
		$status['is_child_theme'] = is_child_theme();
		$status['parent_theme']   = $parent->get( 'Name' );
		$status['parent_version'] = $parent->get( 'Version' );

		/** WordPress Environment */
		$wp_upload_dir              = wp_upload_dir();
		$status['home_url']         = home_url( '/' );
		$status['site_url']         = site_url();
		$status['login_url']        = wp_login_url();
		$status['wp_version']       = get_bloginfo( 'version', 'display' );
		$status['is_multisite']     = is_multisite();
		$status['wp_debug']         = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$status['memory_limit']     = ini_get( 'memory_limit' );
		$status['wp_memory_limit']  = WP_MEMORY_LIMIT;
		$status['wp_language']      = get_locale();
		$status['writeable_upload'] = wp_is_writable( $wp_upload_dir['basedir'] );
		$status['count_category']   = wp_count_terms( 'category' );
		$status['count_tag']        = wp_count_terms( 'post_tag' );

		/** Server Environment */
		$remote     = wp_remote_get( home_url() ); /*  see SubIhWCP*/
		$gd_support = array();

		if ( function_exists( 'gd_info' ) ) {
			foreach ( gd_info() as $key => $value ) {
				$gd_support[ $key ] = $value;
			}
		}

		$status['server_info']        = jnews_server_info();
		$status['php_version']        = PHP_VERSION;
		$status['post_max_size']      = ini_get( 'post_max_size' );
		$status['max_input_vars']     = ini_get( 'max_input_vars' );
		$status['max_execution_time'] = ini_get( 'max_execution_time' );
		$status['suhosin']            = extension_loaded( 'suhosin' );
		$status['imagick']            = extension_loaded( 'imagick' );
		$status['gd']                 = extension_loaded( 'gd' ) && function_exists( 'gd_info' );
		$status['gd_webp']            = extension_loaded( 'gd' ) && $gd_support['WebP Support'];
		$status['fileinfo']           = extension_loaded( 'fileinfo' ) && ( function_exists( 'finfo_open' ) || function_exists( 'mime_content_type' ) );
		$status['curl']               = extension_loaded( 'curl' ) && function_exists( 'curl_version' );
		$status['wp_remote_get']      = ! is_wp_error( $remote ) && $remote['response']['code'] >= 200 && $remote['response']['code'] < 300;

		/** Plugins */
		$status['plugins'] = $this->data_active_plugin();

		return $status;
	}

	/**
	 * Data active plugin
	 *
	 * @return array
	 */
	public function data_active_plugin() {
		$active_plugin = array();

		$plugins = array_merge(
			array_flip( (array) get_option( 'active_plugins', array() ) ),
			(array) get_site_option( 'active_sitewide_plugins', array() )
		);

		$plugins = array_intersect_key( get_plugins(), $plugins );

		if ( count( $plugins ) > 0 ) {
			foreach ( $plugins as $plugin ) {
				$item                = array();
				$item['uri']         = isset( $plugin['PluginURI'] ) ? esc_url( $plugin['PluginURI'] ) : '#';
				$item['name']        = isset( $plugin['Name'] ) ? $plugin['Name'] : esc_html__( 'unknown', 'jnews' );
				$item['author_uri']  = isset( $plugin['AuthorURI'] ) ? esc_url( $plugin['AuthorURI'] ) : '#';
				$item['author_name'] = isset( $plugin['Author'] ) ? $plugin['Author'] : esc_html__( 'unknown', 'jnews' );
				$item['version']     = isset( $plugin['Version'] ) ? $plugin['Version'] : esc_html__( 'unknown', 'jnews' );

				$content = esc_html__( 'by', 'jnews' );

				$active_plugin[] = array(
					'type'            => 'status',
					'title'           => $item['name'],
					'content'         => $content,
					'link'            => $item['author_uri'],
					'link_text'       => $item['author_name'],
					'additional_text' => $item['version'],
				);
			}
		}

		return $active_plugin;
	}

	/**
	 * Generate Object Name
	 *
	 * @param string $name Name that will convert to object name.
	 * @param string $separator Separator use in name.
	 *
	 * @return string
	 */
	private function get_object_name( $name, $separator ) {
		$object_name = str_replace( ' ', '', ucwords( str_replace( $separator, ' ', $name ) ) );
		return $object_name;
	}

	/**
	 * Load dashboard page
	 */
	public function dashboard_page() {
		?>
		<div id="jnews-admin-dashboard"></div>
		<div id="jnews-admin-dashboard-import"></div>
		<div id="jnews-admin-dashboard-plugin"></div>
		<?php
	}

	/**
	 * Temporary function to remove the old jnews theme mods
	 * Only fired once
	 */
	function delete_old_jnews_theme_mods() {
		$theme_options = get_theme_mods();
		foreach ( $theme_options as $key => $option ) {

			if ( strpos( $key, 'jnews_option' ) !== false ) {
				remove_theme_mod( $key );
			}
		}
		update_option( 'jnews_remove_old_theme_mods', true );
	}

	/**
	 * END New React Dashboard
	 */
}
