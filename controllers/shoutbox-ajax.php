<?php

class shoutbox2024_ajax
{
    public $actions;
    public $allowed_html = array(
        'span' => array(
            'style' => array(),
        )
    );

    public function __construct()
    {

        if (session_id() == '') {
            session_start();
        }
    }

    public function initialize()
    {
        $this->configure_actions();
    }

    public function configure_actions()
    {
        $this->actions = array(
            // Get Shout Messages
            "shoutbox2024_get_shouts" => array("action" => "shoutbox2024_get_shouts", "function" => "shoutbox2024_get_shouts_function", "logged" => true),

            // Get Smilies
            "shoutbox2024_get_smilies" => array("action" => "shoutbox2024_get_smilies", "function" => "shoutbox2024_get_smilies_function", "logged" => true),

            // Add Shout
            "shoutbox2024_add_shout" => array("action" => "shoutbox2024_add_shout", "function" => "shoutbox2024_add_shout_function", "logged" => true),

            // Add PM
            "shoutbox2024_add_pm" => array("action" => "shoutbox2024_add_pm", "function" => "shoutbox2024_add_pm_function", "logged" => true),

            // Delete Shout
            "shoutbox2024_delete_shout" => array("action" => "shoutbox2024_delete_shout", "function" => "shoutbox2024_delete_shout_function", "logged" => true),
        );

        /*
         * Add the AJAX actions into WordPress
         */
        foreach ($this->actions as $custom_key => $custom_action) {

            if (isset($custom_action["logged"]) && $custom_action["logged"]) {
                // Actions for users who are logged in
                add_action("wp_ajax_" . $custom_action['action'], array($this, $custom_action["function"]));
            } else if (isset($custom_action["logged"]) && !$custom_action["logged"]) {
                // Actions for users who are not logged in
                add_action("wp_ajax_nopriv_" . $custom_action['action'], array($this, $custom_action["function"]));
            } else {
                // Actions for users who are logged in and not logged in
                add_action("wp_ajax_nopriv_" . $custom_action['action'], array($this, $custom_action["function"]));
                add_action("wp_ajax_" . $custom_action['action'], array($this, $custom_action["function"]));
            }
        }

    }

    public function shoutbox2024_get_shouts_function()
    {
        // Check for nonce security      
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'shoutbox2024_nonce')) {
            die('Nah m8!');
        }
        header("Content-Type: application/json");

        global $wpdb;
        $userId = get_current_user_id();

        $shouts = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching	
            $wpdb->prepare("SELECT * FROM ( SELECT s.id, s.shout_from, s.shout_to, u.display_name, s.timestamp, s.message, s.archived FROM {$wpdb->base_prefix}shoutbox_shouts s JOIN {$wpdb->base_prefix}users u ON s.shout_from = u.ID WHERE s.shout_to IS NULL OR s.shout_to = %d or (s.shout_from = %d AND s.shout_to IS NOT NULL) ORDER BY s.id DESC LIMIT 25 ) AS sub ORDER BY id ASC;", $userId, $userId)
        );

        if ($wpdb->last_error) {
            throw new Exception(esc_html($wpdb->last_error), 1);
        }

        echo wp_json_encode($shouts);
        exit;
    }

    public function shoutbox2024_get_smilies_function()
    {
        // Check for nonce security      
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'shoutbox2024_nonce')) {
            die('Nah m8!');
        }
        header("Content-Type: application/json");

        global $wpdb;

        $smilies = $wpdb->get_results("SELECT name, code, url FROM {$wpdb->base_prefix}shoutbox_smilies WHERE code <> '';"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching	

        if ($wpdb->last_error) {
            throw new Exception(esc_html($wpdb->last_error), 1);
        }

        echo wp_json_encode($smilies);
        exit;
    }

    public function shoutbox2024_add_shout_function()
    {
        // Check for nonce security      
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'shoutbox2024_nonce')) {
            die('Nah m8!');
        }
        header("Content-Type: application/json");

        global $wpdb;
        $userId = get_current_user_id();
        $message = wp_kses($_POST['message'], $this->allowed_html);

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching	
            $wpdb->base_prefix . 'shoutbox_shouts',
            array(
                'shout_from' => $userId,
                'message' => $message
            )
        );

        if ($wpdb->last_error) {
            throw new Exception(esc_html($wpdb->last_error), 1);
        }

        echo wp_json_encode(array("status" => "success", "id" => $wpdb->insert_id));
        exit;
    }

    public function shoutbox2024_add_pm_function()
    {
        // Check for nonce security      
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'shoutbox2024_nonce')) {
            die('Nah m8!');
        }
        header("Content-Type: application/json");

        global $wpdb;
        $userId = get_current_user_id();
        $message = wp_kses($_POST['message'], $this->allowed_html);
        $to = sanitize_text_field($_POST['shout_to']);

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching	
            $wpdb->base_prefix . 'shoutbox_shouts',
            array(
                'shout_from' => $userId,
                'shout_to' => $to,
                'message' => $message
            )
        );

        if ($wpdb->last_error) {
            throw new Exception(esc_html($wpdb->last_error), 1);
        }

        echo wp_json_encode(array("status" => "success", "id" => $wpdb->insert_id));
        exit;
    }

    public function shoutbox2024_delete_shout_function()
    {
        // Check for nonce security      
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'shoutbox2024_nonce')) {
            die('Nah m8!');
        }

        // Check if current user is admin and can archive shouts
        if (!current_user_can('administrator')) {
            header("Content-Type: application/json");
            echo wp_json_encode(array("status" => "error", "message" => "Permission Denied"));
            exit;
        }
        header("Content-Type: application/json");

        global $wpdb;
        $shoutId = sanitize_text_field($_POST['shout_id']);

        $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching	
            $wpdb->base_prefix . 'shoutbox_shouts',
            array(
                'archived' => 1
            ),
            array(
                'id' => $shoutId
            )
        );

        if ($wpdb->last_error) {
            throw new Exception(esc_html($wpdb->last_error), 1);
        }

        echo wp_json_encode(array("status" => "success"));
        exit;
    }
}

