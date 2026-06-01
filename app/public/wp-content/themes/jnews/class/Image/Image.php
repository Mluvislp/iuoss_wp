<?php
/**
 * Image
 *
 * @author : Jegtheme
 * @package jnews
 */

namespace JNews\Image;

/**
 * Class JNews Image
 */
class Image {

	/**
	 * Instance
	 *
	 * @var Image
	 */
	private static $instance;

	/**
	 * Image Size
	 *
	 * @var array
	 */
	private $image_size = array();

	/**
	 * Prefix
	 *
	 * @var string
	 */
	private $prefix = 'jnews-';

	/**
	 * Instance
	 *
	 * @return Image
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
		$this->setup_image_size();

		add_action( 'wp_loaded', array( $this, 'image_hook' ) );

		add_action( 'after_setup_theme', array( $this, 'generate_image' ), 99 );
	}

	/**
	 * Method add_image_size
	 *
	 * @return void
	 */
	public function add_image_size() {
		foreach ( $this->image_size as $id => $image ) {
			add_image_size( $id, $image['width'], $image['height'], $image['crop'] );
		}
	}

	/**
	 * Method custom_size
	 *
	 * @param array $sizes $sizes.
	 *
	 * @return array
	 */
	public function custom_size( $sizes ) {
		$this->setup_image_size();
		foreach ( $this->image_size as $key => $size ) {
			$sizes[ $key ] = esc_html__( 'Custom Size', 'jnews' );
		}
		return $sizes;
	}

	/**
	 * Method generate_image
	 *
	 * @return void
	 */
	public function generate_image() {
		$generate_image = get_theme_mod( 'jnews_image_generator', 'normal' );

		if ( 'dynamic' === $generate_image ) {
			add_filter( 'image_downsize', array( $this, 'image_down_size' ), 99, 3 );
		} else {
			add_action( 'init', array( $this, 'add_image_size' ) );
		}
	}

	/**
	 * Method get_image_size
	 *
	 * @param string $size $size.
	 *
	 * @return object
	 */
	public function get_image_size( $size ) {
		if ( strpos( apply_filters( 'jnews_use_custom_image', $size ), 'jnews-' ) !== false ) {
			$size = apply_filters( 'jnews_use_custom_image', $size ); /* see N8xEb1VO */
		}
		return $this->image_size[ $size ];
	}

	/**
	 * Method generate_image_retina
	 *
	 * @param string  $image $image.
	 * @param string  $imageretina $imageretina.
	 * @param string  $alt $alt.
	 * @param boolean $echo $echo.
	 * @param string  $image_dm $image_dm.
	 * @param string  $imageretina_dm $imageretina_dm.
	 *
	 * @return string|void
	 */
	public static function generate_image_retina( $image, $imageretina, $alt, $echo, $image_dm, $imageretina_dm ) {
		$srcset          = '';
		$src             = 'src="' . esc_url( $image ) . '" ';
		$datasrclight    = 'data-light-src="' . esc_url( $image ) . '" ';
		$datasrcsetlight = 'data-light-srcset="' . esc_url( $image ) . ' 1x, ' . esc_url( $imageretina ) . ' 2x" ';
		$datasrcdark     = 'data-dark-src="' . esc_url( $image_dm ) . '" ';
		$datasrcsetdark  = 'data-dark-srcset="' . esc_url( $image_dm ) . ' 1x, ' . esc_url( $imageretina_dm ) . ' 2x"';
		$dm_options      = get_theme_mod( 'jnews_dark_mode_options', 'jeg_toggle_light' );
		$print_size      = jnews_get_image_size( $image );
		if ( ! empty( $imageretina ) ) {
			$srcset = 'srcset="' . esc_url( $image ) . ' 1x, ' . esc_url( $imageretina ) . ' 2x"';
		}
		if ( ( isset( $_COOKIE['darkmode'] ) && 'true' === $_COOKIE['darkmode'] && ( 'jeg_toggle_light' === $dm_options || 'jeg_toggle_dark' === $dm_options || 'jeg_timed_dark' === $dm_options || 'jeg_device_dark' === $dm_options || 'jeg_device_toggle' === $dm_options ) ) || ( 'jeg_full_dark' === $dm_options ) || ( ! isset( $_COOKIE['darkmode'] ) && 'jeg_toggle_dark' === $dm_options ) ) {
			$src    = 'src="' . esc_url( $image_dm ) . '" ';
			$srcset = 'srcset="' . esc_url( $image_dm ) . ' 1x, ' . esc_url( $imageretina_dm ) . ' 2x"';
		}

		$header_logo = "<img class='jeg_logo_img' " . $src . $srcset . ' alt="' . esc_attr( $alt ) . '"' . $datasrclight . $datasrcsetlight . $datasrcdark . $datasrcsetdark . $print_size . '>';

		if ( $echo ) {
			echo jnews_sanitize_output( $header_logo );
		} else {
			return $header_logo;
		}
	}

