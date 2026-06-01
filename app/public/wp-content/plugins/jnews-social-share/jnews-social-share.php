<?php
/*
	Plugin Name: JNews - Social Share
	Plugin URI: http://jegtheme.com/
	Description: Social bar, Social Counter and Initial Counter functionality for JNews
	Version: 11.6.5
	Author: Jegtheme
	Author URI: http://jegtheme.com
	License: GPL2
*/

defined( 'JNEWS_SOCIAL_SHARE' ) or define( 'JNEWS_SOCIAL_SHARE', 'jnews-social-share' );
defined( 'JNEWS_SOCIAL_SHARE_VERSION' ) || define( 'JNEWS_SOCIAL_SHARE_VERSION', '11.6.5' );
defined( 'JNEWS_SOCIAL_SHARE_URL' ) or define( 'JNEWS_SOCIAL_SHARE_URL', plugins_url( JNEWS_SOCIAL_SHARE ) );
defined( 'JNEWS_SOCIAL_SHARE_FILE' ) or define( 'JNEWS_SOCIAL_SHARE_FILE', __FILE__ );
defined( 'JNEWS_SOCIAL_SHARE_DIR' ) or define( 'JNEWS_SOCIAL_SHARE_DIR', plugin_dir_path( __FILE__ ) );

defined( 'JNEWS_SOCIAL_COUNTER_LAST_UPDATE' ) or define( 'JNEWS_SOCIAL_COUNTER_LAST_UPDATE', 'jnews_social_counter_last_update' );
defined( 'JNEWS_SOCIAL_COUNTER_TOTAL' ) or define( 'JNEWS_SOCIAL_COUNTER_TOTAL', 'jnews_social_counter_total' );
defined( 'JNEWS_SOCIAL_COUNTER_ALL' ) or define( 'JNEWS_SOCIAL_COUNTER_ALL', 'jnews_social_counter_all' );

add_filter(
	'jnews_v11.6.0_plugin_compat',
	function( $plugin_list ){
		$plugin_list[] = 'JNews - Social Share';
		return $plugin_list;
	}
);

require 'class.jnews-social-background-queue.php';
require 'class.jnews-social-background-process.php';
require 'class.jnews-share-bar.php';
require 'class.jnews-social-counter.php';
require 'class.jnews-initial-counter.php';
require 'class.jnews-social-view-counter.php';
require 'class.jnews-select-share.php';

/**
 * Get jnews option
 *
 * @param $setting
 * @param $default
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

/**
 * Need to create background process instance when init, so all hook can be executed
 */
add_action( 'init', 'jnews_create_social_background_instance' );

if ( ! function_exists( 'jnews_create_social_background_instance' ) ) {
	function jnews_create_social_background_instance() {
		JNews_Social_Background_Queue::getInstance();
	}
}

/**
 * Social Share Option
 */
add_action( 'jeg_register_customizer_option', 'jnews_social_share_option' );

if ( ! function_exists( 'jnews_social_share_option' ) ) {
	function jnews_social_share_option() {
		require_once 'class.jnews-social-option.php';
		JNews_Social_Option::getInstance();
	}
}

add_filter( 'jeg_register_lazy_section', 'jnews_social_register_lazy_section' );

if ( ! function_exists( 'jnews_social_register_lazy_section' ) ) {
	function jnews_social_register_lazy_section( $result ) {
		$result['jnews_social_like_section'][] = JNEWS_SOCIAL_SHARE_DIR . 'social-option.php';
		return $result;
	}
}

/**
 * Select Share option
 */

add_filter( 'jeg_register_lazy_section', 'jnews_select_share_register_lazy_section' );

if ( ! function_exists( 'jnews_select_share_register_lazy_section' ) ) {
	function jnews_select_share_register_lazy_section( $result ) {
		$result['jnews_select_share_section'][] = JNEWS_SOCIAL_SHARE_DIR . 'select-share-option.php';
		return $result;
	}
}

/**
 * Initialize View Script
 */
add_action( 'plugins_loaded', 'jnews_social_view_counter' );

