<?php

class WordPress_Helpdesk_Slack extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    protected $client;
    /**
     * Slack Integration Constructor
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
     * Init Slack Integration with option credentials
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

        $username = $this->get_option('supportName');
        $webhokURL = $this->get_option('integrationsSlackWebhokURL');
        $channel = $this->get_option('integrationsSlackChannel');
        $icon = $this->get_option('integrationsSlackIcon');

        $settings = array(
            'username' => $username,
            'channel' => $channel,
            'link_names' => true
        );

        if(isset($icon['url']) && !empty($icon['url'])) {
            $settings['icon'] = $icon['url'];
        }

        $this->client = new Maknz\Slack\Client($webhokURL, $settings);
    }

    /**
     * Slack information when Ticket created
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

        if (!$this->get_option('integrationsSlackNewTicket')) {
            return false;
        }

        $author = get_userdata($post->post_author)->data;

        $this->attach = array(
            "fallback" => sprintf(__('New Ticket created: %s', 'wordpress-helpdesk'), htmlspecialchars($post->post_title)),
            "author_name" => $author->display_name,
            "author_link" => $author->user_url,
            "title" => htmlspecialchars(sprintf(__('[Ticket: %s] - %s', 'wordpress-helpdesk'), $post->ID, $post->post_title)),
            "title_link" => get_permalink($post->ID),
            "text" => sprintf(__('New Ticket created: %s', 'wordpress-helpdesk'), htmlspecialchars($post->post_content)),
        );
        
        $this->send();
    }

    /**
     * Slack information when Comment created
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

        if (!$this->get_option('integrationsSlackCommentAdded')) {
            return false;
        }

        $this->attach = array(
            "fallback" => sprintf(__('New Comment added on Ticket %s', 'wordpress-helpdesk'), htmlspecialchars($post->post_title)),
            "author_name" => $comment->comment_author,
            "author_link" => $comment->comment_author_url,
            "title" => htmlspecialchars(sprintf(__('[Ticket: %s] - %s', 'wordpress-helpdesk'), $post->ID, $post->post_title)),
            "title_link" => get_permalink($post->ID),
            "text" => sprintf(__('New Comment added by %s:', 'wordpress-helpdesk'), $comment->comment_author) . ' ' . htmlspecialchars($comment->comment_content),
        );

        $this->send();

        return true;
    }

    /**
     *  Slack information when Ticket status changed
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

        if (!$this->get_option('integrationsSlack' . $taxonomy->label . 'Change')) {
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
                    
                    $this->attach = array(
                        "fallback" => sprintf(__('[Ticket: %s] %s changed', 'wordpress-helpdesk'), $post->ID, htmlspecialchars($taxonomy->label)),
                        "color" => "#36a64f",
                        "title" => htmlspecialchars(sprintf(__('[Ticket: %s] - %s', 'wordpress-helpdesk'), $post->ID, $post->post_title)),
                        "title_link" => get_permalink($post->ID),
                        "text" => htmlspecialchars(sprintf(__('%s changed from %s to: %s', 'wordpress-helpdesk'), $taxonomy->label, $oldTerm->name, $newTerm->name)),
                    );
                    $this->send();
                }
            } else {
                $post = get_post($object_id);
                
                $this->attach = array(
                    "fallback" => sprintf(__('[Ticket: %s] %s changed', 'wordpress-helpdesk'), $post->ID, htmlspecialchars($taxonomy->label)),
                    "color" => "#36a64f",
                    "title" => htmlspecialchars(sprintf(__('[Ticket: %s] - %s', 'wordpress-helpdesk'), $post->ID, $post->post_title)),
                    "title_link" => get_permalink($post->ID),
                    "text" => htmlspecialchars(sprintf(__('%s changed to: %s', 'wordpress-helpdesk'), $taxonomy->label, $newTerm->name)),
                );
                $this->send();
            }
        }

        return true;
    }

    /**
     * Slack information when assigned Agent has changed
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

        if (!$this->get_option('integrationsSlackAgentChanged')) {
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
        

        $post = get_post($object_id);
        $this->attach = array(
            "fallback" => sprintf(__('[Ticket: %s] Agent changed', 'wordpress-helpdesk'), $post->ID),
            "color" => "#36a64f",
            "title" => htmlspecialchars(sprintf(__('[Ticket: %s] - %s', 'wordpress-helpdesk'), $post->ID, $post->post_title)),
            "title_link" => get_permalink($post->ID),
            "text" => htmlspecialchars(sprintf(__('Assigned from %s to %s', 'wordpress-helpdesk'), $agent_before_name, $agent_after_name)),
        );
        $this->send();
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

        $post = get_post($object_id);
        $this->attach = array(
            "fallback" => sprintf(__('[Ticket: %s] Agent assigned', 'wordpress-helpdesk'), $post->ID),
            "color" => "#36a64f",
            "title" => htmlspecialchars(sprintf(__('[Ticket: %s] - %s', 'wordpress-helpdesk'), $post->ID, $post->post_title)),
            "title_link" => get_permalink($post->ID),
            "text" => htmlspecialchars(sprintf(__('Assigned to %s', 'wordpress-helpdesk'), $agent->display_name)),
        );
        $this->send();
    }

    /**
     * Send Message to Slack Service
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function send()
    {
        if (!$this->get_option('integrationsSlack')) {
            return false;
        }
        
        $this->client->attach($this->attach)->send();
    }
}
