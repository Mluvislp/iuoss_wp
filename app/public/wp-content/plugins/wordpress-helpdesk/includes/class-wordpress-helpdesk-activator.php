<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.welaunch.io
 * @since      1.0.0
 *
 * @package    WordPress_Helpdesk
 * @subpackage WordPress_Helpdesk/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WordPress_Helpdesk
 * @subpackage WordPress_Helpdesk/includes
 * @author     Daniel Barenkamp <contact@db-dzine.de>
 */
class WordPress_Helpdesk_Activator {


    private $all_caps = array(
        // Tickets
        'publish_tickets',
        'edit_tickets',
        'edit_others_tickets',
        'delete_tickets',
        'delete_others_tickets',
        'delete_published_tickets',
        'read_private_tickets',
        'edit_ticket',
        'delete_ticket',
        'read_ticket',
        'edit_published_tickets',

        'manage_ticket_status',
        'edit_ticket_status',
        'delete_ticket_status',
        'assign_ticket_status',

        'manage_ticket_type',
        'edit_ticket_type',
        'delete_ticket_type',
        'assign_ticket_type',

        'manage_ticket_system',
        'edit_ticket_system',
        'delete_ticket_system',
        'assign_ticket_system',

        'manage_ticket_priority',
        'edit_ticket_priority',
        'delete_ticket_priority',
        'assign_ticket_priority',

        // FAQs
        'publish_faqs',
        'edit_faqs',
        'edit_others_faqs',
        'delete_faqs',
        'delete_others_faqs',
        'delete_published_faqs',
        'read_private_faqs',
        'edit_faq',
        'delete_faq',
        'read_faq',
        'edit_published_faqs',

        'manage_faq_topics',
        'edit_faq_topics',
        'delete_faq_topics',
        'assign_faq_topics',

        // Saved replies
        'publish_saved_replies',
        'edit_saved_replies',
        'edit_others_saved_replies',
        'delete_saved_replies',
        'delete_others_saved_replies',
        'delete_published_saved_replies',
        'read_private_saved_replies',
        'edit_saved_reply',
        'delete_saved_reply',
        'read_saved_reply',
        'edit_published_saved_replies',

        'manage_saved_reply_topics',
        'edit_saved_reply_topics',
        'delete_saved_reply_topics',
        'assign_saved_reply_topics',

        'manage_saved_reply_tags',
        'edit_saved_reply_tags',
        'delete_saved_reply_tags',
        'assign_saved_reply_tags',
    );

    private $repoter_caps = array(
        'read',
        'read_ticket',
    );

    private $agent_caps = array(
        'read',
        'publish_tickets',
        'edit_ticket',
        'edit_tickets',
        'edit_others_tickets',
        'edit_published_tickets',
        'delete_tickets',
        'delete_others_tickets',
        'delete_ticket',
        'read_ticket',
        'read_private_tickets',
        'assign_ticket_status',
        'assign_ticket_system',
        'assign_ticket_type',
        'assign_ticket_priority',

        'publish_faqs',
        'edit_faq',
        'edit_faqs',
        'edit_others_faqs',
        'edit_published_faqs',
        'delete_faqs',
        'delete_others_faqs',
        'delete_faq',
        'read_faq',
        'read_private_faqs',
        'assign_faq_topics',

        'publish_saved_replies',
        'edit_saved_reply',
        'edit_saved_replies',
        'edit_others_saved_replies',
        'edit_published_saved_replies',
        'delete_saved_replies',
        'delete_others_saved_replies',
        'delete_saved_reply',
        'read_saved_reply',
        'read_private_saved_replies',
        'assign_saved_reply_tags',
    );

    private $all_roles = array(
        'helpdesk_reporter',
        'helpdesk_agent',
        'translator',
        'shop_manager',
        'customer',
        'subscriber',
        'contributor',
        'author',
        'editor',
        'administrator',
    );

    /**
     * On plugin activation -> Assign Caps
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
	public function activate() 
    {

        if (! wp_next_scheduled ( 'run_wordpress_helpdesk_inbox_fetching' )) {
            $options = get_option('wordpress_helpdesk_options');
            $recurrence = $options['mailAccountRecurrence'];
            if(empty($recurrence)) {
                $recurrence = 'hourly';
            }
            wp_schedule_event(time(), $recurrence, 'run_wordpress_helpdesk_inbox_fetching');
        }

        $sites = array();
        if(function_exists('get_sites')) {
            $sites = get_sites();    
        }
        
        if(empty($sites)) {
            $this->add_roles();
        } else {

            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);

                $this->add_roles();

                restore_current_blog();
            }
        }
    }

    public function add_roles()
    {
        $translator = get_role('translator');
        $shop_manager = get_role('shop_manager');
        $customer = get_role('customer');
        $contributor = get_role('contributor');
        $author = get_role('author');
        $editor = get_role('editor');
        $administrator = get_role('administrator');
        $subscriber = get_role('subscriber');

        // Agent
        remove_role('helpdesk_agent');               
        $agent = add_role('helpdesk_agent', __('Helpdesk Agent', 'wordpress-helpdesk'), $subscriber->capabilities);
        $agent_caps = apply_filters('wordpress_helpdesk_agent_caps', $this->agent_caps);
        foreach ($agent_caps as $cap) {
            if(!empty($agent)) {
                $agent->add_cap($cap);
            }
            if(!empty($shop_manager)) {
                $shop_manager->add_cap($cap);
            }
            if(!empty($contributor)) {
                $contributor->add_cap($cap);
            }
            if(!empty($author)) {
                $author->add_cap($cap);
            }
            if(!empty($editor)) {
                $editor->add_cap($cap);
            }
        }
        
        // Reporter
        remove_role('helpdesk_reporter');
        $reporter = add_role('helpdesk_reporter', __('Helpdesk Reporter', 'wordpress-helpdesk'), $subscriber->capabilities);
        $repoter_caps = apply_filters('wordpress_helpdesk_repoter_caps', $this->repoter_caps);
        foreach ($repoter_caps as $cap) {
            if(!empty($reporter)) {
                $reporter->add_cap($cap);
            }
            if(!empty($translator)) {
                $translator->add_cap($cap);
            }
            if(!empty($customer)) {
                $customer->add_cap($cap);
            }
            if(!empty($subscriber)) {
                $subscriber->add_cap($cap);
            }
        }

        // Admin
        $all_caps = apply_filters('wordpress_helpdesk_all_caps', $this->all_caps);
        foreach ($all_caps as $cap) {
            if(!empty($administrator)) {
                $administrator->add_cap($cap);
            }
        }
	}
}