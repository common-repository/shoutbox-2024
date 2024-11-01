<?php

class shoutbox2024_database
{
    public function __construct()
    {

    }

    public function create_tables()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "shoutbox_shouts";

        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

        $shouts_sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}shoutbox_shouts` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `shout_from` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            `shout_to` bigint(20) UNSIGNED DEFAULT NULL,
            `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
            `message` text COLLATE utf8mb4_bin NOT NULL,
            `archived` tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `shout_from` (`shout_from`),
            KEY `shout_to` (`shout_to`),
            KEY `timestamp` (`timestamp`),
            KEY `archived` (`archived`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Shoutbox Messages';";

        $shouts_constraints_sql = "ALTER TABLE `{$wpdb->base_prefix}shoutbox_shouts` ADD  CONSTRAINT `shout_from` FOREIGN KEY (`shout_from`) REFERENCES `{$wpdb->base_prefix}users`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
        ALTER TABLE `{$wpdb->base_prefix}shoutbox_shouts` ADD  CONSTRAINT `shout_to` FOREIGN KEY (`shout_to`) REFERENCES `{$wpdb->base_prefix}users`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE;";

        $smilies_sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}shoutbox_smilies` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `name` text NOT NULL,
            `code` text NOT NULL,
            `url` text NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`(768)) USING BTREE,
            KEY `name` (`name`(768)),
            KEY `url` (`url`(768))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Shoutbox Smilies';";

        dbDelta($shouts_sql);
        dbDelta($shouts_constraints_sql);
        dbDelta($smilies_sql);
    }

}