	/**
	 * Method image_hook
	 *
	 * @return void
	 */
	public function image_hook() {
		$mechanism = get_theme_mod( 'jnews_image_load', 'lazyload' );

		if ( 'lazyload' === $mechanism ) {
			$image = ImageLazyLoad::getInstance();
		} elseif ( 'background' === $mechanism ) {
			$image = ImageBackgroundLoad::getInstance();
		} else {
			$image = ImageNormalLoad::getInstance();
		}

		add_filter( 'jnews_image_thumbnail', array( $image, 'image_thumbnail' ), null, 2 );
		add_filter( 'jnews_image_thumbnail_unwrap', array( $image, 'image_thumbnail_unwrap' ), null, 2 );
		if ( defined( 'JNEWS_ESSENTIAL' ) ) {
			add_filter( 'jnews_image_lazy_owl', array( $image, 'owl_lazy_image' ), null, 2 );
			add_filter( 'jnews_single_image_lazy_owl', array( $image, 'owl_lazy_single_image' ), null, 2 );

			add_filter( 'jnews_single_image_unwrap', array( $image, 'single_image_unwrap' ), null, 2 );
			add_filter( 'jnews_single_image_owl', array( $image, 'owl_single_image' ), null, 2 );

			add_filter( 'jnews_single_image', array( $image, 'single_image' ), null, 3 );
			add_filter( 'image_size_names_choose', array( $this, 'custom_size' ) );
		}

		add_filter( 'wp_img_tag_add_loading_attr', array( $this, 'lazy_load_filter' ), 10, 3 );
	}

	/**
	 * Lazy Load Filter
	 *
	 * Filters the `loading` attribute value to add to an image. Default `lazy`.
	 *
	 * Returning `false` or an empty string will not add the attribute.
	 * Returning `true` will add the default value.
	 *
	 * @since x.x.x
	 *
	 * @param string|bool $value   The `loading` attribute value. Returning a falsey value will result in
	 *                             the attribute being omitted for the image.
	 * @param string      $image   The HTML `img` tag to be filtered.
	 * @param string      $context Additional context about how the function was called or where the img tag is.
	 */
	public function lazy_load_filter( $value, $image, $context ) {
		$pattern = '/class=["\'][^"\']*\blazyload\b[^"\']*["\']/'; /** Search `lazyload` pattern in htl class */

		if ( ! preg_match( $pattern, $image ) ) {
			return false; /** Do not add loading attribute if lazyload class is present */
		}

		return $value;
	}

	/**
	 * The image downside filter

	 * @param  boolean $ignore ignore.
	 * @param  integer $id id.
	 * @param  string  $size size.
	 * @return mixed
	 */
	public function image_down_size( $ignore = false, $id = null, $size = 'medium' ) {
		if ( wp_doing_ajax() && isset( $_POST['action'] ) && ( 'query-attachments' === $_POST['action'] || 'upload-attachment' === $_POST['action'] ) ) {
			return false;
		}

		global $_wp_additional_image_sizes;

		$image = wp_get_attachment_url( $id );

		/* return immediately if user using external link */
		if ( ! ( strpos( $image, home_url() ) === 0 ) ) { /*  see oMiE0vVN */
			return false;
		}

		$meta    = wp_get_attachment_metadata( $id );
		$width   = 0;
		$height  = 0;
		$crop    = false;
		$dynamic = false;

		// return immediately if the size is "thumbnail".
		if ( $size == 'thumbnail' ) {
			return false;
		}

		// return immediately if intermediate image size found.
		if ( image_get_intermediate_size( $id, $size ) || is_array( $size ) ) {
			return false;
		}

		// check if the image size is defined by 'add_image_size()' but not created yet.
		if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
			$width  = $_wp_additional_image_sizes[ $size ]['width'];
			$height = $_wp_additional_image_sizes[ $size ]['height'];
			$crop   = $_wp_additional_image_sizes[ $size ]['crop'];
		}

		// here we assume that the size is dynamic e.g. '200x200'.
		elseif ( $sizeArr = $this->parse_size( $size ) ) {
			$width   = isset( $sizeArr['width'] ) ? $sizeArr['width'] : 0;
			$height  = isset( $sizeArr['height'] ) ? $sizeArr['height'] : 999999;
			$crop    = isset( $sizeArr['height'] ) ? true : false;
			$dynamic = true;
		}

