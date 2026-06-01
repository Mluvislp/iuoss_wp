<?php

class WordPress_Helpdesk_Comments extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;

    public $errors = array();
    public $success = array();
    public $comment_id = '';

    /**
     * Construct Comments Processor
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
     * Init Comments
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return [type] [description]
     */
    public function init()
    {
        global $wordpress_helpdesk_options;
        $this->options = $wordpress_helpdesk_options;
    }

    /**
     * Comment Editor Changes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $settings  [description]
     * @param   [type]                       $editor_id [description]
     * @return  [type]                                  [description]
     */
    public function comment_editor($settings, $editor_id)
    {
        if($editor_id !== "replycontent") {
            return $settings;  
        }

        $screen = get_current_screen();
        if($screen->post_type !== "ticket") {
            return $settings;
        }
        // $settings['tinymce'] = true;
        $settings['media_buttons'] = true;
        return $settings;
    }

    /**
     * Enable comment editor on frontend
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function enable_comment_editor($field)
    {
        global $post;

        if (!is_single() || !is_singular('ticket')) {
            return $field;
        }

        if(!isset($post->ID)) {
            return;
        }

        $agentID = get_post_meta($post->ID, 'agent', true);
        $agentName = '';
        if($agentID) {
            $agentUser = get_userdata($agentID);
            if($agentUser) {
                $agentName = $agentUser->display_name;
            }
        }
        $userName = get_the_author();                

        $defaultReply = '';

        if($this->get_option('enableReplyTemplate') && current_user_can('edit_posts')) {
            $defaultReply = $this->get_option('savedRepliesReplyTemplate');
            if(!empty($defaultReply)) {
                $defaultReply = str_replace( array('{user_name}', '{agent_name}'), array($userName, $agentName), $defaultReply);
            }
        }

        ob_start();
        $settings = array(
            'textarea_rows' => 15,
            'media_buttons' => false,
            'teeny' => true,
            'drag_drop_upload' => true,
        );
        wp_editor($defaultReply, 'comment', $settings);
        $editor = ob_get_contents();
        ob_end_clean();

        //make sure comment media is attached to parent post
        $editor = str_replace('post_id=0', 'post_id=' . get_the_ID(), $editor);

        return $editor;
    }

    /**
     * Allow Comments for all tickets
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $option [description]
     * @return [type]         [description]
     */
    public function allow_comments_for_all_ticket($option)
    {
        global $post;

        if(empty($post)) {
            return $option;
        }

        if ($post->post_type == "ticket") {
            return 0;
        }

        return $option;
    }

    public function close_comments_on_solved($open, $post_id)
    {
        global $post;
        if($post && ( isset($post->post_type) && $post->post_type !== "ticket") ) {
            return $open;
        }

        if(!$this->get_option('commentsClosedWhenTicketClosed')) {
            return $open;
        }

        $status = get_the_terms($post_id, 'ticket_status');
        $solvedStatus = absint($this->get_option('defaultSolvedStatus') );

        if(empty($status)) {
            return $open;
        }

        if(empty($solvedStatus)) {
            return $open;
        }

        if($status[0]->term_id == $solvedStatus) {
            $open = false;
        }

        return $open;
    }

    public function not_notify_on_ticket_reply($maybe_notify, $comment_id )
    {

        $comment = get_comment($comment_id);
        if(!$comment) {
            return $maybe_notify;
        }

        if(get_post_type($comment->comment_post_ID) === "ticket") {
            return false;
        }

        return $maybe_notify;
    }
}
