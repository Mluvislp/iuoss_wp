<?php

class WordPress_Helpdesk_Desktop_Notifications extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;

    /**
     * Construct Desktop Notifications Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @param   string                         $plugin_name
     * @param   string                         $version
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Init Desktop Notification
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
    }

    /**
     * Get All comment IDs for current users tickets
     * This due to check if new comments will be added (intersection)
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_comment_ids()
    {
        $response = array(
            'status' => 'false',
            'message' => '',
            'comment_ids' => array(),
        );
        if(!is_user_logged_in()) {
            $response['message'] = "Not logged in";
            die(json_encode($response));
        }

        $args = array(
            'post_type' => 'ticket',
            'orderby' => 'date',
            'order' => 'DESC',
            'hierarchical' => false,
            'posts_per_page' => -1,
            'author' => get_current_user_id(),
        );
        $tickets = get_posts($args);

        $comments = array();
        foreach ($tickets as $ticket) {
            $args = array(
                'author__not_in' => array(get_current_user_id()),
                'post_id' => $ticket->ID,
            );
            $comments = array_merge($comments, get_comments($args));
        }

        $comment_ids = array();
        foreach ($comments as $comment) {
            $comment_ids[] = $comment->comment_ID;
        }

        $response = array(
            'status' => 'true',
            'comment_ids' => $comment_ids,
        );

        die(json_encode($response));
    }

    /**
     * Get new comments (Intersection of old & just added)
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_new_comments()
    {
        $response = array(
            'status' => 'false',
        );

        $args = array(
            'post_type' => 'ticket',
            'orderby' => 'date',
            'order' => 'DESC',
            'hierarchical' => false,
            'posts_per_page' => -1,
            'author' => get_current_user_id(),
        );

        $old_comment_ids = isset($_POST['comment_ids']) ? $_POST['comment_ids'] : array();

        $comments = array();
        $tickets = get_posts($args);
        foreach ($tickets as $ticket) {
            $args = array(
                'author__not_in' => array(get_current_user_id()),
                'post_id' => $ticket->ID,
                'comment__not_in' => $old_comment_ids
            );

            // If 1 new comment is found break and push it out!
            $comments = get_comments($args);
            if (isset($comments[0]) && !empty($comments[0])) {
                $response = array(
                    'status' => 'true',
                    'title' => strip_tags(sprintf(__('New comment on %s', 'wordpress-helpdesk'), $ticket->post_title)),
                    'body' => strip_tags($comments[0]->comment_content),
                    'link' => substr(wp_make_link_relative(get_permalink($ticket->ID)), 1),
                    'comment_id' => $comments[0]->comment_ID,
                );
                break;
            };
        }

        die(json_encode($response));
    }
}