if ( ! function_exists( 'jnews_social_view_counter' ) ) {
	function jnews_social_view_counter() {
		JNews_Social_View_Counter::getInstance();
	}
}

/**
 * Initialize Select & Share
 */
add_action( 'plugins_loaded', 'jnews_select_share' );

if ( ! function_exists( 'jnews_select_share' ) ) {
	function jnews_select_share() {
		JNews_Select_Share::getInstance();
	}
}

/**
 * Ajax view counter
 */

add_action( 'jnews_do_first_load_action', 'jnews_ajax_total_view', null, 2 );

if ( ! function_exists( 'jnews_ajax_total_view' ) ) {
	function jnews_ajax_total_view( $response, $action ) {
		if ( in_array( 'view_counter', $action ) ) {
			$post_id    = (int) sanitize_text_field( $_POST['jnews_id'] );
			$counter    = JNews_Initial_Counter::getInstance();
			$total      = apply_filters( 'jnews_get_total_view', 0, $post_id, 'all' );
			$total_view = $counter->get_total_fake_view( $total, $post_id );

			$social_counter = new JNews_Social_Counter( $post_id );
			$share          = $social_counter->get_share( 'total' );
			$total_share    = $counter->get_total_fake_share( $share, $post_id );

			$response['counter']['total_view']  = jnews_number_format( $total_view );
			$response['counter']['total_share'] = jnews_number_format( $total_share );
		}

		return $response;
	}
}

/**
 * Share Top Bar
 */
add_action( 'jnews_share_top_bar', 'jnews_share_top_bar' );

if ( ! function_exists( 'jnews_share_top_bar' ) ) {
	function jnews_share_top_bar( $post_id ) {
		$share_bar = JNews_Share_Bar::getInstance();
		$share_bar->set_post_id( $post_id );
		$share_bar->top_share();
	}
}

/**
 * Share Float Bar
 */
add_action( 'jnews_share_float_bar', 'jnews_share_float_bar' );

if ( ! function_exists( 'jnews_share_float_bar' ) ) {
	function jnews_share_float_bar( $post_id ) {
		$share_bar = JNews_Share_Bar::getInstance();
		$share_bar->set_post_id( $post_id );
		$share_bar->float_share();
	}
}

/**
 * Share Bottom Bar
 */
add_action( 'jnews_share_bottom_bar', 'jnews_share_bottom_bar' );

if ( ! function_exists( 'jnews_share_bottom_bar' ) ) {
	function jnews_share_bottom_bar( $post_id ) {
		$share_bar = JNews_Share_Bar::getInstance();
		$share_bar->set_post_id( $post_id );
		$share_bar->bottom_share();
	}
}

/**
 * Share AMP Bar
 */
add_action( 'jnews_share_amp_bar', 'jnews_share_amp_bar' );

if ( ! function_exists( 'jnews_share_amp_bar' ) ) {
	function jnews_share_amp_bar( $post_id ) {
		$share_bar = JNews_Share_Bar::getInstance();
		$share_bar->set_post_id( $post_id );
		$share_bar->amp_share();
	}
}

/**
 * Share Post Block
 */
add_filter( 'jnews_share_block_output', 'jnews_share_block_output', null, 2 );

if ( ! function_exists( 'jnews_share_block_output' ) ) {
	function jnews_share_block_output( $output, $post_id ) {
		$share_bar = JNews_Share_Bar::getInstance();
		$share_bar->set_post_id( $post_id );
		return $share_bar->block_share_output();
	}
}

/**
 * Share Post Flat
 */
add_filter( 'jnews_share_flat_output', 'jnews_share_flat_output', null, 2 );

if ( ! function_exists( 'jnews_share_flat_output' ) ) {
	function jnews_share_flat_output( $output, $post_id ) {
		$share_bar = JNews_Share_Bar::getInstance();
		$share_bar->set_post_id( $post_id );
		return $share_bar->flat_share_output();
	}
}


/**
 * Get total share post
 */
add_filter( 'jnews_total_share', 'jnews_total_share', null, 2 );

