<?php

class WordPress_Helpdesk_Form extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    protected $ticket_processor;
    protected $allowedFormTypes;

    /**
     * Construct Form
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $plugin_name      [description]
     * @param   [type]                       $version          [description]
     * @param   [type]                       $ticket_processor [description]
     */
    public function __construct($plugin_name, $version, $ticket_processor)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->ticket_processor = $ticket_processor;

        $this->allowedFormTypes = array(
            'checkbox',
            'checkbox',
            'color',
            'date',
            'datetime',
            'email',
            // 'file',
            'hidden',
            // 'image',
            'month',
            'number',
            'password',
            'radio',
            'range',
            'reset',
            'search',
            'tel',
            'text',
            'time',
            'url',
            'week',
        );
    }

    /**
     * Init Helpdesk Forms
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

        add_shortcode('new_ticket', array( $this, 'new_ticket_form' ));
    }

    /**
     * Render new ticket shortcode [new_ticket type="Simple|WooCommerce|Envato|Chat"]
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $atts [description]
     * @return  [type]                             [description]
     */
    public function new_ticket_form($atts)
    {
        if(is_singular('ticket')) {
            return;
        }

        $args = shortcode_atts(array(
            'type' => 'Simple',
            'types' => '',
            'departments' => '',
            'priorities' => '',
        ), $atts);

        $type = $args['type'];
        $types = $args['types'];
        $departments = $args['departments'];
        $priorities = $args['priorities'];

        do_action('wordpress_helpdesk_start_new_ticket_form');

        ob_start();
        echo '<div class="wordpress-helpdesk">';
            echo '<div class="wordpress-helpdesk-row">';

                $checks = array('both', 'only_ticket');
                if(in_array($this->get_option('supportSidebarDisplay'), $checks) && ($this->get_option('supportSidebarPosition') == "left") && ($type !== "WooCommerce")) {
                ?>
                <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-sidebar">
                    <?php dynamic_sidebar('helpdesk-sidebar'); ?>
                </div>
                <?php
                }

                $checks = array('none', 'only_faq');
                if(in_array($this->get_option('supportSidebarDisplay'), $checks) || ($type == "WooCommerce")) {
                    echo '<div class="wordpress-helpdesk-col-sm-12">';
                } else {
                    echo '<div class="wordpress-helpdesk-col-sm-8">';
                }

                $supportMyTicketsPage = $this->get_option('supportMyTicketsPage');
                if (!empty($supportMyTicketsPage)) {
                    $redirect_base = get_permalink($supportMyTicketsPage);
                    echo '<a href="' . $redirect_base . '" id="wordpress_helpdesk_back_to_my_tickets" class="wordpress_helpdesk_back_to_my_tickets">' 
                    . esc_html__('< Back to My Tickets', 'wordpress-helpdesk') . 
                    '</a>';
                }

                // Ticket submitted Check
                if (isset($_POST['helpdesk_form'])) {

                    $is_valid = true;
                    if($this->get_option('enableCaptchaBestWebSoft')) {
                        $is_valid = apply_filters( 'cptch_verify', true );
                    }

                    if(!$is_valid) {
                        echo '<div class="alert alert-danger" role="alert">';
                            echo __('Recaptcha not passed!', 'wordpress-helpdesk') . '<br/>';
                        echo '</div>';
                    } else {

                        $status = $this->ticket_processor->form_sanitation($_POST, $type);
                        if ($status && $is_valid) {
                            echo '<div class="alert alert-success" role="alert">';
                                echo sprintf(__('Ticket successfully created! You can <a href="%s">view it here</a>.<br/>', 'wordpress-helpdesk'), get_permalink($this->ticket_processor->post_id));
                                echo implode('<br/>', $this->ticket_processor->success);
                            echo '</div>';
                            // unset($_POST);
                        } else {
                            echo '<div class="alert alert-danger" role="alert">';
                                echo __('Ticket could not be created!', 'wordpress-helpdesk') . '<br/>';
                                echo implode('<br/>', $this->ticket_processor->errors);
                            echo '</div>';
                        };
                    }
                }

                $wooSubjects = array_filter( array(
                    'fieldsWooCommerceProducts' => $this->get_option('fieldsWooCommerceProducts'),
                    'fieldsWooCommerceOrders' => $this->get_option('fieldsWooCommerceOrders'),
                ) );

                if ($type === "WooCommerce" && !empty( $wooSubjects) ) {
                    $this->get_woo_form_types();
                    echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" enctype="multipart/form-data" class="wordpress-helpdesk-form wordpress-helpdesk-' . $type . '" autocomplete="off" style="display: none;" method="post">';
                } else {
                    echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" enctype="multipart/form-data" class="wordpress-helpdesk-form wordpress-helpdesk-' . $type . '" autocomplete="off" method="post">';
                }
                
                if($this->get_option('enableCaptchaBestWebSoft')) {
                    echo apply_filters( 'cptch_display', '' );
                }

                echo '<input class="form-control" name="helpdesk_form" type="hidden" value="' . $type . '">';

                do_action('wordpress_helpdesk_before_new_ticket_form');

                if (!is_user_logged_in()) {
                    if ($type === "WooCommerce") {
                        echo sprintf(__('Please <a href="%s" title="Login">login to submit a ticket.</a>', 'wordpress-helpdesk'), wp_login_url(get_permalink()));
                            $output_string = ob_get_contents();
                            ob_end_clean();
                            return $output_string;
                    } else {
                        if($this->get_option('supportOnlyLoggedIn')) {
                            echo sprintf(__('Please <a href="%s" title="Login">login to submit a ticket.</a>', 'wordpress-helpdesk'), wp_login_url(get_permalink()));
                            $output_string = ob_get_contents();
                            ob_end_clean();
                            return $output_string;
                        }
                        $this->getUsernameField();
                        $this->getEmailField();
                    }
                }

                if(is_user_logged_in() && $this->get_option('supportAgentCanCreateTickets') && current_user_can('edit_posts')) {
                    $this->getUsernameField();
                    $this->getEmailField();
                }

                if ($type === "Envato") {
                    $this->getPurchaseCodeField($type);
                    $this->getEnvatoItemsField($type);
                }

                $this->getCustomRequiredFields($type);
                $this->getCustomOptionalFields($type);

                if ($type === "WooCommerce") {
                    echo '<div class="wordpress-helpdesk-order-form wordpress-helpdesk-hidden">';
                        $this->getOrderField($type);
                        $this->getOrderSubjectField($type);
                    echo '</div>';
                    echo '<div class="wordpress-helpdesk-product-form wordpress-helpdesk-hidden">';
                        $this->getProductsField($type);
                        $this->getProductsSubjectField($type);
                    echo '</div>';

                    if(empty($wooSubjects)) {
                        echo '<div class="wordpress-helpdesk-other-form">';
                    } else {
                        echo '<div class="wordpress-helpdesk-other-form wordpress-helpdesk-hidden">';
                    }
                        $this->getSubjectField($type);
                    echo '</div>';
                } else {
                    $this->getSubjectField($type);
                }

                $this->getDepartmentField($type, $departments);
                $this->getTypesField($type, $types);
                $this->getPriorityField($type, $priorities);

                $this->getWebsiteURLField($type);
                $this->getAttachmentsField($type);

                $this->getMessageField();

                $this->getDataPrivayCheckbox($type);

                $this->getTextBeforeSubmitButton($type);

                do_action('wordpress_helpdesk_after_new_ticket_form');

                echo '<div class="form-group"><input type="submit" name="helpdesk_submitted" value="' . __('Create Ticket', 'wordpress-helpdesk') . '"/></div>';
                echo '</form>';

                echo '</div>';

                $checks = array('both', 'only_ticket');
                if(in_array($this->get_option('supportSidebarDisplay'), $checks) && ($this->get_option('supportSidebarPosition') == "right") && ($type !== "WooCommerce")) {
                ?>
                <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-sidebar">
                    <?php dynamic_sidebar('helpdesk-sidebar'); ?>
                </div>
                <?php
                }
            echo '</div>';
        echo '</div>';
        
        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }

    /**
     * Extra WooCommerce Fields
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_woo_form_types()
    {
        ?>
        <div class="wordpress-helpdesk-row wordpress-helpdesk-WooCommerce-types">
            <?php if ($this->get_option('fieldsWooCommerceOrders')) { ?>
            <a class="wordpress-helpdesk-woo-form-show" data-show="order" href="#">
            <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-center">
                <div class="wordpress-helpdesk-box">
                    <i class="fa fa-truck fa-3x"></i><br/>
                    <strong><?php echo __('Order Support', 'wordpress-helpdesk') ?></strong>
                </div>
            </div>
            </a>
            <?php } ?>
            <?php if ($this->get_option('fieldsWooCommerceProducts')) { ?>
            <a class="wordpress-helpdesk-woo-form-show" data-show="product" href="#">
            <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-center">
                <div class="wordpress-helpdesk-box">
                    <i class="fa fa-archive fa-3x"></i><br/>
                    <strong><?php echo __('Product Support', 'wordpress-helpdesk') ?></strong>
                </div>
            </div>
            </a>
            <?php } ?>
            <a class="wordpress-helpdesk-woo-form-show" data-show="other" href="#">
            <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-center">
                <div class="wordpress-helpdesk-box">
                    <i class="fa fa-question fa-3x"></i><br/>
                    <strong><?php echo __('Other', 'wordpress-helpdesk') ?></strong>
                </div>
            </div>
            </a>
        </div>
        <?php
    }

    /**
     * Get Username Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getUsernameField()
    {
        echo '<div class="form-group">';

            if(is_user_logged_in() && $this->get_option('supportAgentCanCreateTickets') && current_user_can('edit_posts')) {
                echo '<label for="helpdesk_username">' . __('Customer Name (leave empty to create ticket with your current user)', 'wordpress-helpdesk') . '</label>';
                echo '<input class="form-control" type="text" name="helpdesk_username" pattern="[a-zA-Z0-9 \u00C0-\u00ff]+" value="' . ( isset($_POST["helpdesk_username"]) ? esc_attr($_POST["helpdesk_username"]) : '' ) . '" size="40" />';
            } else {
                echo '<label for="helpdesk_username">' . __('Your Name', 'wordpress-helpdesk') . '  *</label>';
                echo '<input class="form-control" type="text" required name="helpdesk_username" pattern="[a-zA-Z0-9 \u00C0-\u00ff]+" value="' . ( isset($_POST["helpdesk_username"]) ? esc_attr($_POST["helpdesk_username"]) : '' ) . '" size="40" />';
            }            
        
        echo '</div>';
    }

    /**
     * Get Email Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getEmailField()
    {
        echo '<div class="form-group">';

            if(is_user_logged_in() && $this->get_option('supportAgentCanCreateTickets') && current_user_can('edit_posts')) {
                echo '<label for="helpdesk_email">' . __('Customer Email (leave empty to create ticket with your current user)', 'wordpress-helpdesk') . '</label>';
                echo '<input class="form-control" type="email" name="helpdesk_email" value="' . ( isset($_POST["helpdesk_email"]) ? esc_attr($_POST["helpdesk_email"]) : '' ) . '" size="40" />';
            } else {
                echo '<label for="helpdesk_email">' . __('Your Email', 'wordpress-helpdesk') . '  *</label>';
                echo '<input class="form-control" required type="email" name="helpdesk_email" value="' . ( isset($_POST["helpdesk_email"]) ? esc_attr($_POST["helpdesk_email"]) : '' ) . '" size="40" />';
            }
            
        echo '</div>';
    }

    /**
     * Get Subject Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getSubjectField($type)
    {
        echo '<div class="form-group">';
            echo '<label for="helpdesk_subject">' . __('Subject', 'wordpress-helpdesk') . ' *</label>';

            if($type == "WooCommerce") {
                echo '<input class="form-control wordpress-helpdesk-faq-searchterm " type="text" name="helpdesk_subject" value="' . ( isset($_POST["helpdesk_subject"]) ? esc_attr($_POST["helpdesk_subject"]) : '' ) . '" />';
            } else {
                echo '<input class="form-control wordpress-helpdesk-faq-searchterm " required type="text" name="helpdesk_subject" value="' . ( isset($_POST["helpdesk_subject"]) ? esc_attr($_POST["helpdesk_subject"]) : '' ) . '" />';    
            }
            echo '<div class="wordpress-helpdesk-faq-live-search-results" style="display: none;"></div>';
        echo '</div>';
    }

    /**
     * Get Subject Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getProductsSubjectField()
    {
        echo '<div class="form-group">';
            echo '<label for="helpdesk_product_subject">' . __('Subject', 'wordpress-helpdesk') . ' *</label>';
            echo '<input class="form-control wordpress-helpdesk-faq-searchterm " type="text" name="helpdesk_product_subject" value="' . ( isset($_POST["helpdesk_product_subject"]) ? esc_attr($_POST["helpdesk_product_subject"]) : '' ) . '" />';
            echo '<div class="wordpress-helpdesk-faq-live-search-results" style="display: none;"></div>';
        echo '</div>';
    }
    

    /**
     * Get Message Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getMessageField()
    {
        $settings = array(
            'textarea_rows' => 15,
            'media_buttons' => false,
            'teeny' => true,
            'drag_drop_upload' => true,
        );

        $defaultText = isset($_POST["helpdesk_message"]) ? wp_kses($_POST["helpdesk_message"], array(
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
        )) : '';

        echo '<div class="form-group">';
            echo '<label for="helpdesk_message">' . __('Message * ', 'wordpress-helpdesk') . '</label>';
            wp_editor($defaultText, 'helpdesk_message', $settings);
            // echo '<textarea class="form-control wp-editor-area" rows="10" cols="35" name="helpdesk_message">' . ( isset($_POST["message"]) ? esc_attr($_POST["message"]) : '' ) . '</textarea>';
        echo '</div>';
    }

    /**
     * Get System Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getDepartmentField($type, $defaultDepartments)
    {
        if (!$this->get_option('fields' . $type . 'System')) {
            return false;
        }
        $systems = apply_filters('wordpress_helpdesk_new_ticket_' . $type .'_systems', get_terms(array(
            'taxonomy' => 'ticket_system',
            'hide_empty' => false,
            'include' => $defaultDepartments
        )));

        if (!empty($systems)) {
            echo '<div class="form-group">';
            echo '<label for="helpdesk_system">' . __('Department', 'wordpress-helpdesk') . '  *</label>';
            echo '<select name="helpdesk_system" required class="form-control">';
            echo '<option value="">' . __('Select a Department', 'wordpress-helpdesk') . '</option>';
            foreach ($systems as $system) {
                if($system->parent !== 0) {
                    $system->name = '-- ' . $system->name;
                }
                echo '<option value="' . $system->term_id . '">' . $system->name . '</option>';
            }
            echo '</select>';
            echo '</div>';
        }
    }

    /**
     * Get Priority Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getPriorityField($type, $defaultPriorities)
    {
        if (!$this->get_option('fields' . $type . 'Priority')) {
            return false;
        }

        $priorities = apply_filters('wordpress_helpdesk_new_ticket_' . $type .'_priorities', get_terms(array(
            'taxonomy' => 'ticket_priority',
            'hide_empty' => false,
            'include' => $defaultPriorities
        )));
        if (!empty($priorities)) {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_priority">' . __('Priority', 'wordpress-helpdesk') . '  *</label>';
                echo '<select name="helpdesk_priority" required class="form-control">';
                    echo '<option value="">' . __('Select a priority', 'wordpress-helpdesk') . '</option>';
            foreach ($priorities as $priority) {
                if($priority->parent !== 0) {
                    $priority->name = '-- ' . $priority->name;
                }
                echo '<option value="' . $priority->term_id . '">' . $priority->name . '</option>';
            }
                echo '</select>';
            echo '</div>';
        }
    }

    /**
     * Get Types Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getTypesField($type, $defaultTypes)
    {
        if (!$this->get_option('fields' . $type . 'Types')) {
            return false;
        }

        $types = apply_filters('wordpress_helpdesk_new_ticket_' . $type .'_types', get_terms(array(
            'taxonomy' => 'ticket_type',
            'hide_empty' => false,
            'include' => $defaultTypes
        )));
        if (!empty($types)) {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_type">' . __('Type', 'wordpress-helpdesk') . '  *</label>';
                echo '<select name="helpdesk_type" required class="form-control">';
                    echo '<option value="">' . __('Select a type', 'wordpress-helpdesk') . '</option>';
            foreach ($types as $type) {
                if($type->parent !== 0) {
                    $type->name = '-- ' . $type->name;
                }
                echo '<option value="' . $type->term_id . '">' . $type->name . '</option>';
            }
                echo '</select>';
            echo '</div>';
        }
    }

    /**
     * Get Order Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getOrderField($type)
    {
        if (!$this->get_option('fields' . $type . 'Orders')) {
            return false;
        }

        $orders = apply_filters('wordpress_helpdesk_new_ticket_' . $type .'_orders', get_posts(array(
            'posts_per_page' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => get_current_user_id(),
            'post_type'   => wc_get_order_types(),
            'post_status' => array_keys(wc_get_order_statuses()),
        )));

        if (!empty($orders)) {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_order">' . __('Your Order', 'wordpress-helpdesk') . '</label>';
                echo '<select name="helpdesk_order" class="form-control">';
                    echo '<option value="">' . __('Select your Order', 'wordpress-helpdesk') . '</option>';
            foreach ($orders as $order) {
                echo '<option value="' . $order->ID . '">#' . $order->ID . '</option>';
            }
                echo '</select>';
            echo '</div>';
        }
    }

    /**
     * Get Order Subject Fields
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getOrderSubjectField($type)
    {   
        $order_subjects = apply_filters('wordpress_helpdesk_new_ticket_' . $type .'_order_subjects', $this->get_option('fieldsWooCommerceOrdersSubjects'));

        if (!empty($order_subjects)) {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_order_subject">' . __('Your Subject', 'wordpress-helpdesk') . '</label>';
                echo '<select name="helpdesk_order_subject" class="form-control">';
                    echo '<option value="">' . __('Select your Subject', 'wordpress-helpdesk') . '</option>';
            foreach ($order_subjects as $order_subject) {
                echo '<option value="' . $order_subject . '">' . $order_subject . '</option>';
            }
                echo '</select>';
            echo '</div>';
        }
    }

    /**
     * Get Products Fields
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getProductsField($type)
    {
        if (!$this->get_option('fields' . $type . 'Products')) {
            return false;
        }

        $products = apply_filters('wordpress_helpdesk_new_ticket_' . $type .'_products', get_posts(array(
            'posts_per_page' => -1,
            'suppress_filters' => false,
            'post_type'   => 'product',
            'orderby'          => 'title',
            'order'            => 'ASC',
        )));

        if (!empty($products)) {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_product">' . __('Product', 'wordpress-helpdesk') . '</label>';
                echo '<select name="helpdesk_product" class="form-control">';
                    echo '<option value="">' . __('Select your product', 'wordpress-helpdesk') . '</option>';
            foreach ($products as $product) {
                $sku = get_post_meta($product->ID, '_sku', true);
                if (empty($sku)) {
                    echo '<option value="' . $product->ID . '">' . $product->post_title . '</option>';
                } else {
                    echo '<option value="' . $product->ID . '">' . $product->post_title . ' (' . $sku . ')</option>';
                }
            }
                echo '</select>';
            echo '</div>';
        }
    }

    /**
     * Get Envato Purchase Code Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getPurchaseCodeField($type)
    {
        if (!$this->get_option('fields' . $type . 'PurchaseCode')) {
            return false;
        }

        if($this->get_option('integrationsEnvatoPurchaseCodeRequired')) {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_">' . __('Purchase Code', 'wordpress-helpdesk') . ' *</label>';
                echo '<input class="form-control" type="text" required name="helpdesk_purchase_code" pattern="[a-zA-Z0-9\-]+" value="' . ( isset($_POST["purchase_code"]) ? esc_attr($_POST["purchase_code"]) : '' ) . '" size="40" />';
            echo '</div>';
        } else {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_">' . __('Purchase Code', 'wordpress-helpdesk') . '</label>';
                echo '<input class="form-control" type="text" name="helpdesk_purchase_code" pattern="[a-zA-Z0-9\-]+" value="' . ( isset($_POST["purchase_code"]) ? esc_attr($_POST["purchase_code"]) : '' ) . '" size="40" />';
            echo '</div>';
        }
    }

    /**
     * Get Envato Items Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getEnvatoItemsField($type)
    {
        if (!$this->get_option('fields' . $type . 'Items')) {
            return false;
        }

        $items = apply_filters('wordpress_helpdesk_new_' . $type . '_ticket_items', $this->getEnvatoItems());
        if (!empty($items)) {
            echo '<div class="form-group">';
                echo '<label for="helpdesk_item">' . __('Select Item', 'wordpress-helpdesk') . '</label>';
                echo '<select name="helpdesk_item" class="form-control">';
                    echo '<option value="">' . __('Select an Item', 'wordpress-helpdesk') . '</option>';
            foreach ($items as $item) {
                echo '<option value="' . $item->term_id . '">' . $item->name . '</option>';
            }
                echo '</select>';
            echo '</div>';
        }
    }

    /**
     * Get Envato Items
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getEnvatoItems()
    {
        $items = array();
        if (!empty($this->get_option('integrationsEnvatoAPIKey')) && (!empty($this->get_option('integrationsEnvatoUsername')))) {
            $token = $this->get_option('integrationsEnvatoAPIKey');
            $username = $this->get_option('integrationsEnvatoUsername');
        }

        $Envato = new DB_Envato($token);

        $items = $Envato->call('/discovery/search/search/item?sort_by=name&sort_direction=asc&username=' . $username);
        
        if (isset($items->error)) {
            $items = array();
            return $items;
        }
        
        return $items->matches;
    }

    /**
     * Get Website URL Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getWebsiteURLField($type)
    {
        if (!$this->get_option('fields' . $type . 'WebsiteURL')) {
            return false;
        }

        echo '<div class="form-group helpdesk-url">';
            echo '<label for="helpdesk_website_url">' . __('Website URL', 'wordpress-helpdesk') . '</label>';
            echo '<input class="form-control" type="url" name="helpdesk_website_url" value="' . ( isset($_POST["website_url"]) ? esc_attr($_POST["website_url"]) : '' ) . '" />';
        echo '</div>';
    }

    /**
     * Get Attachments Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getAttachmentsField($type)
    {
        if (!$this->get_option('fields' . $type . 'Attachments')) {
            return false;
        }

         echo '<div class="form-group helpdesk-attachments">';
            echo '<label for="helpdesk-attachments">' . __('Attachments', 'wordpress-helpdesk') . '</label>';
            echo '<input name="helpdesk-attachments[]" type="file" multiple>';
         echo '</div>';
    }

    /**
     * Get Data Privacy Checkbox
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getDataPrivayCheckbox($type)
    {
        if (!$this->get_option('fields' . $type . 'DataPrivacy')) {
            return false;
        }


        $privacyPolicyURL = get_privacy_policy_url();
        $privacyPolicyLink = '<a href="' . $privacyPolicyURL . '" target="_blank">' . esc_html__('Privacy Policy', 'wordpress-helpdesk') . '</a>';

         echo '<div class="form-group helpdesk-privacy">';
            echo 
                '<label for="helpdesk_privacy">' .
                '<input id="helpdesk_privacy" name="helpdesk_privacy" type="checkbox" required>' .
                    sprintf( esc_html__('I have read and agree to the %s', 'wordpress-helpdesk'), $privacyPolicyLink) . ' *' .
                    
                '</label>';
         echo '</div>';
    }

    /**
     * Get Data Privacy Checkbox
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getTextBeforeSubmitButton($type)
    {
        if (!$this->get_option('fields' . $type . 'TextBeforeSubmit')) {
            return false;
        }

         echo '<div class="form-group helpdesk-text-before">';
           echo wpautop( $this->get_option('fields' . $type . 'TextBeforeSubmit') );
         echo '</div>';
    }


    /**
     * Get Order Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getCustomRequiredFields($type)
    {

        $requiredFields = $this->get_option('fields' . $type . 'RequiredCustom');
        if (!$requiredFields) {
            return false;
        }

        $requiredFields = array_filter($requiredFields);
        foreach ($requiredFields as $requiredField) {

            $requiredField = explode('|', $requiredField);

            $formType = 'text';
            if(isset($requiredField[1]) && !empty($requiredField[1]) && in_array($requiredField[1], $this->allowedFormTypes)) {
                $formType = $requiredField[1];
            }

            $requiredFieldLabel = esc_html($requiredField[0]);
            if(isset($requiredField[2]) && !empty($requiredField[2])) {
                $requiredFieldLabel = esc_html( $requiredField[2] );
            }
            
            $requiredFieldName = $this->slugify($requiredField[0]);

            $value = isset($_POST[$requiredFieldName]) ? esc_attr($_POST[$requiredFieldName]) : '';
            if($formType == "checkbox" || $formType == "radio") {
                $value = $requiredFieldLabel;
            }

            echo '<div class="form-group helpdesk-form-' . $formType . '">';
                echo '<label>' . $requiredFieldLabel . ' ' . __('*', 'wordpress-helpdesk');
                    echo '<input class="form-control" required type="' . $formType . '" name="' . $requiredFieldName . '" value="' . $value . '" />';
                echo '</label>';
            echo '</div>';  
        }
    }

    /**
     * Get Order Field
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function getCustomOptionalFields($type)
    {
        $optionalFields = $this->get_option('fields' . $type . 'OptionalCustom');
        if (!$optionalFields) {
            return false;
        }

        $optionalFields = array_filter($optionalFields);
        foreach ($optionalFields as $optionalField) {

            $optionalField = explode('|', $optionalField);

            $formType = 'text';
            if(isset($optionalField[1]) && !empty($optionalField[1]) && in_array($optionalField[1], $this->allowedFormTypes)) {
                $formType = $optionalField[1];
            }

            $optionalFieldLabel = esc_html($optionalField[0]);
            if(isset($optionalField[2]) && !empty($optionalField[2])) {
                $optionalFieldLabel = esc_html( $optionalField[2] );
            }
            
            $optionalFieldName = $this->slugify($optionalField[0]);

            $value = isset($_POST[$optionalFieldName]) ? esc_attr($_POST[$optionalFieldName]) : '';
            if($formType == "checkbox" || $formType == "radio") {
                $value = $optionalFieldLabel;
            }

            echo '<div class="form-group helpdesk-form-' . $formType . '">';
                echo '<label>' . $optionalFieldLabel ;
                    echo '<input class="form-control" type="' . $formType . '" name="' . $optionalFieldName . '" value="' . $value . '" />';
                echo '</label>';
            echo '</div>';  
        }
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