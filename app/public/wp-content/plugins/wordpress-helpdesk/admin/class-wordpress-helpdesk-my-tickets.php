<?php

class WordPress_Helpdesk_My_Tickets extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;

    /**
     * Construct My Tickets Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $plugin_name      [description]
     * @param   [type]                       $version          [description]
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Init My Tickets Class
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

        add_shortcode('my_tickets', array( $this, 'my_tickets' ));
    }

    /**
     * Render my tickets shortcode [my_tickets orderby="date" order="DESC"]
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $atts [description]
     * @return  [type]                             [description]
     */
    public function my_tickets($atts)
    {
        if (!is_user_logged_in()) {
            return sprintf(__('Please <a href="%s" title="Login">login to view your tickets</a>', 'wordpress-helpdesk'), wp_login_url(get_permalink()));
        }

        $args = shortcode_atts(array(
            'orderby' => 'date',
            'order' => 'DESC',
        ), $atts);

        $orderby = $args['orderby'];
        $order = $args['order'];

        $query_args = array(
            'post_type' => 'ticket',
            'orderby' => $orderby,
            'order' => $order,
            'hierarchical' => false,
            'posts_per_page' => -1,
            'suppress_filters' => false,
            'author' => get_current_user_id(),
        );
        $query_args2 = ""; 

        $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $role = array_shift($roles);
        
        $agentRoles = array('helpdesk_agent');
        if (in_array($role, $agentRoles)) {
        
            $query_args2 = $query_args;
            unset($query_args2['author']);
            $query_args2['meta_query'] =  array(
                    array(
                        'key' => 'agent',
                        'value' => get_current_user_id(),
                        'compare' => '='
                    ),
                );
        }

        ob_start();

        echo '<div class="wordpress-helpdesk wordpress-helpdesk-my-tickets">';

        $checks = array('both', 'only_ticket');

        if(in_array($this->get_option('supportSidebarDisplay'), $checks) && $this->get_option('supportSidebarPosition') == "left" && !(function_exists('is_account_page') && is_account_page()) ) {
        ?>
        <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-sidebar">
            <?php dynamic_sidebar('helpdesk-sidebar'); ?>
        </div>
        <?php
        }

        $checks = array('none', 'only_faq');
        if(in_array($this->get_option('supportSidebarDisplay'), $checks) || (function_exists('is_account_page') && is_account_page()) ) {
            echo '<div class="wordpress-helpdesk-col-sm-12">';
        } else {
            echo '<div class="wordpress-helpdesk-col-sm-8">';
        }
        ?>
            <table class="wordpress-helpdesk-my-tickets-table responsive display nowrap">
                <thead class="wordpress-helpdesk-my-tickets-header">

                    <?php if($this->get_option('myTicketsShowName')) { ?>
                    <th class="wordpress-helpdesk-my-ticket-col-title">
                        <span class="wordpress-helpdesk-my-tickets-title"><?php echo __('Name', 'wordpress-helpdesk') ?></span>
                    </th>
                    <?php } ?>

                    <?php if($this->get_option('myTicketsShowDate')) { ?>
                    <th class="wordpress-helpdesk-my-ticket-col-date">
                        <span class="wordpress-helpdesk-my-tickets-date"><?php echo __('Date', 'wordpress-helpdesk') ?></span>
                    </th>
                    <?php } ?>

                    <?php if($this->get_option('myTicketsShowStatus')) { ?>
                    <th class="wordpress-helpdesk-my-ticket-col-status">
                        <span class="wordpress-helpdesk-my-tickets-status"><?php echo __('Status', 'wordpress-helpdesk') ?></span>
                    </th>
                    <?php } ?>

                    <?php if($this->get_option('myTicketsShowSystem')) { ?>
                    <th class="wordpress-helpdesk-my-ticket-col-system">
                        <span class="wordpress-helpdesk-my-tickets-system"><?php echo __('Department', 'wordpress-helpdesk') ?></span>
                    </th>
                    <?php } ?>

                    <?php if($this->get_option('myTicketsShowType')) { ?>
                    <th class="wordpress-helpdesk-my-ticket-col-type">
                        <span class="wordpress-helpdesk-my-tickets-type"><?php echo __('Type', 'wordpress-helpdesk') ?></span>
                    </th>
                    <?php } ?>

                    <th class="wordpress-helpdesk-my-ticket-col-action">
                        <?php echo __('Actions', 'wordpress-helpdesk') ?></span>
                    </th>
                </thead>
                <?php

                $tickets = get_posts($query_args);
                if(!empty($query_args2)) {
                    $agentTickets = get_posts($query_args2);

                    if(!empty($agentTickets)) {
                        $tickets = array_merge($tickets, $agentTickets);
                    }
                }

                if (empty($tickets)) {
                    echo __('No tickets submitted yet.', 'wordpress-helpdesk');
                } else {
                    foreach ($tickets as $ticket) {
                        ?>
                        <tr>
                            <?php if($this->get_option('myTicketsShowName')) { ?>
                            <td class="wordpress-helpdesk-my-ticket-col-title" data-order="<?php echo $ticket->ID ?>">
                                <a href="<?php echo get_permalink($ticket->ID) ?>">
                                    <span class="wordpress-helpdesk-my-tickets-title"><?php echo sprintf(__('[Ticket: %s]', 'wordpress-helpdesk'), $ticket->ID) . ' ' . $ticket->post_title ?></span>
                                </a>
                            </td>
                            <?php } ?>

                            <?php if($this->get_option('myTicketsShowDate')) { ?>
                            <td class="wordpress-helpdesk-my-ticket-col-date" data-order="<?php echo date_i18n( 'Y-m-d', strtotime($ticket->post_date) ) ?>">
                                <span class="wordpress-helpdesk-my-tickets-date"><?php echo date_i18n( get_option( 'date_format' ), strtotime($ticket->post_date) ) ?></span>
                            </td>
                            <?php } ?>

                            <?php if($this->get_option('myTicketsShowStatus')) { ?>
                            <td class="wordpress-helpdesk-my-ticket-col-status">
                            <?php
                                $status = get_the_terms($ticket->ID, 'ticket_status');
                                if (!empty($status)) {
                                    $status_color = get_term_meta($status[0]->term_id, 'wordpress_helpdesk_color');
                                    if (isset($status_color[0]) && !empty($status_color[0])) {
                                        $status_color = $status_color[0];
                                    } else {
                                        $status_color = '#000000';
                                    }
                                    echo '<span class="wordpress-helpdesk-my-tickets-status label wordpress-helpdesk-status-' . $status[0]->slug . '" style="background-color: ' . $status_color . '">' . $status[0]->name . '</span>';
                                }
                                ?>
                            </td>
                            <?php } ?>

                            <?php if($this->get_option('myTicketsShowSystem')) { ?>
                            <td class="wordpress-helpdesk-my-ticket-col-system">
                            <?php
                                $system = get_the_terms($ticket->ID, 'ticket_system');
                                if (!empty($system)) {
                                    $system_color = get_term_meta($system[0]->term_id, 'wordpress_helpdesk_color');
                                    if (isset($system_color[0]) && !empty($system_color[0])) {
                                        $system_color = $system_color[0];
                                    } else {
                                        $system_color = '#000000';
                                    }
                                    echo '<span class="wordpress-helpdesk-my-tickets-system label wordpress-helpdesk-system-' . $system[0]->slug . '" style="background-color: ' . $system_color . '">' . $system[0]->name . '</span>';
                                }
                                ?>
                            </td>
                            <?php } ?>

                            <?php if($this->get_option('myTicketsShowType')) { ?>
                            <td class="wordpress-helpdesk-my-ticket-col-type">
                            <?php
                                $type = get_the_terms($ticket->ID, 'ticket_type');
                                if (!empty($type)) {
                                    $type_color = get_term_meta($type[0]->term_id, 'wordpress_helpdesk_color');
                                    if (isset($type_color[0]) && !empty($type_color[0])) {
                                        $type_color = $type_color[0];
                                    } else {
                                        $type_color = '#000000';
                                    }
                                    echo '<span class="wordpress-helpdesk-my-tickets-type label wordpress-helpdesk-type-' . $type[0]->slug . '" style="background-color: ' . $type_color . '">' . $type[0]->name . '</span>';
                                }
                                ?>
                            </td>
                            <?php } ?>
                            
                            <td class="wordpress-helpdesk-my-ticket-col-action">
                                <a href="<?php echo get_permalink($ticket->ID) ?>"><span class="wordpress-helpdesk-my-tickets-action"><?php echo __('View', 'wordpress-helpdesk') ?></span></a>
                            </td>
                        </tr>
                        <?php
                    }
                }

            echo '</table>';
        echo '</div>';


        $checks = array('both', 'only_ticket');
        if(in_array($this->get_option('supportSidebarDisplay'), $checks) && $this->get_option('supportSidebarPosition') == "right" && !(function_exists('is_account_page') && is_account_page()) ) {
        ?>
        <div class="wordpress-helpdesk-col-sm-4 wordpress-helpdesk-sidebar">
            <?php dynamic_sidebar('helpdesk-sidebar'); ?>
        </div>
        <?php
        }
        
        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }
}