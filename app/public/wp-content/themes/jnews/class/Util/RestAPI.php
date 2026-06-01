<?php
/**
 * Rest API
 *
 * @author : Jegtheme
 * @package jnews
 */

namespace JNews\Util;

use JNews\Dashboard\AdminDashboard;
use JNews\Dashboard\SystemDashboard;
use JNews\Util\Api\Importer;
use JNews\Util\Api\Plugin;
use JNews\MetaboxBuilder;

/**
 * Rest API
 */
class RestAPI {

	/**
	 * Endpoint Path
	 *
	 * @var string
	 */
	const ENDPOINT = 'jnews/v1';

	/**
	 * Class instance
	 *
	 * @var Api
	 */
	private static $instance;

	/**
	 * Return class instance
	 *
	 * @return Api
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Class constructor
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'prepare_assets' ) );
	}

	/**
	 * Check plugin remote
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return \WP_Error|array
	 */
	public function check_plugin_remote( $request ) {
		$slug   = $request->get_param( 'slug' );
		$source = $request->get_param( 'source' );
		$nonce  = sanitize_key( $request->get_param( 'nonce' ) );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		$result = '';
		if ( ! empty( $source ) ) {
			if ( file_exists( JNEWS_THEME_DIR_PLUGIN . $source ) ) {
				$result = 'bundle';
			} else {
				$result = 'server';
			}
		} elseif ( is_wp_error( Plugin::retrieve_plugin_source( $slug ) ) ) {
				$result = 'server';
		} else {
			$result = 'remote';
		}

		return $this->response_success( $result );
	}

