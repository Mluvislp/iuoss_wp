<?php
/**
 * @author : Jegtheme
 */

namespace JNews;

use JNews\Util\VideoAttribute;

/**
 * Class Plugin Metabox
 */
class MetaboxBuilder {
    private static $pool = array();
    
    /**
     * Method set_meta
     *
     * @param array $array metaboxes.
     *
     * @return array
     */
    public static function set_meta( $metaboxes ) {
        self::$pool[ $metaboxes['id'] ] = $metaboxes;
    }
    
    /**
     * Method get_meta
     *
     * @return array
     */
    public static function get_meta() {
        return self::$pool;
    }
    
    /**
     * Method get_values
     *
     * @param integer $post_id post id.
     * @param string  $key key.
     *
     * @return mixed
     */
    public static function get_mode( $post_id, $key ) {
        $metaboxes = self::get_meta();
        $mode = '';

        if ( !empty( $metaboxes ) ) {
            foreach( $metaboxes as $meta ) {
                if( $key === $meta['id'] && isset( $meta['mode'] ) ) {
                   return $meta['mode'];
                }
            }
        }

        return $mode;
    }
    
    /**
     * Method save_meta
     *
     * @param integer $post_id post id.
     * @param string  $meta_key meta key.
     * @param mixed   $values value.
     *
     */
    public static function save_meta( $post_id, $meta_key, $values ) {
        if( isset( $values ) ) {
            update_post_meta( $post_id, $meta_key, $values );
        }
    }
    
    /**
     * Method update_custom_meta
     *
     * @param string $post_id post id.
     * @param array $metadata meta data.
     *
     * @return void
     */
    public static function update_custom_meta ( $post_id, $metadata ) {
        $get_video_attr = false;

        if ( isset( $metadata['jnews_single_post'] ) ) {
            if ( isset( $metadata['jnews_single_post']['format'] ) ) {
				set_post_format( $post_id, sanitize_text_field( $metadata['jnews_single_post']['format'] ) );
			}

            if ( isset( $metadata['jnews_single_post']['sponsored_post'] ) ) {
                update_post_meta( $post_id, 'jnews_sponsor_post', sanitize_text_field( $metadata['jnews_single_post']['sponsored_post'] ) );
			}
    
            if ( isset( $metadata['jnews_single_post']['format'] ) && 'video' === $metadata['jnews_single_post']['format'] && isset( $metadata['jnews_single_post']['video'] ) ) {
                $attribute = get_post_meta( $post_id, 'jnews_video_cache', true ); /*  get old video attribute */
    
                /* only fetch video attribut if new url is different from old video attribute */
                if ( ! isset( $attribute['url'] ) || ( isset( $attribute['url'] ) && $attribute['url'] != $metadata['jnews_single_post']['video'] ) ) {
                    $new_attribute = VideoAttribute::getInstance()->get_video_attribute( $metadata['jnews_single_post']['video'] );
                    $result        = self::save_attribute_to_post( $new_attribute, $post_id );
                    VideoAttribute::getInstance()->remove_attachment( $attribute, $post_id );
                    if ( ! empty( $result['video_option'] ) ) {
                        $get_video_attr = true;
                        $metadata['jnews_single_post']['jnews_video_option_group'][0] = $result['video_option'];
                        update_post_meta( $post_id, 'jnews_single_post', $metadata['jnews_single_post'] );
                        update_post_meta( $post_id, 'jnews_video_option', jnews_sanitize_output( $result['video_option'] ) );
                    }
    
                    if ( $result['featured_image'] && ! empty( $result['featued_image'] ) ) {
                        $metadata['featured_media'] = $result['featued_image'];
                    }
                }
            }
    
            if ( isset( $metadata['jnews_single_post']['jnews_video_option_group'] ) && ! empty( $metadata['jnews_single_post']['jnews_video_option_group'] ) ) {
                if ( ! empty( $metadata['jnews_single_post']['jnews_video_option_group'][0] ) && ! $get_video_attr ) {
                    update_post_meta( $post_id, 'jnews_video_option', jnews_sanitize_output( $metadata['jnews_single_post']['jnews_video_option_group'][0] ) );
                }
            }

            if ( isset( $metadata['jnews_single_post']['jpwt_enable_post_donation'] ) ) {
                update_post_meta( $post_id, 'jpwt_enable_post_donation', (bool) $metadata['jnews_single_post']['jpwt_enable_post_donation'] );
            } elseif ( isset( $metadata['jnews_single_post']['jpwt_disable_post_donation'] ) ) {
                update_post_meta( $post_id, 'jpwt_disable_post_donation', (bool) $metadata['jnews_single_post']['jpwt_disable_post_donation'] );
            }
        } 
    }

    /**
	 * Method to save video attribute to post meta
	 *
	 * @param object $attribute video attribute.
	 * @param object $post_id post id.
	 *
	 * @return array
	 */
	public static function save_attribute_to_post( $attribute, $post_id ) {

		$result = array(
			'featured_image' => false,
			'video_option'   => array(),
		);

		// save thumbnail first.
		if ( isset( $attribute['thumbnail'] ) && ! get_post_thumbnail_id( $post_id ) ) {
			$attachment_id = VideoAttribute::getInstance()->save_to_media_library( $post_id, $attribute['thumbnail'] );
			set_post_thumbnail( $post_id, $attachment_id );
			$attribute['thumbnail']   = $attachment_id;
			$result['featured_image'] = $attachment_id;
		}

		// save duration.
		if ( isset( $attribute['duration'] ) && ! empty( $attribute['duration'] ) ) {
			$result['video_option']['video_duration'] = $attribute['duration'];
		}

		// save preview.
		if ( isset( $attribute['video_preview'] ) && ! empty( $attribute['video_preview'] ) ) {
			add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 99, 0 );
			$attachment = VideoAttribute::getInstance()->save_to_media_library( $post_id, $attribute['video_preview'] );
			remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array', 99 );
			$result['video_option']['video_preview'] = wp_get_attachment_url( $attachment );
			$attribute['video_preview']              = $attachment;
		}

		update_post_meta( $post_id, 'jnews_video_cache', $attribute );

		return $result;
	}
}
