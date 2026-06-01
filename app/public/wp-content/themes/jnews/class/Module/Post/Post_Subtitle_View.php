<?php
/**
 * @author : Jegtheme
 */

namespace JNews\Module\Post;

use JNews\Single\SinglePost;

/**
 * Post Subtitle Widget View
 */
class Post_Subtitle_View extends PostViewAbstract {
	/**
	 * Render Module Backend
	 *
	 * @param array  $attr Attribute.
	 * @param string $column_class Column Class.
	 *
	 * @return string HTML
	 */
	public function render_module_back( $attr, $column_class ) {
		return "<div {$this->element_id($attr)} class='jeg_custom_subtitle_wrapper {$attr['scheme']} {$attr['el_class']} {$this->get_vc_class_name()}'>
                <h2 class=\"jeg_post_subtitle\">Sed lorem nisi, facilisis vitae maximus eu, dignissim vel leo. Ut venenatis sem rutrum ligula facilisis.</h2>
            </div>";
	}

	/**
	 * Render Module Frontend
	 *
	 * @param array  $attr Attribute.
	 * @param string $column_class Column Class.
	 *
	 * @return string HTML
	 */
	public function render_module_front( $attr, $column_class ) {
		$single = SinglePost::getInstance();
		$single->set_post_id( get_the_ID() );

		if ( ! $single->is_subtitle_empty() ) {
			return "<div {$this->element_id($attr)} class='jeg_custom_subtitle_wrapper {$attr['scheme']} {$attr['el_class']} {$this->get_vc_class_name()}'>
                    <h2 class=\"jeg_post_subtitle\">{$single->render_subtitle()}</h2>
                </div>";
		}
	}
}