if ( ! function_exists( 'jnews_total_share' ) ) {
	function jnews_total_share( $total, $post_id ) {
		$initial_counter = JNews_Initial_Counter::getInstance();
		$counter         = new JNews_Social_Counter( $post_id );
		$total           = $counter->get_share( 'total' );
		return $initial_counter->get_total_fake_share( $total, $post_id );
	}
}

/**
 * Get total view post
 */
add_filter( 'jnews_get_total_fake_view', 'jnews_get_total_fake_view', null, 2 );

if ( ! function_exists( 'jnews_get_total_fake_view' ) ) {
	function jnews_get_total_fake_view( $total, $post_id ) {
		$initial_counter = JNews_Initial_Counter::getInstance();
		return $initial_counter->get_total_fake_view( null, $post_id );
	}
}

/**
 * Get social share each network
 */
add_filter( 'jnews_total_share_social', 'jnews_total_share_social', null, 3 );

if ( ! function_exists( 'jnews_total_share_social' ) ) {
	function jnews_total_share_social( $total, $post_id, $social ) {
		$initial_counter = JNews_Initial_Counter::getInstance();
		$counter         = new JNews_Social_Counter( $post_id );
		return $initial_counter->social_share( $counter->get_share( $social ), $post_id, $social );
	}
}

/**
 * Get social share url
 */
add_filter( 'jnews_social_share_url', 'jnews_social_share_url', null, 3 );

if ( ! function_exists( 'jnews_social_share_url' ) ) {
	function jnews_social_share_url( $url, $post_id, $social ) {
		return JNews_Share_Bar::get_social_share_url( $social, $post_id );
	}
}

add_filter( 'jnews_view_initial_counter', 'jnews_view_initial_counter', null, 2 );

if ( ! function_exists( 'jnews_view_initial_counter' ) ) {
	function jnews_view_initial_counter( $counter, $post_id ) {
		if ( jnews_get_metabox_value( 'jnews_override_counter.override_view_counter', false, $post_id ) ) {
			return jnews_get_metabox_value( 'jnews_override_counter.view_counter_number', false, $post_id );
		}

		return $counter;
	}
}

add_filter( 'jnews_share_initial_counter', 'jnews_share_initial_counter', null, 2 );

if ( ! function_exists( 'jnews_share_initial_counter' ) ) {
	function jnews_share_initial_counter( $counter, $post_id ) {
		if ( jnews_get_metabox_value( 'jnews_override_counter.override_share_counter', false, $post_id ) ) {
			return jnews_get_metabox_value( 'jnews_override_counter.share_counter_number', false, $post_id );
		}

		return $counter;
	}
}

/**
 * Alter total like
 */
add_filter( 'jnews_total_like', 'jnews_total_like', null, 2 );

if ( ! function_exists( 'jnews_total_like' ) ) {
	function jnews_total_like( $total, $post_id ) {
		if ( jnews_get_metabox_value( 'jnews_override_counter.override_like_counter', false, $post_id ) ) {
			$fake = jnews_get_metabox_value( 'jnews_override_counter.like_counter_number', false, $post_id );
			return (int) $total + (int) $fake;
		} else {
			$initial_counter = JNews_Initial_Counter::getInstance();
			return $initial_counter->total_like( $total, $post_id );
		}
	}
}

/**
 * Alter total dislike
 */
add_filter( 'jnews_total_dislike', 'jnews_total_dislike', null, 2 );

