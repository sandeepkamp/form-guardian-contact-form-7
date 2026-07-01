<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query(
    "DROP TABLE IF EXISTS {$wpdb->prefix}fgucf7_logs"
);

// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange

delete_option('fgucf7_block_keywords');
delete_option('fgucf7_disposable_domains');
delete_option('fgucf7_max_attempts');