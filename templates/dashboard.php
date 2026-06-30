<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap wfg-wrap">

  <h1>Contact Form 7 Guardian</h1>

  <div class="wfg-cards">

    <div class="wfg-card">
      <h2><?php echo esc_html($total); ?></h2>
      <p>Total Submissions</p>
    </div>

    <div class="wfg-card">
      <h2><?php echo esc_html($blocked); ?></h2>
      <p>Blocked</p>
    </div>

    <div class="wfg-card">
      <h2><?php echo esc_html($passed); ?></h2>
      <p>Passed</p>
    </div>

    <div class="wfg-card">
      <h2><?php echo esc_html($today); ?></h2>
      <p>Today's Blocks</p>
    </div>

  </div>


  <div class="wfg-chart-card">

    <h2>
      Submission Trends
    </h2>

    <canvas
      id="wfgChart"
      height="100">
    </canvas>

  </div>

  <div class="wfg-actions">

    <a
      href="<?php echo esc_url(
              admin_url(
                'admin.php?page=wfg-logs'
              )
            ); ?>"
      class="button button-primary">
      <?php esc_html_e(
        'View Logs',
        'form-guardian-for-contact-form-7'
      ); ?>
    </a>

    <a
      href="<?php echo esc_url(
              admin_url(
                'admin.php?page=form-guardian-for-contact-form-7'
              )
            ); ?>"
      class="button">
      <?php esc_html_e(
        'Settings',
        'form-guardian-for-contact-form-7'
      ); ?>
    </a>

  </div>

</div>