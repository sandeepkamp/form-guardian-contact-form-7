<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap fgucf7-wrap">

  <div class="fgucf7-header">
    <div>
      <h1>Contact Form 7 Guardian</h1>
      <p class="fgucf7-subtitle">Dashboard for monitoring blocked submissions, trends, and protection status.</p>
    </div>
    <div class="fgucf7-header-actions">
      <a href="<?php echo esc_url(admin_url('admin.php?page=fgucf7-logs')); ?>" class="button button-primary">
        <?php esc_html_e('View Logs', 'form-guardian-for-contact-form-7'); ?>
      </a>
      <a href="<?php echo esc_url(admin_url('admin.php?page=form-guardian-for-contact-form-7')); ?>" class="button">
        <?php esc_html_e('Settings', 'form-guardian-for-contact-form-7'); ?>
      </a>
    </div>
  </div>

  <div class="fgucf7-cards">

    <div class="fgucf7-card fgucf7-card--primary">
      <span>Total Submissions</span>
      <strong><?php echo esc_html($total); ?></strong>
    </div>

    <div class="fgucf7-card fgucf7-card--danger">
      <span>Blocked</span>
      <strong><?php echo esc_html($blocked); ?></strong>
    </div>

    <div class="fgucf7-card fgucf7-card--success">
      <span>Passed</span>
      <strong><?php echo esc_html($passed); ?></strong>
    </div>

    <div class="fgucf7-card fgucf7-card--warning">
      <span>Today's Blocks</span>
      <strong><?php echo esc_html($today); ?></strong>
    </div>

  </div>

  <div class="fgucf7-body-grid">
    <section class="fgucf7-panel fgucf7-panel--large">
      <div class="fgucf7-panel__header">
        <h2><?php esc_html_e('Submission Trends', 'form-guardian-for-contact-form-7'); ?></h2>
        <p><?php esc_html_e('Recent blocked and passed submissions over time.', 'form-guardian-for-contact-form-7'); ?></p>
      </div>
      <div class="fgucf7-panel__content fgucf7-chart-wrapper">
        <div class="fgucf7-chart-summary">
          <div class="fgucf7-chart-summary__item fgucf7-chart-summary__item--blocked">
            <span><?php esc_html_e('Blocked this month', 'form-guardian-for-contact-form-7'); ?></span>
            <strong><?php echo esc_html($blocked); ?></strong>
          </div>
          <div class="fgucf7-chart-summary__item fgucf7-chart-summary__item--passed">
            <span><?php esc_html_e('Passed this month', 'form-guardian-for-contact-form-7'); ?></span>
            <strong><?php echo esc_html($passed); ?></strong>
          </div>
        </div>
        <div class="fgucf7-chart-area">
          <canvas id="fgucf7Chart" height="260"></canvas>
          <div class="fgucf7-chart-tooltip" aria-hidden="true"></div>
        </div>
        <div class="fgucf7-chart-legend">
          <span class="fgucf7-chart-legend__item fgucf7-chart-legend--blocked"><?php esc_html_e('Blocked', 'form-guardian-for-contact-form-7'); ?></span>
          <span class="fgucf7-chart-legend__item fgucf7-chart-legend--passed"><?php esc_html_e('Passed', 'form-guardian-for-contact-form-7'); ?></span>
        </div>
      </div>
    </section>

    <section class="fgucf7-panel fgucf7-panel--small">
      <div class="fgucf7-panel__header">
        <h2><?php esc_html_e('Quick Tips', 'form-guardian-for-contact-form-7'); ?></h2>
      </div>
      <div class="fgucf7-panel__content fgucf7-quick-list">
        <ul>
          <li><?php esc_html_e('Review recent logs to identify suspicious senders.', 'form-guardian-for-contact-form-7'); ?></li>
          <li><?php esc_html_e('Adjust duplicate and rate limit windows in settings.', 'form-guardian-for-contact-form-7'); ?></li>
          <li><?php esc_html_e('Update blocked email domains and keywords regularly.', 'form-guardian-for-contact-form-7'); ?></li>
        </ul>
      </div>
    </section>
  </div>

</div>