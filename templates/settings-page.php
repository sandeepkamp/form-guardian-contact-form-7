<?php
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$tab = isset($_GET['tab'])
    ? sanitize_key(wp_unslash($_GET['tab']))
    : 'general';
?>

<div class="wrap">

    <h1>Contact Form 7 Guardian Settings</h1>

    <nav class="nav-tab-wrapper">

        <a href="?page=form-guardian-for-contact-form-7&tab=general"
           class="nav-tab <?php echo $tab === 'general' ? 'nav-tab-active' : ''; ?>">
            General
        </a>

        <a href="?page=form-guardian-for-contact-form-7&tab=security"
           class="nav-tab <?php echo $tab === 'security' ? 'nav-tab-active' : ''; ?>">
            Security
        </a>

        <a href="?page=form-guardian-for-contact-form-7&tab=logs"
           class="nav-tab <?php echo $tab === 'logs' ? 'nav-tab-active' : ''; ?>">
            Logs
        </a>

    </nav>

    <form method="post" action="options.php">

        <?php
        settings_fields('fgucf7_settings_group');
        do_settings_sections('fgucf7_settings_group');
        ?>

        <?php if ($tab === 'general') : ?>

<table class="form-table">

<tr>
    <th>Maximum Attempts Per Hour</th>
    <td>
        <input
            type="number"
            name="fgucf7_max_attempts"
            value="<?php echo esc_attr(
                get_option('fgucf7_max_attempts', 5)
            ); ?>">
    </td>
</tr>

</table>

<?php endif; ?>

<?php if ($tab === 'security') : ?>

<table class="form-table">

<tr>
    <th>Blocked Keywords</th>
    <td>
        <textarea
            name="fgucf7_block_keywords"
            rows="8"
            cols="60"><?php echo esc_textarea(
                get_option('fgucf7_block_keywords')
            ); ?></textarea>
    </td>
</tr>

<tr>
    <th>Disposable Domains</th>
    <td>
        <textarea
            name="fgucf7_disposable_domains"
            rows="8"
            cols="60"><?php echo esc_textarea(
                get_option('fgucf7_disposable_domains')
            ); ?></textarea>
    </td>
</tr>

</table>

<?php endif; ?>

<tr>
    <th>Log Retention</th>
    <td>
        <input
            type="number"
            name="fgucf7_log_retention_days"
            value="<?php echo esc_attr(
                get_option(
                    'fgucf7_log_retention_days',
                    90
                )
            ); ?>">
        days
    </td>
</tr>

        <?php submit_button(); ?>

    </form>

</div>