<?php
if (!defined('ABSPATH')) {
    exit;
}

$fgucf7_max_attempts = get_option('fgucf7_max_attempts', 5);
$fgucf7_block_keywords = get_option('fgucf7_block_keywords', '');
$fgucf7_disposable_domains = get_option('fgucf7_disposable_domains', '');
$fgucf7_enable_mx_validation = get_option(
    'fgucf7_enable_mx_validation',
    0
);
$fgucf7_duplicate_minutes = get_option('fgucf7_duplicate_minutes', 30);
$fgucf7_max_urls = get_option('fgucf7_max_urls', 2);
$fgucf7_allowed_emails = get_option('fgucf7_allowed_emails', '');
$fgucf7_blocked_ips = get_option('fgucf7_blocked_ips', '');
$fgucf7_allowed_ips = get_option('fgucf7_allowed_ips', '');
$fgucf7_enable_auto_ip_ban = get_option('fgucf7_enable_auto_ip_ban', 0);
$fgucf7_auto_ip_ban_limit = get_option('fgucf7_auto_ip_ban_limit', 5);
$fgucf7_log_retention_days = get_option('fgucf7_log_retention_days', 90);
?>

<div class="wrap fgucf7-wrap">
    <div class="fgucf7-header">
        <div>
            <h1><?php esc_html_e('Form Guardian Settings', 'form-guardian-for-contact-form-7'); ?></h1>
            <p class="fgucf7-subtitle">
                <?php esc_html_e('Configure spam protection, rate limiting, and logging with a cleaner admin experience.', 'form-guardian-for-contact-form-7'); ?>
            </p>
        </div>
        <div class="fgucf7-header-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fgucf7-dashboard')); ?>" class="button">
                <?php esc_html_e('Back to Dashboard', 'form-guardian-for-contact-form-7'); ?>
            </a>
        </div>
    </div>

    <form method="post" action="options.php" class="fgucf7-settings-form">
        <?php settings_fields('fgucf7_settings_group'); // phpcs:ignore WordPress.Security.NonceVerification.Recommended 
        ?>
        <?php wp_nonce_field('fgucf7_settings_group', 'fgucf7_settings_nonce'); ?>

        <div class="fgucf7-settings-grid">
            <div class="fgucf7-settings-main">
                <section class="fgucf7-settings-panel">
                    <div class="fgucf7-settings-panel__header">
                        <div>
                            <h2><?php esc_html_e('Protection Controls', 'form-guardian-for-contact-form-7'); ?></h2>
                            <p><?php esc_html_e('Fine-tune how aggressively the plugin blocks repeated or suspicious submissions.', 'form-guardian-for-contact-form-7'); ?></p>
                        </div>
                    </div>

                    <div class="fgucf7-settings-fields">
                        <div class="fgucf7-field-row">
                            <label for="fgucf7_max_attempts"><?php esc_html_e('Maximum attempts per hour', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <input type="number" id="fgucf7_max_attempts" name="fgucf7_max_attempts" min="1" value="<?php echo esc_attr($fgucf7_max_attempts); ?>" />
                                <p class="description"><?php esc_html_e('Limit the number of submissions a source can send in a rolling hour.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>

                        <div class="fgucf7-field-row">
                            <label for="fgucf7_duplicate_minutes"><?php esc_html_e('Duplicate submission window', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <input type="number" id="fgucf7_duplicate_minutes" name="fgucf7_duplicate_minutes" min="1" max="1440" value="<?php echo esc_attr($fgucf7_duplicate_minutes); ?>" />
                                <p class="description"><?php esc_html_e('Block repeated submissions that match the same email and message content.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>

                        <div class="fgucf7-field-row">
                            <label for="fgucf7_max_urls"><?php esc_html_e('Maximum URLs allowed', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <input type="number" id="fgucf7_max_urls" name="fgucf7_max_urls" min="0" max="50" value="<?php echo esc_attr($fgucf7_max_urls); ?>" />
                                <p class="description"><?php esc_html_e('Block messages that contain more URLs than this threshold.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="fgucf7-settings-panel">
                    <div class="fgucf7-settings-panel__header">
                        <div>
                            <h2><?php esc_html_e('Filter Rules', 'form-guardian-for-contact-form-7'); ?></h2>
                            <p><?php esc_html_e('Add keywords, domains, and addresses that should be blocked by default.', 'form-guardian-for-contact-form-7'); ?></p>
                        </div>
                    </div>

                    <div class="fgucf7-settings-fields">
                        <div class="fgucf7-field-row">
                            <label for="fgucf7_block_keywords"><?php esc_html_e('Blocked keywords', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <textarea id="fgucf7_block_keywords" name="fgucf7_block_keywords" rows="8"><?php echo esc_textarea($fgucf7_block_keywords); ?></textarea>
                                <p class="description"><?php esc_html_e('Enter one keyword per line. Matching content will be blocked.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>

                        <div class="fgucf7-field-row">
                            <label for="fgucf7_disposable_domains"><?php esc_html_e('Disposable domains', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <textarea id="fgucf7_disposable_domains" name="fgucf7_disposable_domains" rows="8"><?php echo esc_textarea($fgucf7_disposable_domains); ?></textarea>
                                <p class="description"><?php esc_html_e('One domain per line, such as mailinator.com or 10minutemail.com.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>

                        <div class="fgucf7-field-row">

                            <label for="fgucf7_enable_mx_validation">
                                <?php
                                esc_html_e(
                                    'MX Record Validation',
                                    'form-guardian-for-contact-form-7'
                                );
                                ?>
                            </label>

                            <div class="fgucf7-field-control">

                                <label>

                                    <input
                                        type="checkbox"
                                        id="fgucf7_enable_mx_validation"
                                        name="fgucf7_enable_mx_validation"
                                        value="1"
                                        <?php checked(
                                            $fgucf7_enable_mx_validation,
                                            1
                                        ); ?>>

                                    <?php
                                    esc_html_e(
                                        'Verify that the email domain has a valid MX record before accepting the submission.',
                                        'form-guardian-for-contact-form-7'
                                    );
                                    ?>

                                </label>

                                <p class="description">
                                    <?php
                                    esc_html_e(
                                        'Enable this option to reject email addresses from domains that cannot receive emails.',
                                        'form-guardian-for-contact-form-7'
                                    );
                                    ?>
                                </p>

                            </div>

                        </div>
                    </div>
                </section>

                <section class="fgucf7-settings-panel">
                    <div class="fgucf7-settings-panel__header">
                        <div>
                            <h2><?php esc_html_e('Access Control', 'form-guardian-for-contact-form-7'); ?></h2>
                            <p><?php esc_html_e('Allow trusted senders and block suspicious IPs or addresses.', 'form-guardian-for-contact-form-7'); ?></p>
                        </div>
                    </div>

                    <div class="fgucf7-settings-fields">
                        <div class="fgucf7-field-row">
                            <label for="fgucf7_allowed_emails"><?php esc_html_e('Allowed email addresses', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <textarea id="fgucf7_allowed_emails" name="fgucf7_allowed_emails" rows="8"><?php echo esc_textarea($fgucf7_allowed_emails); ?></textarea>
                                <p class="description"><?php esc_html_e('One email address per line.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>

                        <div class="fgucf7-field-row">
                            <label for="fgucf7_blocked_ips"><?php esc_html_e('Blocked IP addresses', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <textarea id="fgucf7_blocked_ips" name="fgucf7_blocked_ips" rows="8"><?php echo esc_textarea($fgucf7_blocked_ips); ?></textarea>
                                <p class="description"><?php esc_html_e('One IP address per line.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>

                        <div class="fgucf7-field-row">
                            <label for="fgucf7_allowed_ips"><?php esc_html_e('Allowed IP addresses', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <textarea id="fgucf7_allowed_ips" name="fgucf7_allowed_ips" rows="8"><?php echo esc_textarea($fgucf7_allowed_ips); ?></textarea>
                                <p class="description"><?php esc_html_e('One trusted IP address per line.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>
                        <div class="fgucf7-field-row">

                            <label for="fgucf7_enable_auto_ip_ban">
                                <?php
                                esc_html_e(
                                    'Automatic IP Blacklisting',
                                    'form-guardian-for-contact-form-7'
                                );
                                ?>
                            </label>

                            <div class="fgucf7-field-control">

                                <label>

                                    <input
                                        type="checkbox"
                                        id="fgucf7_enable_auto_ip_ban"
                                        name="fgucf7_enable_auto_ip_ban"
                                        value="1"
                                        <?php checked(
                                            $fgucf7_enable_auto_ip_ban,
                                            1
                                        ); ?>>

                                    <?php
                                    esc_html_e(
                                        'Automatically block IP addresses after repeated spam attempts.',
                                        'form-guardian-for-contact-form-7'
                                    );
                                    ?>

                                </label>

                                <p class="description">
                                    <?php
                                    esc_html_e(
                                        'When enabled, spammer IPs will automatically be added to the blocked IP list after reaching the configured limit.',
                                        'form-guardian-for-contact-form-7'
                                    );
                                    ?>
                                </p>

                            </div>

                        </div>
                        <div class="fgucf7-field-row">

                            <label for="fgucf7_auto_ip_ban_limit">
                                <?php
                                esc_html_e(
                                    'Spam Attempts Before Auto Ban',
                                    'form-guardian-for-contact-form-7'
                                );
                                ?>
                            </label>

                            <div class="fgucf7-field-control">

                                <input
                                    type="number"
                                    id="fgucf7_auto_ip_ban_limit"
                                    name="fgucf7_auto_ip_ban_limit"
                                    min="2"
                                    max="100"
                                    value="<?php echo esc_attr(
                                                $fgucf7_auto_ip_ban_limit
                                            ); ?>">

                                <p class="description">
                                    <?php
                                    esc_html_e(
                                        'After this many blocked submissions, the IP address will be permanently added to the blocked IP list.',
                                        'form-guardian-for-contact-form-7'
                                    );
                                    ?>
                                </p>

                            </div>

                        </div>
                    </div>
                </section>

                <section class="fgucf7-settings-panel">
                    <div class="fgucf7-settings-panel__header">
                        <div>
                            <h2><?php esc_html_e('Logging', 'form-guardian-for-contact-form-7'); ?></h2>
                            <p><?php esc_html_e('Control how long security events are retained.', 'form-guardian-for-contact-form-7'); ?></p>
                        </div>
                    </div>

                    <div class="fgucf7-settings-fields">
                        <div class="fgucf7-field-row">
                            <label for="fgucf7_log_retention_days"><?php esc_html_e('Log retention period', 'form-guardian-for-contact-form-7'); ?></label>
                            <div class="fgucf7-field-control">
                                <input type="number" id="fgucf7_log_retention_days" name="fgucf7_log_retention_days" min="1" value="<?php echo esc_attr($fgucf7_log_retention_days); ?>" />
                                <p class="description"><?php esc_html_e('Older log entries are removed automatically after this many days.', 'form-guardian-for-contact-form-7'); ?></p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="fgucf7-settings-sidebar">
                <section class="fgucf7-settings-panel fgucf7-settings-panel--highlight">
                    <h3><?php esc_html_e('Recommended defaults', 'form-guardian-for-contact-form-7'); ?></h3>
                    <ul class="fgucf7-help-list">
                        <li><?php esc_html_e('Keep rate limits low for public forms.', 'form-guardian-for-contact-form-7'); ?></li>
                        <li><?php esc_html_e('Use a 30-minute duplicate window for most sites.', 'form-guardian-for-contact-form-7'); ?></li>
                        <li><?php esc_html_e('Review logs weekly to spot suspicious traffic.', 'form-guardian-for-contact-form-7'); ?></li>
                    </ul>
                </section>

                <section class="fgucf7-settings-panel">
                    <h3><?php esc_html_e('Quick note', 'form-guardian-for-contact-form-7'); ?></h3>
                    <p class="fgucf7-muted-text">
                        <?php esc_html_e('These controls help protect your forms without overwhelming the admin experience.', 'form-guardian-for-contact-form-7'); ?>
                    </p>
                </section>
            </aside>
        </div>

        <div class="fgucf7-form-actions">
            <?php submit_button(); ?>
        </div>
    </form>
</div>