<?php

if (!defined('ABSPATH')) {
    exit;
}

class WFG_Settings
{

    public function __construct()
    {

        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        register_setting(
            'wfg_settings_group',
            'wfg_block_keywords',
            [
                'sanitize_callback' => 'sanitize_textarea_field',
            ]
        );

        register_setting(
            'wfg_settings_group',
            'wfg_disposable_domains',
            [
                'sanitize_callback' => 'sanitize_textarea_field',
            ]
        );

        register_setting(
            'wfg_settings_group',
            'wfg_max_attempts',
            [
                'sanitize_callback' => 'absint',
            ]
        );

        register_setting(
            'wfg_settings_group',
            'wfg_log_retention_days',
            [
                'sanitize_callback' => 'absint',
            ]
        );
    }
}
