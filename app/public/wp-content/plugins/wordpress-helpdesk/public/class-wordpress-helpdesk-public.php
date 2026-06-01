<?php

class WordPress_Helpdesk_Public
{
    private $plugin_name;
    private $version;
    private $options;

    /**
     * Store Locator Plugin Construct
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
     * Enqueue Styles
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function enqueue_styles()
    {
        global $wordpress_helpdesk_options, $post;

        $this->options = $wordpress_helpdesk_options;

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/wordpress-helpdesk-public.css', array(), $this->version, 'all');

        if(!$this->get_option('disableFontAwesomeLoading')) {
            wp_enqueue_style('font-awesome', plugin_dir_url(__FILE__) . 'vendor/fontawesome-free-5.15.3-web/css/all.css', array(), '5.15.3', 'all');
        }

        if(is_single('ticket')) {
            wp_enqueue_style('Luminous', plugin_dir_url(__FILE__) . 'vendor/luminous-2.2.1/dist/luminous-basic.min.css', array(), '2.2.1', 'all');
        }

        if($this->get_option('myTicketsDatatablesEnable') && !is_tax() ) {

            $isMyTicketsPage = $this->get_option('supportMyTicketsPage');
            if(!empty($isMyTicketsPage) && isset($post->ID) && $post->ID != $isMyTicketsPage) {
                
            } else {
                wp_enqueue_style('datatables', plugin_dir_url(__FILE__) . 'vendor/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css', array(), '1.10.18', 'all');
                wp_enqueue_style('datatables', plugin_dir_url(__FILE__) . 'css/responsive.dataTables.min.css', array('datatables'), '2.3.3', 'all');
            }
        }

        $customCSS = $this->get_option('customCSS');

        if(!empty($customCSS)) {
            echo '<style>' . $customCSS . '</style>';
        }

        return true;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function enqueue_scripts()
    {
        global $wordpress_helpdesk_options;

        $this->options = $wordpress_helpdesk_options;

        if($this->get_option('enableDesktopNotifications')) {
            wp_enqueue_script('push', plugin_dir_url(__FILE__).'vendor/push-js/bin/push.min.js', array('jquery'), '1.0.7', true);
        }
        
        if(is_single('ticket')) {
            wp_enqueue_script('Luminous', plugin_dir_url(__FILE__).'vendor/luminous-2.2.1/dist/Luminous.min.js', array('jquery'), '2.2.1', true);
        }

        if($this->get_option('myTicketsDatatablesEnable') && !is_tax()) {

            $isMyTicketsPage = $this->get_option('supportMyTicketsPage');
            if(!empty($isMyTicketsPage) && isset($post->ID) && $post->ID != $isMyTicketsPage) {
                
            } else {
                wp_enqueue_script('jquery-datatables', plugin_dir_url(__FILE__).'vendor/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js', array('jquery'), '1.10.18', true);
                wp_enqueue_script('jquery-datatables-responsive', plugin_dir_url(__FILE__) . 'js/dataTables.responsive.min.js', array('jquery', 'jquery-datatables'), '2.2.3', true);
            }
        }

        wp_enqueue_script($this->plugin_name.'-public', plugin_dir_url(__FILE__) . 'js/wordpress-helpdesk-public.js', array('jquery'), $this->version, true);
        


        $forJS = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'integrationsWooCommerce' => $this->get_option('integrationsWooCommerce'),
            
            // Live Chat
            'enableLiveChat' => $this->get_option('enableLiveChat'),
            'liveChatAJAXInterval' => $this->get_option('liveChatAJAXInterval') ?  $this->get_option('liveChatAJAXInterval') : 2000,
            
            // FAQ
            'FAQShowSearch' => $this->get_option('FAQShowSearch'),
            'FAQRatingEnable' => $this->get_option('FAQRatingEnable'),
            
            // Desktop Notifications
            'enableDesktopNotifications' => $this->get_option('enableDesktopNotifications'),
            'desktopNotificationsWelcomeTitle' => $this->get_option('desktopNotificationsWelcomeTitle'),
            'desktopNotificationsWelcomeText' => $this->get_option('desktopNotificationsWelcomeText'),
            'desktopNotificationsIcon' => isset($this->get_option('desktopNotificationsIcon')['url']) ? $this->get_option('desktopNotificationsIcon')['url'] : '',
            'desktopNotificationsTimeout' => $this->get_option('desktopNotificationsTimeout'),
            'desktopNotificationsWelcomeTimeout' => $this->get_option('desktopNotificationsWelcomeTimeout'),
            'desktopNotificationsAJAXInterval' => $this->get_option('desktopNotificationsAJAXInterval') ?  $this->get_option('desktopNotificationsAJAXInterval') : 2000,
            
            // Datatables
            'myTicketsDatatablesEnable' => $this->get_option('myTicketsDatatablesEnable'),
            'myTicketsDatatablesLanguageURL' => '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/' . $this->get_option('myTicketsDatatablesLanguage') . '.json',
        );
        wp_localize_script($this->plugin_name.'-public', 'helpdesk_options', $forJS);

        $customJS = $this->get_option('customJS');
        if (empty($customJS)) {
            return false;
        }

        file_put_contents(dirname(__FILE__)  . '/js/wordpress-helpdesk-custom.js', $customJS);

        wp_enqueue_script($this->plugin_name.'-custom', plugin_dir_url(__FILE__).'js/wordpress-helpdesk-custom.js', array('jquery'), $this->version, false);

        return true;
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
    private function get_option($option)
    {
        if (!is_array($this->options)) {
            return false;
        }

        if (!array_key_exists($option, $this->options)) {
            return false;
        }

        return $this->options[$option];
    }

    /**
     * Init the Public
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function init()
    {
        global $wordpress_helpdesk_options;

        $this->options = $wordpress_helpdesk_options;

        if (!$this->get_option('enable')) {
            return false;
        }

        return true;
    }

    public function maybe_add_crisp_code()
    {
        $crispLiveChat = $this->get_option('enableLiveChatCrisp');
        if(!$crispLiveChat) {
            return false;
        }

        $code = $this->get_option('liveChatCrispCode');
        if(!empty($code)) {
            echo $code;
        }
    }

    public function maybe_add_pure_chat_code()
    {
        $PureChatLiveChat = $this->get_option('enableLiveChatPureChat');
        if(!$PureChatLiveChat) {
            return false;
        }

        $code = $this->get_option('liveChatPureChatCode');
        if(!empty($code)) {
            echo $code;
        }
    }

    public function maybe_add_chatra_code()
    {
        $ChatraLiveChat = $this->get_option('enableLiveChatChatra');
        if(!$ChatraLiveChat) {
            return false;
        }

        $code = $this->get_option('liveChatChatraCode');
        if(!empty($code)) {
            echo $code;
        }
    }

    // https://www.simoahava.com/analytics/add-facebook-messenger-chat-google-tag-manager/
    // https://developers.facebook.com/docs/messenger-platform/discovery/customer-chat-plugin
    public function maybe_add_fb_messenger()
    {
        $FBMessengerLiveChat = $this->get_option('enableLiveChatFBMessenger');
        if(!$FBMessengerLiveChat) {
            return false;
        }

        $code = $this->get_option('liveChatFBMessengerCode');
        if(!empty($code)) {
            echo $code;
        }
    }

    public function add_helpdesk_body_classes($classes) 
    {
        global $post;

        if( isset( $post ) && is_object( $post ) ) {


            // Is my Tickets Page
            $isMyTicketsPage = $this->get_option('supportMyTicketsPage');
            if($post->ID == $isMyTicketsPage) {
                $classes[] = 'wordpress-helpdesk-my-tickets';
            }

            $isNewTicketPage = $this->get_option('supportNewTicketPage');
            if($post->ID == $isNewTicketPage) {
                $classes[] = 'wordpress-helpdesk-new-ticket';
            }            
        }

        return $classes;
    }

    /**
     * Add attachment fields to comment for
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function add_ticket_frontend_fields($fields)
    {
        global $post;

        if($post->post_type !== "ticket") {
            return $fields;
        }

        if(!is_user_logged_in()) {
            return $fields;
        }

        $user = wp_get_current_user(); //Get the current user's data

        $allowedRoles = array(
            'helpdesk_agent',
            'shop_manager',
            'contributor',
            'author',
            'editor',
            'administrator',
        );

        $isReporter = count(array_intersect($allowedRoles, (array) $user->roles)) === 0;

        ?>

        <div class="wordpress-helpdesk-row wordpress-helpdesk-reply-container">

            <?php 
            // Attachments
            $ticketSource = get_post_meta( $post->ID, 'source', true);
            if (in_array($ticketSource, array('Simple', 'WooCommerce', 'Envato')) && !$this->get_option('fields' . $ticketSource . 'Attachments')) {
                
            } else {
                echo '
                <div class="wordpress-helpdesk-col-sm-4">
                    <p class="wordpress-helpdesk-attachments">
                        <label for="author">' . __('Attachments', 'wordpress-helpdesk') . '</label>
                        <input name="helpdesk-attachments[]" type="file" multiple>
                    </p>
                </div>';
            }

            // Saved replies
            if ($this->get_option('enableSavedReplies') && !$isReporter) {
               
                $args = array(
                    'post_type' => 'saved_reply',
                    'numberposts' => -1,
                    'fields' => 'ids,post_title',
                );
                $all_replies = get_posts($args);

                if(!empty($all_replies)) {
                    echo 
                    '<div class="wordpress-helpdesk-col-sm-4">
                        <p class="wordpress-helpdesk-saved-replies">
                            <label for="select-saved-reply">' . __('Load saved reply', 'wordpress-helpdesk') . '</label>
                            <select name="select-saved-reply" id="select-saved-reply">
                            <option value="">' . __('Load saved Reply', 'wordpress-helpdesk') . '</option>';

                            foreach($all_replies as $reply) {
                                echo '<option value="' . $reply->ID . '">' . $reply->post_title . '</option>';
                            }

                        echo 
                            '</select>
                        </p>
                    </div>';
                }
            }

            // Open AI
            $openAIKey = $this->get_option('integrationsOpenAIKey');
            if(!empty($openAIKey) && $this->get_option('integrationsOpenAI') && !$isReporter) {
                echo '
                <div class="wordpress-helpdesk-col-sm-4">     
                    <p class="wordpress-helpdesk-openai-chat-button-container">
                        <label>' . __('OpenAI ChatGPT', 'wordpress-helpdesk') . '</label>
                        <a href="#" class="btn button btn-primary primary button-primary wordpress-helpdesk-openai-chat-button">' . esc_html( $this->get_option('integrationsOpenAIButtonText') ) . '</a>
                    </p>
                </div>';

            }
        ?>

        </div>

        <?php
        return $fields;
    }
}