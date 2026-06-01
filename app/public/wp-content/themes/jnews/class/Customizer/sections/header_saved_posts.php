<?php

$options   = array();
$options[] = array(
	'id'          => 'jnews_header_saved_posts_hide',
	'transport'   => 'postMessage',
	'default'     => false,
	'type'        => 'jnews-toggle',
	'label'       => esc_html__( 'Hide when logged-in', 'jnews' ),
	'description' => esc_html__( 'Hide saved posts element whwen user already logged-in.', 'jnews' ),
);

	$options[] = array(
		'id'              => 'jnews_header_button_saved_posts_style',
		'transport'       => 'postMessage',
		'default'         => 'default',
		'type'            => 'jnews-radio-buttonset',
		'label'           => esc_html__( 'Button Style', 'jnews' ),
		'description'     => esc_html__( 'Choose button style.', 'jnews' ),
		'choices'         => array(
			'default' => esc_attr__( 'Default', 'jnews' ),
			'round'   => esc_attr__( 'Round', 'jnews' ),
			'outline' => esc_attr__( 'Outline', 'jnews' ),
		),
		'partial_refresh' => array(
			'jnews_header_button_saved_posts_style' => array(
				'selector'        => '.jeg_saved_posts',
				'render_callback' => function () {
					jnews_saved_posts_button();
				},
			),
		),
	);

	$options[] = array(
		'id'          => 'jnews_header_button_saved_posts_background_color',
		'transport'   => 'postMessage',
		'default'     => '',
		'type'        => 'jnews-color',
		'label'       => esc_html__( 'Background Color', 'jnews' ),
		'description' => esc_html__( 'Background color.', 'jnews' ),
		'output'      => array(
			array(
				'method'   => 'inject-style',
				'element'  => '.jeg_saved_posts .btn',
				'property' => 'background',
			),
		),
	);

	$options[] = array(
		'id'          => 'jnews_header_button_saved_posts_background_hover_color',
		'transport'   => 'postMessage',
		'default'     => '',
		'type'        => 'jnews-color',
		'label'       => esc_html__( 'Background Hover Color', 'jnews' ),
		'description' => esc_html__( 'Background hover color.', 'jnews' ),
		'output'      => array(
			array(
				'method'   => 'inject-style',
				'element'  => '.jeg_saved_posts .btn:hover',
				'property' => 'background',
			),
		),
	);

	$options[] = array(
		'id'          => 'jnews_header_button_saved_posts_text_color',
		'transport'   => 'postMessage',
		'default'     => '',
		'type'        => 'jnews-color',
		'label'       => esc_html__( 'Text Color', 'jnews' ),
		'description' => esc_html__( 'Text color.', 'jnews' ),
		'output'      => array(
			array(
				'method'   => 'inject-style',
				'element'  => '.jeg_saved_posts .btn',
				'property' => 'color',
			),
		),
	);

	$options[] = array(
		'id'          => 'jnews_header_button_saved_posts_border_color',
		'transport'   => 'postMessage',
		'default'     => '',
		'type'        => 'jnews-color',
		'label'       => esc_html__( 'Border Color', 'jnews' ),
		'description' => esc_html__( 'Button border color.', 'jnews' ),
		'output'      => array(
			array(
				'method'   => 'inject-style',
				'element'  => '.jeg_saved_posts .btn',
				'property' => 'border-color',
			),
		),
	);





	return $options;
