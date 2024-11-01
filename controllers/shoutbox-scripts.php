<?php

class shoutbox2024_scripts
{
    public $version;

    public function __construct($version)
    {
        $this->version = $version;
    }

    public function enqueue_scripts()
    {
        add_action('wp_enqueue_scripts', array($this, 'include_scripts_styles'));
    }

    public function include_scripts_styles()
    {
        wp_register_script('shoutbox2024_js', plugins_url('js/shoutbox.js', dirname(__FILE__)), [], $this->version, false);
        wp_enqueue_script('shoutbox2024_js');

        wp_register_style('shoutbox2024_css', plugins_url('css/shoutbox-style.css', dirname(__FILE__)), [], $this->version);
        wp_enqueue_style('shoutbox2024_css');

        $nonce = wp_create_nonce("shoutbox2024_nonce");

        $shoutbox_ajax = new shoutbox2024_ajax();
        $shoutbox_ajax->initialize();

        $config_array = array(
            'ajaxURL' => admin_url('admin-ajax.php'),
            'ajaxActions' => $shoutbox_ajax->actions,
            'ajaxNonce' => $nonce,
            'siteURL' => site_url(),
            'pluginsURL' => plugins_url(),
            'userID' => get_current_user_id(),
            'isAdmin' => current_user_can('administrator'),
            'refreshInterval' => get_option('shoutbox2024_refresh-interval'),
            'locale' => get_option('shoutbox2024_locale'),
            'timezone' => wp_timezone_string(),
            'defaultColour' => get_option('shoutbox2024_default-font-colour'),
            'pmBGColour' => get_option('shoutbox2024_pm-background-colour'),
            'newMsgBgColour' => get_option('shoutbox2024_new-message-colour'),
            'shoutboxHeight' => get_option('shoutbox2024_height'),
        );

        wp_localize_script('shoutbox2024_js', 'shoutbox2024_conf', $config_array);
    }

}