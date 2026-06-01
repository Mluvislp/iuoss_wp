<?php

class WordPress_Helpdesk_Attachments extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;

    /**
     * Construct Attachments Class
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

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
    }

    /**
     * Init Attachments
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
     * Save comment attachments
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function save_comment_attachments($id, $comment)
    {
        if (!isset($comment->comment_post_ID) || empty($comment->comment_post_ID)) {
            return false;
        }

        $postID = $comment->comment_post_ID;
        $post = get_post($postID);

        if ($post->post_type !== "ticket") {
            return false;
        }

        $attachment_ids = array();
        if (isset($_FILES['helpdesk-attachments']) && !empty($_FILES['helpdesk-attachments'])) {
            
            $files =  $this->diverse_array($_FILES['helpdesk-attachments']);
            $upload_overrides = array( 'test_form' => false );

            foreach ($files as $file) {

                $secCheck = $this->check_extension($file);
                if(!$secCheck) {
                    continue;
                }

                $movefile = wp_handle_upload($file, $upload_overrides);
                if ($movefile && ! isset($movefile['error'])) {
                    $attachment_ids[] = $this->insert_attachment($movefile['file'], $postID);
                }
            }
        }
        
        if (!empty($attachment_ids)) {
            update_comment_meta($comment->comment_ID, 'wordpress_helpdesk_attachments', $attachment_ids);
        }
        return $attachment_ids;
    }

    /**
     * Save post / ticket attachments
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  boolean
     */
    public function save_ticket_attachments($post_id, $post, $update)
    {
        if ($post->post_type !== "ticket") {
            return false;
        }

        $attachment_ids = array();
        if (isset($_FILES['helpdesk-attachments']) && !empty($_FILES['helpdesk-attachments'])) {
            $files =  $this->diverse_array($_FILES['helpdesk-attachments']);
            $upload_overrides = array( 'test_form' => false );

            foreach ($files as $file) {
                $secCheck = $this->check_extension($file);
                if(!$secCheck) {
                    continue;
                }

                $movefile = wp_handle_upload($file, $upload_overrides);

                if ($movefile && ! isset($movefile['error'])) {
                    $attachment_ids[] = $this->insert_attachment($movefile['file'], $post_id);
                }
            }
        }

        if (!empty($attachment_ids)) {
            update_post_meta($post_id, 'wordpress_helpdesk_attachments', $attachment_ids);
        }
        return true;
    }

    /**
     * Diverse array of $_FILES
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  array
     */
    private function diverse_array($files)
    {
        $result = array();
        foreach ($files as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                $result[$key2][$key1] = $value2;
            }
        }
        return $result;
    }

    /**
     * Insert attachments
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  attachment_id
     */
    private function insert_attachment($filename, $post_id)
    {
        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype(basename($filename), null);

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
        wp_update_attachment_metadata($attach_id, $attach_data);

        set_post_thumbnail($post_id, $attach_id);

        return $attach_id;
    }

    /**
     * Show Comment Attachments
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://www.welaunch.io
     * @return  array
     */
    public function show_comment_attachments($comment_text, $comment)
    {
        if(get_post_type($comment->post_id) === "ticket") {
            $attachment_ids = get_comment_meta($comment->comment_ID, 'wordpress_helpdesk_attachments');
            
            if (isset($attachment_ids[0]) && !empty($attachment_ids[0])) {
                $html = '<div class="wordpress-helpdesk-comment-attachments">';

                $attachment_ids = $attachment_ids[0];

                foreach ($attachment_ids as $attachment_id) {

                    $attachment = get_post($attachment_id);
                    $full_url = wp_get_attachment_url($attachment_id);
                    $thumb_url = wp_get_attachment_thumb_url($attachment_id);

                    $image_mime_types = array(
                        'image/png',
                        'image/jpeg',
                        'image/jpeg',
                        'image/jpeg',
                        'image/gif',
                        'image/bmp',
                        'image/vnd.microsoft.icon',
                        'image/tiff',
                        'image/tiff',
                        'image/svg+xml',
                        'image/svg+xml',
                    );

                    $isImage = false;
                    if(in_array($attachment->post_mime_type, $image_mime_types)) {
                        $isImage = true;
                    }

                    if($isImage) {
                        $html .= '<a class="is-image" href="' . $full_url . '" target="_blank">';
                            $html .= '<img src="' . $thumb_url . '" alt="">';
                        $html .= '</a>';
                    } else {
                        $html .= '<a href="' . $full_url . '" target="_blank">';
                            $html .= '<i class="fa fa-download"></i> ' . $attachment->post_title;
                        $html .= '</a>';
                    }
                }
                
                $html .= '</div>';
                $comment_text = $comment_text . $html;
            }
        }

        return $comment_text;
    }

    private function check_extension($file_name, $allow_all_types = false)
    {
        $allowed_types = array(
            /* images extensions */
            'jpeg', 'bmp', 'png', 'gif', 'tiff', 'jpg',
            /* audio extensions */
            'mp3', 'wav', 'midi', 'aac', 'ogg', 'wma', 'm4a', 'mid', 'orb', 'aif',
            /* movie extensions */                              
            'mov', 'flv', 'mpeg', 'mpg', 'mp4', 'avi', 'wmv', 'qt',
            /* document extensions */                               
            'txt', 'pdf', 'ppt', 'pps', 'xls', 'doc', 'xlsx', 'pptx', 'ppsx', 'docx'
         );


        $mime_type_black_list= array(
            # HTML may contain cookie-stealing JavaScript and web bugs
            'text/html', 'text/javascript', 'text/x-javascript',  'application/x-shellscript',
            # PHP scripts may execute arbitrary code on the server
            'application/x-php', 'text/x-php', 'text/x-php',
            # Other types that may be interpreted by some servers
            'text/x-python', 'text/x-perl', 'text/x-bash', 'text/x-sh', 'text/x-csh',
            'text/x-c++', 'text/x-c',
            # Windows metafile, client-side vulnerability on some systems
            # 'application/x-msmetafile',
            # A ZIP file may be a valid Java archive containing an applet which exploits the
            # same-origin policy to steal cookies      
            # 'application/zip',
        );


        $tmp_file_extension = strtolower(pathinfo($file_name['name'], PATHINFO_EXTENSION));

        if(!strlen($tmp_file_extension) || (!$allow_all_types &&
          !in_array($tmp_file_extension, $allowed_types))) {
            return false;
        }

        $mime = $file_name['type'];

        $mime = explode(" ", $mime);
        $mime = $mime[0];

        if (substr($mime, -1, 1) == ";") {
            $mime = trim(substr($mime, 0, -1));
        }

        return (in_array($mime, $mime_type_black_list) == false);
    }
}