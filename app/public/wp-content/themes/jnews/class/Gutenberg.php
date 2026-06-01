<?php
/**
 * Gutenberg
 *
 * @author : Jegtheme
 * @package jnews
 */

namespace JNews;

/**
 * Class Gutenberg
 */
class Gutenberg {

	/**
	 * Instance
	 *
	 * @var Init
	 */
	private static $instance;

	/**
	 * Settings
	 *
	 * @var array
	 */
	private static $settings;

	/**
	 * Instance
	 *
	 * @var class
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Construct
	 */
	private function __construct() {
		if ( self::is_classic() ) {
			return;
		}

		$this->setup_hook();
	}

	/**
	 * Method setup_hook
	 *
	 * @return void
	 */
	protected function setup_hook() {
		global $pagenow;
		if ( 'post.php' === $pagenow ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'post_metabox' ) );
		}
		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_font' ) );

			if ( get_theme_mod( 'jnews_gutenberg_editor_style', true ) ) {
				// eMAHmTKT .
				add_action( 'admin_print_styles', array( $this, 'load_style' ), 99 );
			}
		} else {
			add_filter( 'get_the_terms', array( $this, 'get_post_format' ), 10, 3 );
			add_filter( 'get_post_metadata', array( $this, 'get_post_format_video' ), 10, 3 );
			add_filter( 'get_post_metadata', array( $this, 'get_post_format_gallery' ), 10, 3 );
			add_filter( 'jnews_load_post_subtitle', '__return_false' );
		}
	}

	/**
	 * Method load_style
	 *
	 * @return void
	 */
	public function load_style() {
		$body_font      = get_theme_mod( 'jnews_body_font' );
		$title_font     = get_theme_mod( 'jnews_h1_font' );
		$paragraph_font = get_theme_mod( 'jnews_p_font' );

		$heading_h1_font = get_theme_mod( 'jnews_blog_h1_font' );
		$heading_h2_font = get_theme_mod( 'jnews_blog_h2_font' );
		$heading_h3_font = get_theme_mod( 'jnews_blog_h3_font' );
		$heading_h4_font = get_theme_mod( 'jnews_blog_h4_font' );
		$heading_h5_font = get_theme_mod( 'jnews_blog_h5_font' );
		$heading_h6_font = get_theme_mod( 'jnews_blog_h6_font' );
		$li_font         = get_theme_mod( 'jnews_li_font' );
		$blockquote_font = get_theme_mod( 'jnews_blobkquote_font' );
		?>
		<style type="text/css" id="jnews-gutenberg-style">
			/*Font Style*/
			@media (max-width: 1200px ) {
				.wp-block {
					width: 85vw;
				}
			}
			<?php if ( ! empty( $body_font ) ) : ?>
			.wp-block {
				font-family: <?php echo esc_attr( $body_font['font-family'] ); ?>;
			}
			<?php endif ?>

			/* Post Title Style */
			<?php if ( ! empty( $title_font ) ) { ?>
				<?php
				$title_size_unit = isset( $title_font['font-size-unit'] ) && '' !== $title_font['font-size-unit'] ? $title_font['font-size-unit'] : 'px';
				?>
			.editor-styles-wrapper .editor-post-title__input {
				font-family: <?php echo esc_attr( $title_font['font-family'] ); ?>;
				font-size: <?php echo '' === $title_font['font-size'] ? '3em' : esc_attr( $title_font['font-size'] . $title_size_unit ); ?>;
				color: <?php echo '' === $title_font['color'] ? '#212121' : esc_attr( $title_font['color'] ); ?>;
				line-height: <?php echo '' === $title_font['line-height'] ? '1.15' : esc_attr( $title_font['line-height'] ); ?>;
			}
			<?php } else { ?>
			.editor-styles-wrapper .editor-post-title__input {
				font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
				font-size: 3em;
				color: '#212121';
				line-height: 1.15;
			}
			<?php } ?>
			/* Post Title Style */

			/* Paragraph Style */
			<?php if ( ! empty( $paragraph_font ) ) { ?>
				<?php
				/* see EsaX16AP */
				$paragraph_size_unit = isset( $paragraph_font['font-size-unit'] ) && '' !== $paragraph_font['font-size-unit'] ? esc_attr( $paragraph_font['font-size-unit'] ) : 'px';
				?>
			.wp-block-paragraph{
				font-family: <?php echo esc_attr( $paragraph_font['font-family'] ); ?>;
				font-size: <?php echo '' === $paragraph_font['font-size'] ? '16px' : esc_attr( $paragraph_font['font-size'] . $paragraph_size_unit ); ?>;
				color: <?php echo '' === $paragraph_font['color'] ? '#333' : esc_attr( $paragraph_font['color'] ); ?>;
				line-height: <?php echo '' === $paragraph_font['line-height'] ? '1.3' : esc_attr( $paragraph_font['line-height'] ); ?>;
			}
			<?php } else { ?>
			.wp-block-paragraph{
				font-family: Revalia,Helvetica,Arial,sans-serif;
				font-size: 16px;
				color: '#333';
				line-height: 1.3;
			}
			<?php } ?>
			/* Paragraph Style */


			/* Block H1 Style */
			<?php
			if ( ! empty( $heading_h1_font ) ) {
				$heading_h1_size_unit = isset( $heading_h1_font['font-size-unit'] ) && '' !== $heading_h1_font['font-size-unit'] ? esc_attr( $heading_h1_font['font-size-unit'] ) : 'px';
				$heading_h1_lh_unit   = isset( $heading_h1_font['line-height-unit'] ) && '' !== $heading_h1_font['line-height-unit'] ? esc_attr( $heading_h1_font['line-height-unit'] ) : 'px';
				$style                = '';
				$style               .= ! empty( $heading_h1_font['font-family'] && '' !== $heading_h1_font['font-family'] ) ? 'font-family:' . $heading_h1_font['font-family'] . ';' : '';
				$style               .= ! empty( $heading_h1_font['color'] && '' !== $heading_h1_font['color'] ) ? 'color:' . $heading_h1_font['color'] . ';' : '';
				$style               .= ! empty( $heading_h1_font['font-size'] && '' !== $heading_h1_font['font-size'] ) ? 'font-size:' . $heading_h1_font['font-size'] . $heading_h1_size_unit . ';' : '';
				$style               .= ! empty( $heading_h1_font['line-height'] && '' !== $heading_h1_font['line-height'] ) ? 'line-height:' . $heading_h1_font['line-height'] . $heading_h1_lh_unit . ';' : '';

				if ( ! empty( $style ) ) {
					?>
				.editor-styles-wrapper h1.wp-block {
					<?php echo esc_attr( $style ); ?>;
				}
					<?php
				}
			}
			?>
			/* End of Block H1 Style */

			/* Block h2 Style */
			<?php
			if ( ! empty( $heading_h2_font ) ) {
				$heading_h2_size_unit = isset( $heading_h2_font['font-size-unit'] ) && '' !== $heading_h2_font['font-size-unit'] ? esc_attr( $heading_h2_font['font-size-unit'] ) : 'px';
				$heading_h2_lh_unit   = isset( $heading_h2_font['line-height-unit'] ) && '' !== $heading_h2_font['line-height-unit'] ? esc_attr( $heading_h2_font['line-height-unit'] ) : 'px';
				$style                = '';
				$style               .= ! empty( $heading_h2_font['font-family'] && '' !== $heading_h2_font['font-family'] ) ? 'font-family:' . $heading_h2_font['font-family'] . ';' : '';
				$style               .= ! empty( $heading_h2_font['color'] && '' !== $heading_h2_font['color'] ) ? 'color:' . $heading_h2_font['color'] . ';' : '';
				$style               .= ! empty( $heading_h2_font['font-size'] && '' !== $heading_h2_font['font-size'] ) ? 'font-size:' . $heading_h2_font['font-size'] . $heading_h2_size_unit . ';' : '';
				$style               .= ! empty( $heading_h2_font['line-height'] && '' !== $heading_h2_font['line-height'] ) ? 'line-height:' . $heading_h2_font['line-height'] . $heading_h2_lh_unit . ';' : '';

				if ( ! empty( $style ) ) {
					?>
				.editor-styles-wrapper h2.wp-block {
					<?php echo esc_attr( $style ); ?>;
				}
					<?php
				}
			}
			?>
			/* End of Block H3 Style */

			/* Block H3 Style */
				<?php
				if ( ! empty( $heading_h3_font ) ) {
					$heading_h3_size_unit = isset( $heading_h3_font['font-size-unit'] ) && '' !== $heading_h3_font['font-size-unit'] ? esc_attr( $heading_h3_font['font-size-unit'] ) : 'px';
					$heading_h3_lh_unit   = isset( $heading_h3_font['line-height-unit'] ) && '' !== $heading_h3_font['line-height-unit'] ? esc_attr( $heading_h3_font['line-height-unit'] ) : 'px';
					$style                = '';
					$style               .= ! empty( $heading_h3_font['font-family'] && '' !== $heading_h3_font['font-family'] ) ? 'font-family:' . $heading_h3_font['font-family'] . ';' : '';
					$style               .= ! empty( $heading_h3_font['color'] && '' !== $heading_h3_font['color'] ) ? 'color:' . $heading_h3_font['color'] . ';' : '';
					$style               .= ! empty( $heading_h3_font['font-size'] && '' !== $heading_h3_font['font-size'] ) ? 'font-size:' . $heading_h3_font['font-size'] . $heading_h3_size_unit . ';' : '';
					$style               .= ! empty( $heading_h3_font['line-height'] && '' !== $heading_h3_font['line-height'] ) ? 'line-height:' . $heading_h3_font['line-height'] . $heading_h3_lh_unit . ';' : '';

					if ( ! empty( $style ) ) {
						?>
				.editor-styles-wrapper h3.wp-block {
						<?php echo esc_attr( $style ); ?>;
				}
						<?php
					}
				}
				?>
			/* End of Block H3 Style */

			/* Block H4 Style */
			<?php
			if ( ! empty( $heading_h4_font ) ) {
				$heading_h4_size_unit = isset( $heading_h4_font['font-size-unit'] ) && '' !== $heading_h4_font['font-size-unit'] ? esc_attr( $heading_h4_font['font-size-unit'] ) : 'px';
				$heading_h4_lh_unit   = isset( $heading_h4_font['line-height-unit'] ) && '' !== $heading_h4_font['line-height-unit'] ? esc_attr( $heading_h4_font['line-height-unit'] ) : 'px';
				$style                = '';
				$style               .= ! empty( $heading_h4_font['font-family'] && '' !== $heading_h4_font['font-family'] ) ? 'font-family:' . $heading_h4_font['font-family'] . ';' : '';
				$style               .= ! empty( $heading_h4_font['color'] && '' !== $heading_h4_font['color'] ) ? 'color:' . $heading_h4_font['color'] . ';' : '';
				$style               .= ! empty( $heading_h4_font['font-size'] && '' !== $heading_h4_font['font-size'] ) ? 'font-size:' . $heading_h4_font['font-size'] . $heading_h4_size_unit . ';' : '';
				$style               .= ! empty( $heading_h4_font['line-height'] && '' !== $heading_h4_font['line-height'] ) ? 'line-height:' . $heading_h4_font['line-height'] . $heading_h4_lh_unit . ';' : '';

				if ( ! empty( $style ) ) {
					?>
				.editor-styles-wrapper h4.wp-block {
					<?php echo esc_attr( $style ); ?>;
				}
						<?php
				}
			}
			?>
			/* End of Block H4 Style */

			/* Block h5 Style */
			<?php
			if ( ! empty( $heading_h5_font ) ) {
				$heading_h5_size_unit = isset( $heading_h5_font['font-size-unit'] ) && '' !== $heading_h5_font['font-size-unit'] ? esc_attr( $heading_h5_font['font-size-unit'] ) : 'px';
				$heading_h5_lh_unit   = isset( $heading_h5_font['line-height-unit'] ) && '' !== $heading_h5_font['line-height-unit'] ? esc_attr( $heading_h5_font['line-height-unit'] ) : 'px';
				$style                = '';
				$style               .= ! empty( $heading_h5_font['font-family'] && '' !== $heading_h5_font['font-family'] ) ? 'font-family:' . $heading_h5_font['font-family'] . ';' : '';
				$style               .= ! empty( $heading_h5_font['color'] && '' !== $heading_h5_font['color'] ) ? 'color:' . $heading_h5_font['color'] . ';' : '';
				$style               .= ! empty( $heading_h5_font['font-size'] && '' !== $heading_h5_font['font-size'] ) ? 'font-size:' . $heading_h5_font['font-size'] . $heading_h5_size_unit . ';' : '';
				$style               .= ! empty( $heading_h5_font['line-height'] && '' !== $heading_h5_font['line-height'] ) ? 'line-height:' . $heading_h5_font['line-height'] . $heading_h5_lh_unit . ';' : '';

				if ( ! empty( $style ) ) {
					?>
				.editor-styles-wrapper h5.wp-block {
					<?php echo esc_attr( $style ); ?>;
				}
						<?php
				}
			}
			?>
			/* End of Block h5 Style */

			/* Block H6 Style */
			<?php
			if ( ! empty( $heading_h6_font ) ) {
				$heading_h6_size_unit = isset( $heading_h6_font['font-size-unit'] ) && '' !== $heading_h6_font['font-size-unit'] ? esc_attr( $heading_h6_font['font-size-unit'] ) : 'px';
				$heading_h6_lh_unit   = isset( $heading_h6_font['line-height-unit'] ) && '' !== $heading_h6_font['line-height-unit'] ? esc_attr( $heading_h6_font['line-height-unit'] ) : 'px';
				$style                = '';
				$style               .= ! empty( $heading_h6_font['font-family'] && '' !== $heading_h6_font['font-family'] ) ? 'font-family:' . $heading_h6_font['font-family'] . ';' : '';
				$style               .= ! empty( $heading_h6_font['color'] && '' !== $heading_h6_font['color'] ) ? 'color:' . $heading_h6_font['color'] . ';' : '';
				$style               .= ! empty( $heading_h6_font['font-size'] && '' !== $heading_h6_font['font-size'] ) ? 'font-size:' . $heading_h6_font['font-size'] . $heading_h6_size_unit . ';' : '';
				$style               .= ! empty( $heading_h6_font['line-height'] && '' !== $heading_h6_font['line-height'] ) ? 'line-height:' . $heading_h6_font['line-height'] . $heading_h6_lh_unit . ';' : '';

				if ( ! empty( $style ) ) {
					?>
				.editor-styles-wrapper h6.wp-block {
					<?php echo esc_attr( $style ); ?>;
				}
						<?php
				}
			}
			?>
			/* End of Block H6 Style */

			/* Block List Style */
			<?php
			if ( ! empty( $li_font ) ) {
				$li_size_unit = isset( $li_font['font-size-unit'] ) && '' !== $li_font['font-size-unit'] ? esc_attr( $li_font['font-size-unit'] ) : 'px';
				$li_lh_unit   = isset( $li_font['line-height-unit'] ) && '' !== $li_font['line-height-unit'] ? esc_attr( $li_font['line-height-unit'] ) : 'px';
				$style                = '';
				$style               .= ! empty( $li_font['font-family'] && '' !== $li_font['font-family'] ) ? 'font-family:' . $li_font['font-family'] . ';' : '';
				$style               .= ! empty( $li_font['color'] && '' !== $li_font['color'] ) ? 'color:' . $li_font['color'] . ';' : '';
				$style               .= ! empty( $li_font['font-size'] && '' !== $li_font['font-size'] ) ? 'font-size:' . $li_font['font-size'] . $li_size_unit . ';' : '';
				$style               .= ! empty( $li_font['line-height'] && '' !== $li_font['line-height'] ) ? 'line-height:' . $li_font['line-height'] . $li_lh_unit . ';' : '';

				if ( ! empty( $style ) ) {
					?>
				li.wp-block {
					<?php echo esc_attr( $style ); ?>;
				}
						<?php
				}
			}
			?>
			/* End of Block List Style */

			/* Block Blockquote Style */
			<?php
			if ( ! empty( $blockquote_font ) ) {
				$blockquote_size_unit = isset( $blockquote_font['font-size-unit'] ) && '' !== $blockquote_font['font-size-unit'] ? esc_attr( $blockquote_font['font-size-unit'] ) : 'px';
				$blockquote_lh_unit   = isset( $blockquote_font['line-height-unit'] ) && '' !== $blockquote_font['line-height-unit'] ? esc_attr( $blockquote_font['line-height-unit'] ) : 'px';
				$style                = '';
				$style               .= ! empty( $blockquote_font['font-family'] && '' !== $blockquote_font['font-family'] ) ? 'font-family:' . $blockquote_font['font-family'] . ';' : '';
				$style               .= ! empty( $blockquote_font['color'] && '' !== $blockquote_font['color'] ) ? 'color:' . $blockquote_font['color'] . ';' : '';
				$style               .= ! empty( $blockquote_font['font-size'] && '' !== $blockquote_font['font-size'] ) ? 'font-size:' . $blockquote_font['font-size'] . $blockquote_size_unit . ';' : '';
				$style               .= ! empty( $blockquote_font['line-height'] && '' !== $blockquote_font['line-height'] ) ? 'line-height:' . $blockquote_font['line-height'] . $blockquote_lh_unit . ';' : '';

				if ( ! empty( $style ) ) {
					?>
				.editor-styles-wrapper blockquote.wp-block * {
					<?php echo esc_attr( $style ); ?>;
				}

				blockquote.wp-block cite {
					color: #a0a0a0;
					font-size: smaller;
					display: block;
					margin-top: 5px;
				}
						<?php
				}
			}
			?>
			/* End of Block Blockquote Style */



		</style>
		<?php
	}

	/**
	 * Method load_font
	 *
	 * @return void
	 */
	public function load_font() {
		if ( class_exists( '\Jeg\Util\Style_Generator' ) ) {
			$style_instance = \Jeg\Util\Style_Generator::get_instance();
			$font_url       = $style_instance->get_font_url();

			if ( $font_url ) {
				wp_enqueue_style( 'jeg_customizer_font', $font_url );
			}
		}
	}

	/**
	 * Method get_post_format
	 *
	 * @param array  $term $term.
	 * @param int    $post_id $post_id.
	 * @param string $taxonomy $taxonomy.
	 *
	 * @return array
	 */
	public function get_post_format( $term, $post_id, $taxonomy ) {

		if ( 'post_format' === $taxonomy && isset( $term[0] ) ) {

			$post_format = jnews_get_metabox_value( 'jnews_single_post.format', null, $post_id );

			if ( $post_format ) {
				$term[0]->slug = 'post-format-' . $post_format;
			}
		}

		return $term;
	}

	/**
	 * Method get_post_format_video
	 *
	 * @param string $value $value.
	 * @param int    $object_id $object_id.
	 * @param string $meta_key $meta_key.
	 *
	 * @return string
	 */
	public function get_post_format_video( $value, $object_id, $meta_key ) {

		if ( isset( $meta_key ) && '_format_video_embed' === $meta_key ) {

			$video = jnews_get_metabox_value( 'jnews_single_post.video', null, $object_id );

			if ( ! empty( $video ) ) {
				$value = $video;
			}
		}

		return $value;
	}

	/**
	 * Method get_post_format_gallery
	 *
	 * @param array  $value $value.
	 * @param int    $object_id $object_id.
	 * @param string $meta_key $meta_key.
	 *
	 * @return array
	 */
	public function get_post_format_gallery( $value, $object_id, $meta_key ) {

		if ( isset( $meta_key ) && '_format_gallery_images' === $meta_key ) {

			$video = jnews_get_metabox_value( 'jnews_single_post.gallery', null, $object_id );

			if ( ! empty( $video ) ) {
				$value = array( explode( ',', $video ) );
			}
		}

		return $value;
	}

	/**
	 * Method get_settings
	 *
	 * @return array
	 */
	private static function get_settings() {
		$settings = apply_filters( 'classic_editor_plugin_settings', false );

		if ( is_array( $settings ) ) {
			return array(
				'editor'           => ( isset( $settings['editor'] ) && $settings['editor'] === 'block' ) ? 'block' : 'classic',
				'allow-users'      => ! empty( $settings['allow-users'] ),
				'hide-settings-ui' => true,
			);
		}

		if ( ! empty( self::$settings ) ) {
			return self::$settings;
		}

		if ( class_exists( 'Classic_Editor' ) ) {
			if ( is_multisite() ) {
				$defaults = array(
					'editor'      => get_network_option( null, 'classic-editor-replace' ) === 'block' ? 'block' : 'classic',
					'allow-users' => false,
				);

				$defaults = apply_filters( 'classic_editor_network_default_settings', $defaults );

				if ( get_network_option( null, 'classic-editor-allow-sites' ) !== 'allow' ) {
					// Per-site settings are disabled. Return default network options nad hide the settings UI.
					$defaults['hide-settings-ui'] = true;

					return $defaults;
				}

				// Override with the site options.
				$editor_option      = get_option( 'classic-editor-replace' );
				$allow_users_option = get_option( 'classic-editor-allow-users' );

				if ( $editor_option ) {
					$defaults['editor'] = $editor_option;
				}
				if ( $allow_users_option ) {
					$defaults['allow-users'] = ( $allow_users_option === 'allow' );
				}

				$editor      = ( isset( $defaults['editor'] ) && $defaults['editor'] === 'block' ) ? 'block' : 'classic';
				$allow_users = ! empty( $defaults['allow-users'] );
			} else {
				$allow_users = ( get_option( 'classic-editor-allow-users' ) === 'allow' );
				$option      = get_option( 'classic-editor-replace' );

				// Normalize old options.
				if ( 'block' === $option || 'no-replace' === $option ) {
					$editor = 'block';
				} else {
					// empty( $option ) || $option === 'classic' || $option === 'replace'.
					$editor = 'classic';
				}
			}

			// Override the defaults with the user options.
			if ( ( ! isset( $GLOBALS['pagenow'] ) || 'options-writing.php' !== $GLOBALS['pagenow'] ) && $allow_users ) {
				$user_options = get_user_option( 'classic-editor-settings' );

				if ( 'block' === $user_options || 'classic' === $user_options ) {
					$editor = $user_options;
				}
			}
		} else {
			$editor      = version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ? 'block' : 'classic';
			$allow_users = false;
		}

		self::$settings = array(
			'editor'           => $editor,
			'hide-settings-ui' => false,
			'allow-users'      => $allow_users,
		);

		return self::$settings;
	}

	/**
	 * Method get_current_post_type
	 *
	 * @return string
	 */
	private static function get_current_post_type() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;

		if ( isset( $uri ) ) {
			$uri_parts = wp_parse_url( $uri );
			if ( isset( $uri_parts['path'] ) ) {
				$file = basename( $uri_parts['path'] );

				if ( $uri && in_array( $file, array( 'post.php', 'post-new.php' ), true ) ) {
					$post_id = self::get_edited_post_id();

					$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null;

					$post_type = $post_id ? get_post_type( $post_id ) : $post_type;

					if ( isset( $post_type ) ) {
						return $post_type;
					}

					return 'post';
				}
			}
		}
	}

	/**
	 * Method get_edited_post_id
	 *
	 * @return int
	 */
	private static function get_edited_post_id() {
		global $post;

		$p_post_id = isset( $_POST['post_ID'] ) ? (int) sanitize_text_field( $_POST['post_ID'] ) : null;

		$g_post_id = isset( $_GET['post'] ) ? (int) sanitize_text_field( $_GET['post'] ) : null;

		$post_id = $g_post_id ? $g_post_id : $p_post_id;

		$post_id = isset( $post->ID ) ? $post->ID : $post_id;

		if ( isset( $post_id ) ) {
			return (int) $post_id;
		}

		return 0;
	}

	/**
	 * Method has_blocks
	 *
	 * @param object $post $post.
	 *
	 * @return boolean
	 */
	private static function has_blocks( $post = null ) {
		if ( ! is_string( $post ) ) {
			$wp_post = get_post( $post );

			if ( $wp_post instanceof WP_Post ) {
				$post = $wp_post->post_content;
			}
		}

		return false !== strpos( (string) $post, '<!-- wp:' );
	}

	/**
	 * Method is_classic
	 *
	 * @param int $post_id $post_id.
	 *
	 * @return boolean
	 */
	public static function is_classic( $post_id = 0 ) {
		if ( self::get_current_post_type() === 'post' ) {
			$settings = self::get_settings();
			if ( ! $post_id ) {
				$post_id = self::get_edited_post_id();
			}
			if ( $settings['allow-users'] ) {
				if ( ! isset( $_GET['classic-editor__forget'] ) ) {
					if ( isset( $_GET['classic-editor'] ) ) {
						return true;
					}

					return 'classic' === $settings['editor'];
				}
				if ( $post_id ) {
					$which = get_post_meta( $post_id, 'classic-editor-remember', true );

					switch ( $which ) {
						case 'classic-editor':
							return true;
							break;
						case 'block-editor':
							return false;
							break;
						default:
							return ( ! self::has_blocks( $post_id ) );
							break;
					}
				}
				if ( isset( $_GET['classic-editor__forget'] ) ) {
					return false;
				}

				return 'classic' === $settings['editor'];
			}

			if ( isset( $_GET['classic-editor'] ) ) {
				return true;
			}

			return 'classic' === $settings['editor'];
		}

		return false;
	}

	/**
	 * Method post_metabox
	 *
	 * @return void
	 */
	public function post_metabox() {

		$screen = get_current_screen();

		if ( $screen->id === 'post' ) {

			$post_id = get_the_ID();

			$this->post_format( $post_id );
			$this->post_format_video( $post_id );
			$this->post_format_gallery( $post_id );
		}
	}

	/**
	 * Method post_format
	 *
	 * @param int $post_id $post_id.
	 *
	 * @return void
	 */
	protected function post_format( $post_id ) {

		$format = jnews_get_metabox_value( 'jnews_single_post.format', null, $post_id );

		if ( empty( $format ) ) {

			// get old post format.
			$format      = get_post_format( $post_id );
			$single_post = get_post_meta( $post_id, 'jnews_single_post', true );

			if ( $format ) {
				if ( isset( $single_post ) && is_array( $single_post ) ) {
					$single_post['format'] = $format;
				} else {
					$single_post = array(
						'format' => $format,
					);
				}
			} elseif ( empty( $single_post ) ) {
					$single_post = array(
						'format' => 'standard',
					);
			} else {
				$single_post['format'] = 'standard';
			}

			// save into post format metabox.
			update_post_meta( $post_id, 'jnews_single_post', $single_post );
		}
	}

	/**
	 * Method post_format_video
	 *
	 * @param int $post_id $post_id.
	 *
	 * @return void
	 */
	protected function post_format_video( $post_id ) {

		$video = jnews_get_metabox_value( 'jnews_single_post.video', null, $post_id );

		if ( empty( $video ) ) {

			// get old post video.
			$video = get_post_meta( $post_id, '_format_video_embed', true );

			if ( ! empty( $video ) ) {

				$single_post          = get_post_meta( $post_id, 'jnews_single_post', true );
				$single_post['video'] = $video;

				// save into post video metabox.
				update_post_meta( $post_id, 'jnews_single_post', $single_post );
			}
		}
	}

	/**
	 * Method post_format_gallery
	 *
	 * @param int $post_id $post_id.
	 *
	 * @return void
	 */
	protected function post_format_gallery( $post_id ) {

		$gallery = jnews_get_metabox_value( 'jnews_single_post.gallery', null, $post_id );

		if ( empty( $gallery ) ) {

			// get old post gallery
			$gallery = get_post_meta( $post_id, '_format_gallery_images', true );

			if ( ! empty( $gallery ) ) {

				$single_post            = get_post_meta( $post_id, 'jnews_single_post', true );
				$single_post['gallery'] = implode( ',', $gallery );

				// save into post gallery metabox
				update_post_meta( $post_id, 'jnews_single_post', $single_post );
			}
		}
	}
}
