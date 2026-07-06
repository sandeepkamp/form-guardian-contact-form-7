<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
    'DROP TABLE IF EXISTS ' . esc_sql($wpdb->prefix . 'fgucf7_logs')
);

// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange

delete_option('fgucf7_block_keywords');
delete_option('fgucf7_disposable_domains');
delete_option('fgucf7_max_attempts');