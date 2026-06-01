<?php
/**
 * @author : Jegtheme
 */

namespace JNews;

use JNews\Util\VideoAttribute;
use JNews\Util\ValidateLicense;
use JNews\MetaboxBuilder;

/**
 * Class Plugin Metabox
 */
class Metabox {
	/**__construct
	 * Method 
	 *
	 * @return void
	 */
	public function __construct() {
		global $pagenow;



		add_action( 'after_setup_theme', array( $this, 'register_metabox_data' ) , 9 );
		add_action( 'init', array( $this, 'add_meta_box' ), 11 );

		if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' || is_customize_preview() || ! is_admin() ) {
			// unset all the metabox with prefix jnews_ to prevent react metabox value got overriden by vp_metabox
			add_action(
				'save_post',
				function ( $post_id ) {
					$prefix = 'jnews_';

					foreach ( $_POST as $key => $value ) {
						if ( strpos( $key, $prefix ) === 0 ) {
							unset( $_POST[ $key ] );
						}
					}

					return $post_id;
				}
			);

			add_action( 'save_post', array( $this, 'update_custom_post_meta' ), 99 );
			add_action( 'edit_post', array( $this, 'update_custom_post_meta' ), 99 );
			add_filter( 'rest_prepare_post', array( $this, 'rest_prepare_post' ), 10, 3 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		}
		remove_action( 'add_meta_boxes', 'vp_pfui_add_meta_boxes' );
	}
	
	public function register_metabox_data(){
		MetaboxBuilder::set_meta( $this->single_post_meta() );
		MetaboxBuilder::set_meta( $this->primary_category_meta() );
		MetaboxBuilder::set_meta( $this->page_loop_meta() );
		MetaboxBuilder::set_meta( $this->page_default_meta() );
	}

	public function update_custom_post_meta( $post_id ) {

		$post_subtitle = false;
		if ( array_key_exists( 'jnews_post_subtitle', $_POST ) ) {
			$post_subtitle = sanitize_text_field( $_POST['jnews_post_subtitle'] );
			update_post_meta( $post_id, 'post_subtitle', $post_subtitle );
			update_post_meta( $post_id, 'post_subtitle_flag', false );
		}

		if ( isset( $_POST['jnews_metabox_classic_meta'] ) ) {
			$post_type      = get_post_type( $post_id );
			$meta_data      = json_decode( wp_unslash( $_POST['jnews_metabox_classic_meta'] ), true );
			$sanitized_meta = array();
			$metaboxes      = MetaboxBuilder::get_meta();

			if ( false !== $post_subtitle ) {
				$meta_data['jnews_single_post']['subtitle'] = $post_subtitle;
			}

			foreach( $metaboxes as $key => $metabox ) {
				if( isset( $metabox['mode'] ) ) {
					foreach( $metabox['fields'] as $meta ) {
						foreach( $meta['fields'] as $field ) {
							if( isset( $meta_data[ $field['id'] ] ) ) {
								$sanitized_meta[ $field['id'] ] = sanitize_meta( $field['id'], $meta_data[ $field['id'] ], $post_type );
								MetaboxBuilder::save_meta( $post_id, $field['id'], $meta_data[ $field['id'] ] );
							}
						}
					}
				} else {
					if( isset( $meta_data[ $key ] ) ) {
						$sanitized_meta[$key] = sanitize_meta( $key, $meta_data[ $key ], $post_type );
						MetaboxBuilder::save_meta( $post_id, $key, $meta_data[ $key ] );
					}
				}
			}

			MetaboxBuilder::update_custom_meta( $post_id, $sanitized_meta );
		}
	}

	/**
	 * Method rest_prepare_post
	 *
	 * @param object $response response.
	 * @param object $post post.
	 * @param object $request request.
	 *
	 * @return object
	 */
	public function rest_prepare_post( $response, $post, $request ) {
		/*  execute only in update post */
		if ( $request->get_method() === 'PUT' ) {
			$post_id = sanitize_key( $response->data['id'] );
			if ( isset( $response->data['meta'] ) ) {

				$post_type      = get_post_type( $post_id );
				$meta           = $response->data['meta'];
				$sanitized_meta = array();

				foreach ( $meta as $key => $values ) {
					$sanitized_values       = sanitize_meta( $key, $values, $post_type );
					$sanitized_meta[ $key ] = $sanitized_values;
				}

				if ( isset( $sanitized_meta['jnews_single_post']['subtitle'] ) ) {
					update_post_meta( $post_id, 'post_subtitle', sanitize_text_field( $sanitized_meta['jnews_single_post']['subtitle'] ) );
				}

				MetaboxBuilder::update_custom_meta( $post_id, $sanitized_meta );
			}
		}

		return $response;
	}

	/**
	 * Method filter_metaboxes
	 *
	 * @return array
	 */
	public function filter_metaboxes( $metabox ) {
		global $pagenow;

		$filtered_metabox = array();

		$post_type = 'post';
		if ( get_post_type() ) {
			$post_type = get_post_type();
		} elseif ( isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}

		foreach ( $metabox as $meta ) {
			if ( isset( $meta['post_types'] ) && in_array( $post_type, $meta['post_types'] ) ) {
				if ( isset( $meta['fields'] ) ) {

					foreach ( $meta['fields'] as $meta_key => $control ) {
						if ( 'tab' === $control['type'] ) {
							if ( isset( $control['fields'] ) ) {
								foreach ( $control['fields'] as $key => $field ) {
									$mode    = isset( $meta['mode'] ) ? $meta['mode'] : '';
									$meta_id = 'extract' === $mode ? $field['id'] : $meta['id'] . '.' . $field['id'];

									$meta['fields'][ $meta_key ]['fields'][ $key ] = $this->additional_control_attr( $meta_id, $field, $mode );
								}
							}
						} else {
							$meta['fields'][ $meta_key ] = $this->additional_control_attr( $meta_key, $control );
						}
					}
				}

				$filtered_metabox[] = $meta;
			}
		}

		return $filtered_metabox;
	}

	public function additional_control_attr( $key, $control, $mode = 'extract' ) {
		if ( ! isset( $_REQUEST['post'] ) ) {
			return $control;
		}

		$post_id = $_REQUEST['post'];

		switch ( $control['type'] ) {
			case 'jnews-group':
				foreach ( $control['fields'] as $index => $group_item ) {
					$field_id                    = 'extract' === $mode ? $group_item['id'] : explode( '.', $key )[0] . '.' . $group_item['id'];
					$control['fields'][ $index ] = $this->additional_control_attr( $field_id, $group_item );
				}
				break;
			case 'jnews-image':
				$id = jnews_get_metabox_value( $key, null, $post_id );
				$control['options'] = $id ? array(
					'url' => wp_get_attachment_url( $id ),
					'id'  => $id,
				) : array( 
					//if the value didn't exist yet, it still need to have url and id attribute to be able to modify the object from js to store image preview
					'url' => '',
					'id'  => '',
				);
				break;
			case 'jnews-multi-image':
				$images = array();

				$gallery_images = '';
				if ( isset( $post_id ) ) {
					if ( jnews_get_metabox_value( 'jnews_single_post.gallery', null, $post_id ) ) {
						$gallery_images = jnews_get_metabox_value( 'jnews_single_post.gallery', null, $post_id );
					} elseif ( is_array( get_post_meta( $post_id, '_format_gallery_images', true ) ) ) {
						$gallery_images = implode( ',', get_post_meta( $post_id, '_format_gallery_images', true ) );
					}
				}
				$images = array();

				if ( ! empty( $gallery_images ) ) {
					$image_ids = is_array( $gallery_images ) ? $gallery_images : explode( ',', $gallery_images );

					foreach ( $image_ids as $id ) {
						$images[] = array(
							'url' => wp_get_attachment_url( $id ),
							'id'  => $id,
						);
					}
				}

				$control['options'] = $images;
				break;
		}

		return $control;
	}


