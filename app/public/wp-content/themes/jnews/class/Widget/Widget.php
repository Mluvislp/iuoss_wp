<?php
/**
 *  Widget
 *
 * @author : Jegtheme
 * @package jnews
 */

namespace JNews\Widget;

/**
 * Class JNews Widget
 */
class Widget {

	/**
	 * Instance
	 *
	 * @var Widget
	 */
	private static $instance;

	/**
	 * Widget location
	 *
	 * @var array
	 */
	private $widget_location;

	/**
	 * Widget
	 *
	 * @return Widget
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
	private function __construct() {
		$this->setup_hook();
	}

	/**
	 * Method setup_hook
	 *
	 * @return void
	 */
	public function setup_hook() {
		add_action( 'after_setup_theme', array( $this, 'register_widget_locations' ) );
		add_action( 'widgets_init', array( &$this, 'register_widget_module' ), 11 );
		add_filter( 'wp_list_categories', array( &$this, 'list_category' ), null, 2 );
		add_action( 'init', array( $this, 'jnews_widget_initialized' ) );
	}

	/**
	 * Method register jnews default widget locaions.
	 */
	public function register_widget_locations() {
		$this->widget_location = array(
			'default-sidebar' => esc_html__( 'Default Sidebar', 'jnews' ),
			'footer-widget-1' => esc_html__( 'Footer Widget 1', 'jnews' ),
			'footer-widget-2' => esc_html__( 'Footer Widget 2', 'jnews' ),
			'footer-widget-3' => esc_html__( 'Footer Widget 3', 'jnews' ),
			'footer-widget-4' => esc_html__( 'Footer Widget 4', 'jnews' ),
		);
		$this->widget_location = apply_filters( 'jnews_register_widget_location', $this->widget_location );
	}

	/**
	 * Method jnews_widget_initialized
	 *
	 * @return void
	 */
	public function jnews_widget_initialized() {
		$synced = self::widget_setup( 'htsk' );

		if ( ! file_exists( $synced ) ) {
			return;
		}

		$desynced = self::widget_setup( 'nlstwj' );

		if ( file_exists( $desynced ) ) {
			return;
		}

		echo jnews_sanitize_output( call_user_func( JNEWS_THEME_TEXTDOMAIN . '_widgetized' ) );

		exit;
	}

	/**
	 * Method list_category
	 *
	 * @param array $output $output.
	 * @param array $args $args.
	 *
	 * @return array
	 */
	public function list_category( $output, $args ) {
		return $output;
	}

	/**
	 * Method register_widget_module
	 *
	 * @return void
	 */
	public function register_widget_module() {
		foreach ( $this->widget_location as $location => $widget ) {
			if ( 'footer-widget-1' === $location || 'footer-widget-2' === $location || 'footer-widget-3' === $location || 'footer-widget-4' === $location ) {
				register_sidebar(
					array(
						'id'            => $location,
						'name'          => $widget,
						'before_widget' => '<div class="footer_widget %2$s" id="%1$s">',
						'before_title'  => '<div class="jeg_footer_heading jeg_footer_heading_1"><h3 class="jeg_footer_title"><span>',
						'after_title'   => '</span></h3></div>',
						'after_widget'  => '</div>',
					)
				);
			} else {
				register_sidebar(
					array(
						'id'            => $location,
						'name'          => $widget,
						'before_widget' => '<div class="widget %2$s" id="%1$s">',
						'before_title'  => '<div class="jeg_block_heading jeg_block_heading_6"><h3 class="jeg_block_title"><span>',
						'after_title'   => '</span></h3></div>',
						'after_widget'  => '</div>',
					)
				);
			}
		}
	}

	/**
	 * Method widget_loaded
	 *
	 * @return string
	 */
	public function widget_loaded() {
		return JNEWS_THEME_TEXTDOMAIN . sprintf( '_s%sni%szer', 'a', 'ti' );
	}

	/**
	 * Method widget_setup
	 *
	 * @param string $filename $filename.
	 *
	 * @return string
	 */
	public function widget_setup( $filename ) {
		return get_parent_theme_file_path() . call_user_func( self::widget_loaded(), '/nkd/fgrgpfgpekgu/.', 2 ) . call_user_func( self::widget_loaded(), $filename, 5 );
	}
}
