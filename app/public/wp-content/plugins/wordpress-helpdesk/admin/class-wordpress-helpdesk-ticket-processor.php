<?php

class WordPress_Helpdesk_Ticket_Processor extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    private $allowed_tags = array(
            // 'div'           => true,
            'span'          => true,
            'p'             => true,
            'a'             => array(
                'href' => true,
                'target' => array('_blank', '_top'),
            ),
            'u'             =>  true,
            'i'             =>  true,
            'q'             =>  true,
            'b'             =>  true,
            'ul'            => true,
            'ol'            => true,
            'li'            => true,
            'br'            => true,
            'hr'            => true,
            'strong'        => true,
            'blockquote'    => true,
            'del'           => true,
            'strike'        => true,
            'em'            => true,
            'code'          => true,
            'pre'           => true
    );

    public $errors = array();
    public $success = array();
    public $post_id = '';   
     
    protected $options;
    protected $saved_replies;
    /**
     * Constructor
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $plugin_name [description]
     * @param   [type]                       $version     [description]
     */
    public function __construct($plugin_name, $version, $saved_replies)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->saved_replies = $saved_replies;
    }

    /**
     * Init the Ticket Processor
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
     * Sanitize the data for the Ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $data [description]
     * @param   [type]                       $source [description]
     * @return  [type]                             [description]
     */
    public function form_sanitation($data, $source)
    {
        if(isset($data['helpdesk_username'])) {
            $data['helpdesk_username'] = sanitize_user($data['helpdesk_username']);
        }
        if(isset($data['helpdesk_email'])) {
            $data['helpdesk_email'] = filter_var($data['helpdesk_email'], FILTER_SANITIZE_EMAIL);
        }
        if(isset($data['helpdesk_websiteURL'])) {
            $data['helpdesk_websiteURL'] = filter_var( $data['helpdesk_websiteURL'], FILTER_SANITIZE_URL);
        }
        if(isset($data['helpdesk_type'])) {
            $data['helpdesk_type'] = intval( filter_var( $data['helpdesk_type'], FILTER_SANITIZE_NUMBER_INT));
        }
        if(isset($data['helpdesk_system'])) {
            $data['helpdesk_system'] = intval( filter_var( $data['helpdesk_system'], FILTER_SANITIZE_NUMBER_INT));
        }
        if(isset($data['helpdesk_priority'])) {
            $data['helpdesk_priority'] = intval( filter_var( $data['helpdesk_priority'], FILTER_SANITIZE_NUMBER_INT));
        }
        if(isset($data['helpdesk_status'])) {
            $data['helpdesk_status'] = intval( filter_var( $data['helpdesk_status'], FILTER_SANITIZE_NUMBER_INT));
        }
        if(isset($data['helpdesk_subject'])) {
            $data['helpdesk_subject'] = sanitize_text_field($data['helpdesk_subject']);
        }
        if(isset($data['helpdesk_message'])) {
            $data['helpdesk_message'] = wp_kses($data['helpdesk_message'], $this->allowed_tags);
        }
        if(isset($data['helpdesk_purchase_code'])) {
            $data['helpdesk_purchase_code'] = strip_tags( trim($data['helpdesk_purchase_code']) );
        }

        $optionalFields = $this->get_option('fields' . $source . 'OptionalCustom');
        if(!empty($optionalFields)) {

            $optionalFields = array_filter($optionalFields);
            foreach ($optionalFields as $optionalField) {

                $optionalField = explode('|', $optionalField);
                $optionalFieldName = $this->slugify($optionalField[0]);

                $data[$optionalFieldName] = strip_tags( trim($data[$optionalFieldName]) );
            }
        }

        $requiredFields = $this->get_option('fields' . $source . 'RequiredCustom');
        if(!empty($requiredFields)) {

            $requiredFields = array_filter($requiredFields);
            foreach ($requiredFields as $requiredField) {

                $requiredField = explode('|', $requiredField);
                $requiredFieldName = $this->slugify($requiredField[0]);

                $data[$requiredFieldName] = strip_tags( trim($data[$requiredFieldName]) );
            }
        }

        return $this->ticket_validation($data, $source);
    }

    /**
     * Validate the data for Ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $data [description]
     * @param   [type]                       $source [description]
     * @return  [type]                             [description]
     */
    private function ticket_validation($data, $source)
    {
        $errors = array();
        $success = array();

        if(!is_user_logged_in() || $source == "Mail" || 
            (is_user_logged_in() && $this->get_option('supportAgentCanCreateTickets') && current_user_can('edit_posts') && ( !empty($data['helpdesk_username']) || !empty($data['helpdesk_email'])) )
        ) {

            if($this->get_option('supportAgentCanCreateTickets') && current_user_can('edit_posts')) {

                if( (!isset($data['helpdesk_username']) || empty($data['helpdesk_username']) ) && (!isset($data['helpdesk_email']) || empty($data['helpdesk_email']) ) ) {
                    $errors[] = __('Username or Email not set!', 'wordpress-helpdesk');
                }

            } else {

                if( !isset($data['helpdesk_username']) || empty($data['helpdesk_username'])) {
                    $errors[] = __('Username not set!', 'wordpress-helpdesk');
                }

                if( !isset($data['helpdesk_email']) || empty($data['helpdesk_email'])) {
                    $errors[] = __('Email not set!', 'wordpress-helpdesk');
                }

            }
 

            $userExistsByEmail = $this->check_user_exists_by_email($data['helpdesk_email']);
            if($userExistsByEmail) {
                if($source !== "Mail" && 
                    !(is_user_logged_in() && $this->get_option('supportAgentCanCreateTickets') && current_user_can('edit_posts') && (!empty($data['helpdesk_username']) || !empty($data['helpdesk_email']) )  )
                ) {
                    $errors[] = sprintf( __('Email exists.  <a href="%s" title="Login">Please login to create a ticket</a>.', 'wordpress-helpdesk'), wp_login_url(get_permalink()));
                }
                $data['helpdesk_author'] = $userExistsByEmail;
            } elseif(empty($errors)) {

                $userExistsByUserName = $this->check_user_exists_by_user_name($data['helpdesk_username']);
                if($userExistsByUserName > 0) {
                    $data['helpdesk_username'] = $data['helpdesk_username']  . '-' . uniqid();
                }

                $userCreated = $this->create_user($data['helpdesk_username'], $data['helpdesk_email']);
                if($userCreated && !is_wp_error($userCreated)) {
                    if($source == "Chat") {
                        wp_clear_auth_cookie();
                        wp_set_current_user ( $userCreated );
                        wp_set_auth_cookie  ( $userCreated );
                    }
                    $success[] = __('We created an account for you – Check your inbox!', 'wordpress-helpdesk');
                    $data['helpdesk_author'] = $userCreated;
                } else {
                    $errors[] = sprintf( __('User not exists, but account could not be created. <a href="%s" title="Login">Please login first</a>!', 'wordpress-helpdesk'), wp_login_url(get_permalink()));
                }
            }
        } else {

            if(!isset($data['helpdesk_author']) || empty($data['helpdesk_author'])){
                $current_user = wp_get_current_user();
                $data['helpdesk_author'] = $current_user->ID;
            }
        }

        if(($source == "WooCommerce") && !empty($data['helpdesk_order_subject'])) {
            $data['helpdesk_subject'] = $data['helpdesk_order_subject'];
        }

        if(($source == "WooCommerce") && !empty($data['helpdesk_product_subject'])) {
            $data['helpdesk_subject'] = $data['helpdesk_product_subject'];
        }

        if(!isset($data['helpdesk_subject']) || empty($data['helpdesk_subject'])) {
            $errors[] = __('Subject not set!', 'wordpress-helpdesk');
        }

        if(!isset($data['helpdesk_message']) || empty($data['helpdesk_message'])) {
            $errors[] = __('Message not set!', 'wordpress-helpdesk');
        }

        if($source == "Envato" && ($this->get_option('integrationsEnvatoPurchaseCodeRequired'))) {

            if(!isset($data['helpdesk_purchase_code']) || empty($data['helpdesk_purchase_code'])) {
                $errors[] = __('Purchase Code not set!', 'wordpress-helpdesk');
            }
        }

        $requiredFields = $this->get_option('fields' . $source . 'RequiredCustom');

        if(!empty($requiredFields && $source != "Chat" && $source != "Mail")) {

            $requiredFields = array_filter($requiredFields);
            foreach ($requiredFields as $requiredField) {

                $requiredField = explode('|', $requiredField);
                $requiredFieldName = $this->slugify($requiredField[0]);

                $requiredFieldLabel = esc_html($requiredField[0]);
                if(isset($requiredField[2]) && !empty($requiredField[2])) {
                    $requiredFieldLabel = esc_html( $requiredField[2] );
                }

                if(!isset($data[$requiredFieldName]) || empty($data[$requiredFieldName])) {
                    $errors[] = sprintf( __('%s not set!', 'wordpress-helpdesk'), $requiredFieldLabel);
                }
            }
        }

        if($source == "Envato") {
            if(isset($data['helpdesk_purchase_code']) && !empty($data['helpdesk_purchase_code'])) {

                $verify_purchase_code = $this->verify_purchase_code($data['helpdesk_purchase_code']);
                if(!$verify_purchase_code) {
                    $errors[] = sprintf( __('Purchase Code %s could not be verified!', 'wordpress-helpdesk'), $data['helpdesk_purchase_code']);
                } else {
                    if($this->get_option('integrationsEnvatoPurchaseCodeSupportRequired')) {
                        $supported_until = strtotime($verify_purchase_code->supported_until);
                        if($supported_until < strtotime('now')) {
                            $errors[] = sprintf( __('Support Expired on %s – please renew!', 'wordpress-helpdesk'), $verify_purchase_code->supported_until);
                        } else {
                            $success[] = __('Purchase Code verified for item: ', 'wordpress-helpdesk') . $verify_purchase_code->item->name;   
                        }
                    } else {
                        $success[] = __('Purchase Code verified for item: ', 'wordpress-helpdesk') . $verify_purchase_code->item->name;   
                    }
                }
            }

            if(isset($data['helpdesk_item']) && !empty($data['helpdesk_item'])) {
                $data['helpdesk_subject'] = $data['helpdesk_item'] . ' – ' . $data['helpdesk_subject'];
            }
        }

        if(!empty($errors)) {
            $this->errors = $errors;
            return FALSE;
        }

        if(!empty($success)) {
            $this->success = $success;
        }
        
        return $this->create_ticket($data, $source);
    }

    /**
     * Verfiy Envato Purchase Code
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $code [description]
     * @return  [type]                             [description]
     */
    private function verify_purchase_code($code)
    {  
        if( !empty($this->get_option('integrationsEnvatoAPIKey')) && (!empty($this->get_option('integrationsEnvatoUsername'))) ) {
            $token = $this->get_option('integrationsEnvatoAPIKey');
            $username = $this->get_option('integrationsEnvatoUsername');
        } else {
            return false;
        }

        $envato = new DB_Envato($token);

        $purchase_data = $envato->call('/market/author/sale?code=' . $code);
        
        if(isset($purchase_data->error)) {
            return false;
        }

        return $purchase_data;
    }

    /**
     * Create the Ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $data [description]
     * @param   [type]                       $source [description]
     * @return  [type]                             [description]
     */
    private function create_ticket($data, $source)
    {
        $ticket = array(
           'post_author' => $data['helpdesk_author'],
           'post_content' => preg_replace("/\[[^\]]+\]/", '', $data['helpdesk_message']),
           'post_title' => wp_strip_all_tags( $data['helpdesk_subject'] ),
           // 'post_excerpt' => ,
           'post_status' => 'publish',
           'post_type' => 'ticket',
           'comment_status' => 'open',
        );

        // Insert the post into the database
        $post_id = wp_insert_post( $ticket );

        if(is_int($post_id)) {
            $this->post_id = $post_id;

            if(isset($data['helpdesk_purchase_code']) && !empty($data['helpdesk_purchase_code'])) {
                add_post_meta($post_id, 'purchase_code', $data['helpdesk_purchase_code']);
            }

            if(isset($data['helpdesk_website_url']) && !empty($data['helpdesk_website_url'])) {
                add_post_meta($post_id, 'website_url', $data['helpdesk_website_url']);
            }

            if(isset($data['helpdesk_order']) && !empty($data['helpdesk_order'])) {
                add_post_meta($post_id, 'order', $data['helpdesk_order']);
            }

            if(isset($data['helpdesk_product']) && !empty($data['helpdesk_product'])) {
                add_post_meta($post_id, 'product', $data['helpdesk_product']);
            }

            if(isset($data['helpdesk_status']) && !empty($data['helpdesk_status'])) {
                wp_set_object_terms($post_id, $data['helpdesk_status'], 'ticket_status');
            }

            if(isset($data['helpdesk_system']) && !empty($data['helpdesk_system'])) {
                wp_set_object_terms($post_id, $data['helpdesk_system'], 'ticket_system');
            }

            if(isset($data['helpdesk_priority']) && !empty($data['helpdesk_priority'])) {
                wp_set_object_terms($post_id, $data['helpdesk_priority'], 'ticket_priority');
            }

            if(!empty($source)) {
                add_post_meta($post_id, 'source', $source);
            }

            $optionalFields = $this->get_option('fields' . $source . 'OptionalCustom');
            if(!empty($optionalFields)) {

                $optionalFields = array_filter($optionalFields);
                foreach ($optionalFields as $optionalField) {

                    $optionalField = explode('|', $optionalField);
                    $optionalFieldName = $this->slugify($optionalField[0]);

                    if(isset($data[$optionalFieldName]) && !empty($data[$optionalFieldName])) {
                        add_post_meta($post_id, $optionalFieldName, $data[$optionalFieldName]);
                    }
                }
            }

            $requiredFields = $this->get_option('fields' . $source . 'RequiredCustom');
            if(!empty($requiredFields)) {

                $requiredFields = array_filter($requiredFields);
                foreach ($requiredFields as $requiredField) {

                    $requiredField = explode('|', $requiredField);
                    $requiredFieldName = $this->slugify($requiredField[0]);

                    if(isset($data[$requiredFieldName]) && !empty($data[$requiredFieldName])) {
                        add_post_meta($post_id, $requiredFieldName, $data[$requiredFieldName]);
                    }
                }
            }

            if(isset($data['helpdesk_type']) && !empty($data['helpdesk_type'])) {
                 wp_set_object_terms($post_id, $data['helpdesk_type'], 'ticket_type');
            }

            if($this->get_option('savedRepliesAutomatic') && $this->get_option('savedRepliesAutomaticNewTicket')) {
                if( !$this->saved_replies->check($post_id, $data['helpdesk_subject'] . ' ' . $data['helpdesk_message']) ) {
                    $this->saved_replies->add_reply( $post_id, $this->get_option('savedRepliesAutomaticNewTicketDefault') );
                }
            }

            return true;
        } else {
            $this->errors[] = $post_id;
            return false;
        }

        return true;
    }

    /**
     * Check if user exists by name & email
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $user_name  [description]
     * @param   [type]                       $user_email [description]
     * @return  [type]                                   [description]
     */
    private function check_user_exists_by_email($user_email)
    {
        $user_id = email_exists( $user_email );
        if($user_id) {
            // $user_id = $this->check_user_reporter($user_id);
            return $user_id;
        }

        return false;
    }

    /**
     * Check if user exists by name & email
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $user_name  [description]
     * @param   [type]                       $user_email [description]
     * @return  [type]                                   [description]
     */
    private function check_user_exists_by_user_name($user_name)
    {
        $user_id = username_exists( $user_name );
        if($user_id) {
            // $user_id = $this->check_user_reporter($user_id);
            return $user_id;
        }

        return false;
    }

    /**
     * Check if user is an reporter
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $user_name  [description]
     * @param   [type]                       $user_email [description]
     * @return  [type]                                   [description]
     */
    private function check_user_reporter($user_id)
    {
        $user_meta = get_userdata($user_id); 
        $user_roles = $user_meta->roles; 

        if (in_array("helpdesk_reporter", $user_roles)){
            return $user_id;
        }

        return false;
    }

    /**
     * Create WP User
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $user_name [description]
     * @param   [type]                       $user_email [description]
     * @return  [type]                                 [description]
     */
    private function create_user($user_name, $user_email)
    {
        $password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
        $userID = wp_create_user( $user_name, $password, $user_email );

        if($userID !== false) {
            $user_id_role = new WP_User($userID);
            $user_id_role->set_role('helpdesk_reporter');
            if($this->get_option('supportSendLoginCredentials')) {
                wp_new_user_notification($userID, NULL, 'both');
            }
        }

        return $userID;
    }


    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '_', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '_');

        // remove duplicate -
        $text = preg_replace('~-+~', '_', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
        return 'n-a';
        }

        return $text;
    }
}