<?php

class shoutbox2024_schedule
{
    public function __construct()
    {
        add_action('shoutbox2024_cleanup', array($this, 'cleanup_shouts'));
    }

    public function cleanup_shouts()
    {
        global $wpdb;
        $shoutbox_options = get_option('shoutbox2024_options');
        $shoutbox_cleanup = $shoutbox_options['shoutbox2024_cleanup'];

        $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching	
            $wpdb->prepare("DELETE FROM {$wpdb->prefix}shoutbox_shouts WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)", $shoutbox_cleanup)
        );
    }

    public function schedule()
    {
        if (!wp_next_scheduled('shoutbox2024_cleanup')) {
            wp_schedule_event(time(), 'daily', 'shoutbox2024_cleanup');
        }
    }

    public function unschedule()
    {
        wp_clear_scheduled_hook('shoutbox2024_cleanup');
    }

    public function hooks()
    {
        add_action('init', array($this, 'schedule'));
        add_action('shoutbox2024_uninstall', array($this, 'unschedule'));
    }

}