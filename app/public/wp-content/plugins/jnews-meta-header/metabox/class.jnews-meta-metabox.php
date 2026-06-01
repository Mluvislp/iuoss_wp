<?php
/**
 * @author : Jegtheme
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JNews_Meta_Metabox {
	/**
	 * @var JNews_Meta_Metabox
	 */
	private static $instance;

	/**
	 * @return JNews_Meta_Metabox
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
		\JNews\MetaboxBuilder::set_meta( $this->social_meta() );
	}

	/**
	 * Method register_metabox
	 *
	 * @return array
	 */
	public function social_meta() {
		return array(
			'id'         => 'jnews_social_meta',
			'post_types' => array( 'post', 'page' ),
			'label'      => esc_html__( 'Social Meta', 'jnews' ),
			'priority'   => 'high',
			'icon'       => 'RoundShareSvg',
			'fields'     => array(
				array(
					'type'   => 'tab',
					'id'     => 'facebook_social_meta',
					'label'  => esc_html__( 'Facebook Social Meta', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-textarea',
							'id'          => 'fb_title',
							'label'       => esc_attr__( 'FB Share Title', 'jnews' ),
							'description' => esc_attr__( 'Leave this option empty to use this post / page title', 'jnews' ),
						),
						array(
							'type'        => 'jnews-textarea',
							'id'          => 'fb_description',
							'label'       => esc_attr__( 'FB Share Description', 'jnews' ),
							'description' => esc_attr__( 'Leave this option empty to use this post / page excerpt', 'jnews' ),
						),
						array(
							'type'        => 'jnews-image',
							'id'          => 'fb_image',
							'label'       => esc_attr__( 'FB Share Image', 'jnews' ),
							'description' => esc_attr__( 'Leave this option empty to use default featured image', 'jnews' ),
						),
					),
				),
				array(
					'type'   => 'tab',
					'id'     => 'twitter_social_meta',
					'label'  => esc_html__( 'Twitter Social Meta', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-text',
							'id'          => 'twitter_title',
							'label'       => esc_attr__( 'Twitter Share Title', 'jnews' ),
							'description' => esc_attr__( 'Leave this option empty to use post / page title', 'jnews' ),
						),
						array(
							'type'        => 'jnews-textarea',
							'id'          => 'twitter_description',
							'label'       => esc_attr__( 'Twitter Share Description', 'jnews' ),
							'description' => esc_attr__( 'Leave this option empty to use post / page excerpt', 'jnews' ),
						),
						array(
							'type'        => 'jnews-image',
							'id'          => 'twitter_image',
							'label'       => esc_attr__( 'Twitter Share Image', 'jnews' ),
							'description' => esc_attr__( 'Leave this option empty to use default featured image', 'jnews' ),
						),
					),
				),
			),
		);
	}
}
