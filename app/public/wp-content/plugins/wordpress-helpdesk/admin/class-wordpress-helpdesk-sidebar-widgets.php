<?php

class WordPress_Helpdesk_Sidebar_Widgets extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;

    /**
     * Construct Sidebar
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
     * Init Sidebar Widgetes
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
     * Register Helpdesk Sidebar
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function register_sidebar()
    {
        $args = array(
            'name' => __('Helpdesk Sidebar', 'wordpress-helpdesk'),
            'id' => 'helpdesk-sidebar',
            'description' => __('Widgets in this area will be shown on all posts and pages.', 'wordpress-helpdesk'),
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
        );

        register_sidebar($args);
    }

    /**
     * Register Helpdesk Widgets
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function register_widgets()
    {
        register_widget( 'FAQ_Posts' );
        register_widget( 'FAQ_Dynamic_Posts' );
        register_widget( 'FAQ_Live_Search' );
        register_widget( 'FAQ_Topics' );
    }
}