	/**
	 * Export Panel Options
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function export_panel_options( $request ) {
		$action      = sanitize_key( $request->get_param( 'action' ) );
		$nonce       = sanitize_key( $request->get_param( 'nonce' ) );
		$panel_nonce = sanitize_key( $request->get_param( 'panelNonce' ) );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) || empty( $panel_nonce ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		return $this->response_success( apply_filters( 'jnews_panel_request_export_option', $action, $panel_nonce ) );
	}

	/**
	 * Method to search the posts by title or ID
	 *
	 * @param \WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function search_posts_by_title_and_id( $request ) {
		if ( ! wp_verify_nonce( sanitize_text_field( $request->get_param( 'nonce' ) ), 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		$search    = sanitize_text_field( $request->get_param( 'search' ) );
		$type      = sanitize_text_field( $request->get_param( 'type' ) );
		$include   = sanitize_text_field( $request->get_param( 'include' ) );
		$post_type = sanitize_text_field( $request->get_param( 'post_type' ) );
		$exclude   = $request->get_param( 'exclude' );
		global $wpdb;
		$posts_data = array();
		if ( 'search' === $type ) {
			if ( ! empty( $exclude ) ) {
				$exclude_ids_placeholder = implode( ',', array_fill( 0, count( $exclude ), '%d' ) );
				$query                   = $wpdb->prepare(
					"
				SELECT ID, post_title
				FROM $wpdb->posts
				WHERE
				post_type = %s
				AND post_status = %s
				AND
				(ID = %d OR post_title LIKE %s)
				AND ID NOT IN ($exclude_ids_placeholder)
				ORDER BY 
				CASE WHEN ID = %d THEN 0 ELSE 1 END, ID DESC
				LIMIT %d;
				",
					array_merge(
						array( $post_type, 'publish', $search, '%' . $wpdb->esc_like( $search ) . '%' ),
						$exclude,
						array( (int) $search, 10 )
					)
				);
			} else {
				$query = $wpdb->prepare(
					"
					SELECT ID, post_title
					FROM $wpdb->posts
					WHERE
					post_type = %s
					AND post_status = %s
					AND
					(ID = %d OR post_title LIKE %s)
					ORDER BY 
					CASE WHEN ID = %d THEN 0 ELSE 1 END, ID DESC
					LIMIT %d;
					",
					$post_type,
					'publish',
					(int) $search,
					'%' . $wpdb->esc_like( $search ) . '%',
					(int) $search,
					10,
				);
			}
		} elseif ( ! empty( $include ) ) {
			$includes                = array_filter( explode( ',', $include ), 'is_numeric' );
			$include_ids_placeholder = implode( ',', array_fill( 0, count( $includes ), '%d' ) );
			$query                   = $wpdb->prepare(
				"
				SELECT ID, post_title
				FROM {$wpdb->posts}
				WHERE ID IN ($include_ids_placeholder);
				",
				$includes
			);
		}
		if ( $query ) {
			$results = $wpdb->get_results( $query );

			if ( ! empty( $results ) ) {
				foreach ( $results as $post ) {
					$posts_data[] = array(
						'value' => $post->ID,
						'label' => $post->post_title,
					);
				}
			}
		}

		return $this->response_success( $posts_data );
	}

		/**
		 * Method to search available image size
		 *
		 * @param \WP_REST_Request $request request.
		 *
		 * @return \WP_REST_Response|array
		 */
	public function search_image_lists( $request ) {
		if ( ! wp_verify_nonce( sanitize_text_field( $request->get_param( 'nonce' ) ), 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		$include = sanitize_text_field( $request->get_param( 'include' ) );

		$size_lists = array(
			array(
				'value' => 'default',
				'label' => esc_attr__( 'Default', 'jnews' ),
			),
			array(
				'value' => 'full',
				'label' => esc_attr__( 'Original Image', 'jnews' ),
			),
		);
		foreach ( wp_get_registered_image_subsizes()  as $key => $image_size ) {
			$size_lists[] = array(
				'value' => $key,
				'label' => $key,
			);
		}
		$search = sanitize_text_field( $request->get_param( 'search' ) );
		$type   = sanitize_text_field( $request->get_param( 'type' ) );
		if ( 'search' === $type && ! empty( $search ) ) {
			$size_lists = array_filter(
				$size_lists,
				function ( $val ) use ( $search ) {
					return ( false !== strpos( $val['value'], $search ) );
				}
			);
		} elseif ( ! empty( $include ) ) {
			$size_lists = array_filter(
				$size_lists,
				function ( $val ) use ( $include ) {
					return ( $val['value'] == $include );
				}
			);
		}

		return $this->response_success( array_values( $size_lists ) );
	}

	/**
	 * Method to search the Users by display_name or ID
	 *
	 * @param \WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function search_authors_by_name_and_id( $request ) {
		if ( ! wp_verify_nonce( sanitize_text_field( $request->get_param( 'nonce' ) ), 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}

		$search  = sanitize_text_field( $request->get_param( 'search' ) );
		$type    = sanitize_text_field( $request->get_param( 'type' ) );
		$include = sanitize_text_field( $request->get_param( 'include' ) );
		$exclude = $request->get_param( 'exclude' );
		global $wpdb;
		$users_data = array();
		if ( 'search' === $type ) {
			if ( ! empty( $exclude ) ) {
				$exclude_ids_placeholder = implode( ',', array_fill( 0, count( $exclude ), '%d' ) );
				$query                   = $wpdb->prepare(
					"
				SELECT ID, display_name
				FROM $wpdb->users
				WHERE ID = %d 
				OR display_name LIKE %s
				AND ID NOT IN ($exclude_ids_placeholder)
				ORDER BY 
				CASE WHEN ID = %d THEN 0 ELSE 1 END, ID DESC
				LIMIT %d;
				",
					array_merge(
						array( (int) $search, '%' . $wpdb->esc_like( $search ) . '%' ),
						$exclude,
						array( (int) $search, 10 )
					)
				);
			} else {
				$query = $wpdb->prepare(
					"
					SELECT ID, display_name
					FROM $wpdb->users
					WHERE ID = %d 
					OR display_name LIKE %s
					ORDER BY 
					CASE WHEN ID = %d THEN 0 ELSE 1 END, ID DESC
					LIMIT %d;
					",
					(int) $search,
					'%' . $wpdb->esc_like( $search ) . '%',
					(int) $search,
					10,
				);
			}
		} elseif ( ! empty( $include ) ) {
			$includes                = array_filter( explode( ',', $include ), 'is_numeric' );
			$include_ids_placeholder = implode( ',', array_fill( 0, count( $includes ), '%d' ) );
			$query                   = $wpdb->prepare(
				"
				SELECT ID, display_name
				FROM {$wpdb->users}
				WHERE ID IN ($include_ids_placeholder);
				",
				$includes
			);
		}
		if ( $query ) {
			$results = $wpdb->get_results( $query );
			if ( ! empty( $results ) ) {
				foreach ( $results as $author ) {
					$users_data[] = array(
						'value' => $author->ID,
						'label' => $author->display_name,
					);
				}
			}
		}

		return $this->response_success( $users_data );
	}

	/**
	 * Method to search the Taxonomy by Name or Id
	 *
	 * @param \WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function search_taxonomies_by_title_and_id( $request ) {
		if ( ! wp_verify_nonce( sanitize_text_field( $request->get_param( 'nonce' ) ), 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}

		$type     = sanitize_text_field( $request->get_param( 'type' ) );
		$search   = sanitize_text_field( $request->get_param( 'search' ) );
		$include  = sanitize_text_field( $request->get_param( 'include' ) );
		$taxonomy = sanitize_text_field( $request->get_param( 'taxonomy' ) );
		$exclude  = $request->get_param( 'exclude' );

		global $wpdb;
		$categories_data = array();
		if ( 'search' === $type ) {
			if ( ! empty( $exclude ) ) {
				$exclude_ids_placeholder = implode( ',', array_fill( 0, count( $exclude ), '%d' ) );

				$query = $wpdb->prepare(
					"
					SELECT cat.term_id, cat.name
					FROM $wpdb->terms as cat
					INNER JOIN $wpdb->term_taxonomy as tax ON cat.term_id = tax.term_id
					WHERE tax.taxonomy = %s
					AND (cat.term_id = %d OR cat.name LIKE %s)
					AND cat.term_id NOT IN ($exclude_ids_placeholder)
					ORDER BY 
					CASE WHEN cat.term_id = %d THEN 0 ELSE 1 END, cat.term_id ASC
					LIMIT %d;
					",
					array_merge(
						array( $taxonomy, (int) $search, '%' . $wpdb->esc_like( $search ) . '%' ),
						$exclude,
						array( (int) $search, 10 ) // Prioritas ID dan batas jumlah hasil
					)
				);
			} else {
				$query = $wpdb->prepare(
					"
					SELECT cat.term_id, cat.name
					FROM {$wpdb->terms} AS cat
					INNER JOIN {$wpdb->term_taxonomy} AS tax ON cat.term_id = tax.term_id
					WHERE tax.taxonomy = %s
					AND (cat.term_id = %d OR cat.name LIKE %s)
					ORDER BY 
					CASE WHEN cat.term_id = %d THEN 0 ELSE 1 END, cat.term_id ASC
					LIMIT %d;
					",
					$taxonomy,
					(int) $search,
					'%' . $wpdb->esc_like( $search ) . '%',
					(int) $search,
					10
				);
			}
		} elseif ( ! empty( $include ) ) {
				$includes                = array_filter( explode( ',', $include ), 'is_numeric' );
				$include_ids_placeholder = implode( ',', array_fill( 0, count( $includes ), '%d' ) );
				$query                   = $wpdb->prepare(
					"
					SELECT term_id, name
					FROM {$wpdb->terms}
					WHERE term_id IN ($include_ids_placeholder);
					",
					$includes
				);
		}

		if ( $query ) {
			$results = $wpdb->get_results( $query );

			if ( ! empty( $results ) ) {
				foreach ( $results as $term ) {
					$categories_data[] = array(
						'value' => $term->term_id,
						'label' => $term->name,
					);
				}
			}
		}

		return $this->response_success( $categories_data );
	}

	/**
	 * Method update_post_meta
	 *
	 * @param \WP_REST_Request $request request.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function update_post_meta( $request ) {
		$post_id = sanitize_key( $request->get_param( 'id' ) );

		if ( ! wp_verify_nonce( sanitize_text_field( $request->get_param( 'nonce' ) ), 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}

		$post_type      = get_post_type( $post_id );
		$meta_data      = json_decode( wp_unslash( $request->get_param( 'meta' ) ), true );
		$sanitized_meta = array();
		$metaboxes      = MetaboxBuilder::get_meta();

		foreach ( $metaboxes as $key => $metabox ) {
			if ( isset( $metabox['mode'] ) ) {
				foreach ( $metabox['fields'] as $meta ) {
					foreach ( $meta['fields'] as $field ) {
						if ( isset( $meta_data[ $field['id'] ] ) ) {
							$sanitized_meta[ $field['id'] ] = sanitize_meta( $field['id'], $meta_data[ $field['id'] ], $post_type );
							MetaboxBuilder::save_meta( $post_id, $field['id'], $meta_data[ $field['id'] ] );
						}
					}
				}
			} elseif ( isset( $meta_data[ $key ] ) ) {
				$sanitized_meta[ $key ] = sanitize_meta( $key, $meta_data[ $key ], $post_type );
				MetaboxBuilder::save_meta( $post_id, $key, $meta_data[ $key ] );
			}
		}

		MetaboxBuilder::update_custom_meta( $post_id, $sanitized_meta );

		return $this->response_success( 'success' );
	}

	/**
	 * Method update_metabox_notice
	 *
	 * @param \WP_REST_Request $request request.
	 *
	 * @return  \WP_REST_Response|array
	 */
	public function update_metabox_notice( $request ) {
		if ( ! wp_verify_nonce( sanitize_text_field( $request->get_param( 'nonce' ) ), 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		update_option( 'jnews_show_metabox_notice', 'hide' );
		return $this->response_success( 'success to hide the JNews metabox notice' );
	}

	/**
	 * Get Dashboard Config
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return array
	 */
	public function get_dashboard_config( $request ) {
		$config = sanitize_key( $request->get_param( 'config' ) );
		$nonce  = sanitize_key( $request->get_param( 'nonce' ) );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) || empty( $config ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		if ( 'system' === $config ) {
			$system = SystemDashboard::get_instance();
			return $this->response_success( $system->jnews_dashboard_config() );
		} elseif ( 'all' === $config ) {
			return $this->response_success( AdminDashboard::jnews_dashboard() );
		}
	}

	/**
	 * Get Validate Notice Length
	 *
	 * @return int
	 */
	public function get_validate_notice_length() {
		return \JNews\Util\ValidateLicense::check_validate_notice_length();
	}

	/**
	 * Get Plugins
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return array
	 */
	public function get_plugins( $request ) {
		$plugins       = Plugin::get_plugin_list();
		$plugin_groups = Plugin::get_plugin_group();

		return $this->response_success(
			array(
				'plugins' => $plugins,
				'groups'  => $plugin_groups,
			)
		);
	}

	/**
	 * Import Panel Options
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function import_panel_options( $request ) {
		$action      = sanitize_key( $request->get_param( 'action' ) );
		$nonce       = sanitize_key( $request->get_param( 'nonce' ) );
		$panel_nonce = sanitize_key( $request->get_param( 'panelNonce' ) );
		$options     = $request->get_param( 'options' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) || empty( $panel_nonce ) || empty( $options ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		return $this->response_success( apply_filters( 'jnews_panel_request_import_option', $options, $action, $panel_nonce ) );
	}

		/**
		 * Manage give plugin
		 *
		 * @param \WP_REST_Request $request Core class used to implement a REST request object.
		 *
		 * @return \WP_Error|array
		 */
	public function manage_plugin( $request ) {
		$from          = sanitize_key( $request->get_param( 'from' ) );
		$nonce         = sanitize_key( $request->get_param( 'nonce' ) );
		$doing         = sanitize_key( $request->get_param( 'doing' ) );
		$plugin        = $request->get_param( 'plugin' );
		$plugin_source = isset( $plugin['source'] ) ? $plugin['source'] : false;
		$plugin        = array_map( 'sanitize_text_field', $plugin );
		if ( $plugin_source ) {
			$plugin['source'] = $plugin_source;
		}
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		if ( isset( $plugin['refresh'] ) && '1' === $plugin['refresh'] ) {
			$plugin['refresh'] = true;
		}
		return $this->response_success( Plugin::manage_plugin( $plugin, $this, $doing, $from ) );
	}

	/**
	 * Manage import demo
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return boolean|array
	 */
	public function manage_demo( $request ) {
		$id       = sanitize_text_field( $request->get_param( 'id' ) );
		$action   = sanitize_text_field( $request->get_param( 'action' ) );
		$step     = sanitize_text_field( $request->get_param( 'step' ) );
		$option   = $request->get_param( 'option' );
		$demo     = $request->get_param( 'demo' );
		$data     = $request->get_param( 'data' );
		$importer = new Importer( $id, $action, $step, $option, $data, $demo );
		$result   = $importer->prepare_import();

		return $this->response_success( $result ? $result : true );
	}

	/**
	 * Check user permissions
	 *
	 * @return boolean
	 */
	public function permission_install_plugin() {
		return current_user_can( 'install_plugins' );
	}


	/**
	 * Prepare assets.
	 */
	public function prepare_assets() {
		$theme = wp_get_theme();
		wp_register_script( 'jnews-hash', apply_filters( 'jnews_get_asset_uri', get_parent_theme_file_uri( 'assets/' ) ) . 'js/admin/jnews.hash.min.js', array(), $theme->get( 'Version' ) );
		wp_register_script( 'jnews-essential-local', apply_filters( 'jnews_get_asset_uri', get_parent_theme_file_uri( 'assets/' ) ) . 'js/admin/jnews-essential.local.js', array( 'jquery', 'jnews-hash', 'wp-api-fetch', 'wp-util' ), $theme->get( 'Version' ) );
		$ls_data             = jnews_get_license();
		$home_url            = home_url();
		$jnews_dashboard_url = menu_page_url( 'jnews', false );
		$callback            = str_replace( $home_url, '', $jnews_dashboard_url );
		$domain              = jnews_get_domain( $home_url );
		$server_url          = add_query_arg(
			array(
				'siteurl'  => $home_url,
				'callback' => $callback,
				'item_id'  => JNEWS_THEME_ID,
			),
			JEGTHEME_SERVER . '/activate/'
		);

		$ls_var = array(
			'domain'     => $domain,
			'url'        => get_site_url(),
			'restUrl'    => get_rest_url(),
			'api'        => JEGTHEME_SERVER,
			'activation' => $server_url,
			'nonce'      => wp_create_nonce( 'wp_rest' ),
		);

		if ( $ls_data && isset( $ls_data['purchase_code'] ) ) {
			$ls_var['license'] = $ls_data['purchase_code'];
		}

		wp_localize_script(
			'jnews-essential-local',
			'jnewsEssential',
			$ls_var
		);
	}

	/**
	 * Check permission manage options
	 *
	 * @return bool
	 */
	public function permission_edit_posts() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check permission manage options
	 *
	 * @return bool
	 */
	public function permission_manage_options() {
		return function_exists( 'jnews_permission_manage_options' ) ? jnews_permission_manage_options() : current_user_can( 'manage_options' );
	}

	/**
	 * Register API
	 *
	 * @return void
	 */
	public function register_routes() {

		// Search available image size.
		register_rest_route(
			self::ENDPOINT,
			'searchImageLists',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'search_image_lists' ),
				'permission_callback' => array( $this, 'permission_edit_posts' ),
			)
		);

		// Search posts by title and id.
		register_rest_route(
			self::ENDPOINT,
			'searchPostsByTitleAndId',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'search_posts_by_title_and_id' ),
				'permission_callback' => array( $this, 'permission_edit_posts' ),
			)
		);

		// Search Taxonomies by title and id.
		register_rest_route(
			self::ENDPOINT,
			'searchTaxonomiesByTitleAndId',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'search_taxonomies_by_title_and_id' ),
				'permission_callback' => array( $this, 'permission_edit_posts' ),
			)
		);

		// Search authors by name and id.
		register_rest_route(
			self::ENDPOINT,
			'searchAuthorsByNameAndId',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'search_authors_by_name_and_id' ),
				'permission_callback' => array( $this, 'permission_edit_posts' ),
			)
		);

		// Meta.
		register_rest_route(
			self::ENDPOINT,
			'updatePostMeta',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_post_meta' ),
				'permission_callback' => array( $this, 'permission_edit_posts' ),
			)
		);

		// Meta.
		register_rest_route(
			self::ENDPOINT,
			'updateMetaboxNotice',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_metabox_notice' ),
				'permission_callback' => array( $this, 'permission_edit_posts' ),
			)
		);

		// Config.
		register_rest_route(
			self::ENDPOINT,
			'getDashboardConfig',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_dashboard_config' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);

		// Panel.
		register_rest_route(
			self::ENDPOINT,
			'savePanelOptions',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'save_panel_options' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);
		register_rest_route(
			self::ENDPOINT,
			'restorePanelOptions',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'restore_panel_options' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);
		register_rest_route(
			self::ENDPOINT,
			'importPanelOptions',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'import_panel_options' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);
		register_rest_route(
			self::ENDPOINT,
			'exportPanelOptions',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'export_panel_options' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);

		// Plugin.
		register_rest_route(
			self::ENDPOINT,
			'getPlugins',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_plugins' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			self::ENDPOINT,
			'validatePlugin',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'validate_plugin' ),
				'permission_callback' => array( $this, 'permission_install_plugin' ),
			)
		);
		register_rest_route(
			self::ENDPOINT,
			'checkPluginRemote',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'check_plugin_remote' ),
				'permission_callback' => array( $this, 'permission_install_plugin' ),
			)
		);
		register_rest_route(
			self::ENDPOINT,
			'managePlugin',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'manage_plugin' ),
				'permission_callback' => array( $this, 'permission_install_plugin' ),
			)
		);

		// Lincese.
		register_rest_route(
			self::ENDPOINT,
			'resetLicense',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'reset_license' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);

		register_rest_route(
			self::ENDPOINT,
			'getValidateNoticeLength',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_validate_notice_length' ),
				'permission_callback' => array( $this, 'permission_edit_posts' ),
			)
		);

		// Import Demo.
		register_rest_route(
			self::ENDPOINT,
			'manageDemo',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'manage_demo' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);

		// Customizer Setting.
		register_rest_route(
			self::ENDPOINT,
			'manageCustomizer',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'save_customizer_options' ),
				'permission_callback' => array( $this, 'permission_manage_options' ),
			)
		);

		// Newsletter Subscribe
		register_rest_route(
			self::ENDPOINT,
			'newsletterSubscribe',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'newsletter_subscribe_handler' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Newsletter Subscribre Handler
	 *
	 * @param \WP_REST_Request $request The Request.
	 */
	public function newsletter_subscribe_handler( $request ) {
		$data     = json_decode( $request->get_body(), true );
		$response = array(
			'status'  => false,
			'message' => esc_html__( 'Bad Request', 'jnews' ),
		);

		if ( isset( $data['email'] ) && isset( $data['site'] ) ) {
			add_filter( 'http_request_host_is_external', '__return_true' );
			if ( is_email( $data['email'] ) && wp_http_validate_url( $data['site'] ) ) {
				$save_request = wp_remote_request(
					JNEWS_THEME_SERVER . '/wp-json/jnews-server/v1/newsletterSubscribe',
					array(
						'method' => 'POST',
						'body'   => array(
							'email' => sanitize_email( $data['email'] ),
							'site'  => esc_url_raw( $data['site'] ),
						),
					)
				);

				$save_response = json_decode( wp_remote_retrieve_body( $save_request ), true );

				if ( ! $save_response['status'] ) {
					$response = array(
						'status'  => false,
						'message' => $save_response['message'] ? $save_response['message'] : esc_html__( 'The email has been subscribed.', 'jnews' ),
					);
				} else {
					$response = array(
						'status'  => true,
						'message' => esc_html__( 'Thank you for subscribing.', 'jnews' ),
					);
				}
			}
			add_filter( 'http_request_host_is_external', '__return_false' );
		}

		return wp_send_json( $response );
	}

	/**
	 * Save Customizer Options
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function save_customizer_options( $request ) {
		if ( ! class_exists( 'WP_Customize_Manager' ) ) {
			require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		}

		$wp_customize = new \WP_Customize_Manager();  /* see RI9E9CPY */

		$nonce   = sanitize_key( $request->get_param( 'nonce' ) );
		$options = $request->get_param( 'options' );

		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) || empty( $options ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}

		// Call the customize_save action.
		do_action( 'customize_save', $wp_customize );

		foreach ( $options as $key => $val ) {
			// Call the customize_save_ dynamic action.
			do_action( 'customize_save_' . $key, $wp_customize );

			// Save the mod.
			if ( $key === 'jnews_additional_css' ) {
				wp_update_custom_css_post( $val );
			} elseif ( strpos( $key, 'jnews_option' ) !== false && strpos( $key, '[' ) !== false && strpos( $key, ']' ) !== false ) {
				$keys = explode( '[', str_replace( ']', '', $key ) );
				if ( $keys[0] === 'jnews_option' ) {
					$value = $this->update_option_plugin( $key, $val );
					jnews_update_option( $keys[1], $value );
				}
			} else {
				set_theme_mod( $key, $val );
			}
		}

		// Call the customize_save_after action.
		do_action( 'customize_save_after', $wp_customize );

		return $this->response_success( esc_html__( 'Saved.', 'jnews' ) );
	}

	/**
	 * Modify Jnews Options
	 *
	 * @param \string $key Core class used to implement a REST request object.
	 *
	 * @param \mixed  $value Core class used to implement a REST request object.
	 *
	 * @return mixed
	 */
	public function update_option_plugin( $key, $value ) {
		$options = get_option( 'jnews_option', array() );
		$keys    = explode( '[', str_replace( ']', '', $key ) );

		if ( $keys[0] === 'jnews_option' ) {
			array_shift( $keys );
		}

		$current = &$options;

		foreach ( $keys as $k ) {

			if ( ! isset( $current[ $k ] ) ) {
				$current[ $k ] = array();
			}

			$current = &$current[ $k ];
		}

		$current = $value;

		return $options[ $keys[0] ];
	}

	/**
	 * Restore Panel Options
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function restore_panel_options( $request ) {
		$action      = sanitize_key( $request->get_param( 'action' ) );
		$nonce       = sanitize_key( $request->get_param( 'nonce' ) );
		$panel_nonce = sanitize_key( $request->get_param( 'panelNonce' ) );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) || empty( $panel_nonce ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		return $this->response_success( apply_filters( 'jnews_panel_request_restore', $action, $panel_nonce ) );
	}

	/**
	 * Reset license handler
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 */
	public function reset_license( $request ) {
		$purchase_code = $request->get_param( 'code' );
		if ( ! empty( $purchase_code ) ) {
			jnews_reset_license();
		}
	}

	/**
	 * Return error response
	 *
	 * @param string $message Error message.
	 *
	 * @return \WP_REST_Response
	 */
	public function response_error( $message ) {
		return new \WP_REST_Response(
			array(
				'message' => $message,
			),
			500
		);
	}

	/**
	 * Return success response
	 *
	 * @param array $args args.
	 *
	 * @return \WP_REST_Response
	 */
	public function response_success( $args ) {
		return new \WP_REST_Response( $args, 200 );
	}

	/**
	 * Save Panel Options
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return \WP_REST_Response|array
	 */
	public function save_panel_options( $request ) {
		$action      = sanitize_key( $request->get_param( 'action' ) );
		$nonce       = sanitize_key( $request->get_param( 'nonce' ) );
		$panel_nonce = sanitize_key( $request->get_param( 'panelNonce' ) );
		$options     = $request->get_param( 'options' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) || empty( $panel_nonce ) || empty( $options ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		return $this->response_success( apply_filters( 'jnews_panel_request_save', $options, $action, $panel_nonce ) );
	}

	/**
	 * Validate plugin
	 *
	 * @param \WP_REST_Request $request Core class used to implement a REST request object.
	 *
	 * @return \WP_Error|array
	 */
	public function validate_plugin( $request ) {
		$plugins = $request->get_param( 'plugins' );
		$nonce   = sanitize_key( $request->get_param( 'nonce' ) );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest', false ) ) {
			return $this->response_error( esc_html__( 'You are not allowed to perform this action.', 'jnews' ) );
		}
		return $this->response_success( Plugin::validate_plugin( $plugins ) );
	}
}
