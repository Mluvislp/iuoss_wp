<?php

class WordPress_Helpdesk_Crisp extends WordPress_Helpdesk
{
    protected $plugin_name;
    protected $version;
    protected $options;
    protected $ticket_processor;
    protected $comments_processor;

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
    public function __construct($plugin_name, $version, $ticket_processor, $comments_processor)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->ticket_processor = $ticket_processor;
        $this->comments_processor = $comments_processor;
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

        if(!$this->get_option('enableLiveChatCrisp')) {
            return false;
        }

        if (isset($_GET['crisp-get-session'])) {

            $crispAccount = $this->get_option('liveChatCrispAccount');

            if(!isset($crispAccount['username']) || empty($crispAccount['username'])) {
                echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>Email Missing</p>';
                echo '</div>';
                return false;
            }

            if(!isset($crispAccount['password']) || empty($crispAccount['password'])) {
                echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>Password Missing</p>';
                echo '</div>';
                return false;
            }

            $email = $crispAccount['username'];
            $password = $crispAccount['password'];

            $session = $this->get_session($email, $password);

            if(!isset($session['data']['identifier']) || empty($session['data']['identifier'])) {
                echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>Identifier Empty</p>';
                echo '</div>';
                return false;
            }

            if(!isset($session['data']['key']) || empty($session['data']['key'])) {
                echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>Key Empty</p>';
                echo '</div>';
                return false;
            }

            echo '<div class="notice notice-success is-dismissible">';
                echo '<p>Identifier: ' . $session['data']['identifier'] . '<br>Key: ' . $session['data']['key'] . '<br>Enter the Identifier & Key into settings.</p>';
            echo '</div>';
            return true;
        }

        if (isset($_GET['crisp-get-websites'])) {
            $this->authenticate();
            $websites = $this->get_websites();

            if(empty($websites)) {
                echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>No Websites found.</p>';
                echo '</div>';
                return false;
            }

            echo '<div class="notice notice-success is-dismissible">';
                foreach ($websites as $website) {
                    echo '<p>Website: ' . $website['name'] . '<br>Key: ' . $website['id'] . '.<br><br></p>';
                }
                
            echo '</div>';
            return true;
        }

        if (isset($_GET['crisp-get-conversations'])) {
            $this->authenticate();

            $website = $this->get_option('liveChatCrispWebsite');
            $this->get_conversations($website);
        }
    }

    /**
     * Get Crisp Session
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function get_session($email, $password)
    {
        $ch = curl_init();

        $data = json_encode( array( 'email' => $email, 'password' => $password ));

        curl_setopt($ch, CURLOPT_URL,"https://api.crisp.chat/v1/user/session/login");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Content-Type: application/json',
                                            'Connection: Keep-Alive'
                                            ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        return json_decode($server_output, true);
    }

    /**
     * Authenticate Crisp
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://www.welaunch.io
     * @return  [type]                       [description]
     */
    public function authenticate()
    {
        $session = $this->get_option('liveChatCrispSession');

        if(!isset($session['username']) || empty($session['username'])) {
            echo '<div class="notice notice-error is-dismissible">';
                echo '<p>Email Missing</p>';
            echo '</div>';
            return false;
        }

        if(!isset($session['password']) || empty($session['password'])) {
            echo '<div class="notice notice-error is-dismissible">';
                echo '<p>Password Missing</p>';
            echo '</div>';
            return false;
        }

        $identifier = $session['username'];
        $key = $session['password'];

        $this->client = new Crisp();

        $this->website = $website;
        // Identifier + Key
        $this->client->authenticate($identifier, $key);
    }


    public function get_websites()
    {
        $websites = $this->client->userWebsites->get();
        return $websites;
    }

    public function get_conversations($website)
    {
        if(!isset($website) || empty($website)) {
            echo '<div class="notice notice-error is-dismissible">';
                echo '<p>Website Key Missing</p>';
            echo '</div>';
            return false;
        }
        
        $conversations = $this->client->websiteConversations->getList($website);
        // var_dump($conversations);
        // die();
    }
}