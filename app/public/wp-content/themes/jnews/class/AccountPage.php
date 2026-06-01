<?php
/**
 * Account Page
 *
 * @author Jegtheme
 * @package jnews
 */

namespace JNews;

/**
 * Class JNews Account Page
 */
class AccountPage {

	/**
	 * Instance
	 *
	 * @var AccountPage
	 */
	private static $instance;

	/**
	 * Endpoint
	 *
	 * @var array
	 */
	private $endpoint;

	/**
	 * Current Page
	 *
	 *  @var string
	 */
	private $current_page;

	/**
	 * Method getInstance
	 *
	 * @return class
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
		$this->setup_hook();
	}

	/**
	 * Method setup_hook
	 *
	 * @return void
	 */
	protected function setup_hook() {
		if ( is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'users_own_attachments' ) );
			add_filter( 'ajax_query_attachments_args', array( $this, 'filter_user_media' ) );
			add_action( 'delete_attachment', array( $this, 'disable_delete_attachment' ), 10, 2 );
		} else {
			add_action( 'wp_loaded', array( $this, 'form_handler' ), 20 );
			add_action( 'template_include', array( $this, 'add_page_template' ) );
			add_action( 'jnews_account_right_content', array( $this, 'get_right_content' ) );
			add_action( 'jnews_account_right_title', array( $this, 'get_right_title' ) );
			add_filter( 'document_title_parts', array( $this, 'account_title' ) );
			add_filter( 'jnews_dropdown_link', array( $this, 'dropdown_link' ) );
		}
		add_action( 'after_setup_theme', array( $this, 'setup_endpoint' ), 11 ); /* see A0cq0obX */
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_action( 'init', array( $this, 'jnews_user_role_capabilitiies' ) );
		add_action( 'after_switch_theme', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'admin_init', array( $this, 'prevent_admin_access' ), 5 );
		add_filter( 'get_avatar', array( $this, 'user_avatar' ), 10, 6 );
		add_filter( 'upload_mimes', array( $this, 'filter_mime_types' ) );
		add_filter( 'upload_size_limit', array( $this, 'upload_size_limit' ) );
	}

	/**
	 * Method admin_post_action
	 *
	 * @return boolean
	 */
	public function admin_post_action() {
		if ( strpos( $_SERVER['REQUEST_URI'], 'admin-post.php' ) !== false ) {
			return false;
		}

		// see bHflfJs0 .
		return true;
	}

	/**
	 * Method add_rewrite_rule
	 *
	 * @return void
	 */
	public function add_rewrite_rule() {
		add_rewrite_endpoint( $this->endpoint['account']['slug'], EP_ROOT | EP_PAGES );
		add_rewrite_rule( '^' . $this->endpoint['account']['slug'] . '/page/?([0-9]{1,})/?$', 'index.php?&paged=$matches[1]&' . $this->endpoint['account']['slug'], 'top' );
	}

	/**
	 * Method add_page_template
	 *
	 * @params string $template $template.
	 *
	 * @return string
	 */
	public function add_page_template( $template ) {
		global $wp;

		if ( $this->is_account_page( $wp ) ) {
			$query_vars = explode( '/', $wp->query_vars[ $this->endpoint['account']['slug'] ] );
			if ( ! empty( $query_vars[0] ) ) {
				$this->setup_current_page( $query_vars[0] );
			} else {
				$this->default_redirect();
				exit;
			}
			if ( ! is_user_logged_in() ) {
				$template = locate_template( 'fragment/account/saved-posts.php', false, false );
			} else {
				$template = locate_template( 'fragment/account/account-page.php', false, false );

			}
		}

		return $template;
	}

	/**
	 * Method to redirect user to default account URL.
	 */
	private function default_redirect() {
		if ( is_user_logged_in() ) {
			wp_safe_redirect( esc_url( jnews_home_url_multilang( $this->endpoint['account']['slug'] . '/' . $this->endpoint['edit_account']['slug'] ) ) );

		} else {
			wp_safe_redirect( $this->saved_posts_enpoint() );
		}
	}

	/**
	 * Method account_title
	 *
	 * @param array $title $title.
	 *
	 * @return array
	 */
	public function account_title( $title ) {
		global $wp;
		$split      = $title;
		$additional = '';

		if ( $this->is_account_page( $wp ) ) {
			if ( isset( $this->current_page ) ) {
				$additional = jnews_return_translation( $this->endpoint[ $this->current_page ]['title'], 'jnews', $this->endpoint[ $this->current_page ]['label'] );
			} else {
				wp_safe_redirect( $this->saved_posts_enpoint() );
			}

			$additional = apply_filters( 'jnews_account_title', $additional, $wp, $this->endpoint );

			global $wp_query;
			$split['title'] = isset( $wp_query->queried_object->post_title );

			if ( ! empty( $additional ) ) {
				$title['title'] = $additional . ' ' . $split['title'];
			}
		}

		return $title;
	}

	/**
	 * Method disable_delete_attachment
	 *
	 * @return void
	 */
	public function disable_delete_attachment( $post_id, $post ) {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'delete_others_pages' ) && get_current_user_id() != $post->post_author ) {
			exit();
		}
	}


	/**
	 * Method dropdown_link
	 *
	 * @param array $dropdown $dropdown.
	 *
	 * @return array
	 */
	public function dropdown_link( $dropdown ) {
		if ( is_user_logged_in() ) {
			$item['account'] = array(
				'text' => jnews_return_translation( $this->endpoint['account']['title'], 'jnews', $this->endpoint['account']['label'] ),
				'url'  => esc_url( jnews_home_url_multilang( $this->endpoint['account']['slug'] ) ),
			);

			if ( isset( $item ) ) {
				$dropdown = array_merge( $item, $dropdown );
			}
		}

		return $dropdown;
	}

	/**
	 * Method do_reset_password
	 *
	 * @param object $user $user.
	 * @param string $new_pass $new_pass.
	 *
	 * @return void
	 */
	protected function do_reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		wp_password_change_notification( $user );
	}


	/**
	 * Method edit_account_handler
	 *
	 * @return void
	 */
	protected function edit_account_handler() {
		$user_id      = get_current_user_id();
		$first_name   = '';
		$last_name    = '';
		$display_name = '';

		try {

			if ( ! empty( $_POST['fname'] ) ) {
				$first_name = sanitize_text_field( $_POST['fname'] );
			} else {
				throw new \Exception( jnews_return_translation( 'First name should not be empty', 'jnews', 'first_name_required' ) );
			}

			if ( ! empty( $_POST['lname'] ) ) {
				$last_name = sanitize_text_field( $_POST['lname'] );
			}

			if ( ! empty( $_POST['dname'] ) ) {
				$display_name = sanitize_text_field( $_POST['dname'] );
			}

			do_action( 'jnews_account_page_on_save' );

			$url         = sanitize_text_field( $_POST['url'] );
			$description = wp_kses_post( $_POST['description'] );

			wp_update_user(
				array(
					'ID'           => $user_id,
					'first_name'   => $first_name,
					'last_name'    => $last_name,
					'display_name' => $display_name,
					'description'  => $description,
					'user_url'     => $url,
				)
			);

			foreach ( $this->user_social_info() as $key => $value ) {
				update_user_meta( $user_id, $key, sanitize_text_field( $_POST[ $key ] ) );
			}

			if ( isset( $_POST['photo'][0] ) && '' != $_POST['photo'][0] ) {
				update_user_meta( $user_id, 'profile_picture', sanitize_text_field( $_POST['photo'][0] ) );
			} else {
				delete_user_meta( $user_id, 'profile_picture' );
			}

			$_POST['success-message'] = jnews_return_translation( 'You have successfully edited your account details', 'jnews', 'success_edit_account' );

		} catch ( \Exception $e ) {
			$_POST['error-message'] = $e->getMessage();
		}
	}

	/**
	 * Method edit_password_handler
	 *
	 * @return void
	 */
	protected function edit_password_handler() {
		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		try {

			if ( ! empty( $_POST['old_password'] ) ) {
				if ( ! wp_check_password( $_POST['old_password'], $user->data->user_pass, $user_id ) ) {
					throw new \Exception( jnews_return_translation( 'Your old password is not valid', 'jnews', 'old_password_error' ) );
				}

				if ( empty( $_POST['new_password'] ) || empty( $_POST['confirm_password'] ) ) {
					throw new \Exception( jnews_return_translation( 'Please enter your new password', 'jnews', 'new_password_empty' ) );
				}

				if ( $_POST['new_password'] !== $_POST['confirm_password'] ) {
					throw new \Exception( jnews_return_translation( 'New Password & Confirm Password do not match', 'jnews', 'confirm_password_error' ) );
				}

				$this->do_reset_password( $user, $_POST['new_password'] );

				$_POST['success-message'] = jnews_return_translation( 'You have successfully changed your password', 'jnews', 'success_change_password' );
			} else {
				throw new \Exception( jnews_return_translation( 'Please enter your old password', 'jnews', 'old_password_empty' ) );
			}
		} catch ( \Exception $e ) {
			$_POST['error-message'] = $e->getMessage();
		}
	}

	/**
	 * Method filter_user_media
	 *
	 * @param array $query $query.
	 *
	 * @return array
	 */
	public function filter_user_media( $query ) {
		if ( ! ( current_user_can( 'manage_options' ) || get_theme_mod( 'jnews_share_library', false ) ) ) {
			$query['author'] = get_current_user_id();
		}

		return $query;
	}

	/**
	 * Method form_handler
	 *
	 * @return void
	 */
	public function form_handler() {
		if ( isset( $_POST['jnews-action'] ) && ! empty( $_POST['jnews-account-nonce'] ) && wp_verify_nonce( $_POST['jnews-account-nonce'], 'jnews-account-nonce' ) ) {
			$action = sanitize_key( $_POST['jnews-action'] );

			switch ( $action ) {
				case 'edit-account':
					$this->edit_account_handler();
					break;
				case 'change-password':
					$this->edit_password_handler();
					break;
			}
		}
	}

	/**
	 * Method filter_mime_types
	 *
	 * @param array $mime_types $mime_types.
	 *
	 * @return array
	 */
	public function filter_mime_types( $mime_types ) {
		if ( 'edit_account' === $this->current_page ) {
			return array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
			);
		}

		return $mime_types;
	}

	/**
	 * Method flush_rewrite_rules
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {
		$this->add_rewrite_rule();

		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	/**
	 * Method get_current_page
	 *
	 * @return string
	 */
	public function get_current_page() {
		return $this->current_page;
	}

	/**
	 * Method get_right_title
	 *
	 * @return void
	 */
	public function get_right_title() {
		if ( isset( $this->current_page ) ) {
			echo jnews_return_translation( $this->endpoint[ $this->current_page ]['title'], 'jnews', $this->endpoint[ $this->current_page ]['label'] );
		}
	}

	/**
	 * Method get_right_content
	 *
	 * @return void
	 */
	public function get_right_content() {
		if ( $this->current_page == 'edit_account' ) {
			jeg_locate_template( locate_template( 'fragment/account/account-edit.php', false, false ), true, $this->get_user_data() );
		} elseif ( $this->current_page == 'change_password' ) {
			jeg_locate_template( locate_template( 'fragment/account/account-password.php', false, false ), true );
		}
	}

	/**
	 * Method get_endpoint
	 *
	 * @return array
	 */
	public function get_endpoint() {
		return $this->endpoint;
	}

	/**
	 * Method to get saved posts URL.
	 *
	 * @return string
	 */
	public function saved_posts_enpoint() {
		foreach ( $this->endpoint as $endpoint ) {
			if ( isset( $endpoint['guest'] ) ) {
				if ( is_user_logged_in() || $endpoint['guest'] ) {
					return esc_url( jnews_home_url_multilang( $this->endpoint['account']['slug'] . '/' . $endpoint['slug'] ) );
				}
			}
		}

		return home_url();
	}


	/**
	 * Method get_user_data
	 *
	 * @return array
	 */
	protected function get_user_data() {
		$user_id = get_current_user_id();

		$user = array(
			'user_firstname' => trim( get_the_author_meta( 'user_firstname', $user_id ) ),
			'user_lastname'  => trim( get_the_author_meta( 'user_lastname', $user_id ) ),
			'description'    => get_the_author_meta( 'description', $user_id ),
			'photo'          => array( get_the_author_meta( 'profile_picture', $user_id ) ),
		);

		foreach ( $this->user_social_info() as $key => $value ) {
			$user['socials'][ $key ] = array(
				'label' => $value,
				'value' => trim( get_the_author_meta( $key, $user_id ) ),
			);
		}

		return $user;
	}

	/**
	 * Method is_account_page
	 *
	 * @param object $wp $wp.
	 *
	 * @return boolean
	 */
	protected function is_account_page( $wp ) {
		$allow_guest = false;
		if ( ! is_user_logged_in() && apply_filters( 'jnews_allow_guest', false ) ) {
			$allow_guest    = true;
			$this->endpoint = array_filter( $this->endpoint, array( $this, 'guest_account_enpoint' ), ARRAY_FILTER_USE_BOTH );
		}
		if ( ( is_user_logged_in() || $allow_guest ) && ! is_admin() ) {
			if ( isset( $wp->query_vars[ $this->endpoint['account']['slug'] ] ) ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'load_script' ) );
					add_action( 'wp_enqueue_scripts', array( $this, 'load_style' ), 98 );
					return true;

			}
		}

		return false;
	}

	/**
	 * Method to filter account enpoint for not logged-in useers.
	 *
	 * @param array  $endpoint account enpoint lists.
	 * @param string $key account enpoint key.
	 *
	 * @return boolean
	 */
	public function guest_account_enpoint( $endpoint, $key ) {
		return 'account' === $key || ( isset( $endpoint['guest'] ) && $endpoint['guest'] );
	}

	/**
	 * Method jnews_user_role_capabilitiies
	 *
	 * @return void
	 */
	public function jnews_user_role_capabilitiies() {
		// see sTYTta6e .
		$theme_mods = get_theme_mods(); // need to get options exist in theme mods, so it won't affect the setting if the option haven't been saved yet.

		// contributor role.
		if ( isset( $theme_mods['jnews_capabilities_contributor_upload_library'] ) && $theme_mods['jnews_capabilities_contributor_upload_library'] ) {
			$subscriber = get_role( 'contributor' );
			$subscriber->add_cap( 'upload_files' );
		} elseif ( isset( $theme_mods['jnews_capabilities_contributor_upload_library'] ) && ! $theme_mods['jnews_capabilities_contributor_upload_library'] ) {
			$subscriber = get_role( 'contributor' );
			$subscriber->remove_cap( 'upload_files' );
		}

		// subscriber role.
		if ( isset( $theme_mods['jnews_capabilities_subscriber_upload_library'] ) && $theme_mods['jnews_capabilities_subscriber_upload_library'] ) {
			$subscriber = get_role( 'subscriber' );
			$subscriber->add_cap( 'upload_files' );
		} elseif ( isset( $theme_mods['jnews_capabilities_subscriber_upload_library'] ) && ! $theme_mods['jnews_capabilities_subscriber_upload_library'] ) {
			$subscriber = get_role( 'subscriber' );
			$subscriber->remove_cap( 'upload_files' );
		}
	}

	/**
	 * Method load_script
	 *
	 * @return void
	 */
	public function load_script() {
		wp_enqueue_media();
	}

	/**
	 * Method load_style
	 *
	 * @return void
	 */
	public function load_style() {
		$theme = wp_get_theme();
		wp_enqueue_style( 'jnews-account-page', apply_filters( 'jnews_get_asset_uri', get_parent_theme_file_uri( 'assets/' ) ) . 'css/account-page.css', null, $theme->get( 'Version' ) );
	}

	/**
	 * Method prevent_admin_access
	 *
	 * @return void
	 */
	public function prevent_admin_access() {
		$prevent_access = ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) && $this->admin_post_action() ? true : false;

		if ( $prevent_access ) {
			wp_safe_redirect( esc_url( jnews_home_url_multilang( '/' ) ) );
			exit;
		}
	}

	/**
	 * Method setup_endpoint
	 *
	 * @return void
	 */
	public function setup_endpoint() {
		$endpoint = array(
			'account'         => array(
				'slug'  => 'account',
				'label' => 'my_account',
				'title' => esc_html__( 'My Account', 'jnews' ),
			),
			'edit_account'    => array(
				'slug'  => 'edit-account',
				'label' => 'edit_account',
				'title' => esc_html__( 'Edit Account', 'jnews' ),
			),
			'change_password' => array(
				'slug'  => 'change-password',
				'label' => 'change_password',
				'title' => esc_html__( 'Change Password', 'jnews' ),
			),
		);

		$this->endpoint = apply_filters( 'jnews_account_page_endpoint', $endpoint );
	}

	/**
	 * Method setup_current_page
	 *
	 * @param string $page $page.
	 *
	 * @return void
	 */
	protected function setup_current_page( $page ) {
		foreach ( $this->endpoint as $key => $value ) {
			if ( $page == $value['slug'] ) {
				$this->current_page = $key;
			}
		}
	}

	/**
	 * Method user_avatar
	 *
	 * @param string $avatar $avatar.
	 * @param int    $user_id $user_id.
	 * @param int    $size $size.
	 * @param string $default $default.
	 * @param string $alt $alt.
	 * @param array  $args $args.
	 *
	 * @return string
	 */
	public function user_avatar( $avatar, $user_id, $size, $default, $alt, $args ) {
		$profile_picture = get_the_author_meta( 'profile_picture', $user_id );

		if ( $profile_picture ) {
			$image = wp_get_attachment_image_src( $profile_picture, 'thumbnail' );

			$class = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );

			if ( ! $args['found_avatar'] || $args['force_default'] ) {
				$class[] = 'avatar-default';
			}

			if ( $args['class'] ) {
				if ( is_array( $args['class'] ) ) {
					$class = array_merge( $class, $args['class'] );
				} else {
					$class[] = $args['class'];
				}
			}

			$avatar = sprintf(
				"<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
				esc_attr( $args['alt'] ),
				esc_url( $image[0] ),
				esc_attr( "$image[0] 2x" ),
				esc_attr( join( ' ', $class ) ),
				(int) $args['height'],
				(int) $args['width'],
				$args['extra_attr']
			);
		}

		return $avatar;
	}

	/**
	 * Method user_social_info
	 *
	 * @return array
	 */
	protected function user_social_info() {
		return array(
			'facebook'   => jnews_return_translation( 'Facebook', 'jnews', 'facebook' ),
			'tiktok'     => jnews_return_translation( 'Tiktok', 'jnews', 'tiktok' ),
			'threads'    => jnews_return_translation( 'Threads', 'jnews', 'threads' ),
			'twitter'    => jnews_return_translation( 'Twitter', 'jnews', 'twitter' ),
			'bluesky'    => jnews_return_translation( 'Bluesky', 'jnews', 'bluesky' ),
			'linkedin'   => jnews_return_translation( 'Linkedin', 'jnews', 'linkedin' ),
			'xing'       => jnews_return_translation( 'Xing', 'jnews', 'xing' ),
			'pinterest'  => jnews_return_translation( 'Pinterest', 'jnews', 'pinterest' ),
			'behance'    => jnews_return_translation( 'Behance', 'jnews', 'behance' ),
			'github'     => jnews_return_translation( 'Github', 'jnews', 'github' ),
			'flickr'     => jnews_return_translation( 'Flickr', 'jnews', 'flickr' ),
			'tumblr'     => jnews_return_translation( 'Tumblr', 'jnews', 'tumblr' ),
			'dribbble'   => jnews_return_translation( 'Dribbble', 'jnews', 'dribbble' ),
			'soundcloud' => jnews_return_translation( 'Soundcloud', 'jnews', 'soundcloud' ),
			'instagram'  => jnews_return_translation( 'Instagram', 'jnews', 'instagram' ),
			'vimeo'      => jnews_return_translation( 'Vimeo', 'jnews', 'vimeo' ),
			'youtube'    => jnews_return_translation( 'Youtube', 'jnews', 'youtube' ),
			'reddit'     => jnews_return_translation( 'Reddit', 'jnews', 'reddit' ),
			'vk'         => jnews_return_translation( 'Vk', 'jnews', 'vk' ),
			'weibo'      => jnews_return_translation( 'Weibo', 'jnews', 'weibo' ),
			'rss'        => jnews_return_translation( 'Rss', 'jnews', 'rss' ),
			'twitch'     => jnews_return_translation( 'Twitch', 'jnews', 'twitch' ),
			'url'        => jnews_return_translation( 'Website', 'jnews', 'website' ),
		);
	}

	/**
	 * Method upload_size_limit
	 *
	 * @param int $size $size.
	 *
	 * @return int
	 */
	public function upload_size_limit( $size ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			$size = apply_filters( 'jnews_frontend_max_upload_size', ( 2 * 1000 * 1024 ) );
		}

		return $size;
	}

	/**
	 * Method users_own_attachments
	 *
	 * @param object $wp_query $wp_query.
	 *
	 * @return void
	 */
	public function users_own_attachments( $wp_query ) {
		if ( $wp_query->is_main_query() && ! get_theme_mod( 'jnews_share_library', false ) ) {
			global $pagenow;

			if ( 'upload.php' === $pagenow || 'media-upload.php' === $pagenow ) {
				if ( ! current_user_can( 'manage_options' ) ) {
					$wp_query->set( 'author', get_current_user_id() );
				}
			}
		}
	}
}
