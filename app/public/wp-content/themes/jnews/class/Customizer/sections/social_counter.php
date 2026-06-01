<?php

$instagram_token       = get_option( 'jnews_option[jnews_instagram]', array() );
$instagram_label       = esc_html__( 'Connect Instagram Account', 'jnews-instagram' );
$instagram_description = sprintf( __( 'Connect your Instagram account by clicking this <a class="%1$s" href="%2$s" target="_blank">link</a> and refer to next page URL.', 'jnews-instagram' ), 'jnews_instagram_social_counter instagram', get_admin_url() . 'widgets.php' );
if ( is_array( $instagram_token ) && ! empty( $instagram_token ) ) {
	$instagram_label       = sprintf( __( 'Connected as %s', 'jnews-instagram' ), $instagram_token['username'] );
	$instagram_description = sprintf( __( 'Connect another account by clicking this <a class="%1$s" href="%2$s" target="_blank">link</a>.', 'jnews-instagram' ), 'jnews_instagram_social_counter instagram', get_admin_url() . 'widgets.php' );
}

$options = array();

$options[] = array(
	'id'      => 'jnews_social_counter_instagram_section',
	'type'    => 'jnews-header',
	'section' => 'jnews_social_counter_instagram_section',
	'label'   => esc_html__( 'Instagram Account', 'jnews' ),
);


$options[] = array(
	'id'          => 'jnews_counter_instagram',
	'type'        => 'jnews-alert',
	'default'     => 'info',
	'section'     => 'jnews_social_counter_instagram_section',
	'label'       => $instagram_label,
	'description' => $instagram_description,
);



$tiktok_setting = get_option( 'jnews_option[jnews_tiktok]', array() );
$tiktok_account = get_option( 'jnews_tiktok_display_name', '' );
if ( defined( 'JNEWS_TIKTOK_API_URL' ) ) {
	/* get account data from TikTok Feeds plugin if active */
	$tiktok_cached = get_option( 'jnews_tiktok_cached_data', array() );
	if ( ! empty( $tiktok_cached ) ) {
		$tiktok_account = $tiktok_cached['user']['display_name'];
	}
}
$options[] = array(
	'id'      => 'jnews_social_counter_tiktok_section',
	'type'    => 'jnews-header',
	'section' => 'jnews_social_counter_tiktok_section',
	'label'   => esc_html__( 'Tiktok Account', 'jnews' ),
);

if ( ! empty( $tiktok_setting ) && ! empty( $tiktok_account ) ) {
	$tiktok_label       = sprintf( __( 'Connected as %s', 'jnews-tiktok' ), $tiktok_account );
	$tiktok_description = sprintf( __( 'Connect another account by clicking this <a class="%1$s" href="%2$s" target="_blank">link</a>.', 'jnews-tiktok' ), 'jnews_tiktok_social_counter instagram', get_admin_url() . 'widgets.php' );

	$options[] = array(
		'id'          => 'jnews_counter_tiktok',
		'type'        => 'jnews-alert',
		'default'     => 'info',
		'section'     => 'jnews_social_counter_tiktok_section',
		'label'       => $tiktok_label,
		'description' => $tiktok_description,
	);
} else {
	$tiktok_label       = esc_html__( 'Connect Tiktok Account', 'jnews-tiktok' );
	$tiktok_description = sprintf( __( 'Connect your Tiktok account by clicking this <a class="%1$s" href="%2$s" target="_blank">link</a> and refer to next page URL.', 'jnews-tiktok' ), 'jnews_tiktok_social_counter tiktok', get_admin_url() . 'widgets.php' );

	$options[] = array(
		'id'          => 'jnews_counter_tiktok',
		'type'        => 'jnews-alert',
		'default'     => 'info',
		'section'     => 'jnews_social_counter_tiktok_section',
		'label'       => $tiktok_label,
		'description' => $tiktok_description,
	);
}



return $options;
