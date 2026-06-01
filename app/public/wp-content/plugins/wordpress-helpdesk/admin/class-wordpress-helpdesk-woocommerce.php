<?php

class WordPress_Helpdesk_WooCommerce extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    protected $user_role;

    /**
     * Construct WooCommerce Integration
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
     * Init WooCommerce Integration
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

        $this->user_role = $this->get_user_role(get_current_user_id());
    }

    /**
     * Add My Tickets & Submit Ticket to WooCommerce My Account page
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $items [description]
     * @return  [type]                              [description]
     */
    public function my_account_ticket_menu( $items ) 
    {
        if(!$this->get_option('integrationsWooCommerce') || !class_exists('WooCommerce')) {
            return $items;
        }
        $logout = $items['customer-logout']; 
        unset( $items['customer-logout'] );    

        $items['my-tickets'] = __( 'My Tickets', 'wordpress-helpdesk' );
        $items['new-ticket'] = __( 'Submit Ticket', 'wordpress-helpdesk' );

        $items['customer-logout'] = $logout;
     
        return $items;
    }

    /**
     * My Account -> My Tickets Endpoint
     * My Account -> New Ticket Endpoint
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function my_account_tickets_endpoint() 
    {
        if(!$this->get_option('integrationsWooCommerce') || !class_exists('WooCommerce')) {
            return false;
        }

        add_rewrite_endpoint( 'my-tickets', EP_PAGES );
        add_rewrite_endpoint( 'new-ticket', EP_PAGES );
    }

    /**
     * My Tickets Callback
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function my_account_my_tickets_endpoint_content() 
    {
        if(!$this->get_option('integrationsWooCommerce') || !class_exists('WooCommerce')) {
            return false;
        }
        echo do_shortcode('[my_tickets]');
    }

    /**
     * New Ticket Callback
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function my_account_new_ticket_endpoint_content() 
    {
        if(!$this->get_option('integrationsWooCommerce') || !class_exists('WooCommerce')) {
            return false;
        }
        echo '<h2>' . __('Submit a new Ticket', 'wordpress-helpdesk') . '</h2>';
        echo do_shortcode('[new_ticket type="WooCommerce"]');
    }

    /**
     * Custom User Redidrect
     * @deprecated 0.9
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function custom_user_redirect( $redirect, $user ) 
    {
        if(!$this->get_option('integrationsWooCommerce') || !class_exists('WooCommerce')) {
            return false;
        }
        // Get the first of all the roles assigned to the user
        $role = $user->roles[0];

        $dashboard = admin_url();
        $myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );
        if( $role == 'administrator' ) {
            //Redirect administrators to the dashboard
            $redirect = $dashboard;
        } elseif ( $role == 'shop-manager' ) {
            //Redirect shop managers to the dashboard
            $redirect = $dashboard;
        } elseif ( $role == 'editor' ) {
            //Redirect editors to the dashboard
            $redirect = $dashboard;
        } elseif ( $role == 'author' ) {
            //Redirect authors to the dashboard
            $redirect = $dashboard;
        } elseif ( $role == 'customer' || $role == 'subscriber' ) {
            //Redirect customers and subscribers to the "My Account" page
            $redirect = $myaccount;
        } else {
            //Redirect any other role to the previous visited page or, if not available, to the home
            $redirect = wp_get_referer() ? wp_get_referer() : home_url();
        }
        return $redirect;
    }

    /**
     * Show admin bar for customers
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function show_admin_bar() 
    {

        if(!$this->get_option('integrationsWooCommerce') || !$this->get_option('integrationsWooCommercePreventAdminBar') || !class_exists('WooCommerce')) {
            return false;
        }
        $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $role = array_shift( $roles );

        $notAllowedRoles = array('helpdesk_reporter', 'customer', 'subscriber');
        if( in_array($role, $notAllowedRoles) ){
            return true;
        }

        return false;
    }

    /**
     * Prevent Admin access
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $array [description]
     * @return  [type]                              [description]
     */
    public function prevent_admin_access($array)
    {
        if(!$this->get_option('integrationsWooCommerce') || !$this->get_option('integrationsWooCommercePreventAdminAccess') || !class_exists('WooCommerce')) {
            return $array;
        }
        $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $role = array_shift( $roles );

        $notAllowedRoles = array('helpdesk_reporter', 'customer', 'subscriber');
        if( in_array($role, $notAllowedRoles) ){
            return false;
        }

        return $array;
    }

    /**
     * [maybe_show_faqs description]
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.1.3
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function maybe_show_faqs($tabs) 
    {
        if(!$this->get_option('integrationsWooCommerce') || !$this->get_option('integrationsWooCommerceFAQ')|| !class_exists('WooCommerce')) {
            return $tabs;
        }

        $tabs['wordpress-helpdesk-faq'] = array(
            'title' => $this->get_option('integrationsWooCommerceFAQTitle'),
            'priority' => 40,
            'callback' => array($this, 'show_faqs')
        );

        return $tabs;
    }


    public function show_faqs($key, $tab) 
    {
        global $post;

        $term_objs = get_the_terms( $post->ID , 'product_cat' );
        if(empty($term_objs)) {
            return false;
        }

        $term_ids = array();
        foreach ($term_objs as $term_obj) {
            $term_ids[] = $term_obj->term_id;
        }

        $args = array( 
            'post_type' => 'faq', 
            'posts_per_page' => -1, 
            'tax_query' => array(
                array(
                    'taxonomy'      => 'product_cat',
                    'field'         => 'term_id', //This is optional, as it defaults to 'term_id'
                    'terms'         => $term_ids,
                    'operator'      => 'IN', // Possible values are 'IN', 'NOT IN', 'AND'.
                    'include_children' => false
                )
            )
        );
        $faqs = get_posts($args);

        if(empty($faqs)) {
            echo '<p class="woocommerce-helpdesk-product-faq-no-faqs">' . 
                    __('Currently we do not have any frequently asked questions.', 'wordpress-helpdesk') . 
                '</p>';
            return false;
        }

        $integrationsWooCommerceFAQExcerpt = $this->get_option('integrationsWooCommerceFAQExcerpt');
        $integrationsWooCommerceFAQReadMore = $this->get_option('integrationsWooCommerceFAQReadMore');
        $integrationsWooCommerceFAQColumns = $this->get_option('integrationsWooCommerceFAQColumns');
        $accordion = $this->get_option('integrationsWooCommerceFAQAccordion');

        $faqs = array_chunk($faqs, $integrationsWooCommerceFAQColumns);

        $integrationsWooCommerceFAQColumns = floor( 12 / intval($integrationsWooCommerceFAQColumns) );

        $loggedInOnlyFAQs = $this->get_option('FAQLoggedInOnly');
        $FAQByUserRole = $this->get_option('FAQByUserRole');

        echo '<div class="wordpress-helpdesk-woocommerce-faqs-container">';

            echo '<h2 class="woocommerce-helpdesk-product-faq-header">' . __('Frequently Asked Questions', 'wordpress-helpdesk') . '</h2>';

            $class = $accordion ? 'wordpress-helpdesk-faq-accordion' : '';

            foreach ($faqs as $faqTemp) {
                
                echo '<div class="wordpress-helpdesk-row">';

                    foreach ($faqTemp as $faq) {            

                        if(is_array($loggedInOnlyFAQs) && in_array($faq->ID, $loggedInOnlyFAQs) && !is_user_logged_in()) {
                            return false;
                        }
                        
                        if($FAQByUserRole) {
                            $FAQUserRoles = get_post_meta($faq->ID, 'user_roles', true);
                            if(!empty($FAQUserRoles) && !in_array($this->user_role, $FAQUserRoles)) {
                                return false;
                            }
                        }

                        echo '<div class="wordpress-helpdesk-col-sm-' . $integrationsWooCommerceFAQColumns . '">';
                            echo '<div class="wordpress-helpdesk wordpress-helpdesk-faq ' . $class . '">';

                                echo '<a class="wordpress-helpdesk-faq-title-link" href="' . get_permalink($faq->ID) . '">';
                                    echo '<h3 class="woocommerce-helpdesk-product-faq-title">';
                                            
                                        echo $faq->post_title;

                                        if($accordion) {
                                            echo '<i class="fa fa-chevron-down wordpress-helpdesk-faq-accordion-icon" aria-hidden="true"></i>';
                                        }

                                    echo '</h3>';
                                echo '</a>';

                                $faqContent = wpautop( do_shortcode($faq->post_content) );
                                if($integrationsWooCommerceFAQExcerpt) {
                                    $faqContent = $this->get_excerpt($faqContent);
                                }

                                if($integrationsWooCommerceFAQReadMore) {
                                    $faqContent .= '<br><br><a class="wordpress-helpdesk-woocommerce-read-more-faq" href="' . get_permalink($faq->ID) . '">' . __('Read More', 'wordpress-helpdesk') . '</a>';
                                }

                                ?>
                                <div class="wordpress-helpdesk-faq-content-container">

                                    <div class="woocommerce-helpdesk-product-faq-content"><?php echo $faqContent ?></div>
                                    <hr class="woocommerce-helpdesk-product-faq-divider">

                                </div>

                            </div>
                        </div>
                        <?php
                    }

                echo '</div>';

            }

        echo '</div>';

        return true;
    }

    /**
     * Get excerpt from string
     * 
     * @param String $str String to get an excerpt from
     * @param Integer $startPos Position int string to start excerpt from
     * @param Integer $maxLength Maximum length the excerpt may be
     * @return String excerpt
     */
    private function get_excerpt($str, $startPos=0, $maxLength=250) {

        $maxLength = $this->get_option('FAQExcerptMaxLength') ? $this->get_option('FAQExcerptMaxLength') : 250;

        $excerpt = strip_tags( do_shortcode($str) );
        
        if(strlen($excerpt) > $maxLength) {
            $excerpt   = substr($excerpt, $startPos, $maxLength-3);
            $lastSpace = strrpos($excerpt, ' ');
            $excerpt   = substr($excerpt, 0, $lastSpace);
            $excerpt  .= '...';
        } else {
            $excerpt = $str;
        }

        $excerpt = strip_shortcodes( preg_replace("/\[[^\]]+\]/", '', $excerpt) );

        return strip_tags( $excerpt );
    }
}