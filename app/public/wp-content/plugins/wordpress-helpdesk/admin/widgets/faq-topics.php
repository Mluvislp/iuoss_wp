<?php

class FAQ_Topics extends WP_Widget {

	public function __construct() {
		$settings = array('description' => __('FAQ Topics', 'wordpress-helpdesk'));
		parent::__construct( false, 'FAQ Topics', $settings );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters('widget_title', $instance['title'] );
		$orderby = $instance['orderby'];
		$order = $instance['order'];
		$max_topics = $instance['max_topics'];
		$hideempty = isset($instance['hideempty']) ? $instance['hideempty'] : 0;
		$showsubcategories = isset($instance['showsubcategories']) ? $instance['showsubcategories'] : 0;

		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

		$termArgs = array(
            'taxonomy'      => 'faq_topics',
            'parent'		=> 0,
            'hide_empty'    => $hideempty,
            'orderby'       => $orderby,
            'order'         => $order,
            'number'		=> $max_topics,
        );
		$userRole = 'guest';
		$userId = get_current_user_id();
		if(!empty($userId)) {

			$user = get_userdata($userId);
			if(!empty($user) && isset($user->roles)) {
				$userRole = array_shift($user->roles);
			}
		}

        $topics = get_terms($termArgs);

		if(!empty($topics)) {
			echo '<ul class="wordpress-helpdesk-topic-list wordpress-helpdesk-faq-list">';
			foreach ($topics as $topic) {

		        $termUserRoles = get_term_meta($topic->term_id, 'wordpress_helpdesk_user_roles', true);
		        if(!empty($termUserRoles) && !in_array($userRole, $termUserRoles)) {
		            continue;
			 	}

		        $topic_image = get_term_meta($topic->term_id, 'wordpress_helpdesk_image');
		        if(!empty($topic_image) && isset($topic_image[0]['url'])) {
		            $topic_icon = '<img src="' . $topic_image[0]['url'] . '" alt="' . $topic->name . '" class=" wordpress-helpdesk-faq-topic-icon">';
		        } else {

		            $topic_icon = get_term_meta($topic->term_id, 'wordpress_helpdesk_icon');
		            if (isset($topic_icon) && !empty($topic_icon)) {
		                $topic_icon = '<i class="' . $topic_icon[0] . ' fa-4x wordpress-helpdesk-faq-topic-icon" aria-hidden="true"></i>';
		            } else {
		                $topic_icon = '<i class="fa fa-file-alt fa-4x wordpress-helpdesk-faq-topic-icon" aria-hidden="true"></i>';
		            }
		        }

				echo '<li><a href="' . get_term_link($topic->term_id) . '">' . $topic_icon . $topic->name . '</a>';

		        if($showsubcategories) {

					$termArgs = array(
			            'taxonomy'      => 'faq_topics',
			            'parent'		=> $topic->term_id,
			            'hide_empty'    => $hideempty,
			            'orderby'       => $orderby,
			            'order'         => $order,
			            'number'		=> $max_topics,
			        );

			        $topicChildren = get_terms($termArgs);

		        	if(!empty($topicChildren)) {

		        		echo '<ul class="wordpress-helpdesk-topic-list wordpress-helpdesk-faq-list wordpress-helpdesk-topic-children-list">';

						foreach ($topicChildren as $topicChild) {

							$topicChild_icon = get_term_meta($topicChild->term_id, 'wordpress_helpdesk_icon');
					        if (isset($topicChild_icon) && !empty($topicChild_icon)) {
					            $topicChild_icon = $topicChild_icon[0];
					        } else {
					            $topicChild_icon = 'fa fa-file-alt';
					        }

					        echo '<li><a href="' . get_term_link($topicChild->term_id) . '"><i class="' . $topicChild_icon . ' fa-1x" aria-hidden="true"></i>' . $topicChild->name . '</a></li>';
				        }

				        echo '</ul>';
		        	}
		        }

		        echo '</li>';
			}
        	echo '</ul>';
		}

		echo $args['after_widget'];
	}


	public function update( $new_instance, $old_instance ) 
	{
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['orderby'] = ( ! empty( $new_instance['orderby'] ) ) ? $new_instance['orderby'] : '';
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? $new_instance['order'] : '';
		$instance['max_topics'] = ( ! empty( $new_instance['max_topics'] ) ) ? $new_instance['max_topics'] : '-1';
		$instance['hideempty'] = isset( $new_instance['hideempty'] ) ? 1 : false;
		$instance['showsubcategories'] = isset( $new_instance['showsubcategories'] ) ? 1 : false;

		return $instance;
	}

	public function form( $instance ) 
	{

		$title = isset($instance['title']) ? $instance['title'] : __('Topics', 'wordpress-helpdesk');
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:', 'wordpress-helpdesk') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr( $title ) . '" />';

		$max_topics = isset($instance['max_topics']) ? $instance['max_topics'] : 99;
		echo '<p><label for="' . $this->get_field_id('max_topics') . '">' . __('Number of max. Topics:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('max_topics') . '" name="' . $this->get_field_name('max_topics') . '" type="number" value="' . $max_topics . '" />';

		$orderbys = array(
			'order' => __('Menu Order', 'wordpress-helpdesk'),
			'name' => __('Name', 'wordpress-helpdesk'),
			'slug' => __('Slug', 'wordpress-helpdesk'),
			'term_group' => __('Term_group', 'wordpress-helpdesk'),
			'term_id' => __('Term_id', 'wordpress-helpdesk'),
			'id' => __('Id', 'wordpress-helpdesk'),
			'description' => __('Description', 'wordpress-helpdesk'),
			'parent' => __('Parent', 'wordpress-helpdesk'),
			'count' => __('Count', 'wordpress-helpdesk'),
		);

		echo '<p><label for="' . $this->get_field_id('orderby') . '">' . __('Order By:', 'wordpress-helpdesk') . '</label>';
		echo '<select name="' . $this->get_field_name('orderby') . '" class="widefat">';
		echo '<option value="">Select a Order Key</option>';
		$selectedOrderby = isset($instance['orderby']) ? $instance['orderby'] : 'order';

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

		$hideempty = isset($instance['hideempty']) ? $instance['hideempty'] : '0';
		?>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'hideempty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hideempty' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $hideempty ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hideempty' ) ); ?>"><?php _e( 'Hide Empy Topics', 'wordpress-helpdesk' ); ?></label>
		</p>

		<?php
		$showsubcategories = isset($instance['showsubcategories']) ? $instance['showsubcategories'] : '0';
		?>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'showsubcategories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'showsubcategories' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $showsubcategories ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'showsubcategories' ) ); ?>"><?php _e( 'Show subcategories', 'wordpress-helpdesk' ); ?></label>
		</p>

		<?php
	}
}
