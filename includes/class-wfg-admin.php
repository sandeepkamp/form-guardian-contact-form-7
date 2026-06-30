<?php

if (!defined('ABSPATH')) {
    exit;
}

class WFG_Admin
{

    private function get_logs_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . 'wfg_logs';
    }

    public function __construct()
    {

        add_action('admin_menu', [$this, 'menu']);

        add_action(
            'admin_post_wfg_export_logs',
            [$this, 'export_logs']
        );

        add_action(
            'wp_dashboard_setup',
            [$this, 'dashboard_widget']
        );

        add_action(
            'admin_enqueue_scripts',
            [$this, 'enqueue_assets']
        );
    }

    public function enqueue_assets($hook)
    {

        if (
            $hook !== 'toplevel_page_wfg-dashboard'
        ) {
            return;
        }

        wp_enqueue_style(
            'wfg-admin',
            WFG_URL . 'assets/css/admin.css',
            [],
            WFG_VERSION
        );

        wp_enqueue_script(
            'wfg-dashboard',
            WFG_URL . 'assets/js/dashboard.js',
            [],
            WFG_VERSION,
            true
        );

        // Prepare and localize chart data
        global $wpdb;
        $chart_data = [];

        for ($i = 29; $i >= 0; $i--) {

            $date = wp_date(
                'Y-m-d',
                strtotime("-{$i} days")
            );

            $chart_data['labels'][] =
                wp_date(
                    'd M',
                    strtotime($date)
                );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $blocked_count = (int) wp_cache_get('wfg_blocked_logs_' . $date, 'form-guardian') ?: $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs' . ' WHERE status = %s AND DATE(created_at) = %s',
                    'blocked',
                    $date
                )
            );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $passed_count = (int) wp_cache_get('wfg_passed_logs_' . $date, 'form-guardian') ?: $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs' . ' WHERE status = %s AND DATE(created_at) = %s',
                    'passed',
                    $date
                )
            );

            $chart_data['blocked'][] =
                $blocked_count;

            $chart_data['passed'][] =
                $passed_count;
        }

        wp_localize_script(
            'wfg-dashboard',
            'wfgStats',
            $chart_data
        );
    }

    public function dashboard_page()
    {

        global $wpdb;

        $table = $this->get_logs_table_name();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = (int) wp_cache_get('wfg_total_logs', 'form-guardian') ?: $wpdb->get_var(
            'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs'
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $blocked = (int) wp_cache_get('wfg_blocked_logs', 'form-guardian') ?: $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs' . ' WHERE status = %s',
                'blocked'
            )
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $passed = (int) wp_cache_get('wfg_passed_logs', 'form-guardian') ?: $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs' . ' WHERE status = %s',
                'passed'
            )
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $today = (int) wp_cache_get('wfg_today_blocked_logs', 'form-guardian') ?: $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs' . ' WHERE status = %s AND DATE(created_at) = CURDATE()',
                'blocked'
            )
        );

        include WFG_PATH . 'templates/dashboard.php';
    }

    // Add dashboard widget
    public function dashboard_widget()
    {

        wp_add_dashboard_widget(
            'wfg_dashboard_widget',
            'WP Form Guardian Stats',
            [$this, 'dashboard_content']
        );
    }

    public function dashboard_content()
    {

        global $wpdb;

        $table = $this->get_logs_table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = (int) wp_cache_get('wfg_total_logs_widget', 'form-guardian') ?: $wpdb->get_var(
            'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs'
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $blocked = (int) wp_cache_get('wfg_blocked_logs_widget', 'form-guardian') ?: $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs' . ' WHERE status = %s',
                'blocked'
            )
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $passed = (int) wp_cache_get('wfg_passed_logs_widget', 'form-guardian') ?: $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs' . ' WHERE status = %s',
                'passed'
            )
        );

?>
        <p>
            <?php echo esc_html__('Total:', 'form-guardian-for-contact-form-7'); ?>
            <?php echo esc_html($total); ?>
        </p>

        <p>
            <?php echo esc_html__('Blocked:', 'form-guardian-for-contact-form-7'); ?>
            <?php echo esc_html($blocked); ?>
        </p>

        <p>
            <?php echo esc_html__('Passed:', 'form-guardian-for-contact-form-7'); ?>
            <?php echo esc_html($passed); ?>
        </p>
    <?php
    }

    // Export logs to CSV
    public function export_logs()
    {

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $logs = wp_cache_get('wfg_export_logs', 'form-guardian') ?: $wpdb->get_results(
            'SELECT * FROM ' . $wpdb->prefix . 'wfg_logs',
            ARRAY_A
        );

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=wfg-logs.csv');

        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        $output = fopen('php://output', 'w');
        // phpcs:enable

        fputcsv(
            $output,
            ['ID', 'Email', 'IP', 'Status', 'Reason', 'Date']
        );

        foreach ($logs as $row) {
            fputcsv($output, $row);
        }

        exit;
    }

    // Add admin menu and pages
    public function menu()
    {

        add_menu_page(
            'WP Form Guardian',
            'Form Guardian',
            'manage_options',
            'wfg-dashboard',
            [$this, 'dashboard_page'],
            'dashicons-shield',
            30
        );

        add_submenu_page(
            'wfg-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'wfg-dashboard',
            [$this, 'dashboard_page']
        );

        add_submenu_page(
            'wfg-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'form-guardian-for-contact-form-7',
            [$this, 'settings_page']
        );

        add_submenu_page(
            'wfg-dashboard',
            'Logs',
            'Logs',
            'manage_options',
            'wfg-logs',
            [$this, 'logs_page']
        );
    }

    // Render settings page
    public function settings_page()
    {
    ?>

        <div class="wrap">

            <h1>WP Form Guardian</h1>

            <form method="post" action="options.php">

                <?php
                settings_fields('wfg_settings_group');
                ?>

                <table class="form-table">

                    <tr>
                        <th>Blocked Keywords</th>
                        <td>
                            <textarea
                                name="wfg_block_keywords"
                                rows="10"
                                cols="50"><?php
                                            echo esc_textarea(
                                                get_option('wfg_block_keywords')
                                            );
                                            ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th>Maximum Attempts Per Hour</th>
                        <td>
                            <input
                                type="number"
                                name="wfg_max_attempts"
                                value="<?php echo esc_attr(
                                            get_option('wfg_max_attempts', 5)
                                        ); ?>"
                                min="1">
                        </td>
                    </tr>

                    <tr>
                        <th>Disposable Domains</th>
                        <td>
                            <textarea
                                name="wfg_disposable_domains"
                                rows="10"
                                cols="50"><?php
                                            echo esc_textarea(
                                                get_option('wfg_disposable_domains')
                                            );
                                            ?></textarea>
                        </td>
                    </tr>

                </table>

                <?php submit_button(); ?>

            </form>

        </div>

    <?php
    }

    // Render logs page
    public function logs_page()
    {

        $table = new WFG_List_Table();

        $table->process_bulk_action();

        $table->prepare_items();

    ?>
        <div class="wrap">

            <h1 class="wp-heading-inline">
                WP Form Guardian Logs
            </h1>

            <form method="post">

                <input
                    type="hidden"
                    name="page"
                    value="wfg-logs">

                <?php wp_nonce_field('wfg_logs_search', 'wfg_logs_search_nonce', false); ?>

                <?php
                $table->search_box(
                    'Search Email',
                    'wfg-search'
                );

                $table->display();
                ?>

            </form>

        </div>
<?php
    }
}
