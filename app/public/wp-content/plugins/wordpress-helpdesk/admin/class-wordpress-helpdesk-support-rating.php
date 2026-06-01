<?php

class WordPress_Helpdesk_Support_Rating extends WordPress_Helpdesk
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

    protected $options;
    protected $headers;
    protected $tpl;


    /**
     * Construct Notifications Class
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
     * Init Notifications (set Mail template)
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

        $tpl = $this->get_option('supportMailTemplate');
        
        $supportName = $this->get_option('supportName');
        $supportMail = $this->get_option('supportMail');
        $supportLogo = $this->get_option('supportLogo');
        $supportFooter = $this->get_option('supportFooter');
        $supportLogoURL = isset($supportLogo['url']) ? $supportLogo['url'] : '';

        $tpl = str_replace('{{support_name}}', $supportName, $tpl);
        $tpl = str_replace('{{support_logo}}', $supportLogoURL, $tpl);
        $tpl = str_replace('{{footer}}', $supportFooter, $tpl);
        $tpl = str_replace('{{ticket_link_text}}', __('View Ticket', 'wordpress-helpdesk'), $tpl);

        $this->headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To:' . $supportName. ' <' . $supportMail . '>',
            'From:' . $supportName. ' <' . $supportMail . '>',
        );

        $this->tpl = $tpl;

        add_shortcode('helpdesk_feedback', array( $this, 'helpdesk_feedback' ));
    }

    public function helpdesk_feedback()
    {
        if(!isset($_GET['satisfied']) || empty($_GET['satisfied']) || empty($_GET['ticket']) || !isset($_GET['ticket'])) {
            return __('No Ticket ID set.', 'wordpress-helpdesk');
        }

        $satisfied = $_GET['satisfied'];
        if($satisfied !== "yes" && $satisfied !== "no") {
            return __('Wrong satisfied value.', 'wordpress-helpdesk');
        }

        if (isset($_POST['helpdesk_feedback_text']) && isset($_POST['helpdesk_feedback_ticket'])) {
            
            if (empty($_POST['helpdesk_feedback_text']) || empty($_POST['helpdesk_feedback_ticket'])) {
                return __('Please set a feedback text.', 'wordpress-helpdesk');
            }

            $feedback_message = wp_kses($_POST['helpdesk_feedback_text'], $this->allowed_tags);
            $ticket_id = intval($_POST['helpdesk_feedback_ticket']);
            
            update_post_meta($ticket_id, 'feedback', $feedback_message);
            $agentId = get_post_meta($ticket_id, 'agent', true);

            if($this->get_option('enableSupportRatingSendAgent') && $agentId) {

                $emailSubject = __('New Feeback', 'wordpress-helpdesk');
                    
                $post = get_post($ticket_id);
                $agent = get_userdata($agentId)->data;
                $feedbackPageLink = get_permalink($feedbackPage);
                
                $this->subject = '';

                $this->id = $post->ID;
                $this->title = __('New Feeback', 'wordpress-helpdesk');

                $content = __('New Feedback for Ticket ' . $post->post_title, 'wordpress-helpdesk') . '<br><br>';
                $content .=  __('Satisfied: ', 'wordpress-helpdesk') . $satisfied . '<br>';
                $content .=  __('Feedback: ', 'wordpress-helpdesk') . $feedback_message;

                $this->content = $content;
                $this->link = get_permalink($post->ID);

                $to = array();
                $to[] = $agent->display_name . '<' . $agent->user_email . '>';

                $this->to = $to;    
                $this->sendMail(); 
            }
        }

        $ticket = intval($_GET['ticket']);

        $feedback_already_sent = get_post_meta($ticket, 'feedback', true);
        if(!empty($feedback_already_sent)) {
            return __('Feedback received. Thanks!', 'wordpress-helpdesk');
        }
        
        update_post_meta($ticket, 'satisfied', $satisfied);

        $satisfiedColor = '';
        $unsatisfiedColor = '';
        if($satisfied == "yes") {
            $satisfiedColor = '#4CAF50';
        } else {
            $unsatisfiedColor = '#F44336';
        }

        ob_start();
        ?>
        <div class="wordpress-helpdesk wordpress-helpdesk-feedback">
            <div class="wordpress-helpdesk-row wordpress-helpdesk-feedback-header">
                <div class="wordpress-helpdesk-col-sm-12 wordpress-helpdesk-center">
                    <h3><?php echo __('Thanks for your feedback!', 'wordpress-helpdesk') ?></h3>
                </div>
                <div class="wordpress-helpdesk-col-sm-6">
                    <i class="fa fa-smile-o" style="color: <?php echo $satisfiedColor ?>;"></i>
                </div>
                <div class="wordpress-helpdesk-col-sm-6">
                    <i class="fa fa-frown-o" style="color: <?php echo $unsatisfiedColor ?>;"></i>
                </div>
            </div>
            <div class="wordpress-helpdesk-row">
                <form action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>" class="wordpress-helpdesk-feedback-form" method="post">
                    <?php
                    if($this->get_option('enableCaptchaBestWebSoft')) {
                        echo apply_filters( 'cptch_display', '' );
                    }
                    ?>
                    <input class="form-control" name="helpdesk_feedback_ticket" type="hidden" value="<?php echo $ticket ?>">
                    <div class="form-group">
                        <label for="helpdesk_feedback_text"><?php echo __('We would be happy if you leave a feedback message:', 'wordpress-helpdesk') ?></label>
                        <textarea class="form-control" name="helpdesk_feedback_text" cols="30" rows="10"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" name="helpdesk_submitted" value="<?php echo __('Send Feedback', 'wordpress-helpdesk') ?>"/>
                    </div>
                </form>
            </div>
        </div>
        <?php

        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;

    }    

    /**
     * Terms changes (status changed)
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $object_id [description]
     * @param   [type]                       $tt_id     [description]
     * @param   [type]                       $taxonomy  [description]
     * @return  [type]                                  [description]
     */
    public function terms_changed($object_id, $tt_id, $taxonomy)
    {
        if(!$this->get_option('enableSupportRating')) {
            return false;
        }

        if(!$this->get_option('enableSupportRatingEmail')) {
            return false;   
        }

        if (get_post_type($object_id) !== "ticket") {
            return false;
        }

        $statusTrigger = $this->get_option('supportRatingStatus');
        if(empty($statusTrigger || empty($tt_id))) {
            return false;
        }

        $feedbackPage = $this->get_option('supportRatingFeedbackPage');
        if(empty($feedbackPage)) {
            return false;
        }

        $statusTrigger = intval($statusTrigger);
        $newTermID = intval($tt_id);
        if($statusTrigger !== $newTermID) {
            return false;
        }

        $newTerm = get_term($tt_id);
        $taxonomy = get_taxonomy($newTerm->taxonomy);

        $emailSubject = $this->get_option('supportRatingEmailSubject');
        $emailIntro = $this->get_option('supportRatingEmailIntro');
        $emailSatisfied = $this->get_option('supportRatingEmailSatisfied');
        $emailUnsatisfied = $this->get_option('supportRatingEmailUnsatisfied');
        $emailOutro = $this->get_option('supportRatingEmailOutro');

        $post = get_post($object_id);
        $author = get_userdata($post->post_author)->data;
        $feedbackPageLink = get_permalink($feedbackPage);
        
        $this->subject = $emailSubject;

        $this->id = $post->ID;
        $this->title = $post->post_title;

        $content = wpautop(sprintf($emailIntro, $author->display_name));
        $content .= '<a href="' . $feedbackPageLink . '?satisfied=yes&ticket=' . $post->ID . '" target="_blank">' . $emailSatisfied . '</a><br/>';
        $content .= '<a href="' . $feedbackPageLink . '?satisfied=no&ticket=' . $post->ID . '" target="_blank">' . $emailUnsatisfied . '</a><br/><br/>';
        $content .= wpautop($emailOutro);

        $this->content = $content;
        $this->link = get_permalink($post->ID);

        $to = array();
        $to[] = $author->display_name . '<' . $author->user_email . '>';

        $this->to = $to;
        
        $this->sendMail();

        return true;
    }

    /**
     * Send out notification Mail
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    private function sendMail()
    {
        $subject = '[Ticket: ' . $this->id . '] ' . $this->title . ' – ' . $this->subject;

        $search = array('{{content}}' , '{{ticket_link}}', '{{title}}');
        $replace = array(
            $this->content,
            $this->link,
            '[Ticket: ' . $this->id . '] ' . $this->title
        );

        $content = str_replace($search, $replace, $this->tpl);

        if (wp_mail($this->to, $subject, $content, $this->headers)) {
            return true;
        } else {
            return false;
        }
    }
}