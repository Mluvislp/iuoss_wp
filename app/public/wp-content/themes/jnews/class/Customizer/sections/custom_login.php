<?php

$options = array();

$options[] = array(
	'id'    => 'jnews_custom_login',
	'type'  => 'jnews-header',
	'label' => esc_html__( 'Custom Login & Registration', 'jnews' ),
);

$options[] = array(
	'id'          => 'jnews_use_custom_login',
	'default'     => false,
	'type'        => 'jnews-toggle',
	'label'       => esc_html__( 'Use Custom Login', 'jnews' ),
	'description' => esc_html__( 'Enable this option to override the JNews popup login with your custom login URL.', 'jnews' ),
);

$options[] = array(
	'id'              => 'jnews_custom_login_url',
	'transport'       => 'postMessage',
	'default'         => '',
	'type'            => 'jnews-text',
	'label'           => esc_html__( 'Custom Login URL', 'jnews' ),
	'description'     => esc_html__( 'The full URL of your custom login page.', 'jnews' ),
	'active_callback' => array(
		array(
			'setting'  => 'jnews_use_custom_login',
			'operator' => '==',
			'value'    => true,
		),
	),
);

$options[] = array(
	'id'              => 'jnews_custom_register_url',
	'transport'       => 'postMessage',
	'default'         => '',
	'type'            => 'jnews-text',
	'label'           => esc_html__( 'Custom Register URL', 'jnews' ),
	'description'     => esc_html__( 'The full URL of your custom register page.', 'jnews' ),
	'active_callback' => array(
		array(
			'setting'  => 'jnews_use_custom_login',
			'operator' => '==',
			'value'    => true,
		),
	),
);

$options[] = array(
	'id'              => 'jnews_redirect_param',
	'default'         => true,
	'type'            => 'jnews-toggle',
	'label'           => esc_html__( 'Add redirect param', 'jnews' ),
	'description'     => esc_html__( 'Enable this option to include the current page as the redirect_to parameter in your custom login URL.', 'jnews' ),
	'active_callback' => array(
		array(
			'setting'  => 'jnews_use_custom_login',
			'operator' => '==',
			'value'    => true,
		),
	),
);


return $options;
