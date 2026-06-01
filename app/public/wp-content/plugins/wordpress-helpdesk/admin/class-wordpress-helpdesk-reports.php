<?php

class WordPress_Helpdesk_Reports extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    /**
     * Construct Reports Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $plugin_name        [description]
     * @param   [type]                       $version            [description]
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Init Reports Page in Admin
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
        
        add_submenu_page(
            'edit.php?post_type=ticket',
            __('Reports', 'wordpress-helpdesk'),
            __('Reports', 'wordpress-helpdesk'),
            'manage_options',
            'helpdesk-reports',
            array($this, 'render_helpdesk_reports')
        );
    }

    /**
     * Action for date filter
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function filter_report()
    {
        $query = array(
            'post_type' => 'ticket',
            'page' => 'helpdesk-reports',
            'date_from' => $_GET['date_from'],
            'date_until' => $_GET['date_until'],
        );
        $url = admin_url('edit.php?' . http_build_query($query));
        wp_redirect($url);
        exit();
    }

    /**
     * Display callback for report page
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function render_helpdesk_reports()
    {

        $date_from = '';
        if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
            $date_from = $this->validateDate($_GET['date_from']);
            if ($date_from) {
                $date_from = $_GET['date_from'];
            } else {
                echo __('Date From is not valid!', 'wordpress-helpdesk');
            }
        }

        $date_until = '';
        if (isset($_GET['date_until']) && !empty($_GET['date_until'])) {
            $date_until = $this->validateDate($_GET['date_until']);
            if ($date_until) {
                $date_until = $_GET['date_until'];
            } else {
                echo __('Date Until is not valid!', 'wordpress-helpdesk');
            }
        }

        $date_query = array();
        if ($date_until && $date_from) {
            $date_query = array(
                'after' => $date_from,
                'before' => $date_until,
                'inclusive' => true,
            );
        } elseif ($date_from) {
            $date_query = array(
                'after' => $date_from,
            );
        } elseif ($date_until) {
            $date_query = array(
                'before' => $date_until,
            );
        }

        $args = array(
            'post_type' => 'ticket',
            'posts_per_page' => -1,
        );

        if (!empty($date_query)) {
            $args['date_query'] = $date_query;
        }

        $tickets = get_posts($args);

        $years = array();
        $months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        $firstTicket = end($tickets);
        $firstTicketDateTime = new DateTime($firstTicket->post_date);
        $firstTicketYear = (int) $firstTicketDateTime->format('Y');

        $ticketsCreatedByMonth = array();
        $ticketsCreatedByYear = array();
        $ticketsCreatedByYearMonth = array();
        for ($i = $firstTicketYear; $i <= date('Y'); $i++) {
            $years[] = $i;
            $ticketsCreatedByYear[$i] = array(
                'label' => $i,
                'value' => 0,
            );
            foreach ($months as $month) {
                $ticketsCreatedByYearMonth[$month] = array(
                    'y' => $month,
                    $i => 0,
                );
                $ticketsCreatedByMonth[$month] = array(
                    'label' => $month,
                    'value' => 0,
                );
            }
        }

        $ticketsAssignedToAgent = array();
        $ticketsByReporter = array();
        $ticketsBySatisfaction = array();
        $ticketsBySource = array();
        foreach ($tickets as $ticket) {
            $d = new DateTime($ticket->post_date);
            $year_created = $d->format('Y');
            $month_created = $d->format('m');

            $ticketsCreatedByYearMonth[$month_created][$year_created]++;
            
            // Tickets by Agent
            $agent = get_post_meta($ticket->ID, 'agent', true);
            if (empty($agent)) {
                $agent = __('Unassigned', 'wordpress-helpdesk');
            } else {
                $agent = get_userdata($agent)->data->display_name;
            }

            $count = 1;
            if (isset($ticketsAssignedToAgent[$agent])) {
                $count = $ticketsAssignedToAgent[$agent]['value'] + 1;
            }
            $ticketsAssignedToAgent[$agent] = array(
                'label' => $agent,
                'value' => $count,
            );

            // Tickets by Reporter
            $reporter = $ticket->post_author;
            if (empty($ticket->post_author)) {
                $reporter = 'None';
            } else {
                $reporter = get_userdata($reporter)->data->display_name;
            }

            $count = 1;
            if (isset($ticketsByReporter[$reporter])) {
                $count = $ticketsByReporter[$reporter]['value'] + 1;
            }
            $ticketsByReporter[$reporter] = array(
                'label' => $reporter,
                'value' => $count,
            );

            // Tickets by Satisfaction
            $satisfied = get_post_meta($ticket->ID, 'satisfied', true);
            if (empty($satisfied)) {
                $satisfied = __('Waiting', 'wordpress-helpdesk');
            }

            $count = 1;
            if (isset($ticketsBySatisfaction[$satisfied])) {
                $count = $ticketsBySatisfaction[$satisfied]['value'] + 1;
            }
            $ticketsBySatisfaction[$satisfied] = array(
                'label' => $satisfied,
                'value' => $count,
            );

            // Tickets by Source
            $source = get_post_meta($ticket->ID, 'source', true);
            if (empty($source)) {
                $source = __('No Source', 'wordpress-helpdesk');
            }

            $count = 1;
            if (isset($ticketsBySource[$source])) {
                $count = $ticketsBySource[$source]['value'] + 1;
            }
            $ticketsBySource[$source] = array(
                'label' => $source,
                'value' => $count,
            );

            // Tickets By Year
            if (isset($ticketsCreatedByYear[$year_created])) {
                $count = $ticketsCreatedByYear[$year_created]['value'] + 1;
            }
            $ticketsCreatedByYear[$year_created] = array(
                'label' => $year_created,
                'value' => $count,
            );

            // Tickets by Month
            if (isset($ticketsCreatedByMonth[$month_created])) {
                $count = $ticketsCreatedByMonth[$month_created]['value'] + 1;
            }
            $ticketsCreatedByMonth[$month_created] = array(
                'label' => $month_created,
                'value' => $count,
            );
        }

        $ticketsByStatus = $this->get_tickets_by_status();
        $ticketsByType = $this->get_tickets_by_type();
        $ticketsByPriority = $this->get_tickets_by_priority();
        $ticketsBySystem = $this->get_tickets_by_system();


        ?>
        <div class="wrap">
            <div class="wordpress-helpdesk-container">
                <h1><?php _e('Reports', 'wordpress-helpdesk'); ?></h1>
                <form action="<?php echo admin_url('edit.php?post_type=ticket&page=helpdesk-reports') ?>" method="get" style="background-color: #FFF; padding: 5px 20px 20px;">
                    <h2><?php _e('Date Filter', 'wordpress-helpdesk') ?></h2>
                    <input type="hidden" name="action" value="wordpress_helpdesk_report_filter">
                     <div class="wordpress-helpdesk-row">
                        <div class="wordpress-helpdesk-col-sm-2">
                            <label for="date_from"><?php echo __('Date From (JJJJ-MM-DD)', 'wordpress-helpdesk') ?></label><br/>
                            <input type="text" name="date_from" placeholder="JJJJ-MM-DD" value="<?php echo $date_from ?>">
                        </div>
                        <div class="wordpress-helpdesk-col-sm-2">
                            <label for="date_until"><?php echo __('Date Until (JJJJ-MM-DD)', 'wordpress-helpdesk') ?></label><br/>
                            <input type="text" name="date_until" placeholder="JJJJ-MM-DD" value="<?php echo $date_until ?>">
                        </div>
                        <div class="wordpress-helpdesk-col-sm-2">
                            <br/><input type="submit" class="button button-primary" value="<?php echo __('Submit', 'wordpress-helpdesk') ?>" >
                        </div>
                    </div>
                </form>
                <div class="wordpress-helpdesk-col-sm-12">
                    <h2><?php echo __('Filtered', 'wordpress-helpdesk') ?></h2>
                    <p><?php echo __('Filtered Tickets:', 'wordpress-helpdesk') . ' ' . count($tickets) ?></p>
                </div>
                <div class="wordpress-helpdesk-row">
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Agent', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-agent"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-agent',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsAssignedToAgent)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Reporter', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-reporter"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-reporter',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsByReporter)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Satisfaction', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-satisfaction"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-satisfaction',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsBySatisfaction)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Source', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-source"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-source',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsBySource)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Year', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-year"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-year',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsCreatedByYear)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Month', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-month"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-month',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsCreatedByMonth)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-6">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets created by Year / Month', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-year-month"></div>
                        <script>Morris.Line({
                            element: 'tickets-by-year-month',
                            colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                            data: <?php echo json_encode(array_values($ticketsCreatedByYearMonth)) ?>,
                            xkey: 'y',
                            ykeys: <?php echo json_encode(array_values($years)) ?>,
                            labels: <?php echo json_encode(array_values($years)) ?>,
                            xLabels: 'year-month',
                            parseTime: false
                        });
                        </script>
                    </div>
                </div>
                <div class="wordpress-helpdesk-row">
                    <div class="wordpress-helpdesk-col-sm-12">
                        <h2><?php echo __('Total', 'wordpress-helpdesk') ?></h2>
                        <p><?php echo __('No Date filter applied here:', 'wordpress-helpdesk') ?></p>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Status', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-status"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-status',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsByStatus)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Type', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-type"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-type',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsByType)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Department', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-system"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-system',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsBySystem)) ?>
                        });</script>
                    </div>
                    <div class="wordpress-helpdesk-col-sm-3">
                        <h3 class="wordpress-helpdesk-center"><?php echo __('Tickets by Priority', 'wordpress-helpdesk') ?></h3>
                        <div id="tickets-by-priority"></div>
                        <script>Morris.Donut({
                          element: 'tickets-by-priority',
                          colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                          data: <?php echo json_encode(array_values($ticketsByPriority)) ?>
                        });</script>
                    </div>
<!--                     <div class="wordpress-helpdesk-col-sm-6">
                        <h3 class="wordpress-helpdesk-center">Created / Completed Tickets by Month</h3>
                        <div id="created-completed-tickets-by-month"></div>
                        <script>Morris.Bar({
                            element: 'created-completed-tickets-by-month',
                            colors: ['#F44336', '#2196F3', '#FFEB3B', '#4CAF50', '#FF9800', '#795548', '#673AB7'],
                            data: [
                                { y: '2006', a: 100, b: 90 },
                                { y: '2007', a: 75,  b: 65 },
                                { y: '2008', a: 50,  b: 40 },
                                { y: '2009', a: 75,  b: 65 },
                                { y: '2010', a: 50,  b: 40 },
                                { y: '2011', a: 75,  b: 65 },
                                { y: '2012', a: 100, b: 90 }
                              ],
                              xkey: 'y',
                              ykeys: ['a', 'b'],
                              labels: ['Series A', 'Series B']
                            });
                        </script>
                    </div> -->
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get Tickets by Status
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function get_tickets_by_status()
    {
        $ticketsByStatus = array();
        $stati = get_terms('ticket_status', array('hide_empty' => false));
        foreach ($stati as $status) {
            $ticketsByStatus[$status->term_id] = array(
                'label' => $status->name,
                'value' => $status->count,
            );
        }

        return $ticketsByStatus;
    }

    /**
     * Get Tickets by Type
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function get_tickets_by_type()
    {
        $ticketsByTypes = array();
        $types = get_terms('ticket_type', array('hide_empty' => false));
        foreach ($types as $type) {
            $ticketsByTypes[$type->term_id] = array(
                'label' => $type->name,
                'value' => $type->count,
            );
        }

        return $ticketsByTypes;
    }

    /**
     * Get Tickets by Type
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.2.4
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function get_tickets_by_priority()
    {
        $ticketsByPriority = array();
        $priorities = get_terms('ticket_priority', array('hide_empty' => false));
        foreach ($priorities as $priority) {
            $ticketsByPriority[$priority->term_id] = array(
                'label' => $priority->name,
                'value' => $priority->count,
            );
        }

        return $ticketsByPriority;
    }

    /**
     * Get Tickets by System
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function get_tickets_by_system()
    {
        $ticketsBySystems = array();
        $systems = get_terms('ticket_system', array('hide_empty' => false));
        foreach ($systems as $system) {
            $ticketsBySystems[$system->term_id] = array(
                'label' => $system->name,
                'value' => $system->count,
            );
        }

        return $ticketsBySystems;
    }

    /**
     * Validate Date
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $date   [description]
     * @param   string                       $format [description]
     * @return  [type]                               [description]
     */
    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}