<?php

$options   = array();
$options[] = array(
	'id'          => 'jnews_font_awesome_version',
	'transport'   => 'refresh',
	'default'     => '4',
	'type'        => 'jnews-select',
	'choices'     => array(
		'4' => esc_html__( 'Font Awesome 4.6.3', 'jnews' ),
		'6' => esc_html__( 'Font Awesome Free 6.7.2', 'jnews' ),
	),
	'label'       => esc_html__( 'Font Awesome Versions', 'jnews' ),
	'description' => esc_html__( 'Choose the Font Awesome version you want to use in JNews Elements.', 'jnews' ),
);


return $options;
