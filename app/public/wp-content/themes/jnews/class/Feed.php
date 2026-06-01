<?php

namespace JNews;

class Feed {

	/**
	 * RSS Feed atribute
	 *
	 * @var array
	 */
	public $attr;

	/**
	 * RSS Feed Post ID
	 *
	 * @var string
	 */
	public $ID;

	/**
	 * RSS Feed Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * RSS Feed Permalink
	 *
	 * @var string
	 */
	public $permalink;

	/**
	 * RSS Feed Description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * RSS Feed Post Author Name
	 *
	 * @var string
	 */
	public $post_author_name;

	/**
	 * RSS Feed Post Published Date
	 *
	 * @var integer
	 */
	public $publish_date;

	/**
	 * RSS Feed Post Update Date
	 *
	 * @var integer||null
	 */
	public $update_date;

	/**
	 * RSS Feed Post Update Date
	 *
	 * @var integer
	 */
	public $featured;

	/**
	 * RSS Feed Filter
	 *
	 * @var string
	 */
	public $filter;



	public function __construct( $feed_object, $attr ) {
		$this->attr             = $attr;
		$this->ID               = jnews_get_rss_post_id();
		$this->title            = $feed_object->get_title();
		$this->permalink        = $feed_object->get_link();
		$this->description      = $this->excerpt( $feed_object->get_description(), isset( $attr['excerpt_length'] ) ? $attr['excerpt_length'] : 20 );
		$this->post_author_name = isset( $feed_object->get_author()->name ) ? $feed_object->get_author()->name : '';
		$this->publish_date     = $feed_object->get_date( 'U' );
		$this->update_date      = $feed_object->get_updated_date( 'U' );
		$this->featured         = $attr['thumbnail'] ? $this->thumbnail( $feed_object ) : ''; /* see sLdJ1HlP */
	}

	private function excerpt( $description, $length ) {
		return wp_trim_words( $description, isset( $length['size'] ) ? $length['size'] : $length );
	}

	private function thumbnail( $feed_object ) {
		$image_thumnail = $feed_object->get_thumbnail();
		if ( is_array( $image_thumnail ) ) {
			$image = $image_thumnail['url'];
		} else {
			$enclosure = $feed_object->get_enclosure();
			if ( is_object( $enclosure ) && ! empty( $enclosure->link ) && $this->is_image_link( $enclosure ) ) {
				$image = $enclosure->link;
			} else {
				$image = $this->get_first_image_url( $feed_object->get_content() ) ? $this->get_first_image_url( $feed_object->get_content() ) : false;
			}
		}
		return $image ? '<img src="' . $image . '">' : '';
	}

	public function get_thumbnail( $size ) {
		$image_size = \JNews\Image\Image::getInstance()->get_image_size( $size );
		if ( isset( $this->attr['fallimage']['id'] ) ) {
			$fallimage = $this->attr['fallimage']['id'];
		} else {
			$fallimage = $this->attr['fallimage'];
		}
		if ( ! $this->featured && $this->attr['fallback'] ) {
			return "<div class=\"thumbnail-container size-{$image_size['dimension']} \">" . ( wp_get_attachment_image( $fallimage, $size ) ?: $this->featured ) . '</div>';
		}

		return $this->featured;
	}

	/**
	 * Get first image from RSS feed content if the content not provide the post thumbnial from enclosure tag.
	 *
	 * @param string $html an string that contain post content.
	 */
	private function get_first_image_url( $html ) {
		if ( ! empty( $html ) && preg_match( '/<img.+?src="(.+?)"/', $html, $matches ) ) {
			return $matches[1];
		}
		return false;
	}

	/**
	 * Check if enclosure link type is image
	 *
	 * @param object $enclosure enclusure content from RSS Feeds items.
	 */
	private function is_image_link( $enclosure ) {
		if ( isset( $enclosure->type ) && strpos( $enclosure->type, 'image/' ) === 0 ) {
			return true;
		}
		$img_extension = array( 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg' );
		$path          = parse_url( $enclosure->link, PHP_URL_PATH );
		$extension     = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( isset( $extension ) && in_array( $extension, $img_extension ) ) {
			return true;
		} else {
			$headers = get_headers( $enclosure->link, 1 );
			if ( isset( $headers['Content-Type'] ) ) {
				if ( strpos( $headers['Content-Type'], 'image/' ) === 0 ) {
					return true;
				}
			}
		}
		return false;
	}
}
