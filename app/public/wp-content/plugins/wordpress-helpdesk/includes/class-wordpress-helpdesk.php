<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.welaunch.io
 * @since      1.0.0
 */

class WordPress_Helpdesk
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     *
     * @var WordPress_Helpdesk_Loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of the plugin.
     */
    protected $version;

    protected $stop_words;
    protected $plugin_admin;
    protected $saved_replies;
    protected $ticket_processor;
    protected $comments;
    protected $comments_processor;
    protected $defaults;
    protected $form;
    protected $crisp;
    protected $woocommerce;
    protected $faq_post_type;
    protected $ticket_post_type;
    protected $ticket_notes;
    protected $desktop_notifications;
    protected $attachments;
    protected $livechat_frontend;
    protected $livechat_backend;
    protected $notifications;
    protected $support_rating;
    protected $my_tickets;
    protected $sidebar_widgets;
    protected $exporter;
    protected $reports;
    protected $log;
    protected $openai;
    protected $plugin_public;

    public $inbox;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct($version)
    {
        $this->plugin_name = 'wordpress-helpdesk';
        $this->version = $version;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WordPress_Helpdesk_Loader. Orchestrates the hooks of the plugin.
     * - WordPress_Helpdesk_i18n. Defines internationalization functionality.
     * - WordPress_Helpdesk_Admin. Defines all hooks for the admin area.
     * - WordPress_Helpdesk_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-wordpress-helpdesk-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'includes/class-wordpress-helpdesk-i18n.php';

        require_once plugin_dir_path(dirname(__FILE__)).'includes/Envato.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-attachments.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-comments.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-comments-processor.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-defaults.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-exporter.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-faq-post-type.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-form.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-inbox.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-livechat-frontend.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-livechat-backend.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-log.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-my-tickets.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-notifications.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-desktop-notifications.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-reports.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-saved-replies.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-sidebar-widgets.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-openai.php';
        // require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-slack.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-support-rating.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-ticket-notes.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-ticket-post-type.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-ticket-processor.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-woocommerce.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/class-wordpress-helpdesk-crisp.php';

        /**
         * Load Widgets
         */
        require_once plugin_dir_path(dirname(__FILE__)).'admin/widgets/faq-posts.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/widgets/faq-dynamic-posts.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/widgets/faq-live-search.php';
        require_once plugin_dir_path(dirname(__FILE__)).'admin/widgets/faq-topics.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)).'public/class-wordpress-helpdesk-public.php';

        // Load the TGM init if it exists
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/tgm/tgm-init.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/tgm/tgm-init.php';
        }

        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/meta-boxes.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/meta-boxes.php';
        }

        if (file_exists(plugin_dir_path(dirname(__FILE__)).'admin/Tax-meta-class/Tax-meta-class.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/Tax-meta-class/Tax-meta-class.php';
        }

        // Load Vendors
        if (file_exists(plugin_dir_path(dirname(__FILE__)).'vendor/autoload.php')) {
            require_once plugin_dir_path(dirname(__FILE__)).'vendor/autoload.php';
        }

        // Load LANG Stopword list
        $locale = get_locale();
        $this->stop_words = array();
        if (!empty($locale)) {
            $language_code = strtolower(substr($locale, 0, 2));
            if (file_exists(plugin_dir_path(dirname(__FILE__)).'vendor/stopwords-json-master/dist/' . $language_code . '.json')) {
                $this->stop_words = json_decode(file_get_contents(plugin_dir_path(dirname(__FILE__)).'vendor/stopwords-json-master/dist/' . $language_code . '.json'), true);
            }
        }

        $this->loader = new WordPress_Helpdesk_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WordPress_Helpdesk_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function set_locale()
    {
        $plugin_i18n = new WordPress_Helpdesk_i18n();

        $this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain', 0);
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_admin_hooks()
    {
        // Admin Interface
        $this->plugin_admin = new WordPress_Helpdesk_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->plugin_admin, 'init', 1);
        $this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles', 999);
        $this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts', 999);
        $this->loader->add_action('admin_head', $this->plugin_admin, 'add_admin_js_vars', 10);
        $this->loader->add_action('plugins_loaded', $this->plugin_admin, 'load_extensions', 5);
        $this->loader->add_filter('login_redirect', $this->plugin_admin, 'maybe_login_redirect', 10, 3);
        $this->loader->add_action('admin_menu', $this->plugin_admin, 'remove_menus');
        $this->loader->add_action('admin_bar_menu', $this->plugin_admin, 'remove_admin_bar_nodes', 99999999, 1 );
        $this->loader->add_filter('show_admin_bar', $this->plugin_admin, 'remove_admin_bar', 9999999999, 1 );
        $this->loader->add_action('current_screen', $this->plugin_admin, 'redirect_dashboard');
        $this->loader->add_filter('login_url', $this->plugin_admin, 'maybe_modify_login_url', 10, 3 );
        $this->loader->add_action('wp_dashboard_setup', $this->plugin_admin, 'remove_dashboard_widgets', 99 );
        $this->loader->add_filter('widget_comments_args', $this->plugin_admin, 'remove_ticket_comments_from_recent_comments_widget', 99, 1 );

        // Saved Replies
        $this->saved_replies = new WordPress_Helpdesk_Saved_Replies_Post_Type($this->get_plugin_name(), $this->get_version(), $this->stop_words);
        $this->loader->add_action('init', $this->saved_replies, 'init', 5);
        $this->loader->add_action('wp_ajax_search_saved_replies', $this->saved_replies, 'get_saved_replies');
        $this->loader->add_action('wp_ajax_get_saved_reply', $this->saved_replies, 'get_saved_reply');
        $this->loader->add_filter('comment_row_actions', $this->saved_replies, 'show_copy_link', 10, 2);
        $this->loader->add_action('admin_action_copy_comment_to_saved_reply', $this->saved_replies, 'copy_comment_to_saved_reply');

        // Ticket Processor
        $this->ticket_processor = new WordPress_Helpdesk_Ticket_Processor($this->get_plugin_name(), $this->get_version(), $this->saved_replies);
        $this->loader->add_action('init', $this->ticket_processor, 'init', 20);

        // Comments
        $this->comments = new WordPress_Helpdesk_Comments($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->comments, 'init', 30);
        $this->loader->add_filter('wp_editor_settings', $this->comments, 'comment_editor', 10, 2);
        $this->loader->add_filter('comment_form_field_comment', $this->comments, 'enable_comment_editor', 10);
        $this->loader->add_filter('pre_option_comment_moderation', $this->comments, 'allow_comments_for_all_ticket', 10, 1);
        $this->loader->add_filter('pre_option_comment_whitelist', $this->comments, 'allow_comments_for_all_ticket', 10, 1);
        $this->loader->add_filter('comments_open', $this->comments, 'close_comments_on_solved', 10, 2);
        $this->loader->add_filter('notify_post_author', $this->comments, 'not_notify_on_ticket_reply', 99999, 2);

        // Comments processor
        $this->comments_processor = new WordPress_Helpdesk_Comments_Processor($this->get_plugin_name(), $this->get_version(), $this->saved_replies);
        $this->loader->add_action('init', $this->comments_processor, 'init', 30);
        $this->loader->add_filter('preprocess_comment', $this->comments_processor, 'sanitize_comment_data');
        $this->loader->add_action('wp_insert_comment', $this->comments_processor, 'check_automatic_reply', 10 , 2);

        // Defaults
        $this->defaults = new WordPress_Helpdesk_Defaults($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->defaults, 'init', 35);
        $this->loader->add_action('transition_post_status', $this->defaults, 'set_defaults', 10, 3);

        // Form Generator & Validation
        $this->form = new WordPress_Helpdesk_Form($this->get_plugin_name(), $this->get_version(), $this->ticket_processor);
        $this->loader->add_action('init', $this->form, 'init', 40);

        // Crisp
        $this->crisp = new WordPress_Helpdesk_Crisp($this->get_plugin_name(), $this->get_version(), $this->ticket_processor, $this->comments_processor);
        $this->loader->add_action('init', $this->crisp, 'init', 50);    

        // WooCommerce Integration
        $this->woocommerce = new WordPress_Helpdesk_WooCommerce($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->woocommerce, 'init');
        $this->loader->add_filter('woocommerce_account_menu_items', $this->woocommerce, 'my_account_ticket_menu', 5, 1);
        $this->loader->add_action('init', $this->woocommerce, 'my_account_tickets_endpoint');
        $this->loader->add_action('woocommerce_account_my-tickets_endpoint', $this->woocommerce, 'my_account_my_tickets_endpoint_content');
        $this->loader->add_action('woocommerce_account_new-ticket_endpoint', $this->woocommerce, 'my_account_new_ticket_endpoint_content');
        // $this->loader->add_filter('woocommerce_login_redirect', $this->woocommerce, 'wc_custom_user_redirect', 10, 2 );
        $this->loader->add_filter('woocommerce_disable_admin_bar', $this->woocommerce, 'show_admin_bar', 10, 2 );
        $this->loader->add_filter('woocommerce_prevent_admin_access', $this->woocommerce, 'prevent_admin_access', 10, 2 );
        $this->loader->add_filter('woocommerce_product_tabs', $this->woocommerce, 'maybe_show_faqs', 10, 2 );

        // FAQ Post Type
        $this->faq_post_type = new WordPress_Helpdesk_FAQ_Post_Type($this->get_plugin_name(), $this->get_version(), $this->stop_words);
        $this->loader->add_action('init', $this->faq_post_type, 'init', 5);
        $this->loader->add_action('wp_ajax_nopriv_search_faqs', $this->faq_post_type, 'ajax_search_faqs');
        $this->loader->add_action('wp_ajax_search_faqs', $this->faq_post_type, 'ajax_search_faqs');
        $this->loader->add_action('wp_ajax_nopriv_count_likes', $this->faq_post_type, 'count_likes');
        $this->loader->add_action('wp_ajax_count_likes', $this->faq_post_type, 'count_likes');
        $this->loader->add_action('wp_ajax_nopriv_count_dislikes', $this->faq_post_type, 'count_dislikes');
        $this->loader->add_action('wp_ajax_count_dislikes', $this->faq_post_type, 'count_dislikes');
        $this->loader->add_filter('the_content', $this->faq_post_type, 'only_logged_in');
        $this->loader->add_action('admin_menu', $this->faq_post_type, 'add_faq_term_page', 130);

        $this->loader->add_action('template_redirect', $this->faq_post_type, 'count_views');
        $this->loader->add_filter('template_include', $this->faq_post_type, 'faq_templates');

        // prevent false pagination in woo shop loops (not query faqs)
        $this->loader->add_action('woocommerce_product_query', $this->faq_post_type, 'add_product_query', 5, 2);

        // Ticket Post Type
        $this->ticket_post_type = new WordPress_Helpdesk_Ticket_Post_Type($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->ticket_post_type, 'init', 5);
        $this->loader->add_action('add_meta_boxes', $this->ticket_post_type, 'add_custom_metaboxes', 10, 2);
        $this->loader->add_action('save_post', $this->ticket_post_type, 'save_custom_metaboxes', 1, 2);
        $this->loader->add_action('pre_get_posts', $this->ticket_post_type, 'filter_not_author_ones');
        $this->loader->add_filter('the_title', $this->ticket_post_type, 'modify_title', 10, 2);
        $this->loader->add_filter('template_include', $this->ticket_post_type, 'ticket_template');
        $this->loader->add_action('post_updated', $this->ticket_post_type, 'update_new_tickets_count', 10, 1);
        $this->loader->add_action('init', $this->ticket_post_type, 'close_old_tickets', 140);
        $this->loader->add_action('init', $this->ticket_post_type, 'ticket_solved_btn', 140);

        // Ticket Notes
        $this->ticket_notes = new WordPress_Helpdesk_Ticket_Notes($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->ticket_notes, 'init', 80);
        $this->loader->add_action('add_meta_boxes', $this->ticket_notes, 'add_custom_metaboxes', 10, 2);
        $this->loader->add_action('wp_ajax_create_ticket_note', $this->ticket_notes, 'create_ticket_note');
        $this->loader->add_action('wp_ajax_delete_ticket_note', $this->ticket_notes, 'delete_ticket_note');

        // Desktop Notifications
        $this->desktop_notifications = new WordPress_Helpdesk_Desktop_Notifications($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_ajax_nopriv_desktop_notifications_get_comment_ids', $this->desktop_notifications, 'get_comment_ids');
        $this->loader->add_action('wp_ajax_desktop_notifications_get_comment_ids', $this->desktop_notifications, 'get_comment_ids');
        $this->loader->add_action('wp_ajax_nopriv_desktop_notifications_get_new_comments', $this->desktop_notifications, 'get_new_comments');
        $this->loader->add_action('wp_ajax_desktop_notifications_get_new_comments', $this->desktop_notifications, 'get_new_comments');

        $this->loader->add_action('wp', $this->ticket_post_type, 'access');
        $this->loader->add_action('admin_init', $this->ticket_post_type, 'access');
        $this->loader->add_filter('wp_dropdown_users_args', $this->ticket_post_type, 'add_subscribers_to_dropdown', 10, 2);
        $this->loader->add_filter('manage_edit-ticket_columns', $this->ticket_post_type, 'ticket_columns', 10, 2);
        $this->loader->add_action('manage_ticket_posts_custom_column', $this->ticket_post_type, 'ticket_columns_content', 10, 2);

        // Inbox Checker
        $this->inbox = new WordPress_Helpdesk_Inbox($this->get_plugin_name(), $this->get_version(), $this->ticket_processor, $this->comments_processor);
        $this->loader->add_action('admin_init', $this->inbox, 'init', 110);

        // Open AI
        $this->openai = new WordPress_Helpdesk_OpenAI($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->openai, 'init', 50);
        $this->loader->add_action('wp_ajax_helpdesk_openai_get_message', $this->openai, 'ajax_get_message');
        
        // Attachments Class
        $this->attachments = new WordPress_Helpdesk_Attachments($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->attachments, 'init', 50);
        $this->loader->add_action('wp_insert_post', $this->attachments, 'save_ticket_attachments', 10, 3);
        $this->loader->add_action('wp_insert_comment', $this->attachments, 'save_comment_attachments', 30, 2);
        $this->loader->add_filter('comment_text', $this->attachments, 'show_comment_attachments', 10, 2);

       // Live Chat Frontend
        $this->livechat_frontend = new WordPress_Helpdesk_Livechat_Frontend($this->get_plugin_name(), $this->get_version(), $this->ticket_processor, $this->comments_processor, $this->attachments);
        $this->loader->add_action('init', $this->livechat_frontend, 'init', 50);
        $this->loader->add_action('wp_footer', $this->livechat_frontend, 'render_chat');

        // Live Chat Frontend AJAX
        $this->loader->add_action('wp_ajax_nopriv_livechat_frontend_check_allowed', $this->livechat_frontend, 'check_allowed');
        $this->loader->add_action('wp_ajax_livechat_frontend_check_allowed', $this->livechat_frontend, 'check_allowed');
        $this->loader->add_action('wp_ajax_nopriv_livechat_frontend_check_status', $this->livechat_frontend, 'check_status');
        $this->loader->add_action('wp_ajax_livechat_frontend_check_status', $this->livechat_frontend, 'check_status');   
        $this->loader->add_action('wp_ajax_nopriv_livechat_frontend_create_ticket', $this->livechat_frontend, 'create_ticket');
        $this->loader->add_action('wp_ajax_livechat_frontend_create_ticket', $this->livechat_frontend, 'create_ticket');
        $this->loader->add_action('wp_ajax_nopriv_livechat_frontend_get_ticket', $this->livechat_frontend, 'get_ticket');
        $this->loader->add_action('wp_ajax_livechat_frontend_get_ticket', $this->livechat_frontend, 'get_ticket');
        $this->loader->add_action('wp_ajax_nopriv_livechat_frontend_get_comments', $this->livechat_frontend, 'get_comments');
        $this->loader->add_action('wp_ajax_livechat_frontend_get_comments', $this->livechat_frontend, 'get_comments');
        $this->loader->add_action('wp_ajax_nopriv_livechat_frontend_comment_ticket', $this->livechat_frontend, 'comment_ticket');
        $this->loader->add_action('wp_ajax_livechat_frontend_comment_ticket', $this->livechat_frontend, 'comment_ticket');
        $this->loader->add_action('wp_ajax_nopriv_livechat_frontend_upload_file', $this->livechat_frontend, 'upload_file');
        $this->loader->add_action('wp_ajax_livechat_frontend_upload_file', $this->livechat_frontend, 'upload_file');

        $this->loader->add_filter('duplicate_comment_id', $this->livechat_frontend, 'allow_duplicate_messages', 10, 2);
        $this->loader->add_filter('comment_flood_filter', $this->livechat_frontend, 'disable_flood_filter');

        // $this->loader->add_action('wp_ajax_nopriv_get_users_online', $this->livechat_frontend, 'get_users_online');
        // $this->loader->add_action('wp_ajax_get_users_online', $this->livechat_frontend, 'get_users_online');

        // Live Chat Backend
        $this->livechat_backend = new WordPress_Helpdesk_Livechat_Backend($this->get_plugin_name(), $this->get_version(), $this->ticket_processor, $this->comments_processor, $this->attachments);
        $this->loader->add_action('init', $this->livechat_backend, 'init', 50);
        $this->loader->add_action('init', $this->livechat_backend, 'set_agents_online');
        $this->loader->add_action('admin_init', $this->livechat_backend, 'set_agents_online');
        $this->loader->add_action('admin_menu', $this->livechat_backend, 'add_livechat_page', 20);

        // Live Chat Backend AJAX        
        $this->loader->add_action('wp_ajax_livechat_backend_get_tickets', $this->livechat_backend, 'get_tickets');
        $this->loader->add_action('wp_ajax_livechat_backend_get_ticket', $this->livechat_backend, 'get_ticket');
        $this->loader->add_action('wp_ajax_livechat_backend_get_comments', $this->livechat_backend, 'get_comments');
        $this->loader->add_action('wp_ajax_livechat_backend_comment_ticket', $this->livechat_backend, 'comment_ticket');
        $this->loader->add_action('wp_ajax_livechat_backend_upload_file', $this->livechat_backend, 'upload_file');

        // $this->loader->add_action('wp_ajax_set_open_chat', $this->livechat, 'set_open_chat');
        // $this->loader->add_action('wp_ajax_nopriv_set_open_chat', $this->livechat, 'set_open_chat');

        // $this->loader->add_action('wp_ajax_get_open_chats', $this->livechat, 'get_open_chats');
        // $this->loader->add_action('wp_ajax_nopriv_get_open_chats', $this->livechat, 'get_open_chats');
        
        // $this->loader->add_action('wp_ajax_close_chat', $this->livechat, 'close_chat');
        // $this->loader->add_action('wp_ajax_nopriv_close_chat', $this->livechat, 'close_chat');

        // $this->loader->add_action('wp_ajax_check_chat_closed', $this->livechat, 'check_chat_closed');
        // $this->loader->add_action('wp_ajax_nopriv_check_chat_closed', $this->livechat, 'check_chat_closed');    

        // Notifications
        $this->notifications = new WordPress_Helpdesk_Notifications($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->notifications, 'init', 90);
        $this->loader->add_action('transition_post_status', $this->notifications, 'ticket_created', 20, 3);
        $this->loader->add_action('wp_insert_comment', $this->notifications, 'comment_created', 30, 2);
        $this->loader->add_action('add_term_relationship', $this->notifications, 'terms_changed', 30, 3);
        $this->loader->add_action('update_post_meta', $this->notifications, 'agent_changed', 30, 4);
        $this->loader->add_action('added_post_meta', $this->notifications, 'agent_added', 30, 4);
        $this->loader->add_filter('notify_post_author', $this->notifications, 'disable_default_notifications', 10, 2);
        $this->loader->add_filter('notify_moderator', $this->notifications, 'disable_default_notifications', 10, 2);

        // Support Rating
        $this->support_rating = new WordPress_Helpdesk_Support_Rating($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->support_rating, 'init', 95);
        $this->loader->add_action('add_term_relationship', $this->support_rating, 'terms_changed', 150, 3);

        // Slack Integration
        // $this->slack = new WordPress_Helpdesk_Slack($this->get_plugin_name(), $this->get_version());
        // $this->loader->add_action('init', $this->slack, 'init', 90);
        // $this->loader->add_action('transition_post_status', $this->slack, 'ticket_created', 30, 3);
        // $this->loader->add_action('wp_insert_comment', $this->slack, 'comment_created', 30, 2);
        // $this->loader->add_action('add_term_relationship', $this->slack, 'terms_changed', 30, 3);
        // $this->loader->add_action('update_post_meta', $this->slack, 'agent_changed', 30, 4);
        // $this->loader->add_action('added_post_meta', $this->slack, 'agent_added', 30, 4);

        // My Tickets
        $this->my_tickets = new WordPress_Helpdesk_My_Tickets($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->my_tickets, 'init', 80);

        // Sidebar / Widget Support
        $this->sidebar_widgets = new WordPress_Helpdesk_Sidebar_Widgets($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->sidebar_widgets, 'init', 120);
        $this->loader->add_action('widgets_init', $this->sidebar_widgets, 'register_sidebar', 10);
        $this->loader->add_action('widgets_init', $this->sidebar_widgets, 'register_widgets', 10);

        // Ticket Exporter Class
        $this->exporter = new WordPress_Helpdesk_Exporter($this->get_plugin_name(), $this->get_version());
        if (isset($_GET['export-tickets'])) {
            $this->loader->add_action('init', $this->exporter, 'init', 140);
        }

        // Reports
        $this->reports = new WordPress_Helpdesk_Reports($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_menu', $this->reports, 'init', 120);
        $this->loader->add_action('admin_action_wordpress_helpdesk_report_filter', $this->reports, 'filter_report');

        // Logger
        $this->log = new WordPress_Helpdesk_Log($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('init', $this->log, 'init', 120);
        $this->loader->add_action('add_meta_boxes', $this->log, 'add_log_metabox', 10, 2);
        $this->loader->add_action('transition_post_status', $this->log, 'ticket_created', 20, 3);
        $this->loader->add_action('wp_insert_comment', $this->log, 'comment_created', 30, 2);
        $this->loader->add_action('add_term_relationship', $this->log, 'terms_changed', 30, 3);
        $this->loader->add_action('update_post_meta', $this->log, 'agent_changed', 30, 4);
        $this->loader->add_action('added_post_meta', $this->log, 'agent_added', 30, 4);
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_public_hooks()
    {
        $this->plugin_public = new WordPress_Helpdesk_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts');

        $this->loader->add_action('init', $this->plugin_public, 'init');

        $this->loader->add_action('wp_head', $this->plugin_public, 'maybe_add_crisp_code', 10);
        $this->loader->add_action('wp_head', $this->plugin_public, 'maybe_add_pure_chat_code', 10);
        $this->loader->add_action('wp_head', $this->plugin_public, 'maybe_add_chatra_code', 10);
        $this->loader->add_action('wp_footer', $this->plugin_public, 'maybe_add_fb_messenger', 10);
        $this->loader->add_filter('body_class', $this->plugin_public, 'add_helpdesk_body_classes', 10);

        $this->loader->add_action('comment_form_after_fields', $this->plugin_public, 'add_ticket_frontend_fields', 100, 1);
        $this->loader->add_action('comment_form_logged_in_after', $this->plugin_public, 'add_ticket_frontend_fields', 100, 1);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     *
     * @return string The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     *
     * @return WordPress_Helpdesk_Loader Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     *
     * @return string The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Get Options
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @param   mixed                         $option The option key
     * @return  mixed                                 The option value
     */
    protected function get_option($option)
    {
        if(!isset($this->options)) {
            return false;
        }

        if (!is_array($this->options)) {
            return false;
        }

        if (!array_key_exists($option, $this->options)) {
            return false;
        }

        return $this->options[$option];
    }

    /**
     * Return the current user role
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    protected function get_user_role($userId)
    {
        $user = get_userdata($userId);
        if(!$user) {
            return 'guest';
        }
        
        $user_roles = $user->roles;
        if(empty($user_roles)) {
            return 'guest';
        }

        $user_role = array_shift($user_roles);

        return $user_role;
    }
}
