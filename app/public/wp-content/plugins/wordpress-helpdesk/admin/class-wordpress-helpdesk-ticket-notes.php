<?php

class WordPress_Helpdesk_Ticket_Notes extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;

    protected $options;

    /**
     * Construct Custom Ticket Post Type Class
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
     * Init Ticket post type class
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
     * Add custom ticket metaboxes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @param   [type]                       $post_type [description]
     * @param   [type]                       $post      [description]
     */
    public function add_custom_metaboxes($post_type, $post)
    {
        add_meta_box('wordpress-helpdesk-notes', __('Private Notes', 'wordpress-helpdesk'), array($this, 'notes_metabox'), 'ticket', 'side', 'default');
    }

    /**
     * Display Metabox Private Notes
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.4.4
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function notes_metabox()
    {
        global $post;

        $notes = get_post_meta($post->ID, 'notes', true);

        ?>
            <ul class="ticket_notes">
                <?php 
                if($notes && !empty($notes)) {
                    foreach ($notes as $key => $note) {
                        echo 
                        '<li id="note-' . $key . '" class="note">
                            <div class="note_content">
                                <p>' . $note['note'] . '</p>
                            </div>
                            <p class="meta">
                                <abbr class="exact-date">' . sprintf( __('created on %s by %s', 'wordpress-helpdesk' ), $note['date'], $note['author']) . '</abbr>
                                <a href="#" data-id="' . $key . '" class="delete_note">' . __('Delete Note', 'wordpress-helpdesk') . '</a>
                            </p>
                        </li>';
                    }
                }
                ?>
            </ul>       
            <div class="add_ticket_note">
                    <h4><?php echo __('Add Note', 'wordpress-helpdesk') ?><span class="woocommerce-help-tip"></span></h4>
                    <p>
                        <input type="hidden" name="ticket_note_ticket_id" id="ticket_note_ticket_id" value="<?php echo $post->ID ?>">
                        <textarea type="text" name="ticket_note" id="ticket_note" class="input-text" cols="20" rows="5"></textarea>
                    </p>
                    <p>
                    <a href="#" id="add_ticket_note_button" class="button"><?php echo __('Add', 'wordpress-helpdesk') ?></a>
                </p>
            </div>
        <?php
    }

    public function create_ticket_note()
    {
        $noteText = $_POST['note'];
        $ticketId = $_POST['id'];

        if ( is_user_logged_in() ) {
            $user                 = get_user_by( 'id', get_current_user_id() );
            $note_author       = $user->display_name;
        } else {
            $note_author        = __( 'Ticket Sytem', 'wordpress-helpdesk' );
        }

        $note = array(
            'note' => strip_tags($noteText),
            'date' => date_i18n( get_option( 'date_format' ), current_time('timestamp')),
            'author' => $note_author
        );

        $notes = get_post_meta($ticketId, 'notes', true);

        if(!$notes) {
            $notes = array($note);
        } else {
            $notes[] = $note;
        }

        update_post_meta($ticketId, 'notes', $notes);

        end($notes);
        $key = key($notes);

        $output = '<li id="note-' . $key . '" class="note">
                        <div class="note_content">
                            <p>' . $note['note'] . '</p>
                        </div>
                        <p class="meta">
                            <abbr class="exact-date">' . sprintf( __('created on %s by %s', 'wordpress-helpdesk' ), $note['date'], $note['author']) . '</abbr>
                            <a href="#" data-id="' . $key . '" class="delete_note">' . __('Delete Note', 'wordpress-helpdesk') . '</a>
                        </p>
                    </li>';

        die(json_encode($output));
    }

    public function delete_ticket_note()
    {
        $noteID = $_POST['noteID'];
        $ticketId = $_POST['id'];

        $notes = get_post_meta($ticketId, 'notes', true);

        if(!$notes) {
            echo "no notes";
            return false;
        }

        unset($notes[$noteID]);

        update_post_meta($ticketId, 'notes', $notes);

        die();
    }
}