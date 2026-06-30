<?php

if (!defined('ABSPATH')) {
    exit;
}

class WFG_Logger
{

    public static function create_table()
    {

        global $wpdb;

        $table = $wpdb->prefix . 'wfg_logs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . ' (
            id BIGINT NOT NULL AUTO_INCREMENT,
            email VARCHAR(255),
            ip_address VARCHAR(100),
            status VARCHAR(50),
            reason TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ' . $charset_collate . ';';

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta($sql);
    }

    public static function drop_table()
    {

        global $wpdb;

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wfg_logs');
    }

    public static function log($email, $ip, $status, $reason)
    {

        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->insert(
            $wpdb->prefix . 'wfg_logs',
            [
                'email' => $email,
                'ip_address' => $ip,
                'status' => $status,
                'reason' => $reason
            ]
        );
    }
}
