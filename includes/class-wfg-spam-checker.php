<?php

if (!defined('ABSPATH')) {
    exit;
}

class WFG_Spam_Checker
{

    public function __construct()
    {

        add_action(
            'wpcf7_before_send_mail',
            [$this, 'check_submission']
        );

        add_filter(
            'wpcf7_form_elements',
            [$this, 'add_honeypot']
        );
    }

    public function check_submission($contact_form)
    {

        if (!class_exists('WPCF7_Submission') || !method_exists('WPCF7_Submission', 'get_instance')) {
            return;
        }

        $submission = WPCF7_Submission::get_instance();

        if (!$submission) {
            return;
        }

        $data = $submission->get_posted_data();

        if (!is_array($data)) {
            $data = [];
        }

        $email = isset($data['your-email']) ? sanitize_email($data['your-email']) : '';
        $message = isset($data['your-message']) ? wp_strip_all_tags(wp_unslash((string) $data['your-message'])) : '';
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

        if (
            isset($data['wfg_website']) && trim(sanitize_text_field(wp_unslash((string) $data['wfg_website']))) !== ''
        ) {

            WFG_Logger::log(
                $email,
                $ip,
                'blocked',
                'Honeypot Triggered'
            );

            $submission->set_status('validation_failed');

            return;
        }

        global $wpdb;

        $count = 0;

        if ($ip !== '') {
            $cache_key = 'wfg_rate_limit_' . md5($ip);
            $count = wp_cache_get($cache_key, 'form-guardian');
            
            if (false === $count) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $count = (int) $wpdb->get_var(
                    $wpdb->prepare(
                        'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs WHERE ip_address = %s AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
                        $ip
                    )
                );
                wp_cache_set($cache_key, $count, 'form-guardian', HOUR_IN_SECONDS);
            }
        }

        $limit = max(1, (int) get_option('wfg_max_attempts', 5));

        if ($count >= $limit) {

            WFG_Logger::log(
                $email,
                $ip,
                'blocked',
                'Rate Limit Exceeded'
            );

            $submission->set_status('validation_failed');

            return;
        }

        $keywords = array_filter(
            array_map(
                'trim',
                preg_split('/\r\n|[\r\n]/', (string) get_option('wfg_block_keywords', ''))
            )
        );

        foreach ($keywords as $keyword) {

            if ($keyword !== '' && stripos($message, $keyword) !== false) {

                WFG_Logger::log(
                    $email,
                    $ip,
                    'blocked',
                    'Keyword Match'
                );

                $submission->set_status('validation_failed');

                return;
            }
        }

        $domain = '';

        if ($email !== '' && strpos($email, '@') !== false) {
            $domain = substr(strrchr($email, '@'), 1);
        }

        $blocked_domains = array_filter(
            array_map(
                'trim',
                preg_split('/\r\n|[\r\n]/', (string) get_option('wfg_disposable_domains', ''))
            )
        );

        if ($domain !== '' && in_array($domain, $blocked_domains, true)) {

            WFG_Logger::log(
                $email,
                $ip,
                'blocked',
                'Disposable Email'
            );

            $submission->set_status('validation_failed');

            return;
        }

        WFG_Logger::log(
            $email,
            $ip,
            'passed',
            'Clean Submission'
        );
    }

    public function add_honeypot($content)
    {

        $honeypot = '
        <p class="wfg-honeypot" style="position:absolute;left:-9999px;">
            <label>
                Leave this field empty
                <input
                    type="text"
                    name="wfg_website"
                    value=""
                    autocomplete="off"
                    tabindex="-1"
                />
            </label>
        </p>';

        return $content . $honeypot;
    }
}
