<?php

if (!defined('ABSPATH')) {
    exit;
}

class FGUCF7_Logger
{

    public static function create_table()
    {

        global $wpdb;

        $table = $wpdb->prefix . 'fgucf7_logs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . $table . " (
            id BIGINT NOT NULL AUTO_INCREMENT,
            email VARCHAR(255),
            submission_hash VARCHAR(64) DEFAULT '' NOT NULL,
            message LONGTEXT,
            ip_address VARCHAR(100),
            status VARCHAR(50),
            reason TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ip_address_created_at (ip_address, created_at),
            KEY status_created_at (status, created_at),
            KEY email_created_at (email, created_at),
            KEY submission_hash_created_at (submission_hash, created_at)
        ) " . $charset_collate . ';';

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta($sql);
        self::maybe_upgrade_table();
    }

    private static function get_submission_hash($email, $message)
    {
        return hash(
            'sha256',
            strtolower(trim($email)) . '|' . trim($message)
        );
    }

    public static function drop_table()
    {

        global $wpdb;

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $table = esc_sql($wpdb->prefix . 'fgucf7_logs');

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
        $wpdb->query('DROP TABLE IF EXISTS ' . $table);
    }

    public static function maybe_upgrade_table()
    {
        global $wpdb;

        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $table = esc_sql($wpdb->prefix . 'fgucf7_logs');
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . $table . " (
            id BIGINT NOT NULL AUTO_INCREMENT,
            email VARCHAR(255),
            submission_hash VARCHAR(64) DEFAULT '' NOT NULL,
            message LONGTEXT,
            ip_address VARCHAR(100),
            status VARCHAR(50),
            reason TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ip_address_created_at (ip_address, created_at),
            KEY status_created_at (status, created_at),
            KEY email_created_at (email, created_at),
            KEY submission_hash_created_at (submission_hash, created_at)
        ) " . $charset_collate . ';';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        dbDelta($sql);
    }

    public static function log($email, $message, $ip, $status, $reason)
    {

        global $wpdb;

        $data = [
            'email' => $email,
            'submission_hash' => self::get_submission_hash($email, $message),
            'message' => $message,
            'ip_address' => $ip,
            'status' => $status,
            'reason' => $reason,
        ];

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->insert(
            $wpdb->prefix . 'fgucf7_logs',
            $data,
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );
    }
}
