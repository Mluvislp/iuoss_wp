<?php

use Orhanerday\OpenAi\OpenAi;

class WordPress_Helpdesk_OpenAI extends WordPress_Helpdesk
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

    public function ajax_get_message()
    {
        
        $response = array(
            'status' => false,
            'message' => ''
        );

        if(!is_user_logged_in()) {
            $response['message'] = 'Not logged in.';
            die(json_encode($response));
        }

        $openAIKey = $this->get_option('integrationsOpenAIKey');
        if(empty($openAIKey)) {
            $response['message'] = 'OpenAI API Key missing.';
            die(json_encode($response));
        }

        $openAI = new OpenAi($openAIKey);
        $openAIOrg = $this->get_option('integrationsOpenAIOrg');
        if(empty($openAIOrg)) {
            $openAI->setORG($openAIOrg);
        }

        $message = esc_html( $_POST['message'] );

        $chat = $openAI->chat([
           'model' => 'gpt-3.5-turbo',
           'messages' => [
               [
                   "role" => "system",
                   "content" => "You are a helpful assistant."
               ],
               [
                   "role" => "user",
                   "content" => $message
               ],
           ],
           'temperature' => 1.0,
           'max_tokens' => 4000,
           'frequency_penalty' => 0,
           'presence_penalty' => 0,
        ]);

        // decode response
        $d = json_decode($chat);

        if(isset($d->error)) {
            $response['message'] = $d->error->message;
            die(json_encode($response));
        }

        // Get Content
        $response['status'] = true;
        $response['message'] = $d->choices[0]->message->content;

        die(json_encode($response));
    }

}