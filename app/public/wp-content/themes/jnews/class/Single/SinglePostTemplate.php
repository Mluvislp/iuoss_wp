<?php
/**
 * Single Post Template
 *
 * @author : Jegtheme
 * @package jnews
 */

namespace JNews\Single;

/**
 * Class Theme SinglePostTemplate
 */
class SinglePostTemplate {
	/**
	 * Single Post Template
	 *
	 * @var SinglePostTemplate
	 */
	private static $instance;

	/**
	 * Single Post
	 *
	 * @var SinglePost
	 */
	private $single_post;

	/**
	 * Instance
	 *
	 * @return SinglePostTemplate
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
		add_action( 'init', array( $this, 'single_template_post_type' ) );

		if ( is_admin() ) {
			add_filter( 'vc_get_all_templates', array( $this, 'post_template' ) );
			add_filter( 'vc_templates_render_category', array( $this, 'post_template_render' ) );
			add_filter( 'vc_templates_render_backend_template', array( $this, 'ajax_template_backend' ), null, 2 );
		} else {
			$this->single_post = SinglePost::getInstance();

			add_action( 'wp_head', array( $this, 'custom_post_css' ), 999 );
			add_action( 'init', array( $this, 'force_load_css' ), 1 );
			add_filter( 'vc_templates_render_frontend_template', array( $this, 'ajax_template_frontend' ), null, 2 );

			add_action(
				'wp_enqueue_scripts',
				function () {
					wp_enqueue_style( 'js_composer_front' );
					wp_enqueue_style( 'elementor-frontend' );
				}
			);
		}

		add_filter( 'post_row_actions', array( $this, 'single_row_action' ), null, 2 );
	}

	/**
	 * Method ajax_template_frontend
	 *
	 * @param int    $template_id $template_id.
	 * @param string $template_type $template_type.
	 *
	 * @return int
	 */
	public function ajax_template_frontend( $template_id, $template_type ) {
		if ( 'post_template' === $template_type ) {
			$saved_templates = $this->get_template( $template_id );
			vc_frontend_editor()->setTemplateContent( $saved_templates );
			vc_frontend_editor()->enqueueRequired();
			vc_include_template(
				'editors/frontend_template.tpl.php',
				array(
					'editor' => vc_frontend_editor(),
				)
			);
			die();
		}

		return $template_id;
	}

	/**
	 * Method add_page_custom_css
	 *
	 * @param int $post_id $post_id.
	 *
	 * @return void
	 */
	public function add_page_custom_css( $post_id ) {
		$post_custom_css = get_post_meta( $post_id, '_wpb_post_custom_css', true );

		if ( ! empty( $post_custom_css ) ) {
			$post_custom_css = strip_tags( $post_custom_css );
			echo '<style type="text/css" data-type="vc_custom-css">';
			echo jnews_sanitize_by_pass( $post_custom_css );
			echo '</style>';
		}
	}

	/**
	 * Method ajax_template_backend
	 *
	 * @param int    $template_id $template_id.
	 * @param string $template_type $template_type.
	 *
	 * @return int|string
	 */
	public function ajax_template_backend( $template_id, $template_type ) {
		if ( 'post_template' === $template_type ) {
			$content = $this->get_template( $template_id );

			return $content;
		}

		return $template_id;
	}

	/**
	 * Method custom_post_css
	 *
	 * @return void
	 */
	public function custom_post_css() {
		if ( $this->is_single_custom() ) {
			$custom_page_id = $this->get_custom_page_id();

			$this->add_page_custom_css( $custom_page_id );
			$this->get_shortcode_custom_css( $custom_page_id );
		}
	}

	/**
	 * Method force_load_css
	 *
	 * @return void
	 */
	public function force_load_css() {
		if ( $this->is_single_custom() ) {
			add_filter( 'jnews_vc_force_load_style', '__return_true' );
		}
	}

