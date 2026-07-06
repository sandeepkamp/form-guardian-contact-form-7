<?php

if (!defined('ABSPATH')) {
    exit;
}

class FGUCF7_Spam_Checker
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
        $ip = $this->get_user_ip();

        /*
|--------------------------------------------------------------------------
| Allowlist Check
|--------------------------------------------------------------------------
*/

        $allowed_emails = $this->get_list_lookup('fgucf7_allowed_emails');
        $allowed_ips = $this->get_list_lookup('fgucf7_allowed_ips');

        if (
            ($email !== '' && isset($allowed_emails[$email])) ||
            ($ip !== '' && isset($allowed_ips[$ip]))
        ) {

            FGUCF7_Logger::log(
                $email,
                $message,
                $ip,
                'passed',
                'Allowlisted'
            );

            return;
        }

        $blocked_ips = $this->get_list_lookup('fgucf7_blocked_ips');

        if ($ip !== '' && isset($blocked_ips[$ip])) {
            FGUCF7_Logger::log(
                $email,
                $message,
                $ip,
                'blocked',
                'Blocked IP Address'
            );
            // $this->auto_block_ip($ip);
            $submission->set_status('validation_failed');
            $submission->set_response(
                __('Your submission has been blocked.', 'form-guardian-for-contact-form-7')
            );

            return;
        }

        if (
            isset($data['fgucf7_website']) && trim(sanitize_text_field(wp_unslash((string) $data['fgucf7_website']))) !== ''
        ) {

            FGUCF7_Logger::log(
                $email,
                $message,
                $ip,
                'blocked',
                'Honeypot Triggered'
            );
            $this->auto_block_ip($ip);
            $submission->set_status('validation_failed');
            $submission->set_response(
                __('Your submission has been blocked.', 'form-guardian-for-contact-form-7')
            );

            return;
        }

        global $wpdb;

        $count = 0;

        if ($ip !== '') {
            $cache_key = 'fgucf7_rate_limit_' . md5($ip);
            $count = wp_cache_get($cache_key, 'form-guardian');

            if (false === $count) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $count = (int) $wpdb->get_var(
                    $wpdb->prepare(
                        'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'fgucf7_logs WHERE ip_address = %s AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
                        $ip
                    )
                );
                wp_cache_set($cache_key, $count, 'form-guardian', HOUR_IN_SECONDS);
            }
        }

        $minutes = max(
            1,
            (int) get_option(
                'fgucf7_duplicate_minutes',
                30
            )
        );

        $limit = max(1, (int) get_option('fgucf7_max_attempts', 5));

        if ($count >= $limit) {

            FGUCF7_Logger::log(
                $email,
                $message,
                $ip,
                'blocked',
                'Rate Limit Exceeded'
            );
            $this->auto_block_ip($ip);
            $submission->set_status('validation_failed');
            $submission->set_response(
                __('Your submission has been blocked due to too many attempts.', 'form-guardian-for-contact-form-7')
            );

            return;
        }

        if ($email !== '' && $message !== '') {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $duplicate = (int) $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'fgucf7_logs WHERE submission_hash = %s AND created_at >= DATE_SUB(NOW(), INTERVAL %d MINUTE)',
                    $this->get_submission_hash($email, $message),
                    $minutes
                )
            );

            if ($duplicate > 0) {
                FGUCF7_Logger::log(
                    $email,
                    $message,
                    $ip,
                    'blocked',
                    'Duplicate Submission'
                );
                $this->auto_block_ip($ip);
                $submission->set_status('validation_failed');
                $submission->set_response(
                    __('Your submission has been blocked due to a duplicate entry.', 'form-guardian-for-contact-form-7')
                );

                return;
            }

            /*
|--------------------------------------------------------------------------
| URL Spam Detection
|--------------------------------------------------------------------------
*/

            $max_urls = max(
                0,
                (int) get_option(
                    'fgucf7_max_urls',
                    2
                )
            );

            if ($max_urls > 0) {

                preg_match_all(
                    '/https?:\/\/[^\s]+|www\.[^\s]+/i',
                    $message,
                    $matches
                );

                $url_count = count($matches[0]);

                if ($url_count > $max_urls) {

                    FGUCF7_Logger::log(
                        $email,
                        $message,
                        $ip,
                        'blocked',
                        sprintf(
                            'Too Many URLs (%d)',
                            $url_count
                        )
                    );

                    $this->auto_block_ip($ip);

                    $submission->set_status(
                        'validation_failed'
                    );

                    $submission->set_response(
                        __(
                            'Your message contains too many links.',
                            'form-guardian-for-contact-form-7'
                        )
                    );

                    return;
                }
            }
        }

        $keywords = $this->get_list_items('fgucf7_block_keywords');

        foreach ($keywords as $keyword) {

            if ($keyword !== '' && stripos($message, $keyword) !== false) {

                FGUCF7_Logger::log(
                    $email,
                    $message,
                    $ip,
                    'blocked',
                    'Keyword Match'
                );
                $this->auto_block_ip($ip);
                $submission->set_status('validation_failed');
                $submission->set_response(
                    __('Your submission has been blocked.', 'form-guardian-for-contact-form-7')
                );

                return;
            }
        }

        $domain = '';

        if ($email !== '' && strpos($email, '@') !== false) {
            $domain = substr(strrchr($email, '@'), 1);
        }

        $blocked_domains = $this->get_list_lookup('fgucf7_disposable_domains');

        if ($domain !== '' && isset($blocked_domains[$domain])) {

            FGUCF7_Logger::log(
                $email,
                $message,
                $ip,
                'blocked',
                'Disposable Email'
            );
            $this->auto_block_ip($ip);
            $submission->set_status('validation_failed');
            $submission->set_response(
                __('Your submission has been blocked.', 'form-guardian-for-contact-form-7')
            );

            return;
        }

        /*
|--------------------------------------------------------------------------
| MX Record Validation
|--------------------------------------------------------------------------
*/
        $enable_mx_validation = (int) get_option(
            'fgucf7_enable_mx_validation',
            get_option('fgucf7_enable_mx_check', 0)
        );

        if (
            $enable_mx_validation === 1 &&
            $domain !== '' &&
            function_exists('checkdnsrr')
        ) {

            if (
                ! checkdnsrr($domain, 'MX') &&
                ! checkdnsrr($domain, 'A')
            ) {

                FGUCF7_Logger::log(
                    $email,
                    $message,
                    $ip,
                    'blocked',
                    'Invalid MX Record'
                );
                $this->auto_block_ip($ip);
                $submission->set_status(
                    'validation_failed'
                );

                $submission->set_response(
                    __(
                        'Please enter a valid email address.',
                        'form-guardian-for-contact-form-7'
                    )
                );

                return;
            }
        }

        FGUCF7_Logger::log(
            $email,
            $message,
            $ip,
            'passed',
            'Clean Submission'
        );
    }

    private function get_user_ip()
    {
        $ip = '';

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded_for = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
            $ips = explode(',', $forwarded_for);
            $ip = trim(reset($ips));
        }

        if ($ip === '' && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        }

        if ($ip === '' && !empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    private function get_list_lookup($option_name)
    {
        $items = array_filter(
            array_map(
                'trim',
                preg_split(
                    '/\r\n|[\r\n]/',
                    (string) get_option($option_name, '')
                )
            )
        );

        return array_fill_keys($items, true);
    }

    private function get_list_items($option_name)
    {
        return array_filter(
            array_map(
                'trim',
                preg_split(
                    '/\r\n|[\r\n]/',
                    (string) get_option($option_name, '')
                )
            )
        );
    }

    private function get_submission_hash($email, $message)
    {
        return hash(
            'sha256',
            strtolower(trim($email)) . '|' . trim($message)
        );
    }

    private function auto_block_ip($ip)
    {
        if (empty($ip)) {
            return;
        }

        $auto_ban_enabled = (int) get_option(
            'fgucf7_enable_auto_ip_ban',
            get_option('fgucf7_enable_auto_ip_block', 0)
        );

        if ($auto_ban_enabled !== 1) {
            return;
        }

        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $blocked_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'fgucf7_logs
            WHERE ip_address = %s
            AND status = %s',
                $ip,
                'blocked'
            )
        );

        $limit = max(
            1,
            (int) get_option(
                'fgucf7_auto_ip_ban_limit',
                get_option('fgucf7_auto_ip_block_limit', 10)
            )
        );

        if ($blocked_count < $limit) {
            return;
        }

        $blocked_ips = $this->get_list_items(
            'fgucf7_blocked_ips'
        );

        if (!in_array($ip, $blocked_ips, true)) {

            $blocked_ips[] = $ip;

            update_option(
                'fgucf7_blocked_ips',
                implode(PHP_EOL, $blocked_ips)
            );

            FGUCF7_Logger::log(
                '',
                '',
                $ip,
                'blocked',
                'Automatically Blacklisted'
            );
        }
    }

    public function add_honeypot($content)
    {

        $honeypot = '
        <p class="fgucf7-honeypot" style="position:absolute;left:-9999px;">
            <label>
                Leave this field empty
                <input
                    type="text"
                    name="fgucf7_website"
                    value=""
                    autocomplete="off"
                    tabindex="-1"
                />
            </label>
        </p>';

        return $content . $honeypot;
    }
}
