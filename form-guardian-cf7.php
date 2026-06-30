<?php

/**
 * Plugin Name: Form Guardian for Contact Form 7
 * Description: Protect Contact Form 7 forms from spam with honeypot protection, rate limiting, keyword filtering, disposable email blocking, and submission logs.
 * Version: 1.0.0
 * Author: Sandeep Kamp
 * License: GPL v2 or later
 * Text Domain: form-guardian-for-contact-form-7
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WFG_VERSION', '1.0.0');
define('WFG_PATH', plugin_dir_path(__FILE__));
define('WFG_URL', plugin_dir_url(__FILE__));

require_once WFG_PATH . 'includes/class-wfg-admin.php';
require_once WFG_PATH . 'includes/class-wfg-settings.php';
require_once WFG_PATH . 'includes/class-wfg-spam-checker.php';
require_once WFG_PATH . 'includes/class-wfg-logger.php';
require_once WFG_PATH . 'includes/class-wfg-list-table.php';

register_activation_hook(__FILE__, ['WFG_Logger', 'create_table']);
register_uninstall_hook(__FILE__, ['WFG_Logger', 'drop_table']);
register_activation_hook(__FILE__, 'wfg_schedule_cleanup');

new WFG_Admin();
new WFG_Settings();
new WFG_Spam_Checker();

add_filter(
    'plugin_action_links_' .
        plugin_basename(__FILE__),
    'wfg_settings_link'
);

function wfg_schedule_cleanup()
{

    if (!wp_next_scheduled('wfg_cleanup_logs')) {

        wp_schedule_event(
            time(),
            'daily',
            'wfg_cleanup_logs'
        );
    }
}

function wfg_settings_link($links)
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
    'wfg_cleanup_logs',
    'wfg_delete_old_logs'
);

function wfg_delete_old_logs()
{

    global $wpdb;

    $days = (int) get_option(
        'wfg_log_retention_days',
        90
    );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            'DELETE FROM ' . $wpdb->prefix . 'wfg_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)',
            $days
        )
    );
}
