<?php

class WordPress_Helpdesk_Log extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    /**
     * Construct Log Class
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
     * Init Log (set Mail template)
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
     * Add the Log metabox for Ticket pages
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     */
    public function add_log_metabox()
    {
        add_meta_box('wordpress-helpdesk-log', __('History', 'wordpress-helpdesk'), array($this, 'log_metabox'), 'ticket', 'normal', 'low');
    }

    /**
     * Content of the Log metabox
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function log_metabox()
    {
        global $post;

        $log = get_post_meta($post->ID, 'wordpress_helpdesk_log', true);

        if (empty($log)) {
            echo "No log available";
            return false;
        }

        foreach ($log as $log_entry) {

            echo date_i18n(get_option('date_format'), $log_entry['da']) . ': <b>' . $log_entry['ti'] . '</b>';

            $user = get_userdata($log_entry['by']);
            if($user) {
                echo  __(' by ', 'wordpress-helpdesk') . $user->data->display_name;
            }
            
            echo '<br/>';
            if(!empty($log_entry['co'])) {
                echo __('Content: ', 'wordpress-helpdesk') . $log_entry['co'] . '<br/>';
            }
            echo '<hr>';
        }
    }

    /**
     * Ticket Created Log
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

        $log = array(
            'ti' => __('New Ticket created', 'wordpress-helpdesk'),
            'by' => $post->post_author,
            'co' => $post->post_content,
            'da' => current_time('timestamp', 1),
        );

        $this->saveLog($post->ID, $log);
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

        $oldTerms = get_the_terms($object_id, $taxonomy->name);
        if (is_array($oldTerms)) {
            foreach ($oldTerms as $oldTerm) {
                if ($oldTerm->term_id == $newTerm->term_id) {
                    continue;
                }
                $log = array(
                    'ti' => sprintf(__('%s changed from %s to %s', 'wordpress-helpdesk'), $taxonomy->label, $oldTerm->name, $newTerm->name),
                    'by' => get_current_user_id(),
                    'co' => '',
                    'da' => current_time('timestamp', 1),
                );
                $this->saveLog($object_id, $log);
            }
        } else {
            $log = array(
                'ti' => sprintf(__('%s changed to: %s', 'wordpress-helpdesk'), $taxonomy->label, $newTerm->name),
                'by' => get_current_user_id(),
                'co' => '',
                'da' => current_time('timestamp', 1),
            );
            $this->saveLog($object_id, $log);
        }

        return true;
    }

    /**
     * Comment Created Log
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

        $log = array(
            'ti' => __('Comment added', 'wordpress-helpdesk'),
            'by' => $comment->user_id,
            'co' => $comment->comment_content,
            'da' => current_time('timestamp', 1),
        );

        $this->saveLog($post->ID, $log);
    }

    /**
     * Assigned Agent changed Log
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

        if (empty($agentAfter)) {
            $agentAfter = $agentBefore;
            $from = __('Unassigned');
        } else {
            $from = $agentBefore->display_name;
        }

        $log = array(
            'ti' => sprintf(__('Assigned to %s', 'wordpress-helpdesk'), $agent_after_name),
            'by' => get_current_user_id(),
            'co' => sprintf(__('Assigned from %s to %s', 'wordpress-helpdesk'), $agent_before_name, $agent_after_name),
            'da' => current_time('timestamp', 1),
        );

        $this->saveLog($object_id, $log);
    }

    /**
     * Assigned Agent added Log
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

        $log = array(
            'ti' => sprintf(__('Assigned to %s', 'wordpress-helpdesk'), $agent->display_name),
            'by' => get_current_user_id(),
            'co' => '',
            'da' => current_time('timestamp', 1),
        );

        $this->saveLog($object_id, $log);
    }

    /**
     * Save log String into custom post meta
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post_id [description]
     * @param   [type]                       $log     [description]
     * @return  [type]                                [description]
     */
    private function saveLog($post_id, $log)
    {
        $checkExists = get_post_meta($post_id, 'wordpress_helpdesk_log');
        if (!empty($checkExists[0])) {
            $checkExists = $checkExists[0];
            $checkExists[] = $log;
            $log = $checkExists;
        } else {
            $log = array($log);
        }

        update_post_meta($post_id, 'wordpress_helpdesk_log', $log);
    }
}
