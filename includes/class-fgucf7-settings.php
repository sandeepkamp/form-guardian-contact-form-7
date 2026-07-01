<?php

if (!defined('ABSPATH')) {
    exit;
}

class FGUCF7_Settings
{

    public function __construct()
    {

        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        register_setting(
            'fgucf7_settings_group',
            'fgucf7_block_keywords',
            [
                'sanitize_callback' => 'sanitize_textarea_field',
            ]
        );

        register_setting(
            'fgucf7_settings_group',
            'fgucf7_disposable_domains',
            [
                'sanitize_callback' => 'sanitize_textarea_field',
            ]
        );

        register_setting(
            'fgucf7_settings_group',
            'fgucf7_max_attempts',
            [
                'sanitize_callback' => 'absint',
            ]
        );

        register_setting(
            'fgucf7_settings_group',
            'fgucf7_log_retention_days',
            [
                'sanitize_callback' => 'absint',
            ]
        );
    }
}
