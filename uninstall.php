<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('shoutbox2024_refresh-interval');
delete_option('shoutbox2024_default-font-colour');
delete_option('shoutbox2024_locale');

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}shoutbox_shouts"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}shoutbox_smilies"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange