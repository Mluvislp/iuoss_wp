<?php
/*
	Plugin Name: JNews - Meta Header
	Plugin URI: http://jegtheme.com/
	Description: Plugin to customize Meta Header (Facebook share / Twitter Card)
	Version: 11.6.2
	Author: Jegtheme
	Author URI: http://jegtheme.com
	License: GPL2
*/

defined( 'JNEWS_META_HEADER' ) or define( 'JNEWS_META_HEADER', 'jnews-meta-header' );
defined( 'JNEWS_META_HEADER_VERSION' ) or define( 'JNEWS_META_HEADER_VERSION', '11.6.2' );
defined( 'JNEWS_META_HEADER_URL' ) or define( 'JNEWS_META_HEADER_URL', plugins_url( JNEWS_META_HEADER ) );
defined( 'JNEWS_META_HEADER_FILE' ) or define( 'JNEWS_META_HEADER_FILE', __FILE__ );
defined( 'JNEWS_META_HEADER_DIR' ) or define( 'JNEWS_META_HEADER_DIR', plugin_dir_path( __FILE__ ) );


add_action(
	'init',
	function () {
		require 'fallback-function.php';
	}
);

add_filter(
	'jnews_v11.6.0_plugin_compat',
	function( $plugin_list ){
		$plugin_list[] = 'JNews - Meta Header';
		return $plugin_list;
	}
);

/**
 * Social Meta Option
 */
add_action( 'jeg_register_customizer_option', 'jnews_social_meta_customizer_option' );

if ( ! function_exists( 'jnews_social_meta_customizer_option' ) ) {
	function jnews_social_meta_customizer_option() {
		require_once 'class.jnews-meta-option.php';
		JNews_Social_Meta_Option::getInstance();
	}
}


add_filter( 'jeg_register_lazy_section', 'jnews_meta_header_lazy_section' );

if ( ! function_exists( 'jnews_meta_header_lazy_section' ) ) {
	function jnews_meta_header_lazy_section( $result ) {
		$result['jnews_header_meta_section'][] = JNEWS_META_HEADER_DIR . 'general-meta-option.php';

		$result['jnews_social_meta_section'][] = JNEWS_META_HEADER_DIR . 'meta-header-option.php';

		return $result;
	}
}

/**
 * Initialize meta header instance
 */
if ( ! function_exists( 'jnews_meta_header_init' ) ) {
	function jnews_meta_header_init() {
		require_once 'class.jnews-meta-header.php';
		JNews_Meta_Header::getInstance();
	}
}

if ( ! function_exists( 'jnews_meta_metabox_load' ) ) {
	add_action( 'after_setup_theme', 'jnews_meta_metabox_load' );
	/**
	 * Method jnews_meta_metabox_load
	 *
	 * @return void
	 */
	function jnews_meta_metabox_load() {
		if ( class_exists( '\JNews\MetaboxBuilder' ) ) {
			require_once 'metabox/class.jnews-meta-metabox.php';
			JNews_Meta_Metabox::getInstance();
		}
	}
}



if ( ! function_exists( 'jnews_get_option' ) ) {
	/**
	 * Get jnews option
	 *
	 * @param $setting
	 * @param $default
	 * @return mixed
	 */
	function jnews_get_option( $setting, $default = null ) {
		$options = get_option( 'jnews_option', array() );
		$value   = $default;
		if ( isset( $options[ $setting ] ) ) {
			$value = $options[ $setting ];
		}
		return $value;
	}
}

/** Print Translation */

if ( ! function_exists( 'jnews_print_translation' ) ) {
	function jnews_print_translation( $string, $domain, $name ) {
		do_action( 'jnews_print_translation', $string, $domain, $name );
	}
}


if ( ! function_exists( 'jnews_print_main_translation' ) ) {
	add_action( 'jnews_print_translation', 'jnews_print_main_translation', 10, 2 );

	function jnews_print_main_translation( $string, $domain ) {
		call_user_func_array( 'esc_html_e', array( $string, $domain ) );
	}
}

/** Return Translation */

if ( ! function_exists( 'jnews_return_translation' ) ) {
	function jnews_return_translation( $string, $domain, $name, $escape = true ) {
		return apply_filters( 'jnews_return_translation', $string, $domain, $name, $escape );
	}
}

if ( ! function_exists( 'jnews_return_main_translation' ) ) {
	add_filter( 'jnews_return_translation', 'jnews_return_main_translation', 10, 4 );

	function jnews_return_main_translation( $string, $domain, $name, $escape = true ) {
		if ( $escape ) {
			return call_user_func_array( 'esc_html__', array( $string, $domain ) );
		} else {
			return call_user_func_array( '__', array( $string, $domain ) );
		}
	}
}

if ( function_exists( 'jnews_get_option' ) ) {
	if ( jnews_get_option( 'social_meta_method', 'jnews' ) !== 'yoast' ) {
		jnews_meta_header_init();
	}
}

add_action( 'admin_notices', 'jnews_meta_header_plugin_notice' );

if ( ! function_exists( 'jnews_meta_header_plugin_notice' ) ) {
	/**
	 * Plugin notice
	 */
	function jnews_meta_header_plugin_notice() {
		if( defined( 'JNEWS_THEME_VERSION' ) && version_compare( JNEWS_THEME_VERSION, '11.6.0', '<' ) ) {

			if( ! defined( 'JNEWS_11_6_0_PLUGIN_COMPAT' ) ) {
				define( 'JNEWS_11_6_0_PLUGIN_COMPAT', true );
				$plugin_list = apply_filters( 'jnews_v11.6.0_plugin_compat', array() );
				$plugins = '';

				foreach ( $plugin_list as $plugin ) {
					$plugins .= '<li>' . $plugin . '</li>';
				}

				echo wp_kses(
					' <div class="notice notice-error">
						<p>
							<span class="jnews-notice-heading">' . __( 'JNews Plugins Compatibility', 'jnews-meta-header' ) . '</span>
							<span>' . __( 'These plugins below only compatible with the <strong>JNews Theme version 11.6.0</strong> and above. Your current JNews version is ' . JNEWS_THEME_VERSION . ', please update JNews theme to the latest version to ensure proper functionality and compatibility.', 'jnews-meta-header' ) . '</span><ul>' .
								$plugins .
							'</ul>
							<span class="jnews-notice-button">
								<a href="' . wp_nonce_url( admin_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( JNEWS_THEME_TEXTDOMAIN ) ), 'upgrade-theme_' . JNEWS_THEME_TEXTDOMAIN ) . '" class="button-primary">' . __( 'Update Theme Now', 'jnews-meta-header' ) . '</a>
							</span>
						</p>
					</div>',
					array(
						'div' => array(
							'class' => true,
						),
						'p' => array(),
						'i' => array(
							'class' => true,
						),
						'ul' => array(),
						'li' => array(),
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
	}
}

/**
 * Load Text Domain
 */

function jnews_meta_header_load_textdomain() {
	load_plugin_textdomain( JNEWS_META_HEADER, false, basename( __DIR__ ) . '/languages/' );
}

jnews_meta_header_load_textdomain();
