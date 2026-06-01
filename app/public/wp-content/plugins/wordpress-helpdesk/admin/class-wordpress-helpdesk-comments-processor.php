<?php

class WordPress_Helpdesk_Comments_Processor extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;
    
    public $errors = array();
    public $success = array();
    public $comment_id = '';

    private $allowed_tags = array(
            // 'div'           => true,
            'span'          => true,
            'p'             => true,
            'a'             => array(
                'href' => true,
                'target' => array('_blank', '_top'),
            ),
            'u'             => true,
            'i'             => true,
            'q'             => true,
            'b'             => true,
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

    /**
     * Construct Comments Processor
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @param   string                         $plugin_name
     * @param   string                         $version
     */
    public function __construct($plugin_name, $version, $saved_replies)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->saved_replies = $saved_replies;
    }

    /**
     * Init Comments
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return [type] [description]
     */
    public function init()
    {
        global $wordpress_helpdesk_options;
        $this->options = $wordpress_helpdesk_options;
    }

    /**
     * Sanitize comment data
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $data [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function form_sanitation($data, $type)
    {
        if (isset($data['helpdesk_username'])) {
            $data['helpdesk_username'] = sanitize_user($data['helpdesk_username']);
        }
        if (isset($data['helpdesk_email'])) {
            $data['helpdesk_email'] = filter_var($data['helpdesk_email'], FILTER_SANITIZE_EMAIL);
        }
        if (isset($data['helpdesk_post_id'])) {
            $data['helpdesk_post_id'] = intval($data['helpdesk_post_id']);
        }
        if (isset($data['helpdesk_message'])) {
            $data['helpdesk_message'] = wp_kses($data['helpdesk_message'], $this->allowed_tags);
        }

        return $this->validation($data, $type);
    }

    /**
     * Validate Comment data
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $data [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    private function validation($data, $type)
    {
        $errors = array();
        $success = array();
        if (!is_user_logged_in() || $type == "Mail") {

            if (!isset($data['helpdesk_username']) || empty($data['helpdesk_username'])) {
                $errors[] = __('Username not set!', 'wordpress-helpdesk');
            }
            if (!isset($data['helpdesk_email']) || empty($data['helpdesk_email'])) {
                $errors[] = __('Email not set!', 'wordpress-helpdesk');
            }

            $userExists = $this->checkUserExists($data['helpdesk_username'], $data['helpdesk_email']);
            if ($userExists) {
                $success[] = __('User Exists. The comment has been created by your account.', 'wordpress-helpdesk');
                $data['helpdesk_author'] = $userExists;
            } elseif(empty($errors)) {
                $userCreated = $this->createUser($data['helpdesk_username'], $data['helpdesk_email']);
                if ($userCreated) {
                    $success[] = __('We created an account for you – Check your inbox!', 'wordpress-helpdesk');
                    $data['helpdesk_author'] = $userCreated;
                } else {
                    $errors[] = __('User not exists, but account could not be created.', 'wordpress-helpdesk');
                }
            }
        } else {
            if (!isset($data['helpdesk_author']) || empty($data['helpdesk_author'])) {
                $current_user = wp_get_current_user();
                $data['helpdesk_author'] = $current_user->ID;
            }
        }

        if (!isset($data['helpdesk_post_id']) || empty($data['helpdesk_post_id'])) {
            $errors[] = __('Post ID not set!', 'wordpress-helpdesk');
        }

        if (isset($data['helpdesk_post_id'])) {
            $checkPostExists = get_post($data['helpdesk_post_id']);
            if (empty($checkPostExists)) {
                $errors[] = __('Post ID not created!', 'wordpress-helpdesk');
            }
        }

        if (!isset($data['helpdesk_message']) || empty($data['helpdesk_message'])) {
            $errors[] = __('Message not set!', 'wordpress-helpdesk');
        }

        if (!empty($errors)) {
            $this->errors = $errors;
            return false;
        }

        if (!empty($success)) {
            $this->success = $success;
        }

        return $this->create_comment($data);
    }

    /**
     * Create the comment
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function create_comment($data)
    {
        $author = get_userdata($data['helpdesk_author'])->data;

        $commentdata = array(
            'comment_post_ID' => $data['helpdesk_post_id'],
            'comment_author' => $author->display_name,
            'comment_author_email' => $author->user_email,
            'comment_author_url' => '',
            'comment_content' =>  $data['helpdesk_message'],
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => $author->ID,
        );

        //Insert new comment and get the comment ID
        $comment_id = wp_new_comment($commentdata, true);

        if (is_int($comment_id)) {
            $this->comment_id = $comment_id;
            return true;
        } else {
            $this->errors[] = $comment_id;
            return false;
        }
    }

    /**
     * Sanitize Comment Data
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $commentdata [description]
     * @return [type]              [description]
     */
    public function sanitize_comment_data($commentdata)
    {
        if (isset($commentdata['helpdesk_comment_author_url']) && !empty($commentdata['helpdesk_comment_author_url'])) {
            $commentdata['helpdesk_comment_author_url'] = filter_var($commentdata['helpdesk_comment_author_url'], FILTER_SANITIZE_URL);
        }
        if (isset($commentdata['helpdesk_comment_author']) && !empty($commentdata['helpdesk_comment_author'])) {
            $commentdata['helpdesk_comment_author'] = filter_var($commentdata['helpdesk_comment_author'], FILTER_SANITIZE_STRING);
        }
        if (isset($commentdata['helpdesk_comment_author_email']) && !empty($commentdata['helpdesk_comment_author_email'])) {
            $commentdata['helpdesk_comment_author_email'] = filter_var($commentdata['helpdesk_comment_author_email'], FILTER_SANITIZE_EMAIL);
        }
        if (isset($commentdata['helpdesk_comment_post_ID']) && !empty($commentdata['helpdesk_comment_post_ID'])) {
            $commentdata['helpdesk_comment_post_ID'] = intval($commentdata['helpdesk_comment_post_ID']);
        }
        if (isset($commentdata['helpdesk_user_ID']) && !empty($commentdata['helpdesk_user_ID'])) {
            $commentdata['helpdesk_user_ID'] = intval($commentdata['helpdesk_user_ID']);
        }
        if (isset($commentdata['helpdesk_comment_content']) && !empty($commentdata['helpdesk_comment_content'])) {
            $commentdata['helpdesk_comment_content'] = wp_kses($commentdata['helpdesk_comment_content'], $this->allowed_tags);
        }        

        return $commentdata;
    }

    /**
     * Check if automatic reply for ticket reply is available
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $comment_id      [description]
     * @param   [type]                       $comment [description]
     * @return  [type]                                [description]
     */
    public function check_automatic_reply($comment_id, $comment)
    {
        if($this->get_option('savedRepliesAutomatic') && $this->get_option('savedRepliesAutomaticNewReply')) {

            $default_author = $this->get_option('savedRepliesAutomaticUser');
            if(empty($default_author)) {
                return false;
            }

            if($default_author == $comment->user_id) {
                return false;
            }

            $post_id = intval($comment->comment_post_ID);
            $post = get_post($post_id);
            if($post->post_type !== "ticket") {
                return false;
            }
            
            if(!$this->saved_replies->check($post_id, $comment->comment_content, $comment_id)) {
                $this->saved_replies->add_reply( $post_id, $this->get_option('savedRepliesAutomaticNewReplyDefault') );
            }
        }
    }

    /**
     * Check User Exists
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $userName [description]
     * @param  [type] $userMail [description]
     * @return [type]           [description]
     */
    private function checkUserExists($userName, $userMail)
    {
        $userMail = email_exists($userMail);
        if ($userMail) {
            return $userMail;
        }

        $userID = username_exists($userName);
        if ($userID) {
            return $userID;
        }

        return false;
    }

    /**
     * Create User
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param  [type] $userName [description]
     * @param  [type] $userMail [description]
     * @return [type]           [description]
     */
    private function createUser($userName, $userMail)
    {
        // $password = wp_generate_password($length = 12, $include_standard_special_chars = false);
        $userID = wp_create_user($userName, $password, $userMail);

        if ($userID !== false) {
            $user_id_role = new WP_User($userID);
            $user_id_role->set_role('helpdesk_reporter');
            if($this->get_option('supportSendLoginCredentials')) {
                wp_new_user_notification($userID);
            }
        }

        return $userID;
    }
}