	/**
	 * Method add_meta_box
	 *
	 * @return void
	 */
	public function add_meta_box() {
		global $pagenow;
		$non_meta  = array( 'jnews-alert' );
		$metaboxes = MetaboxBuilder::get_meta();

		foreach ( $metaboxes as $key => $metabox ) {
			if ( isset( $metabox['post_types'] ) ) {
				if ( isset( $metabox['fields'] ) ) {
					foreach ( $metabox['fields'] as $key => $control ) {
						if ( ! in_array( $control, $non_meta ) ) {
							if ( 'tab' === $control['type'] ) {
								if ( isset( $control['fields'] ) ) {
									foreach ( $control['fields'] as $key => $field ) {
										$control_properties[ $field['id'] ] = $this->get_control_base_properties( $field, $metabox['post_types'] );
									}
								}
							} else {
								$control_properties[ $control['id'] ] = $this->get_control_base_properties( $control, $metabox['post_types'] );
							}
						}
					}
				}
				foreach ( $metabox['post_types'] as $key => $post_type ) {
					$this->regsiter_post_meta(
						$metabox['id'],
						$post_type,
						array(
							'type'       => 'object',
							'properties' => $control_properties,
						)
					);
				}
				if ( isset( $metabox['mode'] ) && 'extract' === $metabox['mode'] ) {
					if ( isset( $metabox['fields'] ) ) {
						foreach ( $metabox['fields'] as $key => $control ) {
							$control_properties = array();
							if ( ! in_array( $control, $non_meta ) ) {
								if ( 'tab' === $control['type'] ) {
									if ( isset( $control['fields'] ) ) {
										foreach ( $control['fields'] as $key => $field ) {
											foreach ( $metabox['post_types'] as $key => $post_type ) {
												$properties = $this->get_control_base_properties( $field, $post_type );
												$this->regsiter_post_meta( $field['id'], $post_type, $properties, $properties['type'] );
											}
										}
									}
								} else {
									foreach ( $metabox['post_types'] as $key => $post_type ) {
										$properties = $this->get_control_base_properties( $field, $post_type );
										$this->regsiter_post_meta( $field['id'], $post_type, $properties, $properties['type'] );
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Method regsiter_post_meta
	 *
	 * @param array  $metabox metabox
	 * @param string $post_type metabox
	 * @param array  $properties properties
	 *
	 * @return void
	 */
	public function regsiter_post_meta( $id, $post_type, $properties, $type = 'object', $description = '', $single = true ) {
		$data = array(
			'description'  => $description, // Description
			'single'       => $single, // Whether the meta field is single-valued or multi-valued
			'type'         => $type,
			'show_in_rest' => array(
				'schema' => $properties,
			),
		);

		register_post_meta(
			$post_type, // Post type
			$id, // Meta key
			$data,
		);
	}

	/**
	 * Method get_control_base_properties
	 *
	 * @param array $control control
	 *
	 * @return array
	 */
	public function get_control_base_properties( $control, $post_types ) {
		$type_string  = array( 'type' => 'string' );
		$type_integer = array( 'type' => 'integer' );
		$type_boolean = array( 'type' => 'boolean' );
		switch ( $control['type'] ) {
			case 'jnews-radio-image':
			case 'jnews-slider':
			case 'jnews-image':
			case 'jnews-url':
			case 'jnews-text':
			case 'jnews-textarea':
			case 'jnews-toggle':
			case 'jnews-select':
			case 'jnews-select-search':
			case 'jnews-color':
				$properties = $type_string;
				break;
			case 'jnews-multi-image':
				$data = array(
					'description'  => '', // Description
					'single'       => true, // Whether the meta field is single-valued or multi-valued
					'type'         => 'array',
					'single'       => true,
					'show_in_rest' => array(
						'schema' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'id'  => array(
										'type' => 'integer',
									),
									'url' => array(
										'type' => 'string',
									),
								),
							),
						),
					),
				);

				if ( is_array( $post_types ) ) {
					foreach ( $post_types as $post_type ) {
						register_post_meta(
							$post_type, // Post type
							'jnews-multi-image_' . $control['id'], // Meta key
							$data,
						);
					}
				} else {
					register_post_meta(
						$post_types, // Post type
						'jnews-multi-image_' . $control['id'], // Meta key
						$data,
					);
				}

				$properties = $type_string;
				break;
			case 'jnews-group':
				$repeater_item_properties = array();
				foreach ( $control['fields'] as $key => $repeater_item ) {
					$repeater_item_properties[ $repeater_item['id'] ] = $this->get_control_base_properties( $repeater_item, $post_types );
				}

				$properties = $this->array_type(
					$this->object_type(
						array_merge(
							array(
								'_key' => $type_string,
							),
							$repeater_item_properties
						),
					),
				);
				break;
			case 'jnews-repeater':
				$repeater_item_properties = array();
				foreach ( $control['fields'] as $key => $repeater_item ) {
					$repeater_item_properties[ $key ] = $this->get_control_base_properties( $repeater_item, $post_types );
				}
				$properties = $this->array_type(
					$this->object_type(
						array_merge(
							array(
								'_key' => $type_string,
							),
							$repeater_item_properties
						),
					),
				);
				break;
			default:
				$properties = array(
					'type' => 'string',
				);
				break;
		}

		return $properties;
	}

	/**
	 * Method array_type
	 *
	 * @param array $items items
	 *
	 * @return array
	 */
	public function array_type( $items ) {
		return array(
			'type'  => 'array',
			'items' => $items,
		);
	}

	/**
	 * Method object_type
	 *
	 * @param array $properties properties
	 *
	 * @return array
	 */
	public function object_type( $properties ) {
		return array(
			'type'       => 'object',
			'properties' => $properties,
		);
	}

	/**
	 * Method load_scripts
	 *
	 * @return void
	 */
	public function load_scripts() {
		global $post;
		global $pagenow;
		$metaboxes = self::filter_metaboxes( \JNews\MetaboxBuilder::get_meta() );

		if ( ! empty( $metaboxes ) ) { /* see OqLO5kIH */
			wp_enqueue_style(
				'jnews-metabox',
				JNEWS_THEME_URL . '/assets/css/admin/metabox.css',
				array(),
				JNEWS_THEME_VERSION
			);

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_enqueue_style(
					'jnews-dashboard',
					JNEWS_THEME_URL . '/assets/css/admin/dashboard.css',
					array(),
					JNEWS_THEME_VERSION
				);
			}

			if ( 'post-new.php' === $pagenow ) {
				wp_enqueue_script( 'react', includes_url( 'js/dist/vendor/react.js' ), array(), null, true );
				wp_enqueue_script( 'react-dom', includes_url( 'js/dist/vendor/react-dom.js' ), array(), null, true );
				wp_enqueue_script( 'wp-data', includes_url( 'js/dist/vendor/data.js' ), array(), null, true );
			}

			if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {  // see Y1xkfATx
				$update_plugin = false;
				if ( defined( 'JNEWS_ESSENTIAL_VERSION' ) ) {
					$update_plugin = version_compare( JNEWS_ESSENTIAL_VERSION, '11.6' ) < 0;
				}

				$localized_data = array(
					'postType'        => get_post_type( $post->ID ),
					'metaboxes'       => $metaboxes,
					'wpVersion'       => version_compare( get_bloginfo( 'version' ), '6.6' ),
					'essentialPlugin' => defined( 'JNEWS_ESSENTIAL' ),
					'pluginUpdate'    => $update_plugin,
					'pluginPage'      => $update_plugin ? esc_url( get_admin_url() . 'admin.php?page=jnews&path=plugin&section=require-update' ) : esc_url( get_admin_url() . 'admin.php?page=jnews&path=plugin' ),
					'showNotice'      => get_option( 'jnews_show_metabox_notice', 'show' ),
					'licenseData'     => ValidateLicense::getInstance()->jnews_dashboard_config(),
					'activate'        => add_query_arg(
						array(
							'siteurl'  => admin_url(),
							'callback' => '/admin.php?page=jnews', /* see 9wKrx9UJ */
							'item_id'  => JNEWS_THEME_ID,
						),
						JEGTHEME_SERVER . '/activate/'
					),
				);
		
				if ( ! self::is_gutenberg_page() ) {
					$script    = 'wp-tinymce';
					$meta_keys = get_post_custom_keys( $post->ID );
					$meta      = array();
					if ( $meta_keys ) {
						foreach ( $meta_keys as $key ) {
							$meta[ $key ] = get_post_meta( $post->ID, $key, true );
						}
					}
		
					$localized_data = array_merge(
						$localized_data,
						array(
							'isClassic' => true,
							'postId'    => $post->ID,
							'metaData'  => $meta,
							'nonce'		=> wp_create_nonce( 'wp_rest' ),
							'postFormat' => get_post_format( $post->ID ),
							'tabData'    => array(
								'gallery'     => $this->additional_control_attr(
									'gallery',
									array(
										'type'        => 'jnews-multi-image',
										'id'          => 'gallery',
										'label'       => 'Gallery Format',
										'description' => 'Insert some images as gallery.',
										'options'     => array(),
									)
								),
								'embed_video' => array(
									'type'        => 'jnews-textarea',
									'id'          => 'video',
									'default'     => $post->ID ? get_post_meta( $post->ID, '_format_video_embed', true ) : '',
									'label'       => esc_html__( 'Video URL', 'jnews' ),
									'allow_html'  => true,
									'description' => esc_html__( 'Insert video URL or embed code.', 'jnews' ),
								),
							),
						)
					);
				} else {
					$script = 'wp-blocks';
				}
		
				wp_localize_script(
					$script,
					'JNewsMetabox',
					$localized_data
				);
			}
		} else {
			add_filter( 'load_jnews_metabox', '__return_false' );
		}
	}

	public function is_gutenberg_page() {
		if ( function_exists( 'is_gutenberg_page' ) &&
				is_gutenberg_page()
		) {
			// The Gutenberg plugin is on.
			return true;
		}
		$current_screen = get_current_screen();
		if ( method_exists( $current_screen, 'is_block_editor' ) &&
				$current_screen->is_block_editor()
		) {
			// Gutenberg page on 5+.
			return true;
		}
		return false;
	}

	/**
	 * Method register_custom_metabox
	 *
	 * @return array
	 */
	public function register_customizer_metabox() {
		return array(
			'panels'   =>
			array(
				'jnews_layout_panel' =>
				array(
					'id'          => 'jnews_layout_panel',
					'title'       => 'JNews : Layout, Color &amp; Scheme',
					'description' => 'JNews Layout Option',
					'priority'    => 171,
				),
			),
			'sections' =>
			array(
				'jnews_global_layout_section'   =>
				array(
					'id'         => 'jnews_global_layout_section',
					'title'      => 'Layout &amp; Background',
					'panel'      => 'jnews_layout_panel',
					'priority'   => 171,
					'type'       => 'jnews-lazy-section',
					'dependency' =>
					array(),
				),
				'jnews_global_sidefeed_section' =>
					array(
						'id'         => 'jnews_global_sidefeed_section',
						'title'      => 'Sidefeed Setting',
						'panel'      => 'jnews_layout_panel',
						'priority'   => 171,
						'type'       => 'jnews-lazy-section',
						'dependency' =>
						array(),
					),
			),
			'fields'   =>
			array(
				'jnews_boxed_layout_header' =>
				array(
					'id'      => 'jnews_boxed_layout_header',
					'type'    => 'jnews-header',
					'label'   => 'Box Layout',
					'section' => 'jnews_global_layout_section',
				),
				'jnews_boxed_layout'        =>
				array(
					'id'          => 'jnews_boxed_layout',
					'transport'   => 'postMessage',
					'default'     => false,
					'type'        => 'jnews-toggle',
					'label'       => 'Enable Boxed Layout',
					'description' => 'By enabling boxed layout, you can use background image.',
					'section'     => 'jnews_global_layout_section',
				),
				'jnews_sidefeed_enable'     =>
				array(
					'id'          => 'jnews_sidefeed_enable',
					'transport'   => 'refresh',
					'default'     => false,
					'type'        => 'jnews-toggle',
					'label'       => 'Enable Sidefeed',
					'description' => 'Turn on this option to enable sidefeed.',
					'section'     => 'jnews_global_sidefeed_section',
				),
			),
			'value'    =>
			array(),
		);
	}

	/**
	 * Method page_loop_meta
	 *
	 * @return array
	 */
	public function page_loop_meta() {
		return array(
			'id'         => 'jnews_page_loop',
			'post_types' => array( 'page' ),
			'label'      => esc_html__( 'JNews : Page Loop', 'jnews' ),
			'priority'   => 'high',
			'icon'       => 'LoopSvg',
			'fields'     => array(
				array(
					'type'   => 'tab',
					'id'     => 'page_loop_enable',
					'label'  => esc_html__( 'Page Loop', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-toggle',
							'id'          => 'enable_page_loop',
							'label'       => esc_html__( 'Enable Page Loop', 'jnews' ),
							'description' => esc_html__( 'Check this option to enable page loop on this page.', 'jnews' ),
						),
					),
				),
				array(
					'type'   => 'tab',
					'id'     => 'page_loop_header',
					'label'  => esc_html__( 'Page Loop Header', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-text',
							'id'          => 'first_title',
							'label'       => esc_attr__( 'First Header Title', 'jnews' ),
							'description' => esc_attr__( 'Main title of header.', 'jnews' ),
							'default'     => 'Latest Post',
						),
						array(
							'type'        => 'jnews-text',
							'id'          => 'second_title',
							'label'       => esc_attr__( 'Second  Title', 'jnews' ),
							'description' => esc_attr__( 'Secondary title of header.', 'jnews' ),
							'default'     => '',
						),
						array(
							'type'            => 'jnews-radio-image',
							'id'              => 'header_type',
							'label'           => esc_html__( 'Header Style', 'jnews' ),
							'description'     => esc_html__( 'Choose your loop header style.', 'jnews' ),
							'item_max_width'  => '118',
							'item_max_height' => '93',
							'options'         => array(
								'heading_1' => array(
									'value' => 'heading_1',
									'label' => esc_html__( 'Header 1', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/1',
								),
								'heading_2' => array(
									'value' => 'heading_2',
									'label' => esc_html__( 'Header 2', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/2',
								),
								'heading_3' => array(
									'value' => 'heading_3',
									'label' => esc_html__( 'Header 3', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/3',
								),
								'heading_4' => array(
									'value' => 'heading_4',
									'label' => esc_html__( 'Header 4', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/4',
								),
								'heading_5' => array(
									'value' => 'heading_5',
									'label' => esc_html__( 'Header 5', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/5',
								),
								'heading_6' => array(
									'value' => 'heading_6',
									'label' => esc_html__( 'Header 6', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/6',
								),
								'heading_7' => array(
									'value' => 'heading_7',
									'label' => esc_html__( 'Header 7', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/7',
								),
								'heading_8' => array(
									'value' => 'heading_8',
									'label' => esc_html__( 'Header 8', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/8',
								),
								'heading_9' => array(
									'value' => 'heading_9',
									'label' => esc_html__( 'Header 9', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header/9',
								),
							),
							'default'         => 'heading_6',
						),

						array(
							'type'        => 'jnews-color',
							'id'          => 'header_background',
							'label'       => esc_html__( 'Header Background', 'jnews' ),
							'description' => esc_html__( 'This option may not work for all of heading type.', 'jnews' ),
							'default'     => '',
							'format'      => 'rgba',
						),

						array(
							'type'        => 'jnews-color',
							'id'          => 'header_text_color',
							'label'       => esc_html__( 'Header Text Color', 'jnews' ),
							'description' => esc_html__( 'This option may not work for all of heading type.', 'jnews' ),
							'default'     => '',
							'format'      => 'rgba',
						),

					),
				),
				array(
					'type'   => 'tab',
					'id'     => 'page_loop_content',
					'label'  => esc_html__( 'Content Template', 'jnews' ),
					'fields' => array(
						array(
							'type'            => 'jnews-radio-image',
							'id'              => 'layout',
							'label'           => esc_html__( 'Page Loop Layout', 'jnews' ),
							'description'     => esc_html__( 'Choose your page loop layout.', 'jnews' ),
							'item_max_width'  => '118',
							'item_max_height' => '93',
							'options'         => array(
								'right-sidebar'        => array(
									'value' => 'right-sidebar',
									'label' => esc_html__( 'Right Sidebar', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/right-sidebar',
								),
								'left-sidebar'         => array(
									'value' => 'left-sidebar',
									'label' => esc_html__( 'Left Sidebar', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/left-sidebar',
								),
								'right-sidebar-narrow' => array(
									'value' => 'right-sidebar-narrow',
									'label' => esc_html__( 'Right Sidebar - Narrow', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/right-sidebar-narrow',
								),
								'left-sidebar-narrow'  => array(
									'value' => 'left-sidebar-narrow',
									'label' => esc_html__( 'Left Sidebar - Narrow', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/left-sidebar-narrow',
								),
								'double-sidebar'       => array(
									'value' => 'double-sidebar',
									'label' => esc_html__( 'Double Sidebar', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/double-sidebar',
								),
								'double-right-sidebar' => array(
									'value' => 'double-right-sidebar',
									'label' => esc_html__( 'Double Right Sidebar', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/double-right-sidebar',
								),
								'no-sidebar'           => array(
									'value' => 'no-sidebar',
									'label' => esc_html__( 'No Sidebar', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/no-sidebar',
								),
							),
							'default'         => 'right-sidebar',
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'sidebar',
							'label'       => esc_html__( 'Page Loop Sidebar', 'jnews' ),
							'description' => wp_kses( __( 'Choose your page loop sidebar. If you need another sidebar, you can create from <strong>WordPress Admin</strong> &raquo; <strong>Appearance</strong> &raquo; <strong>Widget</strong>.', 'jnews' ), wp_kses_allowed_html() ),
							'default'     => '{{first}}',
							'dependency'  => array(
								array(
									'field'    => 'layout',
									'operator' => '!=',
									'value'    => 'no-sidebar',
								),
							),
							'choices'     => call_user_func( 'jnews_get_sidebar' ),
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'second_sidebar',
							'label'       => esc_html__( 'Second Page Loop Sidebar', 'jnews' ),
							'description' => wp_kses( __( 'Choose your second sidebar for the page loop. If you need another sidebar, you can create from <strong>WordPress Admin</strong> &raquo; <strong>Appearance</strong> &raquo; <strong>Widget</strong>.', 'jnews' ), wp_kses_allowed_html() ),
							'default'     => '{{first}}',
							'dependency'  => array(
								array(
									'field'    => 'layout',
									'operator' => 'in',
									'value'    => array( 'double-sidebar', 'double-right-sidebar' ),
								),
							),
							'choices'     => call_user_func( 'jnews_get_sidebar' ),
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'sticky_sidebar',
							'label'       => esc_html__( 'Page Loop Sticky Sidebar', 'jnews' ),
							'description' => esc_html__( 'Enable sticky sidebar on page loop.', 'jnews' ),
							'default'     => '1',
							'dependency'  => array(
								array(
									'field'    => 'layout',
									'operator' => '!=',
									'value'    => 'no-sidebar',
								),
							),
						),

						array(
							'type'            => 'jnews-radio-image',
							'id'              => 'module',
							'label'           => esc_html__( 'Page Loop Module Template', 'jnews' ),
							'description'     => esc_html__( 'You can use module template for your index loop.', 'jnews' ),
							'item_max_width'  => '118',
							'item_max_height' => '93',
							'options'         => array(
								'3'  => array(
									'value' => '3',
									'label' => esc_html__( 'Module Bock 3', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/3',
								),
								'4'  => array(
									'value' => '4',
									'label' => esc_html__( 'Module Bock 4', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/4',
								),
								'5'  => array(
									'value' => '5',
									'label' => esc_html__( 'Module Bock 5', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/5',
								),
								'6'  => array(
									'value' => '6',
									'label' => esc_html__( 'Module Bock 6', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/6',
								),
								'7'  => array(
									'value' => '7',
									'label' => esc_html__( 'Module Bock 7', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/7',
								),
								'9'  => array(
									'value' => '9',
									'label' => esc_html__( 'Module Bock 9', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/9',
								),
								'10' => array(
									'value' => '10',
									'label' => esc_html__( 'Module Bock 10', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/10',
								),
								'11' => array(
									'value' => '11',
									'label' => esc_html__( 'Module Bock 11', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/11',
								),
								'12' => array(
									'value' => '12',
									'label' => esc_html__( 'Module Bock 12', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/12',
								),
								'14' => array(
									'value' => '14',
									'label' => esc_html__( 'Module Bock 14', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/14',
								),
								'15' => array(
									'value' => '15',
									'label' => esc_html__( 'Module Bock 15', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/15',
								),
								'18' => array(
									'value' => '18',
									'label' => esc_html__( 'Module Bock 18', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/18',
								),
								'22' => array(
									'value' => '22',
									'label' => esc_html__( 'Module Bock 22', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/22',
								),
								'23' => array(
									'value' => '23',
									'label' => esc_html__( 'Module Bock 23', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/23',
								),
								'25' => array(
									'value' => '25',
									'label' => esc_html__( 'Module Bock 25', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/25',
								),
								'26' => array(
									'value' => '26',
									'label' => esc_html__( 'Module Bock 26', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/26',
								),
								'27' => array(
									'value' => '27',
									'label' => esc_html__( 'Module Bock 27', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/27',
								),
								'32' => array(
									'value' => '32',
									'label' => esc_html__( 'Module Bock 32', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/32',
								),
								'33' => array(
									'value' => '33',
									'label' => esc_html__( 'Module Bock 33', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/33',
								),
								'34' => array(
									'value' => '34',
									'label' => esc_html__( 'Module Bock 34', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/34',
								),
								'35' => array(
									'value' => '35',
									'label' => esc_html__( 'Module Bock 35', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/35',
								),
								'36' => array(
									'value' => '36',
									'label' => esc_html__( 'Module Bock 36', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/36',
								),
								'37' => array(
									'value' => '37',
									'label' => esc_html__( 'Module Bock 37', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/37',
								),
								'38' => array(
									'value' => '38',
									'label' => esc_html__( 'Module Bock 38', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/38',
								),
								'39' => array(
									'value' => '39',
									'label' => esc_html__( 'Module Bock 39', 'jnews' ),
									'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/module/39',
								),
							),
							'default'         => '3',
						),

						array(
							'type'        => 'jnews-select-search',
							'id'          => 'main_custom_image_size',
							'label'       => esc_html__( 'Rendered Image Size in Main Thumbnail', 'jnews' ),
							'description' => esc_html__( 'Choose the image size that you want to rendered in main thumbnail in this module.', 'jnews' ),
							'default'     => 'default',
							'onSearch'    => 'searchImageLists',
							'isMulti'     => false,

						),

						array(
							'type'        => 'jnews-select-search',
							'id'          => 'second_custom_image_size',
							'label'       => esc_html__( 'Rendered Image Size in Second Thumbnail', 'jnews' ),
							'description' => esc_html__( 'Choose the image size that you want to rendered in second thumbnail in this module.', 'jnews' ),
							'default'     => 'default',
							'dependency'  => array(
								array(
									'field'    => 'module',
									'operator' => 'in',
									'value'    => array( '14' ),
								),
							),
							'onSearch'    => 'searchImageLists',
							'isMulti'     => false,

						),
						array(
							'type'        => 'jnews-toggle',
							'id'          => 'boxed',
							'label'       => esc_html__( 'Enable Boxed', 'jnews' ),
							'description' => esc_html__( 'This option will turn the module into boxed.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'module',
									'operator' => 'in',
									'value'    => array( '3', '4', '5', '6', '7', '9', '10', '14', '18', '22', '23', '25', '26', '27', '39' ),
								),
							),
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'boxed_shadow',
							'label'       => esc_html__( 'Enable Shadow', 'jnews' ),
							'description' => esc_html__( 'Enable shadow on the module template.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'module',
									'operator' => 'in',
									'value'    => array( '3', '4', '5', '6', '7', '9', '10', '14', '18', '22', '23', '25', '26', '27', '39' ),
								),
								array(
									'field'    => 'boxed',
									'operator' => '==',
									'value'    => 'yes',
								),
							),
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'box_shadow',
							'label'       => esc_html__( 'Enable Shadow', 'jnews' ),
							'description' => esc_html__( 'Enable shadow on the module template.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'module',
									'operator' => 'in',
									'value'    => array( '37', '35', '33', '36', '32', '38' ),
								),
							),
						),

						array(
							'type'        => 'jnews-slider',
							'id'          => 'excerpt_length',
							'label'       => esc_html__( 'Excerpt Length', 'jnews' ),
							'description' => esc_html__( 'Set word length of excerpt on post.', 'jnews' ),
							'choices'     => array(
								'min'  => '0',
								'max'  => '200',
								'step' => '1',
							),
							'default'     => '20',
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'content_date',
							'label'       => esc_html__( 'Date Format for Content', 'jnews' ),
							'description' => esc_html__( 'Choose which date format you want to use for search for content.', 'jnews' ),
							'choices'     => array(
								array(
									'value' => 'ago',
									'label' => esc_attr__( 'Relative Date/Time Format (ago)', 'jnews' ),
								),
								array(
									'value' => 'default',
									'label' => esc_attr__( 'WordPress Default Format', 'jnews' ),
								),
								array(
									'value' => 'custom',
									'label' => esc_attr__( 'Custom Format', 'jnews' ),
								),
							),
							'default'     => 'default',
						),

						array(
							'type'        => 'jnews-text',
							'id'          => 'date_custom',
							'label'       => esc_attr__( 'Custom Date Format for Content', 'jnews' ),
							'description' => wp_kses(
								sprintf(
									__(
										"Please set custom date format for post content. For more detail about this format, please refer to
										<a href='%s' target='_blank'>Developer Codex</a>.",
										'jnews'
									),
									'https://developer.wordpress.org/reference/functions/current_time/'
								),
								wp_kses_allowed_html()
							),
							'default'     => 'Y/m/d',
							'dependency'  => array(
								array(
									'field'    => 'content_date',
									'operator' => '==',
									'value'    => 'custom',
								),
							),
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'content_pagination',
							'label'       => esc_html__( 'Pagination Mode', 'jnews' ),
							'description' => esc_html__( 'Choose which pagination mode that fit with your block.', 'jnews' ),
							'choices'     => array(
								array(
									'value' => 'nav_1',
									'label' => esc_attr__( 'Normal - Navigation 1', 'jnews' ),
								),
								array(
									'value' => 'nav_2',
									'label' => esc_attr__( 'Normal - Navigation 2', 'jnews' ),
								),
								array(
									'value' => 'nav_3',
									'label' => esc_attr__( 'Normal - Navigation 3', 'jnews' ),
								),
							),
							'default'     => 'nav_1',
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'pagination_align',
							'label'       => esc_html__( 'Pagination Align', 'jnews' ),
							'description' => esc_html__( 'Choose pagination alignment.', 'jnews' ),
							'choices'     => array(
								array(
									'value' => 'left',
									'label' => esc_attr__( 'Left', 'jnews' ),
								),
								array(
									'value' => 'center',
									'label' => esc_attr__( 'Center', 'jnews' ),
								),
							),
							'default'     => 'center',
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'show_navtext',
							'label'       => esc_html__( 'Show Navigation Text', 'jnews' ),
							'description' => esc_html__( 'Show navigation text (next, prev).', 'jnews' ),
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'show_pageinfo',
							'label'       => esc_html__( 'Show Page Info', 'jnews' ),
							'description' => esc_html__( 'Show page info text (Page x of y).', 'jnews' ),
						),
					),
				),
				array(
					'type'   => 'tab',
					'id'     => 'page_loop_filter',
					'label'  => esc_html__( 'Content Filter', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-toggle',
							'id'          => 'post_sticky',
							'label'       => esc_html__( 'Show Sticky Post', 'jnews' ),
							'description' => esc_html__( 'Show Sticky Post in Page Loop', 'jnews' ),
							'default'     => '0',
						),
						array(
							'type'        => 'jnews-slider',
							'id'          => 'post_offset',
							'label'       => esc_html__( 'Post Offset', 'jnews' ),
							'description' => esc_html__( 'Number of post offset (start of content).', 'jnews' ),
							'default'     => '0',
							'choices'     => array(
								'min'  => '0',
								'max'  => '100',
								'step' => '1',
							),
						),
						array(
							'type'        => 'jnews-slider',
							'id'          => 'posts_per_page',
							'label'       => esc_html__( 'Posts Per Page', 'jnews' ),
							'description' => esc_html__( 'Number of posts per page.', 'jnews' ),
							'default'     => '5',
							'choices'     => array(
								'min'  => '1',
								'max'  => '100',
								'step' => '1',
							),
						),
						array(
							'type'        => 'jnews-select-search',
							'multiple'    => PHP_INT_MAX,
							'id'          => 'include_post',
							'label'       => esc_attr__( 'Include Post ID', 'jnews' ),
							'description' => wp_kses( __( 'Tips :<br/> - You can search post id by inputing title, clicking search title, and you will have your post id.<br/>- You can also directly insert your post id, and click enter to add it on the list.', 'jnews' ), wp_kses_allowed_html() ),
							'onSearch'    => 'searchPostsByTitleAndId',
						),
						array(
							'type'        => 'jnews-select-search',
							'multiple'    => PHP_INT_MAX,
							'id'          => 'exclude_post',
							'label'       => esc_attr__( 'Exclude Post ID', 'jnews' ),
							'description' => wp_kses( __( 'Tips :<br/> - You can search post id by inputing title, clicking search title, and you will have your post id.<br/>- You can also directly insert your post id, and click enter to add it on the list.', 'jnews' ), wp_kses_allowed_html() ),
							'onSearch'    => 'searchPostsByTitleAndId',
						),
						array(
							'type'        => 'jnews-select-search',
							'id'          => 'include_category',
							'multiple'    => PHP_INT_MAX,
							'label'       => esc_html__( 'Include Category', 'jnews' ),
							'description' => esc_html__( 'Choose which category you want to show on this module.', 'jnews' ),
							'onSearch'    => 'searchCategoriesByTitleAndId',
						),
						array(
							'type'        => 'jnews-select-search',
							'id'          => 'exclude_category',
							'multiple'    => PHP_INT_MAX,
							'label'       => esc_html__( 'Exclude Category', 'jnews' ),
							'description' => esc_html__( 'Choose excluded category for this module.', 'jnews' ),
							'onSearch'    => 'searchCategoriesByTitleAndId',
						),
						array(
							'type'        => 'jnews-select-search',
							'multiple'    => PHP_INT_MAX,
							'id'          => 'include_author',
							'label'       => esc_html__( 'Author', 'jnews' ),
							'description' => esc_html__( 'Write to search post author.', 'jnews' ),
							'onSearch'    => 'searchAuthorsByNameAndId',
						),
						array(
							'type'        => 'jnews-select-search',
							'multiple'    => PHP_INT_MAX,
							'id'          => 'include_tag',
							'label'       => esc_html__( 'Include Tags', 'jnews' ),
							'description' => esc_html__( 'Write to search post tag.', 'jnews' ),
							'onSearch'    => 'searchTagsByTitleAndId',
						),
						array(
							'type'        => 'jnews-select-search',
							'multiple'    => PHP_INT_MAX,
							'id'          => 'exclude_tag',
							'label'       => esc_html__( 'Exclude Tags', 'jnews' ),
							'description' => esc_html__( 'Write to search post tag.', 'jnews' ),
							'onSearch'    => 'searchTagsByTitleAndId',
						),
						array(
							'type'        => 'jnews-select',
							'id'          => 'sort_by',
							'label'       => esc_html__( 'Sort By', 'jnews' ),
							'description' => esc_html__( 'Choose sort type for this module.', 'jnews' ),
							'default'     => 'latest',
							'choices'     => array(
								array(
									'value' => 'latest',
									'label' => esc_html__( 'Latest Post - Published Date', 'jnews' ),
								),
								array(
									'value' => 'latest_modified',
									'label' => esc_html__( 'Latest Post - Modified Date', 'jnews' ),
								),
								array(
									'value' => 'oldest',
									'label' => esc_attr__( 'Oldest Post - Published Date', 'jnews' ),
								),
								array(
									'value' => 'oldest_modified',
									'label' => esc_attr__( 'Oldest Post - Modified Date', 'jnews' ),
								),
								array(
									'value' => 'alphabet_asc',
									'label' => esc_attr__( 'Alphabet Asc', 'jnews' ),
								),
								array(
									'value' => 'alphabet_desc',
									'label' => esc_attr__( 'Alphabet Desc', 'jnews' ),
								),
								array(
									'value' => 'random',
									'label' => esc_attr__( 'Random Post', 'jnews' ),
								),
								array(
									'value' => 'random_week',
									'label' => esc_attr__( 'Random Post (7 Days)', 'jnews' ),
								),
								array(
									'value' => 'random_month',
									'label' => esc_attr__( 'Random Post (30 Days)', 'jnews' ),
								),
								array(
									'value' => 'most_comment',
									'label' => esc_attr__( 'Most Comment', 'jnews' ),
								),
								array(
									'value' => 'rate',
									'label' => esc_attr__( 'Highest Rate - Review', 'jnews' ),
								),
								array(
									'value' => 'like',
									'label' => esc_attr__( 'Most Like (Thumb up)', 'jnews' ),
								),
								array(
									'value' => 'share',
									'label' => esc_attr__( 'Most Share', 'jnews' ),
								),
							),
						),
					),
				),

			),
		);
	}

	/**
	 * Method page_default_meta
	 *
	 * @return array
	 */
	public function page_default_meta() {
		return array(
			'id'         => 'jnews_single_page',
			'post_types' => array( 'page' ),
			'label'      => esc_html__( 'JNews : Single Page Default', 'jnews' ),
			'icon'       => 'SinglePageSvg',
			'priority'   => 'high',
			'fields'     => array(
				array(
					'type'            => 'jnews-radio-image',
					'id'              => 'layout',
					'label'           => esc_html__( 'Single Page Layout', 'jnews' ),
					'description'     => esc_html__( 'Choose your single page layout.', 'jnews' ),
					'item_max_width'  => '118',
					'item_max_height' => '93',
					'options'         => array(
						'right-sidebar'        => array(
							'value' => 'right-sidebar',
							'label' => esc_html__( 'Right Sidebar', 'jnews' ),
							'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/right-sidebar',
						),
						'left-sidebar'         => array(
							'value' => 'left-sidebar',
							'label' => esc_html__( 'Left Sidebar', 'jnews' ),
							'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/left-sidebar',
						),
						'right-sidebar-narrow' => array(
							'value' => 'right-sidebar-narrow',
							'label' => esc_html__( 'Right Sidebar - Narrow', 'jnews' ),
							'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/right-sidebar-narrow',
						),
						'left-sidebar-narrow'  => array(
							'value' => 'left-sidebar-narrow',
							'label' => esc_html__( 'Left Sidebar - Narrow', 'jnews' ),
							'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/left-sidebar-narrow',
						),
						'double-sidebar'       => array(
							'value' => 'double-sidebar',
							'label' => esc_html__( 'Double Sidebar', 'jnews' ),
							'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/double-sidebar',
						),
						'double-right-sidebar' => array(
							'value' => 'double-right-sidebar',
							'label' => esc_html__( 'Double Right Sidebar', 'jnews' ),
							'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/double-right-sidebar',
						),
						'no-sidebar'           => array(
							'value' => 'no-sidebar',
							'label' => esc_html__( 'No Sidebar', 'jnews' ),
							'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/no-sidebar',
						),
					),
					'default'         => 'no-sidebar',
				),

				array(
					'type'        => 'jnews-select',
					'id'          => 'sidebar',
					'label'       => esc_html__( 'Single Page Sidebar', 'jnews' ),
					'description' => wp_kses( __( 'Choose your page sidebar. If you need another sidebar, you can create from <strong>WordPress Admin</strong> &raquo; <strong>Appearance</strong> &raquo; <strong>Widget</strong>.', 'jnews' ), wp_kses_allowed_html() ),
					'default'     => '{{first}}',
					'dependency'  => array(
						array(
							'field'    => 'layout',
							'operator' => '!=',
							'value'    => 'no-sidebar',
						),
					),
					'choices'     => call_user_func( 'jnews_get_sidebar' ),
				),

				array(
					'type'        => 'jnews-select',
					'id'          => 'second_sidebar',
					'label'       => esc_html__( 'Second Single Page Sidebar', 'jnews' ),
					'description' => wp_kses( __( 'Choose your second sidebar for this page. If you need another sidebar, you can create from <strong>WordPress Admin</strong> &raquo; <strong>Appearance</strong> &raquo; <strong>Widget</strong>.', 'jnews' ), wp_kses_allowed_html() ),
					'default'     => '{{first}}',
					'dependency'  => array(
						array(
							'field'    => 'layout',
							'operator' => 'in',
							'value'    => array( 'double-sidebar', 'double-right-sidebar' ),
						),
					),
					'choices'     => call_user_func( 'jnews_get_sidebar' ),
				),

				array(
					'type'        => 'jnews-toggle',
					'id'          => 'sticky_sidebar',
					'label'       => esc_html__( 'Single Page Sticky Sidebar', 'jnews' ),
					'description' => esc_html__( 'Enable sticky sidebar on single page.', 'jnews' ),
					'default'     => '1',
					'dependency'  => array(
						array(
							'field'    => 'layout',
							'operator' => '!=',
							'value'    => 'no-sidebar',
						),
					),
				),

				array(
					'type'        => 'jnews-toggle',
					'id'          => 'show_post_title',
					'label'       => esc_html__( 'Show Page Title', 'jnews' ),
					'description' => esc_html__( 'Show page title for this page.', 'jnews' ),
					'default'     => '1',
				),

				array(
					'type'        => 'jnews-toggle',
					'id'          => 'show_post_breadcrumbs',
					'label'       => esc_html__( 'Show Page Breadcrumbs', 'jnews' ),
					'description' => esc_html__( 'Show page breadcrumbs for this page.', 'jnews' ),
					'default'     => '1',
				),

				array(
					'type'        => 'jnews-toggle',
					'id'          => 'show_post_featured',
					'label'       => esc_html__( 'Show Featured Image', 'jnews' ),
					'description' => esc_html__( 'Show featured image for this page.', 'jnews' ),
					'default'     => '1',
				),

				array(
					'type'        => 'jnews-toggle',
					'id'          => 'show_post_meta',
					'label'       => esc_html__( 'Show Page Meta', 'jnews' ),
					'description' => esc_html__( 'Show page meta on page header.', 'jnews' ),
				),

				array(
					'type'        => 'jnews-select',
					'id'          => 'share_position',
					'label'       => esc_html__( 'Share Position', 'jnews' ),
					'description' => esc_html__( 'Choose your share position.', 'jnews' ),
					'choices'     => array(
						array(
							'value' => 'top',
							'label' => esc_attr__( 'Only Top', 'jnews' ),
						),
						array(
							'value' => 'float',
							'label' => esc_attr__( 'Only Float', 'jnews' ),
						),
						array(
							'value' => 'bottom',
							'label' => esc_attr__( 'Only Bottom', 'jnews' ),
						),
						array(
							'value' => 'topbottom',
							'label' => esc_attr__( 'Top + Bottom', 'jnews' ),
						),
						array(
							'value' => 'floatbottom',
							'label' => esc_attr__( 'Float + Bottom', 'jnews' ),
						),
						array(
							'value' => 'hide',
							'label' => esc_attr__( 'Hide All', 'jnews' ),
						),
					),
					'default'     => 'top',
				),

				array(
					'type'        => 'jnews-select',
					'id'          => 'share_color',
					'label'       => esc_html__( 'Float Share Style', 'jnews' ),
					'description' => esc_html__( 'Choose your float share style.', 'jnews' ),
					'choices'     => array(
						array(
							'value' => 'share-normal',
							'label' => esc_attr__( 'Color', 'jnews' ),
						),
						array(
							'value' => 'share-monocrhome',
							'label' => esc_attr__( 'Monochrome', 'jnews' ),
						),
					),
					'default'     => 'share-monocrhome',
					'dependency'  => array(
						array(
							'field'    => 'share_position',
							'operator' => 'in',
							'value'    => array( 'float', 'floatbottom' ),
						),
					),
				),

			),
		);
	}

	/**
	 * Method single_post_meta
	 *
	 * @return array
	 */
	public function single_post_meta() {

		$options = array(
			'id'         => 'jnews_single_post',
			'post_types' => array( 'post', 'sp_event', 'sp_team', 'sp_player', 'sp_staff' ),
			'label'      => esc_html__( 'Single Post Setting', 'jnews' ),
			'icon'       => 'SettingsSvg',
			'priority'   => 'high',
			'fields'     => array(
				array(
					'type'   => 'tab',
					'id'     => 'override_default_template_tab',
					'label'  => esc_html__( 'Override Default Template', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-toggle',
							'id'          => 'override_template',
							'label'       => esc_html__( 'Override Global Template Setting', 'jnews' ),
							'description' => esc_html__( 'Check this option and you will have option to override global template setting for only this post.', 'jnews' ),
						),
						array(
							'type'        => 'jnews-group',
							'id'          => 'override',
							'label'       => esc_html__( 'Template Override Option', 'jnews' ),
							'description' => esc_html__( 'Option for overriding jnews single post template.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'override_template',
									'operator' => '==',
									'value'    => '1',
								),
							),
							'fields'      => array(
								array(
									'type'            => 'jnews-radio-image',
									'id'              => 'template',
									'label'           => esc_html__( 'Post Header Template', 'jnews' ),
									'description'     => esc_html__( 'This template may not work for every post format, for more information please refer to documentation.', 'jnews' ),
									'item_max_width'  => '118',
									'item_max_height' => '93',
									'options'         => array(
										'1'      => array(
											'value' => '1',
											'label' => esc_html__( 'Post Template 1', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/1',
										),
										'2'      => array(
											'value' => '2',
											'label' => esc_html__( 'Post Template 2', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/2',
										),
										'3'      => array(
											'value' => '3',
											'label' => esc_html__( 'Post Template 3', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/3',
										),
										'4'      => array(
											'value' => '4',
											'label' => esc_html__( 'Post Template 4', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/4',
										),
										'5'      => array(
											'value' => '5',
											'label' => esc_html__( 'Post Template 5', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/5',
										),
										'6'      => array(
											'value' => '6',
											'label' => esc_html__( 'Post Template 6', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/6',
										),
										'7'      => array(
											'value' => '7',
											'label' => esc_html__( 'Post Template 7', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/7',
										),
										'8'      => array(
											'value' => '8',
											'label' => esc_html__( 'Post Template 8', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/8',
										),
										'9'      => array(
											'value' => '9',
											'label' => esc_html__( 'Post Template 9', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/9',
										),
										'10'     => array(
											'value' => '10',
											'label' => esc_html__( 'Post Template 10', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/10',
										),
										'custom' => array(
											'value' => 'custom',
											'label' => esc_html__( 'Post Template Custom', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/header_template/custom',
										),
									),
									'default'         => get_theme_mod( 'jnews_single_blog_template', '1' ),
								),
								array(
									'type'        => 'jnews-select-search',
									'id'          => 'single_blog_custom',
									'label'       => esc_html__( 'Custom Single Post Template', 'jnews' ),
									'description' => wp_kses( sprintf( __( 'Create custom single post template from <a href="%s" target="_blank">here</a>', 'jnews' ), get_admin_url() . 'edit.php?post_type=custom-post-template' ), wp_kses_allowed_html() ),
									'default'     => get_theme_mod( 'jnews_single_blog_custom', '' ),
									'onSearch'    => 'searchCustomPostTemplate',
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '==',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'parallax',
									'label'       => esc_html__( 'Enable Parallax Effect', 'jnews' ),
									'description' => esc_html__( 'Turn on this option if you want your featured image have parallax effect.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_blog_enable_parallax', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => 'in',
											'value'    => array( '4', '5' ),
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'fullscreen',
									'label'       => esc_html__( 'Enable Fullscreen Featured Image', 'jnews' ),
									'description' => esc_html__( 'Turn on this option if you want your post header have fullscreen image featured.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_blog_enable_fullscreen', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => 'in',
											'value'    => array( '4', '5' ),
										),
									),
								),
								array(
									'type'            => 'jnews-radio-image',
									'id'              => 'layout',
									'label'           => esc_html__( 'Single Blog Post Layout', 'jnews' ),
									'description'     => esc_html__( 'Choose your single blog post layout.', 'jnews' ),
									'item_max_width'  => '118',
									'item_max_height' => '93',
									'options'         => array(
										'right-sidebar'  => array(
											'value' => 'right-sidebar',
											'label' => esc_html__( 'Right Sidebar', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/right-sidebar',
										),
										'left-sidebar'   => array(
											'value' => 'left-sidebar',
											'label' => esc_html__( 'Left Sidebar', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/left-sidebar',
										),
										'right-sidebar-narrow' => array(
											'value' => 'right-sidebar-narrow',
											'label' => esc_html__( 'Right Sidebar - Narrow', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/right-sidebar-narrow',
										),
										'left-sidebar-narrow' => array(
											'value' => 'left-sidebar-narrow',
											'label' => esc_html__( 'Left Sidebar - Narrow', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/left-sidebar-narrow',
										),
										'double-sidebar' => array(
											'value' => 'double-sidebar',
											'label' => esc_html__( 'Double Sidebar', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/double-sidebar',
										),
										'double-right-sidebar' => array(
											'value' => 'double-right-sidebar',
											'label' => esc_html__( 'Double Right Sidebar ', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/double-right-sidebar',
										),
										'no-sidebar'     => array(
											'value' => 'no-sidebar',
											'label' => esc_html__( 'No Sidebar', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/no-sidebar',
										),
										'no-sidebar-narrow' => array(
											'value' => 'no-sidebar-narrow',
											'label' => esc_html__( 'No Sidebar - Narrow', 'jnews' ),
											'img'   => JNEWS_THEME_URL . '/assets/img/admin/metabox/post_layout/no-sidebar-narrow',
										),
									),
									'default'         => get_theme_mod( 'jnews_single_blog_layout', 'right-sidebar' ),
									'dependency'      => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-select',
									'id'          => 'sidebar',
									'label'       => esc_html__( 'Single Post Sidebar', 'jnews' ),
									'description' => wp_kses( __( 'Choose your single post sidebar. If you need another sidebar, you can create from <strong>WordPress Admin</strong> &raquo; <strong>Appearance</strong> &raquo; <strong>Widget</strong>.', 'jnews' ), wp_kses_allowed_html() ),
									'default'     => get_theme_mod( 'jnews_single_sidebar', 'default-sidebar' ),
									'dependency'  => array(
										array(
											'field'    => 'layout',
											'operator' => 'in',
											'value'    => array(
												'left-sidebar',
												'right-sidebar',
												'left-sidebar-narrow',
												'right-sidebar-narrow',
												'double-sidebar',
												'double-right-sidebar',
											),
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
									'choices'     => call_user_func( 'jnews_get_sidebar' ),
								),
								array(
									'type'        => 'jnews-select',
									'id'          => 'second_sidebar',
									'label'       => esc_html__( 'Second Single Post Sidebar', 'jnews' ),
									'description' => wp_kses( __( 'Choose your single post sidebar for the second sidebar. If you need another sidebar, you can create from <strong>WordPress Admin</strong> &raquo; <strong>Appearance</strong> &raquo; <strong>Widget</strong>.', 'jnews' ), wp_kses_allowed_html() ),
									'default'     => get_theme_mod( 'jnews_single_second_sidebar', 'default-sidebar' ),
									'dependency'  => array(
										array(
											'field'    => 'layout',
											'operator' => 'in',
											'value'    => array( 'double-sidebar', 'double-right-sidebar' ),
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
									'choices'     => call_user_func( 'jnews_get_sidebar' ),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'sticky_sidebar',
									'label'       => esc_html__( 'Single Post Sticky Sidebar', 'jnews' ),
									'description' => esc_html__( 'Enable sticky sidebar on single post page.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_sticky_sidebar', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'layout',
											'operator' => 'in',
											'value'    => array(
												'left-sidebar',
												'right-sidebar',
												'left-sidebar-narrow',
												'right-sidebar-narrow',
												'double-sidebar',
												'double-right-sidebar',
											),
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-select',
									'id'          => 'share_position',
									'label'       => esc_html__( 'Single Post Share Position', 'jnews' ),
									'description' => esc_html__( 'Choose your share position.', 'jnews' ),
									'choices'     => array(
										array(
											'value' => 'top',
											'label' => esc_attr__( 'Only Top', 'jnews' ),
										),
										array(
											'value' => 'float',
											'label' => esc_attr__( 'Only Float', 'jnews' ),
										),
										array(
											'value' => 'bottom',
											'label' => esc_attr__( 'Only Bottom', 'jnews' ),
										),
										array(
											'value' => 'topbottom',
											'label' => esc_attr__( 'Top + Bottom', 'jnews' ),
										),
										array(
											'value' => 'floatbottom',
											'label' => esc_attr__( 'Float + Bottom', 'jnews' ),
										),
										array(
											'value' => 'hide',
											'label' => esc_attr__( 'Hide All', 'jnews' ),
										),
									),
									'default'     => get_theme_mod( 'jnews_single_share_position', 'top' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-select',
									'id'          => 'share_float_style',
									'label'       => esc_html__( 'Float Share Style', 'jnews' ),
									'description' => esc_html__( 'Choose your float share style.', 'jnews' ),
									'choices'     => array(
										array(
											'value' => 'share-normal',
											'label' => esc_attr__( 'Color', 'jnews' ),
										),
										array(
											'value' => 'share-monocrhome',
											'label' => esc_attr__( 'Monochrome', 'jnews' ),
										),
									),
									'default'     => get_theme_mod( 'jnews_single_share_float_style', 'share-monocrhome' ),
									'dependency'  => array(
										array(
											'field'    => 'share_position',
											'operator' => 'in',
											'value'    => array( 'float', 'floatbottom' ),
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_share_counter',
									'label'       => esc_html__( 'Show Share Counter', 'jnews' ),
									'description' => wp_kses( __( 'Show or hide share counter, share counter may be hidden depend on your setup on <strong>Single Post Share Position</strong>.', 'jnews' ), wp_kses_allowed_html() ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_share_counter', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'share_position',
											'operator' => 'in',
											'value'    => array( 'top', 'topbottom' ),
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),

								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_view_counter',
									'label'       => esc_html__( 'Show View Counter', 'jnews' ),
									'description' => wp_kses( __( 'Show or hide view counter, share counter may be hidden depend on your setup on <strong>Single Post Share Position</strong>.', 'jnews' ), wp_kses_allowed_html() ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_view_counter', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'share_position',
											'operator' => 'in',
											'value'    => array( 'top', 'topbottom' ),
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),

								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_featured',
									'label'       => esc_html__( 'Show Featured Image/Video', 'jnews' ),
									'description' => esc_html__( 'Show featured image, gallery or video on single post.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_featured', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),

								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_meta',
									'label'       => esc_html__( 'Show Post Meta', 'jnews' ),
									'description' => esc_html__( 'Show post meta on post header.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_post_meta', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_author',
									'label'       => esc_html__( 'Show Post Author', 'jnews' ),
									'description' => esc_html__( 'Show post author on post meta container.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_post_author', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_author_image',
									'label'       => esc_html__( 'Show Post Author Image', 'jnews' ),
									'description' => esc_html__( 'Show post author image on post meta container.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_post_author_image', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_date',
									'label'       => esc_html__( 'Show Post Date', 'jnews' ),
									'description' => esc_html__( 'Show post date on post meta container.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_post_date', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-select',
									'id'          => 'post_date_format',
									'label'       => esc_html__( 'Post Date Format', 'jnews' ),
									'description' => esc_html__( 'Choose which date format you want to use for single post meta.', 'jnews' ),
									'default'     => get_theme_mod( 'jnews_single_post_date_format', 'default' ),
									'choices'     => array(
										array(
											'value' => 'ago',
											'label' => esc_attr__( 'Relative Date/Time Format (ago)', 'jnews' ),
										),
										array(
											'value' => 'default',
											'label' => esc_attr__( 'WordPress Default Format', 'jnews' ),
										),
										array(
											'value' => 'custom',
											'label' => esc_attr__( 'Custom Format', 'jnews' ),
										),
									),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'show_post_date',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-text',
									'id'          => 'post_date_format_custom',
									'label'       => esc_html__( 'Custom Date Format', 'jnews' ),
									'description' => wp_kses(
										sprintf(
											__(
												"Please set custom date format for single post meta. For more detail about this format, please refer to
												<a href='%s' target='_blank'>Developer Codex</a>.",
												'jnews'
											),
											'https://developer.wordpress.org/reference/functions/current_time/'
										),
										wp_kses_allowed_html()
									),
									'default'     => get_theme_mod( 'jnews_single_post_date_format_custom', 'Y/m/d' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'show_post_date',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'post_date_format',
											'operator' => 'in',
											'value'    => array( 'custom' ),
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_category',
									'label'       => esc_html__( 'Show Category', 'jnews' ),
									'description' => esc_html__( 'Show post category on post meta container.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_category', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_reading_time',
									'label'       => esc_html__( 'Show Reading Time', 'jnews' ),
									'description' => esc_html__( 'Show estimate reading time on post meta container.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_reading_time', '0' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-text',
									'id'          => 'post_reading_time_wpm',
									'label'       => esc_html__( 'Words Per Minute', 'jnews' ),
									'description' => esc_html__( 'Set the average reading speed for the user.', 'jnews' ),
									'default'     => get_theme_mod( 'jnews_single_reading_time_wpm', '300' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
										array(
											'field'    => 'show_post_reading_time',
											'operator' => '==',
											'value'    => '1',
										),
									),
								),

								array(
									'type'        => 'jnews-select',
									'id'          => 'post_calculate_word_method',
									'label'       => esc_html__( 'Word Count Calculation Method', 'jnews' ),
									'description' => esc_html__( 'Select the Word Count Calculation Method.', 'jnews' ),
									'default'     => get_theme_mod( 'jnews_calculate_word_method', 'str_word_count' ),
									'choices'     => array(
										array(
											'value' => 'str_word_count',
											'label' => esc_attr__( 'PHP Word Counter Method', 'jnews' ),
										),
										array(
											'value' => 'count_blank',
											'label' => esc_attr__( 'Count the spaces between the words', 'jnews' ),
										),
									),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
										array(
											'field'    => 'show_post_reading_time',
											'operator' => '==',
											'value'    => '1',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_zoom_button',
									'label'       => esc_html__( 'Show Zoom Button', 'jnews' ),
									'description' => esc_html__( 'Show zoom button on the post meta container.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_zoom_button', '0' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-slider',
									'id'          => 'zoom_button_out_step',
									'label'       => esc_html__( 'Number of Zoom Out Step', 'jnews' ),
									'description' => esc_html__( 'Set the number of zoom out step to limit when zoom out button clicked.', 'jnews' ),
									'min'         => '1',
									'max'         => '5',
									'step'        => '1',
									'choices'     => array(
										'min'  => '1',
										'max'  => '5',
										'step' => '1',
									),
									'default'     => get_theme_mod( 'jnews_single_zoom_button_out_step', '2' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'show_zoom_button',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-slider',
									'id'          => 'zoom_button_in_step',
									'label'       => esc_html__( 'Number of Zoom In Step', 'jnews' ),
									'description' => esc_html__( 'Set the number of zoom in step to limit when zoom in button clicked.', 'jnews' ),
									'min'         => '1',
									'max'         => '5',
									'step'        => '1',
									'choices'     => array(
										'min'  => '1',
										'max'  => '5',
										'step' => '1',
									),
									'default'     => get_theme_mod( 'jnews_single_zoom_button_in_step', '3' ),
									'dependency'  => array(
										array(
											'field'    => 'show_post_meta',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'show_zoom_button',
											'operator' => '==',
											'value'    => '1',
										),
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_tag',
									'label'       => esc_html__( 'Show Post Tag', 'jnews' ),
									'description' => esc_html__( 'Show single post tag (below article).', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_tag', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_prev_next_post',
									'label'       => esc_html__( 'Show Prev / Next Post', 'jnews' ),
									'description' => esc_html__( 'Show previous or next post navigation (below article).', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_prev_next_post', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_popup_post',
									'label'       => esc_html__( 'Show Popup Post', 'jnews' ),
									'description' => esc_html__( 'Show bottom right popup post widget.', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_popup_post', '1' ),
								),
								array(
									'type'        => 'jnews-slider',
									'id'          => 'number_popup_post',
									'label'       => esc_html__( 'Number of Post', 'jnews' ),
									'description' => esc_html__( 'Set the number of post to show when popup post appear.', 'jnews' ),
									'min'         => '1',
									'max'         => '5',
									'step'        => '1',
									'choices'     => array(
										'min'  => '1',
										'max'  => '5',
										'step' => '1',
									),
									'default'     => get_theme_mod( 'jnews_single_number_popup_post', '1' ),
									'dependency'  => array(
										array(
											'field'    => 'show_popup_post',
											'operator' => '==',
											'value'    => '1',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_author_box',
									'label'       => esc_html__( 'Show Author Box', 'jnews' ),
									'description' => esc_html__( 'Show author box (below article).', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_author_box', '0' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_post_related',
									'label'       => esc_html__( 'Show Post Related', 'jnews' ),
									'description' => esc_html__( 'Show post related (below article).', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_show_post_related', '0' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
								array(
									'type'        => 'jnews-toggle',
									'id'          => 'show_inline_post_related',
									'label'       => esc_html__( 'Show Inline Post Related', 'jnews' ),
									'description' => esc_html__( 'Show inline post related (inside article).', 'jnews' ),
									'default'     => (string) get_theme_mod( 'jnews_single_post_show_inline_related', '0' ),
									'dependency'  => array(
										array(
											'field'    => 'template',
											'operator' => '!=',
											'value'    => 'custom',
										),
									),
								),
							),
						),
					),
				),

				array(
					'type'   => 'tab',
					'id'     => 'override_image_size_tab',
					'label'  => esc_html__( 'Override Image Size', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-toggle',
							'id'          => 'override_image_size',
							'label'       => esc_html__( 'Override Image Thumbnail Size', 'jnews' ),
							'description' => esc_html__( 'Check this option and you will have option to override your image thumbnail size. If you are using post template with full size image, this option will be ignored.', 'jnews' ),
						),
						array(
							'type'        => 'jnews-group',
							'id'          => 'image_override',
							'label'       => esc_html__( 'Image Size Override Option', 'jnews' ),
							'description' => esc_html__( 'Option for overriding jnews single image size.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'override_image_size',
									'operator' => '==',
									'value'    => '1',
								),
							),
							'fields'      => array(
								array(
									'type'        => 'jnews-select',
									'id'          => 'single_post_thumbnail_size',
									'label'       => esc_html__( 'Post Thumbnail Size', 'jnews' ),
									'description' => esc_html__( 'Choose image thumbnail size.', 'jnews' ),
									'default'     => get_theme_mod( 'jnews_single_post_thumbnail_size', 'crop-500' ),
									'choices'     => array(
										array(
											'value' => 'no-crop',
											'label' => esc_html__( 'No Crop', 'jnews' ),
										),
										array(
											'value' => 'crop-500',
											'label' => esc_html__( 'Crop 1/2 Dimension', 'jnews' ),
										),
										array(
											'value' => 'crop-715',
											'label' => esc_html__( 'Crop Default Dimension', 'jnews' ),
										),
									),
								),
								array(
									'type'        => 'jnews-select',
									'id'          => 'single_post_gallery_size',
									'label'       => esc_html__( 'Post Gallery Thumbnail Size', 'jnews' ),
									'description' => esc_html__( 'Choose image gallery thumbnail size.', 'jnews' ),
									'default'     => get_theme_mod( 'jnews_single_post_gallery_size', 'crop-500' ),
									'choices'     => array(
										array(
											'value' => 'crop-500',
											'label' => esc_html__( 'Crop 1/2 Dimension', 'jnews' ),
										),
										array(
											'value' => 'crop-715',
											'label' => esc_html__( 'Crop Default Dimension', 'jnews' ),
										),
									),
								),
							),
						),
					),
				),

				array(
					'type'   => 'tab',
					'id'     => 'trending_post_tab',
					'label'  => esc_html__( 'Trending Post', 'jnews' ),
					'fields' => array(

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'trending_post',
							'label'       => esc_html__( 'Trending Post', 'jnews' ),
							'description' => esc_html__( 'Set this post as a trending post.', 'jnews' ),
						),

						array(
							'type'        => 'jnews-select',
							'id'          => 'trending_post_position',
							'label'       => esc_html__( 'Trending Post Position', 'jnews' ),
							'description' => esc_html__( 'Choose trending post flag position.', 'jnews' ),
							'default'     => 'meta',
							'choices'     => array(
								array(
									'value' => 'meta',
									'label' => esc_html__( 'Inline Post Meta', 'jnews' ),
								),
								array(
									'value' => 'title',
									'label' => esc_html__( 'Above Post Title', 'jnews' ),
								),
							),
							'dependency'  => array(
								array(
									'field'    => 'trending_post',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),

						array(
							'type'        => 'jnews-text',
							'id'          => 'trending_post_label',
							'label'       => esc_html__( 'Trending Post Label', 'jnews' ),
							'description' => esc_html__( 'Insert a text for trending post label.', 'jnews' ),
							'default'     => esc_html__( 'Trending', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'trending_post',
									'operator' => '==',
									'value'    => '1',
								),
								array(
									'field'    => 'trending_post_position',
									'operator' => '==',
									'value'    => 'title',
								),
							),
						),

					),
				),
				array(
					'type'   => 'tab',
					'id'     => 'sponsored_post_tab',
					'label'  => esc_html__( 'Sponsored Post', 'jnews' ),
					'fields' => array(

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'sponsored_post',
							'label'       => esc_html__( 'Sponsored Post', 'jnews' ),
							'description' => esc_html__( 'Set this post as a sponsored post.', 'jnews' ),
						),

						array(
							'type'        => 'jnews-text',
							'id'          => 'sponsored_post_label',
							'label'       => esc_html__( 'Sponsored Post Label', 'jnews' ),
							'description' => esc_html__( 'Insert a text for sponsored post label.', 'jnews' ),
							'default'     => esc_html__( 'Sponsored by', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'sponsored_post',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),

						array(
							'type'        => 'jnews-text',
							'id'          => 'sponsored_post_name',
							'label'       => esc_html__( 'Sponsored Post Name', 'jnews' ),
							'description' => esc_html__( 'Insert the name of sponsor.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'sponsored_post',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),

						array(
							'type'        => 'jnews-url',
							'id'          => 'sponsored_post_url',
							'label'       => esc_html__( 'Sponsored Post URL', 'jnews' ),
							'description' => esc_html__( 'Insert url for the sponsor.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'sponsored_post',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),

						array(
							'type'        => 'jnews-toggle',
							'id'          => 'sponsored_post_logo_enable',
							'label'       => esc_html__( 'Show Sponsor Logo', 'jnews' ),
							'description' => esc_html__( 'Enable this option to show the sponsor logo instead of sponsor name.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'sponsored_post',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),

						array(
							'type'        => 'jnews-image',
							'id'          => 'sponsored_post_logo',
							'label'       => esc_html__( 'Sponsored Post Logo', 'jnews' ),
							'description' => esc_html__( 'Insert an image as sponsor logo.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'sponsored_post',
									'operator' => '==',
									'value'    => '1',
								),
								array(
									'field'    => 'sponsored_post_logo_enable',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),

						array(
							'type'        => 'jnews-textarea',
							'id'          => 'sponsored_post_desc',
							'label'       => esc_html__( 'Sponsored Description', 'jnews' ),
							'description' => esc_html__( 'Insert some text for sponsored post description.', 'jnews' ),
							'dependency'  => array(
								array(
									'field'    => 'sponsored_post',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),

					),
				),
				array(
					'type'   => 'tab',
					'id'     => 'ad_post_tab',
					'label'  => esc_html__( 'Ad Option', 'jnews' ),
					'fields' => array(
						array(
							'type'        => 'jnews-toggle',
							'id'          => 'disable_ad',
							'label'       => esc_html__( 'Disable Ad', 'jnews' ),
							'description' => esc_html__( "Don't Display ad on this post.", 'jnews' ),
							'default'     => '0',
						),
					),
				),
			),
		);

		$post_id     = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';
		$post_format = get_post_format( $post_id );

		$general_setting = array(
			'type'   => 'tab',
			'id'     => 'general_setting',
			'label'  => esc_html__( 'General Settings', 'jnews' ),
			'fields' => array(
				array(
					'type'          => 'jnews-text',
					'id'            => 'subtitle',
					'hideOnClassic' => true,
					'default'       => $post_id ? get_post_meta( $post_id, 'post_subtitle', true ) : '',
					'label'         => esc_html__( 'Post Subtitle', 'jnews' ),
					'description'   => esc_html__( 'Insert some text as post subtitle.', 'jnews' ),
				),
				array(
					'type'        => 'jnews-textarea',
					'id'          => 'video',
					'default'     => $post_id ? get_post_meta( $post_id, '_format_video_embed', true ) : '',
					'label'       => esc_html__( 'Video URL', 'jnews' ),
					'allow_html'  => true,
					'description' => esc_html__( 'Insert video URL or embed code.', 'jnews' ),
					'dependency'  => array(
						array(
							'field'    => 'format',
							'operator' => '==',
							'value'    => 'video',
						),
					),
					'hideOnClassic' => true,
				),
				array(
					'type'        => 'jnews-multi-image',
					'id'          => 'gallery',
					'label'       => esc_html__( 'Gallery Format', 'jnews' ),
					'description' => esc_html__( 'Insert some images as gallery.', 'jnews' ),
					'dependency'  => array(
						array(
							'field'    => 'format',
							'operator' => '==',
							'value'    => 'gallery',
						),
					),
					'options'     => array(),
					'hideOnClassic' => true,
				),
				array(
					'type'        => 'jnews-text',
					'id'          => 'source_name',
					'label'       => esc_html__( 'Source Name', 'jnews' ),
					'description' => esc_html__( 'This source name will show at the end of the post content.', 'jnews' ),
				),

				array(
					'type'        => 'jnews-url',
					'id'          => 'source_url',
					'label'       => esc_html__( 'Source URL', 'jnews' ),
					'description' => esc_html__( 'Insert source url link.', 'jnews' ),
				),

				array(
					'type'        => 'jnews-text',
					'id'          => 'via_name',
					'label'       => esc_html__( 'Via Name', 'jnews' ),
					'description' => esc_html__( 'This via name will show at the end of the post content.', 'jnews' ),
				),

				array(
					'type'        => 'jnews-url',
					'id'          => 'via_url',
					'label'       => esc_html__( 'Via URL', 'jnews' ),
					'description' => esc_html__( 'Insert via url link.', 'jnews' ),
				),
			),
		);

		$post_format_field = array(
			'type'        => 'jnews-select',
			'id'          => 'format',
			'default'     => $post_format ? $post_format : 'standard',
			'label'       => esc_html__( 'Post Format Type', 'jnews' ),
			'description' => esc_html__( 'Choose post format type for the current post.', 'jnews' ),
			'hideOnClassic' => true,
			'choices'     => array(
				array(
					'value' => 'standard',
					'label' => esc_attr__( 'Standard', 'jnews' ),
				),
				array(
					'value' => 'gallery',
					'label' => esc_attr__( 'Gallery', 'jnews' ),
				),
				array(
					'value' => 'video',
					'label' => esc_attr__( 'Video', 'jnews' ),
				),
			),
		);

		$general_setting['fields'] = array_merge( array( $post_format_field ), $general_setting['fields'] );

		$general_setting['fields'] = apply_filters( 'jnews_add_post_general_metabox_field', $general_setting['fields'] );

		array_unshift( $options['fields'], $general_setting );

		return $options;
	}

	/**
	 * Method primary_category
	 *
	 * @return array
	 */
	public function primary_category_meta() {
		$post_id          = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';
		$primary_category = jnews_get_metabox_value( 'jnews_primary_category.id', null, $post_id );
		$cat              = get_term( $primary_category );
		if ( empty( $primary_category ) && ! isset( $cat ) ) {
			$primary_category = false;
		}

		return array(
			'id'         => 'jnews_primary_category',
			'post_types' => array( 'post' ),
			'label'      => esc_html__( 'Primary Category', 'jnews' ),
			'priority'   => 'high',
			'context'    => 'side',
			'icon'       => 'LabelSvg',
			'fields'     => array(
				array(
					'type'        => 'jnews-select-search',
					'id'          => 'id',
					'default'     => $primary_category ? $primary_category : '',
					'label'       => esc_html__( 'Primary Category', 'jnews' ),
					'description' => wp_kses( __( 'You can search the post category by <strong>inputting the category name</strong>, clicking search result, and you will have your post category.<br>Primary category will show as your <strong>breadcrumb</strong> category on single Blog Post. <br/> Other <strong>page that require single category</strong> to show, this category will be used.', 'jnews' ), wp_kses_allowed_html() ),
					'isMulti'     => false,
					'onSearch'    => 'searchCategoriesByTitleAndId',
				),
				array(
					'type'        => 'jnews-select-search',
					'id'          => 'hide',
					'label'       => esc_html__( 'Hide Category', 'jnews' ),
					'description' => wp_kses( __( 'You can search the post category by <strong>inputting the category name</strong>, clicking search result, and you will have your post category.<br>Hide category will not show your category on single Blog Post.', 'jnews' ), wp_kses_allowed_html() ),
					'multiple'    => PHP_INT_MAX,
					'isMulti'     => false,
					'onSearch'    => 'searchCategoriesByTitleAndId',
				),
			),
		);
	}
}
