<?php

class WordPress_Helpdesk_Admin extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;

    /**
     * Construct Helpdesk Admin Class
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
     * Enqueue Admin Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function enqueue_styles()
    {
        global $post;

        wp_enqueue_style($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'css/wordpress-helpdesk-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css', array(), '5.14.0', 'all');

        if(isset($_GET['page']) && $_GET['page'] == "helpdesk-reports") {
            wp_enqueue_style('morris', 'https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css', array(), '0.5.1', 'all');
        }
        

        if(isset($post->post_type) && $post->post_type == "ticket") {    
            wp_enqueue_style('Luminous', 'https://cdnjs.cloudflare.com/ajax/libs/luminous-lightbox/1.0.1/luminous-basic.min.css', array(), '1.0.1', 'all');
        }
    }
    
    /**
     * Enqueue Admin Scripts
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        global $post;

        if(isset($post->post_type) && $post->post_type == "ticket") {    
            wp_enqueue_script('Luminous', 'https://cdnjs.cloudflare.com/ajax/libs/luminous-lightbox/1.0.1/Luminous.min.js', array('jquery'), '1.0.1', true);
        }

        wp_enqueue_script($this->plugin_name.'-admin', plugin_dir_url(__FILE__).'js/wordpress-helpdesk-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name.'-livechat', plugin_dir_url(__FILE__).'js/wordpress-helpdesk-livechat.js', array('jquery'), $this->version, false);

        if(isset($_GET['page']) && $_GET['page'] == "helpdesk-reports") {
            wp_enqueue_script('raphael', 'https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js', array('jquery'), '2.1.0', false);
            wp_enqueue_script('morris', 'https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js', array('jquery', 'raphael'), '0.5.1', false);
        }
    }

    public function remove_dashboard_widgets() {
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    }

    public function remove_ticket_comments_from_recent_comments_widget($args)
    {
        $allPostTypes = get_post_types();
        if(isset($allPostTypes['ticket'])) {
            unset($allPostTypes['ticket']);
        }

        $args['post_type'] = $allPostTypes;

        return $args;
    }

    /**
     * Add admin JS vars
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function add_admin_js_vars()
    {
    ?>
    <script type='text/javascript'>
        var wordpress_helpdesk_settings = <?php echo json_encode(array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'liveChatAJAXInterval' => $this->get_option('liveChatAJAXInterval') ?  $this->get_option('liveChatAJAXInterval') : 2000,
        )); ?>
    </script>
    <?php
    }

    /**
     * Load Extensions
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function load_extensions()
    {

        if(!is_admin() || !current_user_can('administrator') || 
            (defined('DOING_AJAX') && DOING_AJAX && 
            (isset($_POST['action']) && $_POST['action'] != "wordpress_helpdesk_options_ajax_save") )){
            return false;
        }

        // Load the theme/plugin options
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/options-init.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/options-init.php';
        }
        return true;
    }

    /**
     * Init
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function init()
    {
        global $wordpress_helpdesk_options;

        if(!is_admin() || !current_user_can('administrator') || (defined('DOING_AJAX') && DOING_AJAX) || empty($wordpress_helpdesk_options)){
            $wordpress_helpdesk_options = get_option('wordpress_helpdesk_options');
        }

        $this->options = $wordpress_helpdesk_options;
    }

    /**
     * Maybe redirect reporters
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  redirect_to
     */
    public function maybe_login_redirect($redirect_to, $request, $user)
    {
        if (!$this->get_option('supportRedirectAfterLogin')) {
            return $redirect_to;
        }

        $supportMyTicketsPage = $this->get_option('supportMyTicketsPage');
        if (empty($supportMyTicketsPage)) {
            return $redirect_to;
        }

        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('helpdesk_reporter', $user->roles)) {
                return get_permalink($supportMyTicketsPage);
            } else {
                return $redirect_to;
            }
        } else {
            return $redirect_to;
        }
    }  

    /**
     * Remove Menus for Agents & Reporter
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.3
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function remove_menus()
    {
        $user = wp_get_current_user();

        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('helpdesk_reporter', $user->roles) || in_array('helpdesk_agent', $user->roles)) {
                remove_menu_page( 'index.php' );
                remove_submenu_page( 'index.php', 'my-sites.php' );
            }
        }
    }

    /**
     * Remove Admin bar nodes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.3
     * @link    https://www.welaunch.io
     * @param   [type]                       $wp_admin_bar [description]
     * @return  [type]                                     [description]
     */
    public function remove_admin_bar_nodes( $wp_admin_bar ) 
    {
        $user = wp_get_current_user();

        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('helpdesk_reporter', $user->roles) || in_array('helpdesk_agent', $user->roles) || in_array('customer', $user->roles)) {
                $wp_admin_bar->remove_node( 'site-name-default' );
                $wp_admin_bar->remove_node( 'site-name' );
                $wp_admin_bar->remove_node( 'dashboard' );
            }
        }
    } 

    /**
     * Remove Admin bar
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.3
     * @link    https://www.welaunch.io
     * @param   [type]                       $wp_admin_bar [description]
     * @return  [type]                                     [description]
     */
    public function remove_admin_bar( $show_bar ) 
    {
        $user = wp_get_current_user();

        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('helpdesk_reporter', $user->roles) || in_array('helpdesk_agent', $user->roles) || in_array('customer', $user->roles)) {
               return false;
            }
        }

        return $show_bar;
    } 

    /**
     * Redirect Reporter to My Profile
     * Redirect Agents to All Tickets
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.3
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function redirect_dashboard()
    {
        $actual_link = (explode('/', $_SERVER['REQUEST_URI']));
        $screen = get_current_screen();
        if($screen->id === "dashboard") {

            $user = wp_get_current_user();

            if (in_array('helpdesk_reporter', $user->roles)) {
                $newLink = admin_url('profile.php');
                header('Location: ' . $newLink);
            }

            if (in_array('helpdesk_agent', $user->roles)) {
                $newLink = admin_url('edit.php?post_type=ticket');
                header('Location: ' . $newLink);
            }

            if (in_array('customer', $user->roles)) {
                $newLink = wc_get_page_permalink( 'myaccount' );
                header('Location: ' . $newLink);
            }
        }
        
        return false;
    }

    /**
     * Maybe modify login url
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  redirect_to
     */
    public function maybe_modify_login_url($login_url, $redirect, $force_reauth ) 
    {
        $supportLoginPage = $this->get_option('supportLoginPage');
        if (empty($supportLoginPage)) {
            return $login_url;
        }

        $login_page = get_permalink($supportLoginPage);
        $login_url = add_query_arg( 'redirect_to', $redirect, $login_page );
        return $login_url;
    }
}