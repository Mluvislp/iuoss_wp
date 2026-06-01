<?php

class WordPress_Helpdesk_Defaults extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;
    
    /**
     * Construct Defaults Class
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
     * Init Defaults
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
     * Set defaults
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param [type] $new_status [description]
     * @param [type] $old_status [description]
     * @param [type] $post       [description]
     */
    public function set_defaults($new_status, $old_status, $post)
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
        
        // Set default Status
        $check_exists = wp_get_object_terms($post->ID, 'ticket_status');
        if (empty($check_exists) && (!isset($_POST['helpdesk_status']) || empty($_POST['helpdesk_status']))) {
            $default_status = $this->get_option('defaultStatus');
            if (!empty($default_status)) {
                $default_status = intval($default_status);
                wp_set_object_terms($post->ID, $default_status, 'ticket_status');
            }
        }

        // Set default Type
        $check_exists = wp_get_object_terms($post->ID, 'ticket_type');
        if (empty($check_exists) && (!isset($_POST['helpdesk_type']) || empty($_POST['helpdesk_type']))) {
            $default_type = $this->get_option('defaultType');
            if (!empty($default_type)) {
                wp_set_object_terms($post->ID, intval($default_type), 'ticket_type');
            }
        }

        // Set default Type
        $check_exists = wp_get_object_terms($post->ID, 'ticket_priority');
        if (empty($check_exists) && (!isset($_POST['helpdesk_priority']) || empty($_POST['helpdesk_priority']))) {
            $default_priority = $this->get_option('defaultPriority');
            if (!empty($default_priority)) {
                wp_set_object_terms($post->ID, intval($default_priority), 'ticket_priority');
            }
        }

        // Set default System
        $check_system_exists = wp_get_object_terms($post->ID, 'ticket_system');
        if (empty($check_system_exists) && (!isset($_POST['helpdesk_system']) || empty($_POST['helpdesk_system']))) {
            $default_system = $this->get_option('defaultSystem');
            if (!empty($default_system)) {
                wp_set_object_terms($post->ID, intval($default_system), 'ticket_system');
            }
        }

        // Set default Agent
        $check_exists = get_post_meta($post->ID, 'agent', true);
        if (empty($check_exists) && (!isset($_POST['agent']) || empty($_POST['agent']))) {
            $system = isset($_POST['helpdesk_system']) ? $_POST['helpdesk_system'] : '';
            if(!empty($system) && !empty($this->get_option('defaultAgent' . $system))) {
                $default_agent = $this->get_option('defaultAgent' . $system);
                if (!empty($default_agent)) {
                    $default_agent = intval($default_agent);
                    update_post_meta($post->ID, 'agent', $default_agent);
                }
            } else {
                $default_agent = $this->get_option('defaultAgent');
                if (!empty($default_agent)) {
                    $default_agent = intval($default_agent);
                    update_post_meta($post->ID, 'agent', $default_agent);
                }
            }
        }
    }
}
