<?php

class FAQ_Dynamic_Posts extends WP_Widget {

	public function __construct() {
		$settings = array('description' => __('Use this if you want to show FAQs dynamically based on the current users FAQ page.', 'wordpress-helpdesk'));
		parent::__construct( false, 'FAQ Dynamic Posts', $settings );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters('widget_title', $instance['title'] );
		$dynamic_title = isset($instance['dynamic_title']) ? $instance['dynamic_title'] : __('Popular FAQs %s', 'wordpress-helpdesk');
		$orderby = $instance['orderby'];
		$order = $instance['order'];
		$max_faqs = $instance['max_faqs'];

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];

        $query_args = array(
            'post_type' => 'faq',
            'orderby' => $orderby,
            'order' => $order,
            'hierarchical' => false,
            'posts_per_page' => $max_faqs,
            'suppress_filters' => false
        );

		if(in_array($orderby, array("faq_popularity", "faq_likes", "faq_dislikes"))) {
			$query_args['orderby'] = 'meta_value_num';
			$query_args['meta_query'] = array(
				array( 
					'key' => 'faq_popularity'
				)
			);
		}

		$queried_object = get_queried_object();
		if(is_archive()) {
			if(isset($queried_object->taxonomy) && $queried_object->taxonomy == "faq_topics") {
				$query_args['tax_query'] = array(
	                array(
		                'taxonomy' => 'faq_topics',
		                'field' => 'id',
		                'terms' => $queried_object->term_id,
	                )
	            );
	            $topic = get_term($queried_object->term_id)->name;
			}
		}

		if(is_single() && (get_post_type() === "faq")) {
			$topics = wp_get_post_terms($queried_object->ID, 'faq_topics');

			if(!empty($topics)) {
				$query_args['tax_query'] = array(
	                array(
		                'taxonomy' => 'faq_topics',
		                'field' => 'id',
		                'terms' => array_map(function ($ar) {return $ar->term_id;}, $topics),
		                'operator' => 'IN',
	                )
	            );
	            $topic = get_term($topics[0])->name;
            }
		}		

		if ( ! empty( $dynamic_title ) && !empty( $topic ) ) {
			echo $args['before_title'] . sprintf($dynamic_title, $topic) . $args['after_title'];
		} elseif(!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
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
		$instance['dynamic_title'] = ( ! empty( $new_instance['dynamic_title'] ) ) ? strip_tags( $new_instance['dynamic_title'] ) : '';
		$instance['orderby'] = ( ! empty( $new_instance['orderby'] ) ) ? $new_instance['orderby'] : '';
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? $new_instance['order'] : '';
		$instance['max_faqs'] = ( ! empty( $new_instance['max_faqs'] ) ) ? $new_instance['max_faqs'] : '';

		return $instance;
	}

	public function form( $instance ) 
	{

		$title = isset($instance['title']) ? $instance['title'] : __('Popular FAQs', 'wordpress-helpdesk');
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:', 'wordpress-helpdesk') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr( $title ) . '" />';

		$dynamic_title = isset($instance['dynamic_title']) ? $instance['dynamic_title'] : __('Popular FAQs %s', 'wordpress-helpdesk');
		echo '<p><label for="' . $this->get_field_id('dynamic_title') . '">' . __('Dynamic Title:', 'wordpress-helpdesk') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('dynamic_title') . '" name="' . $this->get_field_name('dynamic_title') . '" type="text" value="' . esc_attr( $dynamic_title ) . '" />';

		$max_faqs = isset($instance['max_faqs']) ? $instance['max_faqs'] : 5;
		echo '<p><label for="' . $this->get_field_id('max_faqs') . '">' . __('Number of max. FAQs:', 'wordpress-helpdesk') . '</label>';
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

		echo '<p><label for="' . $this->get_field_id('orderby') . '">' . __('Order By:', 'wordpress-helpdesk') . '</label>';
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

		echo '<p><label for="' . $this->get_field_id('order') . '">' . __('Order:', 'wordpress-helpdesk') . '</label>';
		echo '<select name="' . $this->get_field_name('order') . '" class="widefat">';
		echo '<option value="">' . __('Select a Order:', 'wordpress-helpdesk') . '</option>';
		$selectedOrder = isset($instance['order']) ? $instance['order'] : 'ASC';

		foreach ($orders as $key => $order) {
			$selected = "";
			if($selectedOrder == $key) {
				$selected = 'selected="selected"';
			}

			echo '<option value="' . $key . '" ' . $selected . '>' . $order . '</option>';
		}

		echo '</select></p>';
	}
}