<?php

$options = array();

$options[] = array(
	'id'          => 'jnews_header_language_text_color',
	'transport'   => 'postMessage',
	'default'     => '',
	'type'        => 'jnews-color',
	'label'       => esc_html__( 'Text Color', 'jnews' ),
	'description' => esc_html__( 'Language text color.', 'jnews' ),
	'output'      => array(
		array(
			'method'   => 'inject-style',
			'element'  => '.jeg_lang_dropdown_wrapper .jeg_lang_btn i, .jeg_lang_dropdown_wrapper .jeg_lang_dropdown a ,.jeg_lang_btn span ,  .jeg_lang_switcher a, .jeg_lang_switcher span, .jeg_lang_dropdown_wrapper .jeg_lang_btn::after',
			'property' => 'color',
		),
		array(
			'method'   => 'inject-style',
			'element'  => '.jeg_header .jeg_midbar.jeg_lang_expanded .jeg_lang_dropdown_wrapper .jeg_lang_btn',
			'property' => 'border-color',
		),


	),
);

$options[] = array(
	'id'          => 'jnews_header_language_background_color',
	'transport'   => 'postMessage',
	'default'     => '',
	'type'        => 'jnews-color',
	'label'       => esc_html__( 'Background Color', 'jnews' ),
	'description' => esc_html__( 'Language background color.', 'jnews' ),
	'output'      => array(
		array(
			'method'   => 'inject-style',
			'element'  => '.jeg_lang_switcher , .jeg_lang_switcher',
			'property' => 'background',
		),
	),
);

$options[] = array(
	'id'              => 'jnews_header_language_dropdown',
	'transport'       => 'postMessage',
	'default'         => false,
	'type'            => 'jnews-toggle',
	'label'           => esc_html__( 'Show As Dropdown', 'jnews' ),
	'description'     => esc_html__( 'Enable this option to show language lists as dropdown menu.', 'jnews' ),
	'partial_refresh' => array(
		'jnews_header_logo_dark_retina' => array(
			'selector'        => '.jeg_nav_item.jeg_lang_switcher',
			'render_callback' => function () {
				return jnews_language_switcher( get_theme_mod( 'jnews_header_language_dropdown', false ) );
			},
		),
	),
);

$options[] = array(
	'id'              => 'jnews_header_language_dropdown_background',
	'transport'       => 'postMessage',
	'default'         => '',
	'type'            => 'jnews-color',
	'label'           => esc_html__( 'Dropdown Background Color', 'jnews' ),
	'description'     => esc_html__( 'Language list dropdown background color.', 'jnews' ),
	'output'          => array(
		array(
			'method'   => 'inject-style',
			'element'  => '.jeg_lang_dropdown_wrapper .jeg_lang_dropdown',
			'property' => 'background',
		),
		array(
			'method'   => 'inject-style',
			'element'  => '.jeg_lang_dropdown_wrapper .jeg_lang_dropdown:before',
			'property' => 'border-bottom-color',
		),
	),
	'active_callback' => array(
		array(
			'setting'  => 'jnews_header_language_dropdown',
			'operator' => '==',
			'value'    => true,
		),
	),
);

return $options;