if ( ! function_exists( 'jnews_total_dislike' ) ) {
	function jnews_total_dislike( $total, $post_id ) {
		if ( jnews_get_metabox_value( 'jnews_override_counter.override_dislike_counter', false, $post_id ) ) {
			$fake = jnews_get_metabox_value( 'jnews_override_counter.dislike_counter_number', false, $post_id );
			return (int) $total + (int) $fake;
		} else {
			$initial_counter = JNews_Initial_Counter::getInstance();
			return $initial_counter->total_dislike( $total, $post_id );
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

if( ! function_exists( 'jnews_register_social_share_meta' ) ) {
	add_filter( 'after_setup_theme', 'jnews_register_social_share_meta' );
	/**
	 * Method jnews_register_social_share_meta
	 *
	 * @return void
	 */
	function jnews_register_social_share_meta() {
		if ( class_exists( '\JNews\MetaboxBuilder' ) ) {
			$metabox = array(
				'id'       => 'jnews_override_counter',
				'post_types' => array('post'),
				'label'    => 'Override Fake Counter',
				'priority' => 'high',
				'icon' => 'DocumentViewSvg',
				'fields' => array(
					array(
						'type'        => 'jnews-toggle',
						'id'        => 'override_view_counter',
						'label'       => esc_html__('Override View Counter Setting', 'jnews'),
						'description' => esc_html__('enable this option to override view counter setting', 'jnews'),
					),
					array(
						'type'            => 'jnews-text',
						'id'            => 'view_counter_number',
						'label'           => esc_html__('Total View Counter', 'jnews'),
						'description'     => esc_html__('please insert number of view counter', 'jnews'),
						'default'         => '0',
						'dependency' => array(
							array(
								'field'    => 'override_view_counter',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					array(
						'type'        => 'jnews-toggle',
						'id'        => 'override_share_counter',
						'label'       => esc_html__('Override Share Counter Setting', 'jnews'),
						'description' => esc_html__('enable this option to override Share counter setting', 'jnews'),
					),
					array(
						'type'            => 'jnews-text',
						'id'            => 'share_counter_number',
						'label'           => esc_html__('Total Share Counter', 'jnews'),
						'description'     => esc_html__('please insert number of share counter', 'jnews'),
						'default'         => '0',
						'dependency' => array(
							array(
								'field'    => 'override_share_counter',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					array(
						'type'        => 'jnews-toggle',
						'id'        => 'override_like_counter',
						'label'       => esc_html__('Override Like Counter Setting', 'jnews'),
						'description' => esc_html__('enable this option to override Like counter setting', 'jnews'),
					),
					array(
						'type'            => 'jnews-text',
						'id'            => 'like_counter_number',
						'label'           => esc_html__('Total Like Counter', 'jnews'),
						'description'     => esc_html__('please insert number of like counter', 'jnews'),
						'default'         => '0',
						'dependency' => array(
							array(
								'field'    => 'override_like_counter',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
					array(
						'type'        => 'jnews-toggle',
						'id'        => 'override_dislike_counter',
						'label'       => esc_html__('Override Dislike Counter Setting', 'jnews'),
						'description' => esc_html__('enable this option to override Dislike counter setting', 'jnews'),
					),
					array(
						'type'            => 'jnews-text',
						'id'            => 'dislike_counter_number',
						'label'           => esc_html__('Total Dislike Counter', 'jnews'),
						'description'     => esc_html__('please insert number of dislike counter', 'jnews'),
						'default'         => '0',
						'dependency' => array(
							array(
								'field'    => 'override_dislike_counter',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
			
				),
			);
	
			\JNews\MetaboxBuilder::set_meta( $metabox );
		}

	}
}

add_action( 'admin_notices', 'jnews_review_plugin_notice' );

if ( ! function_exists( 'jnews_review_plugin_notice' ) ) {
	/**
	 * Plugin notice
	 */
	function jnews_review_plugin_notice() {
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
							<span class="jnews-notice-heading">' . __( 'JNews Plugins Compatibility', 'jnews-social-share' ) . '</span>
							<span>' . __( 'These plugins below only compatible with the <strong>JNews Theme version 11.6.0</strong> and above. Your current JNews version is ' . JNEWS_THEME_VERSION . ', please update JNews theme to the latest version to ensure proper functionality and compatibility.', 'jnews-social-share' ) . '</span><ul>' .
								$plugins .
							'</ul>
							<span class="jnews-notice-button">
								<a href="' . wp_nonce_url( admin_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( JNEWS_THEME_TEXTDOMAIN ) ), 'upgrade-theme_' . JNEWS_THEME_TEXTDOMAIN ) . '" class="button-primary">' . __( 'Update Theme Now', 'jnews-social-share' ) . '</a>
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

function jnews_social_share_load_textdomain() {
	load_plugin_textdomain( JNEWS_SOCIAL_SHARE, false, basename( __DIR__ ) . '/languages/' );
}

jnews_social_share_load_textdomain();
