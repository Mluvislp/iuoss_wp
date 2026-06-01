<?php

class WordPress_Helpdesk_Ticket_Post_Type extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;

    /**
     * Construct Custom Ticket Post Type Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $plugin_name [description]
     * @param   [type]                       $version     [description]
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Init Ticket post type class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function init()
    {
        global $wordpress_helpdesk_options;

        $this->options = $wordpress_helpdesk_options;

        $this->register_ticket_post_type();
        $this->register_ticket_taxonomy();
        $this->add_custom_meta_fields();

        add_action('restrict_manage_posts', array($this, 'filter_post_type_by_taxonomy' ));
        add_filter('parse_query', array($this, 'convert_id_to_term_in_query' ));
    }

    /**
     * Make filtering for custom Taxonomies possible
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function filter_post_type_by_taxonomy()
    {
        global $typenow;
        $post_type = 'ticket'; // change to your post type
        
        $taxonomies = array('ticket_status', 'ticket_type', 'ticket_system', 'ticket_priority'); // change to your taxonomy
        foreach ($taxonomies as $taxonomy) {
            if ($typenow == $post_type) {
                $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
                $info_taxonomy = get_taxonomy($taxonomy);
                wp_dropdown_categories(array(
                    'show_option_all' => __("Show All {$info_taxonomy->label}"),
                    'taxonomy'        => $taxonomy,
                    'name'            => $taxonomy,
                    'orderby'         => 'name',
                    'selected'        => $selected,
                    'show_count'      => true,
                    'hide_empty'      => true,
                ));
            };
        }
    }

    /**
     * Build the query for custom tax filtering
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $query [description]
     * @return  [type]                              [description]
     */
    public function convert_id_to_term_in_query($query)
    {
        global $pagenow;
        $post_type = 'ticket'; // change to your post type

        $taxonomies = array('ticket_status', 'ticket_type', 'ticket_system', 'ticket_priority'); // change to your taxonomy
        foreach ($taxonomies as $taxonomy) {
            $q_vars    = &$query->query_vars;
            if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
                $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
                $q_vars[$taxonomy] = $term->slug;
            }
        }
    }

    /**
     * Register Ticket Post Type
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function register_ticket_post_type()
    {
        $redirect_base = "";
        $supportMyTicketsPage = $this->get_option('supportMyTicketsPage');
        if (!empty($supportMyTicketsPage)) {
            $redirect_base = get_post_field('post_name', $supportMyTicketsPage) . '/';
        }

        $newTicketsCount = 0;
        if(is_admin()) {
            $newTicketsCount = get_option('helpdesk_new_tickets_count');
        }

        $singular = __('Ticket', 'wordpress-helpdesk');
        $plural = __('Tickets', 'wordpress-helpdesk');

        if(!$newTicketsCount || $newTicketsCount == 0) {
            $all_tickets = sprintf(__('All %s', 'wordpress-helpdesk'), $plural);
        } else {
            $all_tickets = sprintf(__('All %s', 'wordpress-helpdesk'), $plural) . 
                        ' <span class="update-plugins count-' . $newTicketsCount . '">
                            <span class="plugin-count" aria-hidden="true">' . $newTicketsCount . '</span><span class="screen-reader-text">' . $newTicketsCount . ' notifications</span></span>';
        }

        $labels = array(
            'name' => __('Tickets', 'wordpress-helpdesk'),
            'all_items' => $all_tickets,
            'singular_name' => $singular,
            'add_new' => sprintf(__('New %s', 'wordpress-helpdesk'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-helpdesk'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-helpdesk'), $singular),
            'new_item' => sprintf(__('New %s', 'wordpress-helpdesk'), $singular),
            'view_item' => sprintf(__('View %s', 'wordpress-helpdesk'), $singular),
            'search_items' => sprintf(__('Search %s', 'wordpress-helpdesk'), $plural),
            'not_found' => sprintf(__('No %s found', 'wordpress-helpdesk'), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in trash', 'wordpress-helpdesk'), $plural),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'show_ui' => true,
            'menu_position' => 70,
            'show_in_rest' => true,
            'rewrite' => array(
                'slug' => $redirect_base . 'ticket',
                'with_front' => false
            ),
            'query_var' => 'tickets',
            'supports' => array('title', 'editor', 'author', 'revisions', 'comments', 'page-attributes'),
            'menu_icon' => 'dashicons-sos',
            'capability_type'     => array('ticket','tickets'),
            'capabilities' => array(
                'publish_posts' => 'publish_tickets',
                'edit_posts' => 'edit_tickets',
                'edit_others_posts' => 'edit_others_tickets',
                'delete_posts' => 'delete_tickets',
                'delete_others_posts' => 'delete_others_tickets',
                'delete_published_posts' => 'delete_published_tickets',
                'read_private_posts' => 'read_private_tickets',
                'edit_post' => 'edit_ticket',
                'delete_post' => 'delete_ticket',
                'read_post' => 'read_ticket',
                'edit_published_posts' => 'edit_published_tickets'
            ),
            'map_meta_cap' => true,
            'taxonomies' => array('ticket_status', 'ticket_type', 'ticket_system'),
        );

        register_post_type('ticket', $args);
    }

    /**
     * Register custom Ticket Taxonomies
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function register_ticket_taxonomy()
    {
        // Ticket Category
        $singular = __('Status', 'wordpress-helpdesk');
        $plural = __('Status', 'wordpress-helpdesk');

        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Search %s', 'wordpress-helpdesk'), $plural),
            'all_items' => sprintf(__('All %s', 'wordpress-helpdesk'), $plural),
            'parent_item' => sprintf(__('Parent %s', 'wordpress-helpdesk'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'wordpress-helpdesk'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-helpdesk'), $singular),
            'update_item' => sprintf(__('Update %s', 'wordpress-helpdesk'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-helpdesk'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'wordpress-helpdesk'), $singular),
            'menu_name' => $plural,
        );

        $args = array(
                'labels' => $labels,
                'public' => false,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array('slug' => 'ticket-status'),
                'capabilities' => array(
                    'manage_terms' => 'manage_ticket_status',
                    'edit_terms' => 'edit_ticket_status',
                    'delete_terms' => 'delete_ticket_status',
                    'assign_terms' => 'assign_ticket_status',
                ),
        );

        register_taxonomy('ticket_status', 'ticket', $args);

        // Type
        $singular = __('Type', 'wordpress-helpdesk');
        $plural = __('Types', 'wordpress-helpdesk');

        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Search %s', 'wordpress-helpdesk'), $plural),
            'all_items' => sprintf(__('All %s', 'wordpress-helpdesk'), $plural),
            'parent_item' => sprintf(__('Parent %s', 'wordpress-helpdesk'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'wordpress-helpdesk'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-helpdesk'), $singular),
            'update_item' => sprintf(__('Update %s', 'wordpress-helpdesk'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-helpdesk'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'wordpress-helpdesk'), $singular),
            'menu_name' => $plural,
        );

        $args = array(
                'labels' => $labels,
                'public' => false,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array('slug' => 'ticket-types'),
                'capabilities' => array(
                    'manage_terms' => 'manage_ticket_type',
                    'edit_terms' => 'edit_ticket_type',
                    'delete_terms' => 'delete_ticket_type',
                    'assign_terms' => 'assign_ticket_type',
                ),
        );

        register_taxonomy('ticket_type', 'ticket', $args);

        // Ticket System / Project
        $singular = __('Department', 'wordpress-helpdesk');
        $plural = __('Departments', 'wordpress-helpdesk');

        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Search %s', 'wordpress-helpdesk'), $plural),
            'all_items' => sprintf(__('All %s', 'wordpress-helpdesk'), $plural),
            'parent_item' => sprintf(__('Parent %s', 'wordpress-helpdesk'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'wordpress-helpdesk'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-helpdesk'), $singular),
            'update_item' => sprintf(__('Update %s', 'wordpress-helpdesk'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-helpdesk'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'wordpress-helpdesk'), $singular),
            'menu_name' => $plural,
        );

        $args = array(
                'labels' => $labels,
                'public' => false,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array('slug' => 'ticket-system'),
                'capabilities' => array(
                    'manage_terms' => 'manage_ticket_system',
                    'edit_terms' => 'edit_ticket_system',
                    'delete_terms' => 'delete_ticket_system',
                    'assign_terms' => 'assign_ticket_system',
                ),
        );

        register_taxonomy('ticket_system', 'ticket', $args);

        // Ticket Priority
        $singular = __('Priority', 'wordpress-helpdesk');
        $plural = __('Priorities', 'wordpress-helpdesk');

        $labels = array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Search %s', 'wordpress-helpdesk'), $plural),
            'all_items' => sprintf(__('All %s', 'wordpress-helpdesk'), $plural),
            'parent_item' => sprintf(__('Parent %s', 'wordpress-helpdesk'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'wordpress-helpdesk'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'wordpress-helpdesk'), $singular),
            'update_item' => sprintf(__('Update %s', 'wordpress-helpdesk'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'wordpress-helpdesk'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'wordpress-helpdesk'), $singular),
            'menu_name' => $plural,
        );

        $args = array(
                'labels' => $labels,
                'public' => false,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => array('slug' => 'ticket-priority'),
                'capabilities' => array(
                    'manage_terms' => 'manage_ticket_priority',
                    'edit_terms' => 'edit_ticket_priority',
                    'delete_terms' => 'delete_ticket_priority',
                    'assign_terms' => 'assign_ticket_priority',
                ),
        );

        register_taxonomy('ticket_priority', 'ticket', $args);
    }


    /**
     * Add custom ticket metaboxes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post_type [description]
     * @param   [type]                       $post      [description]
     */
    public function add_custom_metaboxes($post_type, $post)
    {
        add_meta_box('wordpress-helpdesk-agent', __('Ticket', 'wordpress-helpdesk') . ' ' . $post->ID, array($this, 'short_information'), 'ticket', 'side', 'high');
        add_meta_box('wordpress-helpdesk-merge', __('Merge Ticket', 'wordpress-helpdesk'), array($this, 'merge_ticket_metabox'), 'ticket', 'side', 'default');
        add_meta_box('wordpress-helpdesk-attachments', __('Attachments', 'wordpress-helpdesk'), array($this, 'attachments'), 'ticket', 'normal', 'default');

        if($this->get_option('enableSupportRating')) {
            add_meta_box('wordpress-helpdesk-feedback', __('Feedback:', 'wordpress-helpdesk'), array($this, 'feedback_metabox'), 'ticket', 'normal', 'low');
        }
    }

    /**
     * Display Metabox Short Information
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function short_information()
    {
        global $post;

        wp_nonce_field(basename(__FILE__), 'wordpress_helpdesk_meta_nonce');

        $this->get_meta_taxonomies($post);
        echo '<div class="wordpress-helpdesk-container">';
            $this->get_created($post);
            $this->get_assigned($post);
        echo '</div>';
        echo '<label for="website_url"><small>' . __('Website:', 'wordpress-helpdesk') . '</small></label>';
        $website_url = get_post_meta($post->ID, 'website_url', true);
        echo '<input name="website_url" type="text" value="' . $website_url . '" style="width: 100%;">';

        $source = get_post_meta($post->ID, 'source', true);
        echo '<input name="source" type="hidden" value="' . $source . '" style="width: 100%;">';

        $optionalFields = $this->get_option('fields' . $source . 'OptionalCustom');
        if(!empty($optionalFields)) {

            $optionalFields = array_filter($optionalFields);

            foreach ($optionalFields as $optionalField) {

                $optionalFieldLabel = esc_html($optionalField);
                $optionalFieldName = $this->slugify($optionalField);
                $optionalFieldValue = get_post_meta($post->ID, $optionalFieldName, true);

                if(!empty($optionalFieldValue)) {
                    echo '<label for="' . $optionalFieldName . '"><small>' . $optionalFieldLabel . ':</small></label>';
                    echo '<input name="' . $optionalFieldName . '" type="text" value="' . $optionalFieldValue . '" style="width: 100%;">';
                }
            }
        }
        
        $requiredFields = $this->get_option('fields' . $source . 'RequiredCustom');
        if(!empty($requiredFields)) {

            $requiredFields = array_filter($requiredFields);

            foreach ($requiredFields as $requiredField) {

                $requiredFieldLabel = esc_html($requiredField);
                $requiredFieldName = $this->slugify($requiredField);
                $requiredFieldValue = get_post_meta($post->ID, $requiredFieldName, true);

                if(!empty($requiredFieldValue)) {
                    echo '<label for="' . $requiredFieldName . '"><small>' . $requiredFieldLabel . ':</small></label>';
                    echo '<input name="' . $requiredFieldName . '" type="text" value="' . $requiredFieldValue . '" style="width: 100%;">';
                }
            }
        }

        if( ($source == "Envato")) {
            $this->get_envato($post);
        }

        if (class_exists('WooCommerce') && ($source == "WooCommerce")) {
            $this->get_woocommerce($post);
        }
    }

    /**
     * Display Metabox Merge Ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.4.3
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function merge_ticket_metabox()
    {
        global $post;

        $query_args = array(
            'post_type' => 'ticket',
            'orderby' => 'date',
            'order' => 'DESC',
            'hierarchical' => false,
            'posts_per_page' => -1,
            'post__not_in' => array( $post->ID )
        );
        $tickets = get_posts($query_args);
        
        $mergeURL = admin_url("edit.php");

        if(!empty($tickets)) {
            echo '<select name="merge_ticket_destination">';
            echo '<option value="">' . __('Select Ticket', 'wordpress-helpdesk') . '</option>';
            foreach ($tickets as $ticket) {
                echo '<option value="' . $ticket->ID . '">' . $ticket->post_title . ' (ID: ' . $ticket->ID . ')</option>';
            }
            echo '</select>';
        }
        ?>
        <button class="button button-primary button-large" href="<?php echo esc_url($mergeURL); ?>"><?php _e('Merge Now', 'wordpress-helpdesk'); ?></button>
        <?php
    }

    /**
     * Copy a ticket content to an FAQ
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.4.3
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function merge_ticket($sourceID, $destinationID)
    {

        if (empty($sourceID) || empty($destinationID)) {
            wp_die(__('No ticket to duplicate has been supplied!', 'wordpress-helpdesk'));
        }

        $sourcePost = get_post($sourceID);

        if (!empty($sourcePost)) {

            $attachment_ids = get_posts(array(
                'post_type' => 'attachment',
                'numberposts' => -1,
                'post_parent' => $sourcePost->ID,
            ));

            if(!empty($attachment_ids)) {
                foreach ($attachment_ids as $attachment_id) {
                    $attachment_id->post_parent = $destinationID;
                    wp_update_post($attachment_id);
                }
            }

            wp_insert_comment(array(
                'comment_content' => $sourcePost->post_content,
                'comment_post_ID' => $destinationID,
                'user_id' => $sourcePost->post_author

            ));

            $comments = get_comments('post_id=' . $sourceID);
            if(!empty($comments)) {
                foreach ($comments as $comment) {
                    $comment = (array) $comment;
                    $comment['comment_post_ID'] = $destinationID;
                    wp_insert_comment($comment);
                }
            }

            wp_delete_post($sourceID);
            wp_redirect(admin_url('post.php?action=edit&post=' . $destinationID));
            exit();
        } else {
            wp_die(__('Could not Merge Ticket.', 'wordpress-helpdesk'));
        }
    }

    /**
     * Get Meta Taxonomies
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post [description]
     * @return  [type]                             [description]
     */
    private function get_meta_taxonomies($post)
    {
        $status = get_the_terms($post->ID, 'ticket_status');
        if (!empty($status)) {
            $status_color = get_term_meta($status[0]->term_id, 'wordpress_helpdesk_color');
            if (isset($status_color[0]) && !empty($status_color[0])) {
                $status_color = $status_color[0];
            } else {
                $status_color = '#000000';
            }
            if (!empty($status)) {
                echo '<span class="wordpress-helpdesk-label wordpress-helpdesk-status-' . $status[0]->slug . '" style="background-color: ' . $status_color . '">' . $status[0]->name .'</span> ';
            }
        }

        $system = get_the_terms($post->ID, 'ticket_system');
        if (!empty($system)) {
            $system_color = get_term_meta($system[0]->term_id, 'wordpress_helpdesk_color');
            if (isset($system_color[0]) && !empty($system_color[0])) {
                $system_color = $system_color[0];
            } else {
                $system_color = '#000000';
            }
            if (!empty($system)) {
                echo '<span class="wordpress-helpdesk-label wordpress-helpdesk-system-' . $system[0]->slug . '" style="background-color: ' . $system_color . '">' . $system[0]->name .'</span> ';
            }
        }

        $type = get_the_terms($post->ID, 'ticket_type');
        if (!empty($type)) {
            $type_color = get_term_meta($type[0]->term_id, 'wordpress_helpdesk_color');
            if (isset($type_color[0]) && !empty($type_color[0])) {
                $type_color = $type_color[0];
            } else {
                $type_color = '#000000';
            }
            if (!empty($type)) {
                echo '<span class="wordpress-helpdesk-label wordpress-helpdesk-type-' . $type[0]->slug . '" style="background-color: ' . $type_color . '">' . $type[0]->name .'</span> ';
            }
        }

        $priority = get_the_terms($post->ID, 'ticket_priority');
        if (!empty($priority)) {
            $priority_color = get_term_meta($priority[0]->term_id, 'wordpress_helpdesk_color');
            if (isset($priority_color[0]) && !empty($priority_color[0])) {
                $priority_color = $priority_color[0];
            } else {
                $priority_color = '#000000';
            }
            if (!empty($priority)) {
                echo '<span class="wordpress-helpdesk-label wordpress-helpdesk-priority-' . $priority[0]->slug . '" style="background-color: ' . $priority_color . '">' . $priority[0]->name .'</span> ';
            }
        }
    }

    /**
     * Get the created information
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post [description]
     * @return  [type]                             [description]
     */
    private function get_created($post)
    {
        echo '<hr>';
        $author = get_userdata($post->post_author)->data;
        echo '<div class="wordpress-helpdesk-row">';
            echo '<div class="wordpress-helpdesk-col-sm-8">';
                echo '<small>' . __('Created on: ', 'wordpress-helpdesk') . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($post->post_date)) . '</small><br/>';
                echo '<small>' . __('Created by: ', 'wordpress-helpdesk') . $author->display_name . '</small><br/>';
                echo '<small> ' . $author->user_email . '</small><br/>';
            echo '</div>';
            echo '<div class="wordpress-helpdesk-col-sm-4">';
                echo get_avatar($post->post_author, 50);
            echo '</div>';
        echo '</div>';
        echo '<hr>';
    }

    /**
     * Get the assigned Information
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post [description]
     * @return  [type]                             [description]
     */
    private function get_assigned($post)
    {
        $args = array(
            'role__in' => array('helpdesk_agent', 'administrator', 'shop_manager')
        );
        $agents = get_users($args);
        $current_agent_id = get_post_meta($post->ID, 'agent', true);
        if (!empty($current_agent_id)) {
            $current_agent = get_userdata($current_agent_id)->data;

            echo '<div class="wordpress-helpdesk-row">';
                echo '<div class="wordpress-helpdesk-col-sm-8">';
                    echo '<small>' . __('Assigned to: ', 'wordpress-helpdesk') . $current_agent->display_name . '</small><br/>';
                    echo '<small> ' . $current_agent->user_email . '</small><br/>';
                echo '</div>';
                echo '<div class="wordpress-helpdesk-col-sm-4">';
                    echo get_avatar($current_agent_id, 50);
                echo '</div>';
            echo '</div>';
        }
        echo '<div class="wordpress-helpdesk-row">';
            echo '<div class="wordpress-helpdesk-col-sm-12">';
                echo '<select name="agent" id="agent" class="widefat">';
                echo '<option value="">' . __('Unassigned', 'wordpress-helpdesk') . '</option>';
                foreach ($agents as $agent) {
                    $agent_id = $agent->data->ID;
                    $agent_name = $agent->data->display_name;

                    $selected = '';
                    if ($agent_id == $current_agent_id) {
                        $selected = 'selected="selected"';
                    }

                    echo '<option value="' . $agent_id . '" ' . $selected . '>' . $agent_name . '</option>';
                }
                echo '</select>';
                echo '<hr>';
            echo '</div>';
        echo '</div>';
    }

    /**
     * Get the Envato Information
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post [description]
     * @return  [type]                             [description]
     */
    private function get_envato($post)
    {
        $purchase_code = get_post_meta($post->ID, 'purchase_code', true);
        echo '<hr>Envato<br/>';
        if (!empty($purchase_code)) {
            $purchase_data = $this->verifyPurchaseCode($purchase_code);

            if (empty($purchase_data)) {
                echo '<small style="color: red;">' . __('Purchase Code could not be verified!', 'wordpress-helpdesk') . '</small>';
            } else {
                echo '<a href="' . $purchase_data->item->url . '" target="_blank"><img src="' . $purchase_data->item->previews->landscape_preview->landscape_url . '" style="width: 100%; max-width: 100%;"></a><br/>';
                echo '<small>' . __('Item: ', 'wordpress-helpdesk') . '<a href="' . $purchase_data->item->url . '" target="_blank">' . $purchase_data->item->name . '</a></small><br/>';
                echo '<small>' . __('License: ', 'wordpress-helpdesk') . $purchase_data->license . '</small><br/>';
                echo '<small>' . __('Support Until:', 'wordpress-helpdesk') . $purchase_data->supported_until . '</a></small>';
            }
        }
        echo '<br/>';
        echo '<label for="purchase_code"><small>Purchase Code:</small></label>';
        $purchase_code = get_post_meta($post->ID, 'purchase_code', true);
        echo '<input name="purchase_code" type="text" value="' . $purchase_code . '" style="width: 100%;">';
    }

    /**
     * Verify Envato purchase Code
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $code [description]
     * @return  [type]                             [description]
     */
    private function verifyPurchaseCode($code)
    {
        if (!empty($this->get_option('integrationsEnvatoAPIKey')) && (!empty($this->get_option('integrationsEnvatoUsername')))) {
            $token = $this->get_option('integrationsEnvatoAPIKey');
            $username = $this->get_option('integrationsEnvatoUsername');
        } else {
            return false;
        }

        $envato = new DB_Envato($token);

        $purchase_data = $envato->call('/market/author/sale?code=' . $code);
        
        if (isset($purchase_data->error)) {
            return false;
        }

        return $purchase_data;
    }

    /**
     * Get WooCommerce Pages
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post [description]
     * @return  [type]                             [description]
     */
    private function get_woocommerce($post)
    {
        $this->get_orders($post);
        $this->get_products($post);
    }

    /**
     * Get WooCommerce Order Pages
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post [description]
     * @return  [type]                             [description]
     */
    public function get_orders($post)
    {
        $current_order = get_post_meta($post->ID, 'order', true);

        if (!empty($current_order)) {
            $order = wc_get_order($current_order);

            echo '<a href="'. admin_url('post.php?post=' . absint($current_order) . '&action=edit') .'" ><b>' . $order->post->post_title . '</b></a><br/>';
            echo 'ID: #' . $order->ID . '<br>';
            echo 'Order Status: ' . $order->post->post_status . '<br><br>';

            echo '<b>Products:</b><br>';
            foreach ($order-> get_items() as $item_key => $item_values) :
                $item_data = $item_values->get_data();

                $product_name = $item_data['name'];
                $quantity = $item_data['quantity'];

                echo $product_name . ' (Quantity: ' . $quantity . ')';
            endforeach;
        }
    }

    /**
     * Get WooCommerce Products
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post [description]
     * @return  [type]                             [description]
     */
    public function get_products($post)
    {
        $current_product = get_post_meta($post->ID, 'product', true);

        if (!empty($current_product)) {
            $product = wc_get_product($current_product);
            if($product) {
                echo 'Product:' . $product->get_name() . ' (' . $product->get_sku() . ')';
                echo '<input type="hidden" name="product" value="' . $product->get_id() . '">';
            }
        }
    }

    /**
     * Get Attachments Metabox
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function attachments()
    {
        global $post;

        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_parent' => $post->ID,
        ));
        
        if ($attachments) {
            echo '<ul>';

            foreach ($attachments as $attachment) {
                $attachment_id = $attachment->ID;
                $full_url = wp_get_attachment_url($attachment_id);
                $thumb_url = wp_get_attachment_thumb_url($attachment_id);

                $image_mime_types = array(
                    'image/png',
                    'image/jpeg',
                    'image/jpeg',
                    'image/jpeg',
                    'image/gif',
                    'image/bmp',
                    'image/vnd.microsoft.icon',
                    'image/tiff',
                    'image/tiff',
                    'image/svg+xml',
                    'image/svg+xml',
                );

                $isImage = false;
                if(in_array($attachment->post_mime_type, $image_mime_types)) {
                    $isImage = true;
                }

                $link = "";
                if($isImage) {
                    $link .= '<a class="is-image" href="' . $full_url . '" target="_blank">';
                        $link .= '<img src="' . $thumb_url . '" alt="">';
                    $link .= '</a>';
                } else {
                    $link .= '<a href="' . $full_url . '" target="_blank">';
                        $link .= '<i class="fa fa-download"></i> ' . $attachment->post_title;
                    $link .= '</a>';
                }
                
                echo '<li>' . $link . '</li>';
            }
            echo '</ul>';
        }
        
    }

    /**
     * Content of the Feedback metabox
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function feedback_metabox()
    {
        global $post;

        $feedback = get_post_meta($post->ID, 'feedback', true);

        if (empty($feedback)) {
            echo __("No feedback available", 'wordpress-helpdesk');
            return false;
        }
        echo $feedback;
    }

    /**
     * Save Custom Metaboxes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post_id [description]
     * @param   [type]                       $post    [description]
     * @return  [type]                                [description]
     */
    public function save_custom_metaboxes($post_id, $post)
    {
        global $post;
        
        if (!is_object($post)) {
            return;
        }

        // Is the user allowed to edit the post or page?
        if (!current_user_can('edit_post', $post->ID)) {
            return $post->ID;
        }

        if (!isset($_POST['wordpress_helpdesk_meta_nonce']) || !wp_verify_nonce($_POST['wordpress_helpdesk_meta_nonce'], basename(__FILE__))) {
            return;
        }

        if(isset($_POST['merge_ticket_destination']) && !empty($_POST['merge_ticket_destination'])) {
            $this->merge_ticket($post->ID, $_POST['merge_ticket_destination']);
        }

        $ticket_meta['agent'] = isset($_POST['agent']) ? $_POST['agent'] : '';
        $ticket_meta['website_url'] = isset($_POST['website_url']) ? $_POST['website_url'] : '';
        $ticket_meta['purchase_code'] = isset($_POST['purchase_code']) ? $_POST['purchase_code'] : '';
        $ticket_meta['order'] = isset($_POST['order']) ? $_POST['order'] : '';
        $ticket_meta['product'] = isset($_POST['product']) ? $_POST['product'] : '';
        $ticket_meta['source'] = isset($_POST['source']) ? $_POST['source'] : 'Simple';

        $optionalFields = $this->get_option('fields' . $ticket_meta['source'] . 'OptionalCustom');
        if(!empty($optionalFields)) {

            $optionalFields = array_filter($optionalFields);
            
            foreach ($optionalFields as $optionalField) {

                $optionalFieldLabel = esc_html($optionalField);
                $optionalFieldName = $this->slugify($optionalField);

                if(isset($_POST[$optionalFieldName]) && !empty($_POST[$optionalFieldName])) {
                    $ticket_meta[$optionalFieldName] = isset($_POST[$optionalFieldName]) ? $_POST[$optionalFieldName] : '';
                }
            }
        }
        
        $requiredFields = $this->get_option('fields' . $ticket_meta['source'] . 'RequiredCustom');
        if(!empty($requiredFields)) {

            $requiredFields = array_filter($requiredFields);

            foreach ($requiredFields as $requiredField) {

                $requiredFieldLabel = esc_html($requiredField);
                $requiredFieldName = $this->slugify($requiredField);

                if(isset($_POST[$optionalFieldName]) && !empty($_POST[$optionalFieldName])) {
                    $ticket_meta[$requiredFieldName] = isset($_POST[$requiredFieldName]) ? $_POST[$requiredFieldName] : '';
                }
            }
        }
        
        // Add values of $ticket_meta as custom fields
        foreach ($ticket_meta as $key => $value) { // Cycle through the $ticket_meta array!
            if ($post->post_type == 'revision') {
                return; // Don't store custom data twice
            }
            
            $value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
            update_post_meta($post->ID, $key, $value);
        }
    }

    /**
     * Add Custom Meta Field Color to System, Type, Status
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     */
    public function add_custom_meta_fields()
    {
        $prefix = 'wordpress_helpdesk_';
        $custom_taxonomy_meta_config = array(
            'id' => 'ticket_meta_box',
            'title' => 'Ticket Meta Box',
            'pages' => array('ticket_system', 'ticket_type', 'ticket_status', 'ticket_priority'),
            'context' => 'side',
            'fields' => array(),
            'local_images' => false,
            'use_with_theme' => false,
        );

        $custom_taxonomy_meta_fields = new Tax_Meta_Class($custom_taxonomy_meta_config);
        $custom_taxonomy_meta_fields->addText($prefix.'color', array('name' => __('Color', 'wordpress-helpdesk')));
        $custom_taxonomy_meta_fields->Finish();
    }

    /**
     * Access check for Tickets
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function access()
    {
        global $post;

        if (empty($post)) {
            if (isset($_GET['post']) && !empty($_GET['post'])) {
                $post = get_post($_GET['post']);
            }
        }

        if (!is_object($post)) {
            return true;
        }

        if ($post->post_type == 'ticket') {
            if (!is_user_logged_in()) {
                wp_die(
                    sprintf(__('Please <a href="%s" title="Login">login to view your tickets</a>', 'wordpress-helpdesk'), wp_login_url(get_permalink())),
                    '', 404);
                return false;
            }

            $current_user = wp_get_current_user();
            $roles = $current_user->roles;
            $role = array_shift($roles);
            $notAllowedRoles = array('helpdesk_reporter', 'subscriber', 'customer');

            if (intval($post->post_author) === intval($current_user->ID)) {
                return true;
            }

            if ($role == "helpdesk_agent") {
                $assignedAgent = get_post_meta($post->ID, 'agent', true);
                if (intval($assignedAgent) !== intval($current_user->ID)) {
                    wp_die('You are not assigned as an agent.', '', 404);
                }
            }

            if (in_array($role, $notAllowedRoles)) {
                wp_die('Not your ticket', '', 404);
            }
        }
    }

    /**
     * Filter not authore ones 
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $query [description]
     * @return  [type]                              [description]
     */
    public function filter_not_author_ones($query)
    {
        if(!function_exists('wp_get_current_user')) {
            return false;
        }

        $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $role = array_shift($roles);

        if(!is_admin()) {
            return;
        }

        if (isset($query->query['post_type']) && ($query->query['post_type'] == "ticket")) {

            if ($role == "helpdesk_reporter") {
                $query->set('author', $current_user->ID);
            }

            if ($role == "helpdesk_agent") {
                $query->set('meta_query', array(
                    array(
                        'key' => 'agent',
                        'value' => get_current_user_id(),
                        'compare' => '='
                    ),
                ));
            }
        }
    }

    /**
     * Modify the Title of Tickets to always have the ID in Front
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $title [description]
     * @param   [type]                       $id    [description]
     * @return  [type]                              [description]
     */
    public function modify_title($title, $id)
    {
        if (empty($title)) {
            return $title;
        }

        if (get_post_type($id) !== "ticket") {
            return $title;
        }

        return sprintf( __('[Ticket: %s] %s', 'wordpress-helpdesk'), $id, $title);
    }

    /**
     * Show all Authors
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $query_args [description]
     * @param   [type]                       $r          [description]
     */
    public function add_subscribers_to_dropdown( $query_args, $r ) {
        $query_args['who'] = '';
        return $query_args;
    }

    /**
     * Load custom FAQ Topics Template
     * Override this via a file in your theme called archive-faq_topic.php
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $template [description]
     * @return  [type]                                 [description]
     */
    public function ticket_template( $template ) 
    {
        global $post;

        if($this->get_option('useThemesTemplate')) {
            return $template;
        }

        if(is_single()) {
            if($post->post_type == "ticket") {
                $theme_files = array('single-ticket.php', 'wordpress-helpdesk/single-ticket.php');
                $exists_in_theme = locate_template($theme_files, false);
                if ( $exists_in_theme != '' ) {
                    return $exists_in_theme;
                } else {
                    return plugin_dir_path(__FILE__) . 'views/single-ticket.php';
                }
            }
        }
        return $template;
    }

    public function ticket_columns( $columns ) 
    {

        $columns = array();
        $columns["cb"] = '<input type="checkbox" />';
        $columns["title"] = __('Ticket', 'wordpress-helpdesk');
        $columns["from"] = __('From', 'wordpress-helpdesk');
        $columns["assigned"] = __('Assigned To', 'wordpress-helpdesk');
        $columns["taxonomy-ticket_status"] = __('Status', 'wordpress-helpdesk');
        $columns["satisfied"] = __('Satisfied', 'wordpress-helpdesk');
        $columns["taxonomy-ticket_type"] = __('Type', 'wordpress-helpdesk');
        $columns["taxonomy-ticket_system"] = __('Department', 'wordpress-helpdesk');
        $columns["taxonomy-ticket_priority"] = __('Priority', 'wordpress-helpdesk');
        $columns["comments"] = __('<span class="vers comment-grey-bubble" title="Comments"><span class="screen-reader-text">Comments</span></span>', 'wordpress-helpdesk');
        $columns["date"] = __('Date', 'wordpress-helpdesk');

        return $columns;
    }

    public function ticket_columns_content( $column, $post_id )
     {
        global $post;

        switch( $column ) {
            case 'assigned' :

                $agentID = get_post_meta( $post_id, 'agent', true );
                if ( empty( $agentID ) ) {
                    echo __( 'Unassigned', 'wordpress-helpdesk' );
                } else {
                    $author = get_userdata($agentID);
                    $url = admin_url('edit.php?post_type=ticket&author=' . $author->ID);
                    echo '<div class="wordpress-helpdesk-row">';
                        echo '<div class="wordpress-helpdesk-col-sm-3">';
                            echo get_avatar($agentID, 50, '', '', array('class' => 'helpdesk-avatar'));
                        echo '</div>';
                        echo '<div class="wordpress-helpdesk-col-sm-8">';
                            printf( '<a href="%s">%s</a>', $url, $author->display_name );
                            printf( '<br/><small>%s</small>',  $author->user_email);
                        echo '</div>';
                    echo '</div>';
                }
                break;
            case 'from' :

                if ( empty( $post->post_author ) ) {
                    echo __( 'No Author', 'wordpress-helpdesk' );
                } else {
                    $author = get_userdata($post->post_author)->data;
                    if(!empty($author)) {
                        $url = admin_url('edit.php?post_type=ticket&author=' . $author->ID);
                        echo '<div class="wordpress-helpdesk-row">';
                            echo '<div class="wordpress-helpdesk-col-sm-3">';
                                echo get_avatar($post->post_author, 50, '', '', array('class' => 'helpdesk-avatar'));
                            echo '</div>';
                            echo '<div class="wordpress-helpdesk-col-sm-8">';
                                printf( '<a href="%s">%s</a>', $url, $author->display_name );
                                printf( '<br/><small>%s</small>',  $author->user_email);
                            echo '</div>';
                        echo '</div>';
                    } else {
                        echo __( 'No Author', 'wordpress-helpdesk' );
                    }
                }
                break;
            case 'satisfied' :

                $satisfied = get_post_meta($post->ID, 'satisfied', true);

                if($satisfied == "yes") {
                    echo __('<i class="fa fa-smile-o fa-2x" style="color: #4CAF50;"></i>');
                } elseif($satisfied == "no") {
                    echo __('<i class="fa fa-frown-o fa-2x" style="color: #F44336;"></i>');
                } else {
                    echo __('<i class="fa fa-pause fa-2x" style="color: #aeaeae;"></i>');
                }
                break;
            default :
                break;
        }
    }

    public function update_new_tickets_count($post_ID)
    {
        if (get_post_type($post_ID) !== "ticket") {
            return false;
        }

        $defaultStatus = $this->get_option('defaultStatus');
        if(!$defaultStatus) {
            return false;
        }

        $newTicketsArgs = array(
            'post_type' => 'ticket',
            'posts_per_page' => -1, 
            'tax_query' => array(
                array(
                    'taxonomy'      => 'ticket_status',
                    'field'         => 'term_id', //This is optional, as it defaults to 'term_id'
                    'terms'         => $defaultStatus,
                    // 'operator'      => 'IN', // Possible values are 'IN', 'NOT IN', 'AND'.
                )
            )
        );
        $newTickets = get_posts($newTicketsArgs);

        update_option('helpdesk_new_tickets_count', count($newTickets));
    }

    public function close_old_tickets()
    {
        if(!$this->get_option('supportCloseTicketsAutomatically')) {
            return false;
        }

        $supportCloseTicketsAutomaticallyDays = $this->get_option('supportCloseTicketsAutomaticallyDays');
        if($supportCloseTicketsAutomaticallyDays < 1) {
            return false;
        }

        $defaultSolvedStatus = $this->get_option('defaultSolvedStatus');
        if(!$defaultSolvedStatus) {
            return false;
        }

        $currentTime = time();
        $lastTimeChecked = get_option('helpdesk_solved_tickets_check');
        if(!$lastTimeChecked) {
            $lastTimeChecked = update_option('helpdesk_solved_tickets_check', $currentTime);
        } else {
            $nextTimeCheck = $lastTimeChecked + (24 * 60 * 60);
            if($currentTime < $nextTimeCheck) {
                return false;
            } else {
                $lastTimeChecked = update_option('helpdesk_solved_tickets_check', $currentTime);
            }
        }

        $allTicketsQuery = array(
            'post_type' => 'ticket',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'ticket_status',
                    'field' => 'id',
                    'terms' => $defaultSolvedStatus,
                    'operator' => 'NOT IN',
                )
            )
        );

        $allTickets = get_posts($allTicketsQuery);
        if(empty($allTickets)) {
            return false;
        }

        foreach ($allTickets as $ticket) {

            $args = array(
                'number' => '1',
                'post_id' => $ticket->ID
            );
            $comments = get_comments($args);
            if(isset($comments[0]) && !empty($comments[0]) && is_object($comments[0])) {
                $lastUpdate = $comments[0]->comment_date;
            } else {
                $lastUpdate = $ticket->post_modified;
            }

            if(empty($lastUpdate)) {
                continue;
            }

            $from = strtotime($lastUpdate);
            $today = time();
            $difference = $today - $from;
            $daysOpen = floor($difference / 86400);  // (60 * 60 * 24)

            if($daysOpen > $supportCloseTicketsAutomaticallyDays) {
                wp_set_object_terms($ticket->ID, intval($defaultSolvedStatus), 'ticket_status');
            }
        }
    }


    public function ticket_solved_btn()
    {
        if(!isset($_POST['helpdesk_ticket_solved'])) {
            return false;
        }

        if(!isset($_POST['helpdesk_ticket']) || empty($_POST['helpdesk_ticket'])) {
            return false;
        }

        $ticket_id = absint($_POST['helpdesk_ticket']);
        $ticket = get_post($ticket_id);

        $current_user = wp_get_current_user();
        $current_agent_id = get_post_meta($ticket_id, 'agent', true);
        if( ( intval($ticket->post_author) !== intval($current_user->ID) ) && ( intval($current_agent_id) !== intval($current_user->ID) ) ) {
            return false;
        }

        $defaultSolvedStatus = $this->get_option('defaultSolvedStatus');
        if(!$defaultSolvedStatus) {
            return false;
        }

        wp_set_object_terms($ticket->ID, intval($defaultSolvedStatus), 'ticket_status');
    }

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '_', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '_');

        // remove duplicate -
        $text = preg_replace('~-+~', '_', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
        return 'n-a';
        }

        return $text;
    }
}