	/**
	 * Method get_template
	 *
	 * @param int $template_id $template_id.
	 *
	 * @return string
	 */
	public function get_template( $template_id ) {
		ob_start();
		include 'template/' . $template_id . '.txt';

		return ob_get_clean();
	}

	/**
	 * Method get_custom_page_id
	 *
	 * @return string
	 */
	public function get_custom_page_id() {
		return $this->single_post->get_custom_template();
	}

	/**
	 * Method get_shortcode_custom_css
	 *
	 * @param int $post_id $post_id.
	 *
	 * @return void
	 */
	public function get_shortcode_custom_css( $post_id ) {
		$shortcodes_custom_css = get_post_meta( $post_id, '_wpb_shortcodes_custom_css', true );

		if ( ! empty( $shortcodes_custom_css ) ) {
			$shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
			echo '<style type="text/css" data-type="vc_shortcodes-custom-css">';
			echo jnews_sanitize_by_pass( $shortcodes_custom_css );
			echo '</style>';
		}
	}


	/**
	 * Method is_single_custom
	 *
	 * @return boolean
	 */
	public function is_single_custom() {
		return 'custom' === $this->single_post->get_template();
	}

	/**
	 * Method is_user_role_excluded
	 *
	 * @param int   $user_id $user_id.
	 * @param array $option $option.
	 *
	 * @return boolean
	 */
	public function is_user_role_excluded( $user_id, $option ) {
		$user = get_user_by( 'id', $user_id );

		if ( empty( $user ) || ! $option ) {
			return false;
		}

		$roles = (array) $user->roles;

		if ( ! empty( $roles ) ) {
			foreach ( $roles as $role ) {
				if ( in_array( $role, $option, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Method library
	 *
	 * @return array
	 */
	public function library() {
		$template = array();

		for ( $i = 1; $i <= 3; $i++ ) {
			$data               = array();
			$data['name']       = 'Post Template ' . $i;
			$data['unique_id']  = 'post_template_' . $i;
			$data['image_path'] = get_template_directory_uri() . '/assets/img/admin/post_template/post_template_' . $i . '.jpg';
			$data['type']       = 'post_template';

			$template[] = $data;
		}

		return $template;
	}

	/**
	 * Method post_template_render
	 *
	 * @param array $category $category.
	 *
	 * @return array
	 */
	public function post_template_render( $category ) {

		if ( 'post_template' === $category['category'] ) {
			$category['output']  = '';
			$category['output'] .= '
            <div class="vc_post_template">
                <div class="vc_column vc_col-sm-12">
                    <div class="vc_ui-template-list vc_templates-list-my_templates vc_ui-list-bar">';

			if ( ! empty( $category['templates'] ) ) {
				$arrays = array_chunk( $category['templates'], 3 );

				foreach ( $arrays as $templates ) {
					$category['output'] .= '<div class="vc_row">';
					foreach ( $templates as $template ) {
						$category['output'] .= $this->render_item_list( $template );
					}
					$category['output'] .= '</div>';
				}
			}

			$category['output'] .= '
				    </div>
			    </div>
			</div>';
		}

		return $category;
	}

	/**
	 * Method post_template
	 *
	 * @param array $data $data.
	 *
	 * @return array
	 */
	public function post_template( $data ) {
		if ( 'custom-post-template' === get_post_type() ) {
			$data[] = array(
				'category'             => 'post_template',
				'category_name'        => esc_html__( 'Post Template', 'jnews' ),
				'category_description' => esc_html__( 'Post Template for JNews', 'jnews' ),
				'category_weight'      => 9,
				'templates'            => $this->library(),
			);
		}

		return $data;
	}

	/**
	 * Method render_item_list
	 *
	 * @param array $template $template.
	 *
	 * @return string
	 */
	public function render_item_list( $template ) {
		$name                = isset( $template['name'] ) ? esc_html( $template['name'] ) : esc_html__( 'No title', 'jnews' );
		$template_id         = esc_attr( $template['unique_id'] );
		$template_id_hash    = md5( $template_id ); // needed for jquery target for TTA.
		$template_name       = esc_html( $name );
		$template_name_lower = esc_attr( vc_slugify( $template_name ) );
		$template_type       = esc_attr( isset( $template['type'] ) ? $template['type'] : 'custom' );
		$custom_class        = esc_attr( isset( $template['custom_class'] ) ? $template['custom_class'] : '' );
		$column              = 12 / 3;

		$template_item = $this->render_single_item( $name, $template );

		$output = "<div class='vc_col-sm-{$column}'>
                        <div class='vc_ui-template vc_templates-template-type-{$template_type} {$custom_class}'
                            data-template_id='{$template_id}'
                            data-template_id_hash='{$template_id_hash}'
                            data-category='{$template_type}'
                            data-template_unique_id='{$template_id}'
                            data-template_name='{$template_name_lower}'
                            data-template_type='{$template_type}'
                            data-vc-content='.vc_ui-template-content'>
                            <div class='vc_ui-list-bar-item'>
                                {$template_item}        
                            </div>
                            <div class='vc_ui-template-content' data-js-content>
                            </div>
                        </div>
                    </div>";

		return $output;
	}

	/**
	 * Method render_single_item
	 *
	 * @param string $name $name.
	 * @param array  $data $data.
	 *
	 * @return string
	 */
	protected function render_single_item( $name, $data ) {
		$template_name  = esc_html( $name );
		$template_image = esc_attr( $data['image_path'] );

		return "<div class='jnews_template_vc_item' data-template-handler=''>
                    <img src='{$template_image}'/>
                    <div class='vc_ui-list-bar-item-trigger'>
			            <h3>{$template_name}</h3>
			        </div>
                </div>";
	}

	/**
	 * Method single_template_post_type
	 *
	 * @return void
	 */
	public function single_template_post_type() {
		if ( ( is_admin() || jeg_is_frontend_vc() || jeg_is_frontend_elementor() ) && ! ( get_theme_mod( 'jnews_dashboard_post_template_disable', false ) && $this->is_user_role_excluded( get_current_user_id(), get_theme_mod( 'jnews_dashboard_post_template_user_roles' ) ) ) ) {
			jnews_register_post_type(
				'custom-post-template',
				array(
					'labels'          =>
						array(
							'name'               => esc_html__( 'Post Template', 'jnews' ),
							'singular_name'      => esc_html__( 'Post Template', 'jnews' ),
							'menu_name'          => esc_html__( 'Post Template', 'jnews' ),
							'add_new'            => esc_html__( 'New Post Template', 'jnews' ),
							'add_new_item'       => esc_html__( 'Build Custom Post Template', 'jnews' ),
							'edit_item'          => esc_html__( 'Edit Post Template', 'jnews' ),
							'new_item'           => esc_html__( 'New Custom Post Template Entry', 'jnews' ),
							'view_item'          => esc_html__( 'View Custom Post Template', 'jnews' ),
							'search_items'       => esc_html__( 'Search Custom Post Template', 'jnews' ),
							'not_found'          => esc_html__( 'No entry found', 'jnews' ),
							'not_found_in_trash' => esc_html__( 'No Custom Post Template in Trash', 'jnews' ),
							'parent_item_colon'  => '',
						),
					'description'     => esc_html__( 'Custom Single Post Template', 'jnews' ),
					'public'          => true,
					'show_in_rest'    => true,
					'show_ui'         => true,
					'menu_position'   => 6,
					'capability_type' => 'post',
					'hierarchical'    => false,
					'supports'        => array( 'title', 'editor' ),
					'map_meta_cap'    => true,
					'rewrite'         => array(
						'slug' => 'post-template',
					),
				)
			);
		}
	}

	/**
	 * Method single_row_action
	 *
	 * @param array  $actions $actions.
	 * @param object $post $post.
	 *
	 * @return array
	 */
	public function single_row_action( $actions, $post ) {
		if ( 'custom-post-template' === $post->post_type ) {
			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}
}
