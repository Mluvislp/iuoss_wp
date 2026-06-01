<?php

class WordPress_Helpdesk_Livechat_Backend extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    protected $ticket_processor;
    protected $comment_processor;
    protected $attachments;

    /**
     * Construct Livechat Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $plugin_name        [description]
     * @param   [type]                       $version            [description]
     * @param   [type]                       $ticket_processor   [description]
     * @param   [type]                       $comments_processor [description]
     */
    public function __construct($plugin_name, $version, $ticket_processor, $comments_processor, $attachments)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->ticket_processor = $ticket_processor;
        $this->comments_processor = $comments_processor;
        $this->attachments = $attachments;
    }

    /**
     * Init Livechat
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
     * Init User status
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function set_agents_online()
    {
        if(!$this->get_option('enableLiveChat')) {
            return false;
        }

        $logged_in_users = get_transient('agents_online'); //Get the active users from the transient.
        $user = wp_get_current_user(); //Get the current user's data

        $allowedRoles = array(
            'helpdesk_agent',
            'shop_manager',
            'contributor',
            'author',
            'editor',
            'administrator',
        );

        if (count(array_intersect($allowedRoles, (array) $user->roles)) === 0) {
            return false;
        }

        //Update the user if they are not on the list, or if they have not been online in the last 60 seconds (1 minutes)
        if (!isset($logged_in_users[$user->ID]['last']) || $logged_in_users[$user->ID]['last'] <= time()-60) {
            $logged_in_users[$user->ID] = array(
                'id' => $user->ID,
                'username' => $user->user_login,
                'last' => time(),
            );
            set_transient('agents_online', $logged_in_users, 60); //Set this transient to expire 1 minutes after it is created.
        }
    }

    /**
     * Check if a specific user has been online in the last 1 minutes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $id [description]
     * @return  boolean                          [description]
     */
    public function is_user_online($id)
    {
        $logged_in_users = get_transient('agents_online'); //Get the active users from the transient.
        return isset($logged_in_users[$id]['last']) && $logged_in_users[$id]['last'] > time()-60; //Return boolean if the user has been online in the last 60 seconds (1 minutes).
    }

    /**
     * Check if any user has been online in the last 1 minutes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_users_online()
    {
        $logged_in_users = get_transient('agents_online'); //Get the active users from the transient.
        $count = 0;

        if($logged_in_users) {
            foreach ($logged_in_users as $logged_in_user) {
                if (isset($logged_in_user['last']) && ($logged_in_user['last'] > (time()-60) )) {
                    $count++;
                }
            }
        } 
        
        echo $count;
        die();
    }

    /**
     * Check when a user was last online.
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $id [description]
     * @return  [type]                           [description]
     */
    public function user_last_online($id)
    {
        $logged_in_users = get_transient('agents_online'); //Get the active users from the transient.
        
        //Determine if the user has ever been logged in (and return their last active date if so).
        if (isset($logged_in_users[$id]['last'])) {
            return $logged_in_users[$id]['last'];
        } else {
            return false;
        }
    }

    /**
     * 
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function set_open_chat()
    {
        $ticket = intval($_POST['ticket']);
        $open_chats = get_transient('open_chats'); //Get the active users from the transient

        if(empty($ticket)) {
            return false;
        }
        $ticket = get_post($ticket);

        if(!$open_chats) {
            $open_chats = array(
                $ticket->ID => $ticket->post_title . __(' by ', 'wordpress-helpdesk') . get_userdata($ticket->post_author)->data->display_name
            );
            set_transient('open_chats', $open_chats, 600); //Set this transient to expire 1 minutes after it is created.
        } else {
            $open_chats[$ticket->ID] = $ticket->post_title . ' by ' . $ticket->post_author;
            set_transient('open_chats', $open_chats, 600);
        }
    }

    /**
     * Create Ticket from Livechat
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function create_ticket()
    {
        $type = 'Chat';

        $response = array(
            'status' => 'false',
            'message' => __('Ticket could not be created. Please contact support email directly.', 'wordpress-helpdesk')
        );
        if (isset($_POST['helpdeskTicket'])) {
            ob_start();

            $status = $this->ticket_processor->form_sanitation($_POST, $type);
            $return = ob_get_clean();

            if ($status !== false) {
                if ($_POST['online'] == "true") {
                    $response = array(
                        'status' => 'true',
                        'message' => sprintf( __('A seperate Chat-Ticket with the ID %s has been created.', 'wordpress-helpdesk'), $this->ticket_processor->post_id),
                        'ticket' => $this->ticket_processor->post_id,
                    );
                } else {
                    $response = array(
                        'status' => 'true',
                        'message' => sprintf( __('Message received and Chat-Ticket %s created. You should have an email.', 'wordpress-helpdesk'), $this->ticket_processor->post_id),
                        'ticket' => $this->ticket_processor->post_id,
                    );
                }
            } else {
                $response = array(
                    'status' => 'false',
                    'message' => $this->ticket_processor->errors[0]
                );
            }
        }
        die(json_encode($response));
    }

    public function add_livechat_page()
    {
        if (!$this->get_option('enableLiveChat')) {
            return false;
        }

        add_menu_page(
            __('Live Chat', 'wordpress-helpdesk'),
            __('Live Chat', 'wordpress-helpdesk'),
            'edit_tickets',
            'helpdesk-livechat',
            array($this, 'get_livechat_page'),
            'dashicons-testimonial',
            72
        );
    }

    public function get_livechat_page()
    {
        ?>
        <h2><?php echo __('Live Chat', 'wordpress-helpdesk') ?></h2>

        <div class="wordpress-helpdesk-container wordpress-helpdesk-backend-livechat">
            <div class="wordpress-helpdesk-row">
                <div class="wordpress-helpdesk-col-sm-3">
                    <div id="wordpress-helpdesk-livechat-sidebar" class="wordpress-helpdesk-livechat-sidebar">

                    </div>  
                </div>
                <div class="wordpress-helpdesk-col-sm-9">
                    <div id="wordpress-helpdesk-livechat-main" class="wordpress-helpdesk-livechat-main">
                        <div id="wordpress-helpdesk-livechat-messages" class="wordpress-helpdesk-livechat-messages">

                        </div>
                        <div id="wordpress-helpdesk-livechat-footer" class="wordpress-helpdesk-livechat-footer">
                            <form id="wordpress-helpdesk-livechat-comment-form" class="wordpress-helpdesk-livechat-comment-form" 
                            action="<?php echo esc_url($_SERVER['REQUEST_URI']) ?>" method="post" enctype="multipart/form-data">
                                <input class="wordpress-helpdesk-livechat-comment-form-message" type="text" name="helpdesk_message" placeholder="<?php echo __('Type your message…', 'wordpress-helpdesk') ?>" autofocus>
                                <?php if($this->get_option('liveChatAllowAttachments')) { ?>
                                <div class="wordpress-helpdesk-livechat-comment-form-attachment">
                                    <label for="helpdesk_attachment">
                                        <i class="fa fa-paperclip"></i>
                                    </label>
                                    <input id="helpdesk_attachment" name="helpdesk_attachment" type="file"/>
                                </div>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    /**
     * Get ticket details
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_comments()
    {
        $this->get_ticket();
    }

    /**
     * Post comment to ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function comment_ticket()
    {
        $response = array(
            'status' => 'true',
            'message' => ''
        );

        $status = $this->comments_processor->form_sanitation($_POST, 'Chat');

        if (!$status) {
            $response['status'] = 'false';
        }

        die(json_encode($response));
    }

    /**
     * Post comment to ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function upload_file()
    {
        if (!isset($_POST['ticket']) || empty($_POST['ticket'])) {
            $response['status'] = false;
            $response['error'] = __('Ticket not set.', 'wordpress-helpdesk');
            die(json_encode($response));
        }

        $id = intval($_POST['ticket']);

        $author = get_userdata(get_current_user_id());

        $commentdata = array(
            'comment_post_ID' => $id,
            'comment_author' => $author->display_name,
            'comment_author_email' => $author->user_email,
            'comment_author_url' => '',
            'comment_content' => sanitize_text_field($_FILES['helpdesk-attachments']['name'][0]),
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => $author->ID,
        );

        //Insert new comment and get the comment ID
        $comment_id = wp_new_comment($commentdata, true);

        if (!is_int($comment_id)) {
            die('Error while inserting comment');
        }

        $response = array(
            'status' => 'true',
            'message' => ''
        );

        $status = $this->attachments->save_comment_attachments(null, get_comment($comment_id) );

        if (!$status) {
            $response['status'] = 'false';
            $response['error'] = __('Error while uploading your attachment.', 'wordpress-helpdesk');
        }

        die(json_encode($response));
    }

    public function get_tickets()
    {
        $return = array(
            'tickets' => array(),
            'message' => '',
            'fetched_ticket_ids' => array(),
        );

        $limit = $_POST['limit'];
        $fetched_ticket_ids = !empty($_POST['fetched_ticket_ids']) ? $_POST['fetched_ticket_ids'] : array();
        
        $query_args = array(
            'post_type' => 'ticket',
            'orderby' => 'date',
            'post__not_in' => $fetched_ticket_ids,
            'order' => 'DESC',
            'hierarchical' => false,
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => 'source',
                    'value' => 'chat',
                    'compare' => '='
                ),
            )
        );

        $tickets = get_posts($query_args);

        if(empty($tickets)) {
            $return['message'] = __('No Chat Tickets created yet', 'wordpress-helpdesk');
            die(json_encode($return));
        }

        $return['message'] = sprintf( __('%d Chat Tickets created yet', 'wordpress-helpdesk'), count($tickets));

        $return = array();
        foreach ($tickets as $ticket) {

            if(!empty( $ticket->post_author) ) {
                $author = get_userdata($ticket->post_author)->data->display_name;
            } else {
                $author =  __( 'No Author', 'wordpress-helpdesk' );
            }

            $avatar = get_avatar_url($ticket->post_author);
            $title = $ticket->post_title;
            $id = $ticket->ID;

            $return['tickets'][] = array(
                'author' => $author,
                'avatar' => $avatar,
                'title' => $title,
                'id' => $id,
            );
            $return['fetched_ticket_ids'][] = $id;
        }

        die(json_encode($return));
    }

    /**
     * Get ticket details
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_ticket()
    {
        $response = array(
            'status' => 'false',
            'chat' => '',
            'error' => '',
        );

        if (!isset($_POST['ticket']) || empty($_POST['ticket'])) {
            $response['error'] = __('Ticket not set.', 'wordpress-helpdesk');
            die(json_encode($response));
        }

        $id = intval($_POST['ticket']);
        $ticket = get_post($id);

        if (empty($ticket)) {
            $response['error'] = __('Ticket does not exists.', 'wordpress-helpdesk');
            die(json_encode($response));
        }

        $current_user = wp_get_current_user();
        $roles = $current_user->roles;
        $role = array_shift($roles);
        $notAllowedRoles = array('administrator', 'helpdesk_agent', 'shop_manager', 'author', 'editor', 'contributor');
        if (!in_array($role, $notAllowedRoles) && (intval($ticket->post_author) !== get_current_user_id())) {
        // if (intval($ticket->post_author) !== get_current_user_id()) {
            $response['error'] = __('This is not your Ticket!', 'wordpress-helpdesk');
            die(json_encode($response));
        }

        $data = array();

        // Get Ticket Post Content for First View
        if (isset($_POST['all']) && ($_POST['all'] === "true")) {
            $data[] = array(
                'author_name' => get_userdata($ticket->post_author)->data->display_name,
                'author_img' => get_avatar_url($ticket->post_author),
                'time' => date('H:i', strtotime($ticket->post_date)),
                'content' => $ticket->post_content,
                'agent' => false,
                'attachment_url' => '',
                'attachment_thumb' => '',
            );
        }

        // Process Ticket Comments into Chat
        $args = array(
            'order' => 'ASC',
            'orderby' => 'date',
            'post_id' => $id,
        );

        $comments = get_comments($args);

        $comment_ids = array();
        foreach ($comments as $comment) {
            if (isset($_POST['comment_ids']) && is_array($_POST['comment_ids']) && in_array($comment->comment_ID, $_POST['comment_ids'])) {
                continue;
            }
            $comment_ids[] = $comment->comment_ID;

            $agent = false;
            if($comment->user_id !== $ticket->post_author) {
                $agent = true;
            }

            $full_url = '';
            $thumb_url = '';
            $attachment_ids = get_comment_meta($comment->comment_ID, 'wordpress_helpdesk_attachments');
            if (isset($attachment_ids[0]) && !empty($attachment_ids[0])) {
                $html = '<div class="wordpress-helpdesk-comment-attachments">';

                $attachment_ids = $attachment_ids[0];
                foreach ($attachment_ids as $attachment_id) {
                    $full_url = wp_get_attachment_url($attachment_id);
                    $thumb_url = wp_get_attachment_thumb_url($attachment_id);
                }
            }

            $data[] = array(
                'author_name' => $comment->comment_author,
                'author_img' => get_avatar_url($comment->user_id),
                'time' => date('H:i', strtotime($comment->comment_date)),
                'content' => $comment->comment_content,
                'agent' => $agent,
                'attachment_url' => $full_url,
                'attachment_thumb' => $thumb_url,
            );
        }

        $response = array(
            'status' => 'true',
            'title' => __('Ticket', 'wordpress-helpdesk') . ' :' . $ticket->ID,
            'comment_ids' => $comment_ids,
            'chat' => $data,
        );

        die(json_encode($response));
    }
}