<?php
/**
 * @author : Jegtheme
 */

class JNews_Meta_Header {

	/**
	 * @var JNews_Meta_Header
	 */
	private static $instance;

	/**
	 * @var JNews_Meta_Facebook
	 */
	private $facebook_meta;

	/**
	 * @var JNews_Meta_Twitter
	 */
	private $twitter_meta;

	/**
	 * @return JNews_Meta_Header
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	private function __construct() {
		add_action( 'wp', array( $this, 'instantiate_post' ), 1 );
		add_action( 'wp_head', array( $this, 'generate_social_meta' ), 1 );
	}

	public function instantiate_post() {
		require_once 'class.jnews-meta-abstract.php';
		require_once 'class.jnews-meta-facebook.php';
		require_once 'class.jnews-meta-twitter.php';

		$post_id = get_the_ID();
		if ( is_front_page() && 'posts' === get_option( 'show_on_front' ) && get_theme_mod( 'jnews_index_top_content' ) ) { /* see ipQdfqlm */
			$post_id = get_theme_mod( 'jnews_index_top_content' );
		}

		$this->facebook_meta = new JNews_Meta_Facebook( $post_id );
		$this->twitter_meta  = new JNews_Meta_Twitter( $post_id );
	}

	public function generate_description_meta() {
	
			$description = '';

			if ( jnews_get_option( 'jnews_enable_pages_meta_description', false ) && is_page() ) {
				$post = get_the_ID();
				$description = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
				$description = ! empty( $description ) ? $description : get_the_title( $post );
			} if ( jnews_get_option( 'jnews_enable_posts_meta_description', false ) && is_single() ) {
				$post = get_post();
				$description = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
				$description = ! empty( $description ) ? $description : get_the_title( $post );
			} else if ( jnews_get_option( 'jnews_enable_tags_meta_description', false ) && is_tag() ) {
				$queried_object = get_queried_object();

				if ( property_exists( $queried_object, 'description' ) || property_exists( $queried_object, 'name' ) ) {
					$description = $queried_object->description;
					$description = ! empty( $description ) ? $description : $queried_object->name;
				}
			} else if ( jnews_get_option( 'jnews_enable_categories_meta_description', false ) && is_category() ) {
				$queried_object = get_queried_object();

				if ( property_exists( $queried_object, 'description' ) || property_exists( $queried_object, 'name' ) ) {
					$description = $queried_object->description;
					$description = ! empty( $description ) ? $description : $queried_object->name;
				}
			} 

			// If there's a description, add it to the meta tag
			if ( ! empty( $description ) ) {
				echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">';
			}
	}

	public function generate_social_meta() {
		// see wPoGON7R
		if ( ! is_admin() && $this->facebook_meta instanceof JNews_Meta_Facebook && $this->twitter_meta instanceof JNews_Meta_Twitter ) {
			// Language is required for gettext.
			// Without it, 'gettext' or '_' will cause error instead of translate text.
			// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions
			$locale      = function_exists( 'jnews_get_locale' ) ? jnews_get_locale() : get_locale();
			$prev_locale = getenv( 'LC_ALL' );
			putenv( 'LC_ALL=' . $locale );

			$this->facebook_meta->render_meta();
			$this->twitter_meta->render_meta();
			$this->generate_description_meta(); //see md7enUWk

			$this->add_fb_app_id();

			// Revert Language.
			if ( $prev_locale ) {
				putenv( 'LC_ALL=' . $prev_locale );
			} else {
				putenv( 'LC_ALL' );
			}
			// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions
		}
	}

	public function add_fb_app_id() {
		$id = jnews_get_option( 'social_meta_fb_app_id', '' );

		if ( ! empty( $id ) ) {
			echo '<meta property="fb:app_id" content="' . $id . '">';
		}
	}
}
