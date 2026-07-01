<?php

/**
 * Plugin Name: Form Guardian for Contact Form 7
 * Description: Protect Contact Form 7 forms from spam with honeypot protection, rate limiting, keyword filtering, disposable email blocking, and submission logs.
 * Version: 1.0.0
 * Author: Sandeep Kamp
 * License: GPL v2 or later
 * Text Domain: form-guardian-for-contact-form-7
 * Domain Path: /languages
 * Requires Plugins: contact-form-7
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FGUCF7_VERSION', '1.0.0');
define('FGUCF7_PATH', plugin_dir_path(__FILE__));
define('FGUCF7_URL', plugin_dir_url(__FILE__));

require_once FGUCF7_PATH . 'includes/class-fgucf7-admin.php';
require_once FGUCF7_PATH . 'includes/class-fgucf7-settings.php';
require_once FGUCF7_PATH . 'includes/class-fgucf7-spam-checker.php';
require_once FGUCF7_PATH . 'includes/class-fgucf7-logger.php';
require_once FGUCF7_PATH . 'includes/class-fgucf7-list-table.php';

register_activation_hook(__FILE__, ['FGUCF7_Logger', 'create_table']);
register_uninstall_hook(__FILE__, ['FGUCF7_Logger', 'drop_table']);
register_activation_hook(__FILE__, 'fgucf7_schedule_cleanup');

new FGUCF7_Admin();
new FGUCF7_Settings();
new FGUCF7_Spam_Checker();

add_filter(
    'plugin_action_links_' .
        plugin_basename(__FILE__),
    'fgucf7_settings_link'
);

function fgucf7_schedule_cleanup()
{

    if (!wp_next_scheduled('fgucf7_cleanup_logs')) {

        wp_schedule_event(
            time(),
            'daily',
            'fgucf7_cleanup_logs'
        );
    }
}

function fgucf7_settings_link($links)
{

    $settings_link =
        '<a href="' .
        admin_url(
            'admin.php?page=form-guardian-for-contact-form-7'
        ) .
        '">Settings</a>';

    array_unshift(
        $links,
        $settings_link
    );

    return $links;
}

add_action(
    'fgucf7_cleanup_logs',
    'fgucf7_delete_old_logs'
);

function fgucf7_delete_old_logs()
{

    global $wpdb;

    $days = (int) get_option(
        'fgucf7_log_retention_days',
        90
    );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            'DELETE FROM ' . $wpdb->prefix . 'fgucf7_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)',
            $days
        )
    );
}
