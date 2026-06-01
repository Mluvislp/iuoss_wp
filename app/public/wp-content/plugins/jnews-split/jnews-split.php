<?php
/*
	Plugin Name: JNews - Split
	Plugin URI: http://jegtheme.com/
	Description: Split post into several page
	Version: 11.6.2
	Author: Jegtheme
	Author URI: http://jegtheme.com
	License: GPL2
*/

defined( 'JNEWS_SPLIT' ) or define( 'JNEWS_SPLIT', 'jnews-split' );
defined( 'JNEWS_SPLIT_VERSION' ) || define( 'JNEWS_SPLIT_VERSION', '11.6.2' );
defined( 'JNEWS_SPLIT_URL' ) or define( 'JNEWS_SPLIT_URL', plugins_url( JNEWS_SPLIT ) );
defined( 'JNEWS_SPLIT_FILE' ) or define( 'JNEWS_SPLIT_FILE', __FILE__ );
defined( 'JNEWS_SPLIT_DIR' ) or define( 'JNEWS_SPLIT_DIR', plugin_dir_path( __FILE__ ) );


add_action(
	'init',
	function () {
		require 'fallback-function.php';
	}
);

add_filter(
	'jnews_v11.6.0_plugin_compat',
	function( $plugin_list ){
		$plugin_list[] = 'JNews - Split';
		return $plugin_list;
	}
);

global $pagenow;

/**
 * Get jnews option
 *
 * @param $setting
 * @param $default
 *
 * @return mixed
 */
if ( ! function_exists( 'jnews_get_option' ) ) {
	function jnews_get_option( $setting, $default = null ) {
		$options = get_option( 'jnews_option', array() );
		$value   = $default;
		if ( isset( $options[ $setting ] ) ) {
			$value = $options[ $setting ];
		}

		return $value;
	}
}

/** Load Split Post Metabox */

if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' || ! is_admin() ) {
	add_action( 'after_setup_theme', 'jnews_split_metabox_load' );

	if ( ! function_exists( 'jnews_split_metabox_load' ) ) {
		function jnews_split_metabox_load() {
			if ( class_exists( '\JNews\MetaboxBuilder' ) ) {
				require_once 'metabox/class.jnews-split-metabox.php';
				JNews_Split_Metabox::getInstance();
			}
		}
	}
}

/**
 * Split Option
 */
add_filter( 'jeg_register_lazy_section', 'jnews_split_lazy_section' );

if ( ! function_exists( 'jnews_split_lazy_section' ) ) {
	function jnews_split_lazy_section( $result ) {
		$result['jnews_global_loader_section'][] = JNEWS_SPLIT_DIR . 'split-option.php';

		return $result;
	}
}

/** Load Split Class */
add_action( 'plugins_loaded', 'jnews_split_content' );

if ( ! function_exists( 'jnews_split_content' ) ) {
	function jnews_split_content() {
		if ( ! is_admin() ) {
			require_once 'class.jnews-split-content-tag.php';
			require_once 'class.jnews-split-tree-node.php';
			require_once 'class.jnews-split-post.php';
			JNews_Split_Post::getInstance();
		}
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

add_action( 'admin_notices', 'jnews_split_plugin_notice' );

if ( ! function_exists( 'jnews_split_plugin_notice' ) ) {
	/**
	 * Plugin notice
	 */
	function jnews_split_plugin_notice() {
		if( defined( 'JNEWS_THEME_VERSION' ) && version_compare( JNEWS_THEME_VERSION, '11.6.0', '<' ) ) {

			if( ! defined( 'JNEWS_11_6_0_PLUGIN_COMPAT' ) ) {
				define( 'JNEWS_11_6_0_PLUGIN_COMPAT', true );
				$plugin_list = apply_filters( 'jnews_v11.6.0_plugin_compat', array() );
				$plugins = '';

				foreach ( $plugin_list as $plugin ) {
					$plugins .= '<li>' . $plugin . '</li>';
				}

				echo wp_kses(
					'<div class="notice notice-error">
						<p>
							<span class="jnews-notice-heading">' . __( 'JNews Plugins Compatibility', 'jnews-split' ) . '</span>
							<span>' . __( 'These plugins below only compatible with the <strong>JNews Theme version 11.6.0</strong> and above. <strong>Your current JNews version is ' . JNEWS_THEME_VERSION . '</strong>, please update JNews theme to the latest version to ensure proper functionality and compatibility.', 'jnews-split' ) . '</span><ul>' .
								$plugins .
							'</ul>
							<span class="jnews-notice-button">
								<a href="' . wp_nonce_url( admin_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( JNEWS_THEME_TEXTDOMAIN ) ), 'upgrade-theme_' . JNEWS_THEME_TEXTDOMAIN ) . '" class="button-primary">' . __( 'Update Theme Now', 'jnews-split' ) . '</a>
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

function jnews_split_load_textdomain() {
	load_plugin_textdomain( JNEWS_SPLIT, false, basename( __DIR__ ) . '/languages/' );
}

jnews_split_load_textdomain();
