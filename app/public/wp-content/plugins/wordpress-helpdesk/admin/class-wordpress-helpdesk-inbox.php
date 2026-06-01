<?php

class WordPress_Helpdesk_Inbox extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;
    protected $ticket_processor;
    protected $comment_processor;

    private $mailbox;

    private $ticketPattern = '/^.*\[Ticket:\s+(\d+)\].*$/';
    private $mailPattern = '/<body[^>]*>(.*?)<\/body>/is';
    private $purchaseCodePattern = '/purchase code(=|:)(.*)/i';

    /**
     * Construct Inbox Class
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $plugin_name       [description]
     * @param   [type]                       $version           [description]
     * @param   [type]                       $ticket_processor  [description]
     * @param   [type]                       $comment_processor [description]
     */
    public function __construct($plugin_name, $version, $ticket_processor, $comment_processor)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->ticket_processor = $ticket_processor;
        $this->comment_processor = $comment_processor;
    }

    /**
     * Init Inbox
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

        if(!$this->get_option('enableInbox') || empty($this->get_option('mailAccountHost'))) {
            return false;
        }

        $tpl = $this->get_option('supportMailTemplate');
        
        $supportName = $this->get_option('supportName');
        $supportMail = $this->get_option('supportMail');
        $supportLogo = $this->get_option('supportLogo');
        $supportFooter = $this->get_option('supportFooter');

        $tpl = str_replace('{{support_name}}', $supportName, $tpl);
        $tpl = str_replace('{{support_logo}}', $supportLogo['url'], $tpl);
        $tpl = str_replace('{{footer}}', $supportFooter, $tpl);

        $this->headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To:' . $supportName. ' <' . $supportMail . '>',
            'From:' . $supportName. ' <' . $supportMail . '>',
        );

        $this->tpl = $tpl;

        // if (! wp_next_scheduled ( 'run_wordpress_helpdesk_inbox_fetching' )) {
        //     $recurrence = $this->get_option('mailAccountRecurrence');
        //     if(empty($recurrence)) {
        //         $recurrence = 'hourly';
        //     }
        //     wp_schedule_event(time(), $recurrence, 'run_wordpress_helpdesk_inbox_fetching');
        // }

        if (isset($_GET['check-inbox'])) {
            if(!extension_loaded('imap')) {
                echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>IMAP PHP Extension not installed.</p>';
                echo '</div>';
            } else {
                if ($this->setupMailbox()) {
                    $this->test_inbox();
                };
            }

        }
        if (isset($_GET['check-folders'])) {
            if ($this->setupMailbox()) {
                $this->get_folders();
            };
        }
        if (isset($_GET['fetch-now'])) {
            if ($this->setupMailbox()) {
                $this->checkInbox();
            }
        }
        
        if(is_user_logged_in() && $this->setupMailbox()) {

            $currentTime = time();
            $lastTimeChecked = get_option('helpdesk_inbox_check');
            if(!$lastTimeChecked) {
                $lastTimeChecked = update_option('helpdesk_inbox_check', $currentTime);
            } else {
                $nextTimeCheck = $lastTimeChecked + (60 * 60);
                if($currentTime < $nextTimeCheck) {
                    return false;
                } else {
                    $lastTimeChecked = update_option('helpdesk_inbox_check', $currentTime);
                }
            }
    
            $this->checkInbox();
        }
    }

    /**
     * Setup the Inbox
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function setupMailbox()
    {
        $mailAccountEmail = $this->get_option('mailAccountEmail');
        $mailAccountUser = $this->get_option('mailAccountUser');
        $user = $mailAccountUser['username'];
        $password = $mailAccountUser['password'];
        $mailAccountHost = $this->get_option('mailAccountHost');
        $mailAccountProtocol = $this->get_option('mailAccountProtocol');
        $mailAccountPort = $this->get_option('mailAccountPort');
        $mailAccountType = $this->get_option('mailAccountType');
        $mailAccountFolder = $this->get_option('mailAccountFolder');
        $mailAccountNovalidateCert = $this->get_option('mailAccountNovalidateCert');
        if($mailAccountNovalidateCert == "1") {
            $mailAccountNovalidateCert = "/novalidate-cert";
        } else {
            $mailAccountNovalidateCert = "";
        }

        try {
            $this->mailbox = new PhpImap\Mailbox('{' . $mailAccountHost . ':' . $mailAccountPort . '/' . $mailAccountType . '/' . $mailAccountProtocol . $mailAccountNovalidateCert . '}' . $mailAccountFolder, $user, $password, wp_upload_dir()['path']);
            $this->mailbox->statusMailbox();
        } catch (Exception $e) {
            echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . $e->getMessage() . '</p>';
            echo '</div>';
            return false;
        }

        return true;
    }

    /**
     * Test the Inbox
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function test_inbox()
    {
        $check = $this->mailbox->checkMailbox();
        echo '<div class="notice notice-success is-dismissible">';
            echo '<h2>Test Successfull</h2>';
            echo '<p>';
                echo 'Date: ' . $check->Date . '<br/>';
                echo 'Driver: ' . $check->Driver . '<br/>';
                echo 'Mailbox: ' . $check->Mailbox . '<br/>';
                echo 'Messages: ' . $check->Nmsgs;
            echo '</p>';
        echo '</div>';

        return true;
    }

    /**
     * Get Folders – this helps to find out what folders are available
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_folders()
    {
        $folders = $this->mailbox->getListingFolders();

        if (empty($folders) || empty($folders[0])) {
            echo '<div class="notice notice-error is-dismissible">';
                echo '<h2>No folders found!</h2>';
                echo '<p>Please make sure you have not set "INBOX" in the folder settings.</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-success is-dismissible">';
                echo '<h2>Folders found:</h2>';
            foreach ($folders as $folder) {
                echo $folder . '<br/>';
            }
            echo '</div>';
        }
    }

    public function run_cronjob()
    {
        if(!$this->get_option('enableInbox') || empty($this->get_option('mailAccountHost'))) {
            return false;
        }

        if ($this->setupMailbox()) {
            $this->checkInbox();
        }
    }

    /**
     * Check Inbox for new Mails
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    protected function checkInbox()
    {
        $unseenMailIds = $this->getUnseenMails();
        if (empty($unseenMailIds)) {
            return false;
        }

        foreach ($unseenMailIds as $unseenMailId) {
            $mail = $this->mailbox->getMail($unseenMailId, false);
            if (empty($mail)) {
                continue;
            }

            $isReply = $this->isReply($mail);
            
            if (!$isReply) {
                $created = $this->processAsTicket($mail);
            } else {
                $created = $this->processAsReply($mail, $isReply);
            }

            if ($created) {
                $this->mailbox->markMailAsRead($unseenMailId);

                $archiveFolder = $this->get_option('mailAccountArchiveFolder');
                if (!empty($archiveFolder)) {
                    $this->mailbox->moveMail($unseenMailId, $archiveFolder);
                }
            } else {
                $this->mailbox->markMailAsRead($unseenMailId);                
            }
        }
    }

    /**
     * Returns all Unseen Mails
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    protected function getUnseenMails()
    {
        return $this->mailbox->searchMailbox('UNSEEN');
    }

    /**
     * Check if mail is a reply or a new ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $mail [description]
     * @return  boolean                            [description]
     */
    protected function isReply($mail)
    {
        $subject = $mail->subject;

        $matches = array();
        preg_match($this->ticketPattern, $subject, $matches);

        if (empty($matches)) {
            return false;
        }

        if (!isset($matches[1])) {
            return false;
        }

        return $matches[1];
    }

    /**
     * Process Mail as new Ticket / Post
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $mail [description]
     * @return  [type]                             [description]
     */
    protected function processAsTicket($mail)
    {
        $type = 'Mail';
        $message = $mail->textPlain;

        if (!empty($mail->textHtml)) {
            $message = $this->formatMailText($mail->textHtml);
            if (!$message) {
                $message = $mail->textPlain;
            }
        }
        $message = trim($message);

        if(empty($mail->fromName)) {
            $mail->fromName = $mail->fromAddress;
        }
        
        if(empty($message)) {
            $errors = array(__('No Message in your Email detected.', 'wordpress-helpdesk'));
            $this->mailerror($mail->fromName, $mail->fromAddress, $errors);
            return false;
        }

        $data = array(
            'helpdesk_username' => $mail->fromName,
            'helpdesk_email' => $mail->fromAddress,
            'helpdesk_subject' => wp_strip_all_tags($mail->subject),
            'helpdesk_message' => $message,
        );

        $purchaseCodeRequired = $this->get_option('integrationsEnvatoPurchaseCodeRequired');
        if($purchaseCodeRequired) {
            $checkPurchaseCode = $this->checkPurchaseCode($message);
            if(!$checkPurchaseCode) {
                $errors = array(
                    __('No purchase code detected. Please make sure the email contains the following pattern "Purchase Code: XXX".', 'wordpress-helpdesk')
                );
                $this->mailerror($mail->fromName, $mail->fromAddress, $errors);
                return false;
            }
            $type = "Envato";
            $data['helpdesk_purchase_code'] = $checkPurchaseCode;
        }

        $status = $this->ticket_processor->form_sanitation($data, $type);

        if (!$status) {
            $this->mailerror($mail->fromName, $mail->fromAddress, $this->ticket_processor->errors);
            return false;
        }

        $this->checkAttachments($mail, $this->ticket_processor->post_id);

        return true;
    }

    /**
     * Process Mail as Reply to a Ticket
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $mail   [description]
     * @param   [type]                       $post_id [description]
     * @return  [type]                               [description]
     */
    protected function processAsReply($mail, $post_id)
    {
        $message = $mail->textPlain;

        if (!empty($mail->textHtml)) {
            $message = $this->formatMailText($mail->textHtml);
            if (!$message) {
                $message = $mail->textPlain;
            }
        }
        $message = trim($message);

        if(empty($message)) {
            $errors = array(__('No Message in your Email detected.', 'wordpress-helpdesk'));
            $this->mailerror($mail->fromName, $mail->fromAddress, $errors);
            return false;
        }

        if(empty($post_id) || !get_post($post_id)) {
            $errors = array(__('Ticket ID for your Reply could not be found.', 'wordpress-helpdesk'));
            $this->mailerror($mail->fromName, $mail->fromAddress, $errors);
            return false;
        }

        $data = array(
            'helpdesk_username' => $mail->fromName,
            'helpdesk_email' => $mail->fromAddress,
            'helpdesk_post_id' => $post_id,
            'helpdesk_subject' => wp_strip_all_tags($mail->subject),
            'helpdesk_message' => $message,
        );

        $comment_id = $this->comment_processor->form_sanitation($data, 'Mail');

        if (!$comment_id || is_wp_error($comment_id)) {
            $this->mailerror($mail->fromName, $mail->fromAddress, $comment_id);
        }

        $this->checkAttachments($mail, $post_id, $comment_id);

        return true;
    }

     /**
     * Try formatting mail HTML (get body of mail)
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $mail   [description]
     * @param   [type]                       $post_id [description]
     * @return  [type]                               [description]
     */
    private function formatMailText($html)
    {
        $matches = array();
        preg_match($this->mailPattern, $html, $matches);

        if (empty($matches)) {
            return false;
        }

        if (!isset($matches[1])) {
            return false;
        }

        return $matches[1];
    }

    private function checkPurchaseCode($html)
    {
        $matches = array();
        preg_match($this->purchaseCodePattern, $html, $matches);

        if (empty($matches)) {
            return false;
        }

        if (!isset($matches[2])) {
            return false;
        }

        return $matches[2];
    }

    /**
     * To be checked
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $mail   [description]
     * @param   [type]                       $post_id [description]
     * @return  [type]                               [description]
     */
    private function mailerror($fromName, $fromAddress, $errors)
    {
        if (is_array($errors)) {
            
            $content = implode('<br>', $errors);
            $subject = __('Sorry ...', 'wordpress-helpdesk') . '';
            $search = array('{{content}}' , '{{ticket_link}}', '{{title}}', '{{ticket_link_text}}');
            $replace = array(
                $content,
                get_site_url(),
                __('Sorry we could not process your mail :(', 'wordpress-helpdesk'),
                __('Visit our Website', 'wordpress-helpdesk'),
            );
            $content = str_replace($search, $replace, $this->tpl);
            
            if (wp_mail($fromName . '<' . $fromAddress . '>', $subject, $content, $this->headers)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Check Attachments in Mail
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $mail       [description]
     * @param   [type]                       $post_id     [description]
     * @param   boolean                      $comment_id [description]
     * @return  [type]                                   [description]
     */
    private function checkAttachments($mail, $post_id, $comment_id = false)
    {
        $attachments = $mail->getAttachments();

        if (empty($attachments)) {
            return false;
        }

        $attachment_ids = array();
        $wp_upload_dir = wp_upload_dir();
        foreach ($attachments as $attachment) {
            $fileName = $attachment->name;
            $filePath = $attachment->filePath;

            $filetype = wp_check_filetype(basename($filePath), null);

            // Prepare an array of post data for the attachment.
            $attachment = array(
                'guid'           => $wp_upload_dir['path'] . '/' . basename($filePath),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filePath)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attachmentInsert = wp_insert_attachment($attachment, $filePath, $post_id, true);

            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once(ABSPATH . 'wp-admin/includes/image.php');
             
            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata($attachmentInsert, $filePath);
            wp_update_attachment_metadata($attachmentInsert, $attach_data);

            $attachment_ids[] = $attachmentInsert;
        }

        if ($is_comment !== false) {
            update_comment_meta($comment_id, 'wordpress_helpdesk_attachments', $attachment_ids);
        } else {
            update_post_meta($post_id, 'wordpress_helpdesk_attachments', $attachment_ids);
        }
    }
}
