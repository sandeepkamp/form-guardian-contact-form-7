<?php
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap fgucf7-wrap">

  <h1>Contact Form 7 Guardian</h1>

  <div class="fgucf7-cards">

    <div class="fgucf7-card">
      <h2><?php echo esc_html($total); ?></h2>
      <p>Total Submissions</p>
    </div>

    <div class="fgucf7-card">
      <h2><?php echo esc_html($blocked); ?></h2>
      <p>Blocked</p>
    </div>

    <div class="fgucf7-card">
      <h2><?php echo esc_html($passed); ?></h2>
      <p>Passed</p>
    </div>

    <div class="fgucf7-card">
      <h2><?php echo esc_html($today); ?></h2>
      <p>Today's Blocks</p>
    </div>

  </div>


  <div class="fgucf7-chart-card">

    <h2>
      Submission Trends
    </h2>

    <canvas
      id="fgucf7Chart"
      height="100">
    </canvas>

  </div>

  <div class="fgucf7-actions">

    <a
      href="<?php echo esc_url(
              admin_url(
                'admin.php?page=fgucf7-logs'
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