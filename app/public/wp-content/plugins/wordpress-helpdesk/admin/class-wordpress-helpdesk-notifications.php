<?php

class WordPress_Helpdesk_Notifications extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    protected $headers;
    protected $tpl;
    /**
     * Construct Notifications Class
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
     * Init Notifications (set Mail template)
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

        $tpl = $this->get_option('supportMailTemplate');
        
        $supportName = $this->get_option('supportName');
        $supportMail = $this->get_option('supportMail');
        $supportLogo = $this->get_option('supportLogo');
        $supportFooter = $this->get_option('supportFooter');

        $supportLogoURL = isset($supportLogo['url']) ? $supportLogo['url'] : '';

        $tpl = str_replace('{{support_name}}', $supportName, $tpl);
        $tpl = str_replace('{{support_logo}}', $supportLogoURL, $tpl);
        $tpl = str_replace('{{footer}}', $supportFooter, $tpl);
        $tpl = str_replace('{{ticket_link_text}}', __('View Ticket', 'wordpress-helpdesk'), $tpl);

        $this->headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To:' . $supportName. ' <' . $supportMail . '>',
            'From:' . $supportName. ' <' . $supportMail . '>',
        );

        $this->tpl = $tpl;
    }

    /**
     * Ticket Created Notification
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $new_status [description]
     * @param   [type]                       $old_status [description]
     * @param   [type]                       $post       [description]
     * @return  [type]                                   [description]
     */
    public function ticket_created($new_status, $old_status, $post)
    {
        if ($post->post_status == "auto-draft") {
            return false;
        }

        if ($post->post_type !== "ticket") {
            return false;
        }

        if ($new_status !== "publish") {
            return false;
        }

        if ($new_status === $old_status) {
            return false;
        }

        if (!$this->get_option('notificationsNewTicket')) {
            return false;
        }

        $this->subject = __('New Ticket created', 'wordpress-helpdesk');

        $this->id = $post->ID;
        $this->title = $post->post_title;
        $this->content = $this->content = '<p><b>' . sprintf(__('A Ticket with the ID %s has been created', 'wordpress-helpdesk'), $post->ID) . ':</b></p>' . $post->post_content;
        $this->link = get_permalink($post->ID);

        $to = array();
        if ($this->get_option('notificationsNewTicketReporter')) {
            $author = get_userdata($post->post_author)->data;
            $to[] = $author->display_name . '<' . $author->user_email . '>';
        }

        if ($this->get_option('notificationsNewTicketAgent')) {
            $assignedAgent = get_post_meta($post->ID, 'agent', true);
            if(!empty($assignedAgent)) {
                $author = get_userdata($assignedAgent);
                $to[] = $author->display_name . '<' . $author->user_email . '>';
            }
        }
        
        $additionalUsers = $this->get_option('notificationsNewTicketUsers');
        if (!empty($additionalUsers)) {
            foreach ($additionalUsers as $additionalUser) {
                $author = get_userdata($additionalUser)->data;
                $to[] = $author->display_name . '<' . $author->user_email . '>';
            }
        }

        $this->to = $to;
        
        $this->sendMail();
    }

    /**
     * Terms changes (status changed)
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $object_id [description]
     * @param   [type]                       $tt_id     [description]
     * @param   [type]                       $taxonomy  [description]
     * @return  [type]                                  [description]
     */
    public function terms_changed($object_id, $tt_id, $taxonomy)
    {
        if (get_post_type($object_id) !== "ticket") {
            return false;
        }

        $newTerm = get_term($tt_id);
        $taxonomy = get_taxonomy($newTerm->taxonomy);

        if (!$this->get_option('notifications' . $taxonomy->label . 'Change')) {
            return false;
        // Only show 1 Taxonomy Change!
        } else {
            $oldTerms = get_the_terms($object_id, $taxonomy->name);
            if (is_array($oldTerms)) {
                foreach ($oldTerms as $oldTerm) {
                    if ($oldTerm->term_id == $newTerm->term_id) {
                        continue;
                    }
                    $post = get_post($object_id);
                    
                    $this->subject =  sprintf(__('%s changed from %s to %s', 'wordpress-helpdesk'), $taxonomy->label, $oldTerm->name, $newTerm->name);

                    $this->id = $post->ID;
                    $this->title = $post->post_title;
                    $this->content = sprintf(__('The %s has changed from %s to %s', 'wordpress-helpdesk'), $taxonomy->label, $oldTerm->name, $newTerm->name);
                    $this->link = get_permalink($post->ID);

                    $to = array();
                    if ($this->get_option('notifications' . $taxonomy->label . 'ChangeReporter')) {
                        $author = get_userdata($post->post_author)->data;
                        $to[] = $author->display_name . '<' . $author->user_email . '>';
                    }

                    if ($this->get_option('notifications' . $taxonomy->label . 'ChangeAgent')) {
                        $assignedAgent = get_post_meta($post->ID, 'agent', true);
                        if(!empty($assignedAgent)) {
                            $author = get_userdata($assignedAgent);
                            $to[] = $author->display_name . '<' . $author->user_email . '>';
                        }
                    }
                    
                    $additionalUsers = $this->get_option('notifications' . $taxonomy->label . 'ChangeUsers');
                    if (!empty($additionalUsers)) {
                        foreach ($additionalUsers as $additionalUser) {
                            $author = get_userdata($additionalUser)->data;
                            $to[] = $author->display_name . '<' . $author->user_email . '>';
                        }
                    }
                    $this->to = $to;
                    
                    $this->sendMail();
                }
            } else {
                $post = get_post($object_id);
                
                $this->subject =  sprintf(__('%s changed to: %s', 'wordpress-helpdesk'), $taxonomy->label, $newTerm->name);

                $this->id = $post->ID;
                $this->title = $post->post_title;
                $this->content = sprintf(__('The %s changed to: %s', 'wordpress-helpdesk'), $taxonomy->label, $newTerm->name);
                $this->link = get_permalink($post->ID);

                $to = array();
                if ($this->get_option('notifications' . $taxonomy->label . 'ChangeReporter')) {
                    $author = get_userdata($post->post_author)->data;
                    $to[] = $author->display_name . '<' . $author->user_email . '>';
                }

                if ($this->get_option('notifications' . $taxonomy->label . 'ChangeAgent')) {
                    $assignedAgent = get_post_meta($post->ID, 'agent', true);
                    if(!empty($assignedAgent)) {
                        $author = get_userdata($assignedAgent);
                        $to[] = $author->display_name . '<' . $author->user_email . '>';
                    }
                }
                
                $additionalUsers = $this->get_option('notifications' . $taxonomy->label . 'ChangeUsers');
                if (!empty($additionalUsers)) {
                    foreach ($additionalUsers as $additionalUser) {
                        $author = get_userdata($additionalUser)->data;
                        $to[] = $author->display_name . '<' . $author->user_email . '>';
                    }
                }
                $this->to = $to;
                
                $this->sendMail();
            }
        }

        return true;
    }

    /**
     * Comment Created Notification
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $id      [description]
     * @param   [type]                       $comment [description]
     * @return  [type]                                [description]
     */
    public function comment_created($id, $comment)
    {
        if (!isset($comment->comment_post_ID) || empty($comment->comment_post_ID)) {
            return false;
        }

        $postID = $comment->comment_post_ID;
        $post = get_post($postID);

        if ($post->post_type !== "ticket") {
            return false;
        }

        if (!$this->get_option('notificationsCommentAdded')) {
            return false;
        }

        $this->subject = __('Comment added', 'wordpress-helpdesk');

        $this->id = $post->ID;
        $this->title = $post->post_title;
        $this->content = '<p><b>' . sprintf(__('New comment added by %s', 'wordpress-helpdesk'), $comment->comment_author) . ':</b></p>' . wpautop( $comment->comment_content );
        $this->link = get_permalink($post->ID);

        $to = array();
        
        $additionalUsers = $this->get_option('notificationsCommentAddedUsers');
        if (!empty($additionalUsers)) {
            foreach ($additionalUsers as $additionalUser) {
                $author = get_userdata($additionalUser)->data;
                $to[] = $author->display_name . '<' . $author->user_email . '>';
            }
        }

        // Reporter commented
        if ($comment->user_id == $post->post_author) {
            if ($this->get_option('notificationsCommentAddedAgent')) {
                $assignedAgent = get_post_meta($post->ID, 'agent', true);
                if(!empty($assignedAgent)) {
                    $author = get_userdata($assignedAgent);
                    $to[] = $author->display_name . '<' . $author->user_email . '>';
                }
            }
        // Manager commented
        } else {
            if ($this->get_option('notificationsCommentAddedReporter')) {
                $author = get_userdata($post->post_author)->data;
                $to[] = $author->display_name . '<' . $author->user_email . '>';
            }
        }

        $this->to = $to;
        $this->sendMail();

        return true;
    }

    /**
     * Assigned Agent changed Notification
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $meta_id     [description]
     * @param   [type]                       $object_id   [description]
     * @param   [type]                       $meta_key    [description]
     * @param   [type]                       $_meta_value [description]
     * @return  [type]                                    [description]
     */
    public function agent_changed($meta_id, $object_id, $meta_key, $_meta_value)
    {
        if ($meta_key !== "agent") {
            return false;
        }

        if (!$this->get_option('notificationsAgentChanged')) {
            return false;
        }

        $agentBeforeID = get_post_meta($object_id, 'agent', true);
        $agentAfterID = $_meta_value;

        if ($agentBeforeID == $agentAfterID) {
            return false;
        }

        if (empty($agentBeforeID)) {
            $agent_before_name = __('Unassigned', 'wordpress-helpdesk');
            $agent_before_email = '';
        } else {
            $agentBefore = get_userdata($agentBeforeID)->data;
            $agent_before_name = $agentBefore->display_name;
            $agent_before_email = $agentBefore->user_email;
        }

        if (empty($agentAfterID)) {
            $agent_after_name = __('Unassigned', 'wordpress-helpdesk');
            $agent_after_email = '';
        } else {
            $agentAfter = get_userdata($agentAfterID)->data;
            $agent_after_name = $agentAfter->display_name;
            $agent_after_email = $agentAfter->user_email;
        }

        $this->subject = sprintf(__('Assigned to %s', 'wordpress-helpdesk'), $agent_after_name);

        $post = get_post($object_id);
        $this->id = $post->ID;
        $this->title = $post->post_title;
        $this->content = '<p>' . sprintf(__('This ticket has been assigned from %s to %s', 'wordpress-helpdesk'), $agent_before_name, $agent_after_name) . '</p>';
        $this->link = get_permalink($post->ID);

        $to = array();
        if ($this->get_option('notificationsAgentChangedReporter')) {
            $author = get_userdata($post->post_author)->data;
            $to[] = $author->display_name . '<' . $author->user_email . '>';
        }

        if ($this->get_option('notificationsAgentChangedAgent')) {
            if ($agent_after_email !== "") {
                $author = $agentAfter;
                $to[] = $agent_after_name . '<' . $agent_after_email . '>';
            }
        }
        
        $additionalUsers = $this->get_option('notificationsAgentChangedUsers');
        if (!empty($additionalUsers)) {
            foreach ($additionalUsers as $additionalUser) {
                $author = get_userdata($additionalUser)->data;
                $to[] = $author->display_name . '<' . $author->user_email . '>';
            }
        }
        $this->to = $to;
        $this->sendMail();
    }

    /**
     * Assigned Agent added Notification
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $meta_id    [description]
     * @param   [type]                       $object_id  [description]
     * @param   [type]                       $meta_key   [description]
     * @param   [type]                       $meta_value [description]
     * @return  [type]                                   [description]
     */
    public function agent_added($meta_id, $object_id, $meta_key, $meta_value)
    {
        if ($meta_key !== "agent") {
            return false;
        }

        if (!$this->get_option('notificationsAgentChanged')) {
            return false;
        }

        $agentID = $meta_value;
        $agent = get_userdata($agentID)->data;

        $this->subject = sprintf(__('Assigned to %s', 'wordpress-helpdesk'), $agent->display_name);

        $post = get_post($object_id);
        $this->id = $post->ID;
        $this->title = $post->post_title;
        $this->content = '<p>' . sprintf(__('This ticket has been assigned to %s', 'wordpress-helpdesk'), $agent->display_name) . '</p>';
        $this->link = get_permalink($post->ID);

        $to = array();
        if ($this->get_option('notificationsAgentChangedReporter')) {
            $author = get_userdata($post->post_author)->data;
            $to[] = $author->display_name . '<' . $author->user_email . '>';
        }

        if ($this->get_option('notificationsAgentChangedAgent')) {
            $author = $agent;
            $to[] = $author->display_name . '<' . $author->user_email . '>';
        }
        
        $additionalUsers = $this->get_option('notificationsAgentChangedUsers');
        if (!empty($additionalUsers)) {
            foreach ($additionalUsers as $additionalUser) {
                $author = get_userdata($additionalUser)->data;
                $to[] = $author->display_name . '<' . $author->user_email . '>';
            }
        }
        $this->to = $to;
        $this->sendMail();
    }

    /**
     * Send out notification Mail
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function sendMail()
    {
        $subject = sprintf( __('[Ticket: %d] ', 'wordpress-helpdesk'), $this->id ) . $this->title . ' – ' . $this->subject;

        if(function_exists('icl_object_id')) {

            $current_lang = apply_filters( 'wpml_current_language', null );
            $this->link = apply_filters( 'wpml_permalink', $this->link, $current_lang );
        }


        $search = array('{{content}}' , '{{ticket_link}}', '{{title}}');
        $replace = array(
            $this->content,
            $this->link,
            sprintf( __('[Ticket: %d] ', 'wordpress-helpdesk'), $this->id ) . $this->title
        );

        $content = str_replace($search, $replace, $this->tpl);

        if (wp_mail($this->to, $subject, $content, $this->headers)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Turn off the default Comment added on Post / Page WP functionality
     * Settings > Discussions
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $maybe_notify [description]
     * @param   [type]                       $comment_ID   [description]
     * @return  [type]                                     [description]
     */
    public function disable_default_notifications($maybe_notify, $comment_ID)
    {
        $comment = get_comment($comment_ID);
        $post = get_post($comment->comment_post_ID);

        if ($post->post_type == "ticket") {
            $maybe_notify = false;
        }

        return $maybe_notify;
    }
}
