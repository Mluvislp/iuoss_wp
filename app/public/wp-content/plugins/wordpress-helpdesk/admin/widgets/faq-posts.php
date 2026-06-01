<?php

class FAQ_Posts extends WP_Widget {

	public function __construct() {
		$settings = array('description' => __('Show some FAQs.', 'wordpress-helpdesk'));
		parent::__construct( false, 'FAQ Posts', $settings );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters('widget_title', $instance['title'] );
		$topic = $instance['topic'];
		$orderby = $instance['orderby'];
		$order = $instance['order'];
		$max_faqs = $instance['max_faqs'];

		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

        $query_args = array(
            'post_type' => 'faq',
            'orderby' => $orderby,
            'order' => $order,
            'hierarchical' => false,
            'posts_per_page' => $max_faqs,
            'suppress_filters' => false
        );

		if($topic !== "all") {
			$query_args['tax_query'] = array(
                array(
                'taxonomy' => 'faq_topics',
                'field' => 'id',
                'terms' => $topic,
                )
            );
		}

		if(in_array($orderby, array("faq_popularity", "faq_likes", "faq_dislikes"))) {
			$query_args['orderby'] = 'meta_value_num';
			$query_args['meta_query'] = array(
				array( 
					'key' => $orderby
				)
			);
		}

		$faqs = get_posts($query_args);
		if(!empty($faqs)) {
			echo '<ul class="wordpress-helpdesk-faq-list">';
			foreach ($faqs as $faq) {
				echo '<li><i class="fa fa-file-alt fa-1x"></i> <a href="' . get_permalink($faq->ID) . '">' . $faq->post_title . '</a></li>';
			}
        	echo '</ul>';
		}

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) 
	{
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['topic'] = ( ! empty( $new_instance['topic'] ) ) ? $new_instance['topic'] : '';
		$instance['orderby'] = ( ! empty( $new_instance['orderby'] ) ) ? $new_instance['orderby'] : '';
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? $new_instance['order'] : '';
		$instance['max_faqs'] = ( ! empty( $new_instance['max_faqs'] ) ) ? $new_instance['max_faqs'] : '';

		return $instance;
	}

	public function form( $instance ) 
	{

		$title = isset($instance['title']) ? $instance['title'] : __('Popular FAQs');
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr( $title ) . '" />';

		$max_faqs = isset($instance['max_faqs']) ? $instance['max_faqs'] : 5;
		echo '<p><label for="' . $this->get_field_id('max_faqs') . '">' . __('Number of max. FAQs:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('max_faqs') . '" name="' . $this->get_field_name('max_faqs') . '" type="number" value="' . $max_faqs . '" />';

		$orderbys = array(
			'none' => 'No order',
			'faq_popularity' => 'Popularity (Views)',
			'faq_likes' => 'Likes',
			'faq_dislikes' => 'Dislikes',
			'ID' => 'Order by post id. Note the capitalization',
			'author' => 'Order by author',
			'title' => 'Order by title',
			'date' => 'Order by date',
			'modified' => 'Order by last modified date',
			'parent' => 'Order by post/page parent id',
			'rand' => 'Random order',
			'comment_count' => 'Order by number of comments',
		);

		echo '<p><label for="' . $this->get_field_id('orderby') . '">' . __('Order By:') . '</label>';
		echo '<select name="' . $this->get_field_name('orderby') . '" class="widefat">';
		echo '<option value="">Select a Order Key</option>';
		$selectedOrderby = isset($instance['orderby']) ? $instance['orderby'] : 'faq_popularity';

		foreach ($orderbys as $key => $orderby) {
			$selected = "";
			if($selectedOrderby == $key) {
				$selected = 'selected="selected"';
			}

			echo '<option value="' . $key . '" ' . $selected . '>' . $orderby . '</option>';
		}

		echo '</select></p>';

		$orders = array(
			'ASC' => 'ASC',
			'DESC' => 'DESC',
		);

		echo '<p><label for="' . $this->get_field_id('order') . '">' . __('Order:') . '</label>';
		echo '<select name="' . $this->get_field_name('order') . '" class="widefat">';
		echo '<option value="">Select a Order</option>';
		$selectedOrder = isset($instance['order']) ? $instance['order'] : 'ASC';

		foreach ($orders as $key => $order) {
			$selected = "";
			if($selectedOrder == $key) {
				$selected = 'selected="selected"';
			}

			echo '<option value="' . $key . '" ' . $selected . '>' . $order . '</option>';
		}

		echo '</select></p>';

		$topics = get_terms('faq_topics', array('hide_empty' => false) );
		if(!empty($topics)) {
			echo '<p><label for="' . $this->get_field_id('topic') . '">' . __('Select a Topic if you want:') . '</label>';
			echo '<select name="' . $this->get_field_name('topic') . '" class="widefat">';
			echo '<option value="all">All</option>';
			$selectedTopic = intval( isset($instance['topic']) ? $instance['topic'] : 'all');

			foreach ($topics as $topic) {
				$selected = "";
				if($selectedTopic == $topic->term_id) {
					$selected = 'selected="selected"';
				}

				echo '<option value="' . $topic->term_id . '" ' . $selected . '>' . $topic->name . '</option>';
			}

			echo '</select></p>';
		}
	}
}
