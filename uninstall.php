<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

if (!function_exists('dbDelta')) {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
}

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query(
    "DROP TABLE IF EXISTS {$wpdb->prefix}wfg_logs"
);

// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange

delete_option('wfg_block_keywords');
delete_option('wfg_disposable_domains');
delete_option('wfg_max_attempts');