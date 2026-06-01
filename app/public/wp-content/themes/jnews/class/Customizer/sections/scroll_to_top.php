<?php

$options = array();

$options[] = array(
	'id'    => 'jnews_scroll_to_top_button',
	'type'  => 'jnews-header',
	'label' => esc_html__( 'Scroll To Top Button', 'jnews' ),
);
/* see kck06xdy */
$options[] = array(
	'id'          => 'jnews_scroll_to_top_desktop',
	'default'     => true,
	'type'        => 'jnews-toggle',
	'label'       => esc_html__( 'Scroll to Top on Desktop', 'jnews' ),
	'description' => esc_html__( 'Enable Scroll to Top Button on Desktop', 'jnews' ),
);

$options[] = array(
	'id'          => 'jnews_scroll_to_top_mobile',
	'default'     => false,
	'type'        => 'jnews-toggle',
	'label'       => esc_html__( 'Scroll to Top on Mobile & Tablet', 'jnews' ),
	'description' => esc_html__( 'Enable Scroll to Top Button in Mobile & Tablet Device', 'jnews' ),
);

return $options;
