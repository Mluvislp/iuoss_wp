<?php

class FAQ_Live_Search extends WP_Widget {

	public function __construct() {
		$settings = array('description' => __('Use this to show the live ajax search for FAQs', 'wordpress-helpdesk'));
		parent::__construct( false, 'FAQ Live Search', $settings );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters('widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		?>
		<div class="wordpress-helpdesk faq">
            <div class="wordpress-helpdesk-row">
                <div class="wordpress-helpdesk-col-sm-12">
                    <form method="get" class="wordpress-helpdesk-faq-searchform" action="<?php echo site_url('/'); ?>" autocomplete="s">
                        <input style="display:none" type="text" name="fakeusernameremembered"/>
                        <input style="display:none" type="password" name="fakepasswordremembered"/>
                        <input type="search" id="wordpress-helpdesk-faq-searchterm" class="wordpress-helpdesk-faq-searchterm form-control" name="s" placeholder="<?php esc_html_e('Search', 'wordpress-helpdesk') ?>">
                        <input type="hidden" name="post_type" value="faq" />
                        <button type="submit" class="searchform-submit">
                            <span class="fa fa-search" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e('Submit', 'wordpress-helpdesk') ?></span>
                        </button>
                        <div class="wordpress-helpdesk-faq-live-search-results" style="display: none;"></div>
                    </form>
                </div>
            </div>
        </div>
		<?php

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) 
	{
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

	public function form( $instance ) 
	{
		$title = isset($instance['title']) ? $instance['title'] : __('Search FAQs');
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr( $title ) . '" />';
	}
}