		// let's continue if original image found, also if width & height are specified.
		if ( $image && $width && $height ) {
			if ( ! $img = $this->make_image( $id, $width, $height, $crop ) ) {
				return false;
			}

			// see e0cXieYq .
			$img = $this->make_image( $id, $width, $height, $crop );
			if ( ! $img || is_wp_error( $img ) ) {
				return false;
			}

			if ( $dynamic ) {
				$img['jnews_dynamic_images'] = true;
			}

			unset( $img['path'] );

			$meta['sizes'][ $size ] = $img;

			// update attachment metadata.
			wp_update_attachment_metadata( $id, $meta );

			// replace original image url with newly created one.
			$image = str_replace( wp_basename( $image ), wp_basename( $img['file'] ), $image );

			// we might need to further constrain it if content_width is narrower.
			list($width, $height) = image_constrain_size_for_editor( $width, $height, $size );

			// finally return the result.
			return array( $image, $width, $height, false );
		}

		return false;
	}

	/**
	 * Create a new image by cropping the original image based on given size.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  integer $id id.
	 * @param  integer $width width.
	 * @param  integer $height height.
	 * @param  boolean $crop crop.
	 * @return array
	 */
	public function make_image( $id, $width, $height = 999999, $crop = false ) {
		$image  = get_attached_file( $id );
		$editor = wp_get_image_editor( $image );

		if ( ! is_wp_error( $editor ) ) {
			$editor->resize( $width, $height, $crop );
			$editor->set_quality( 100 );

			$filename = $editor->generate_filename();

			return $editor->save( $filename );
		}
	}

	/**
	 * Parse image size.

	 * @param  string $string string.
	 * @return array
	 */
	public function parse_size( $string ) {
		$size = array();

		if ( ! is_array( $string ) && substr( $string, 0, strlen( $this->prefix ) ) === $this->prefix ) {
			$string = substr( $string, strlen( $this->prefix ) );
			$array  = explode( 'x', $string );

			foreach ( $array as $key => $value ) {
				$value = absint( trim( $value ) );

				if ( ! $value ) {
					continue;
				}

				if ( 0 === $key ) {
					$size['width'] = $value;
				}

				if ( 1 === $key ) {
					$size['height'] = $value;
				}
			}
		} else {
			return $string;
		}

		return $size;
	}

	/**
	 * Method setup_image_size
	 *
	 * @return void
	 */
	public function setup_image_size() {
		$this->image_size = array(
			$this->prefix . '350x250' => array(
				'width'     => 350,
				'height'    => 250,
				'crop'      => true,
				'dimension' => 715,
			),
		);
		if ( defined( 'JNEWS_ESSENTIAL' ) ) {
			$this->image_size = array(
				// dimension : 0.5.
				$this->prefix . '360x180'       => array(
					'width'     => 360,
					'height'    => 180,
					'crop'      => true,
					'dimension' => 500,
				),
				$this->prefix . '750x375'       => array(
					'width'     => 750,
					'height'    => 375,
					'crop'      => true,
					'dimension' => 500,
				),
				$this->prefix . '1140x570'      => array(
					'width'     => 1140,
					'height'    => 570,
					'crop'      => true,
					'dimension' => 500,
				),

				// dimension : 0.715.
				$this->prefix . '120x86'        => array(
					'width'     => 120,
					'height'    => 86,
					'crop'      => true,
					'dimension' => 715,
				),
				$this->prefix . '350x250'       => array(
					'width'     => 350,
					'height'    => 250,
					'crop'      => true,
					'dimension' => 715,
				),
				$this->prefix . '750x536'       => array(
					'width'     => 750,
					'height'    => 536,
					'crop'      => true,
					'dimension' => 715,
				),
				$this->prefix . '1140x815'      => array(
					'width'     => 1140,
					'height'    => 815,
					'crop'      => true,
					'dimension' => 715,
				),

				// dimension.
				$this->prefix . '360x504'       => array(
					'width'     => 360,
					'height'    => 504,
					'crop'      => true,
					'dimension' => 1400,
				),

				// dimension 1.
				$this->prefix . '75x75'         => array(
					'width'     => 75,
					'height'    => 75,
					'crop'      => true,
					'dimension' => 1000,
				),
				$this->prefix . '350x350'       => array(
					'width'     => 350,
					'height'    => 350,
					'crop'      => true,
					'dimension' => 1000,
				),

				// featured post.
				$this->prefix . 'featured-750'  => array(
					'width'     => 750,
					'height'    => 0,
					'crop'      => true,
					'dimension' => 1000,
				),
				$this->prefix . 'featured-1140' => array(
					'width'     => 1140,
					'height'    => 0,
					'crop'      => true,
					'dimension' => 1000,
				),
			);
		}
	}
}
