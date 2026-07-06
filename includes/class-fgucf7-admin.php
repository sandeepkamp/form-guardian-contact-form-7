<?php

if (!defined('ABSPATH')) {
    exit;
}

class FGUCF7_Admin
{

    private function get_logs_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . 'fgucf7_logs';
    }

    public function __construct()
    {

        add_action('admin_menu', [$this, 'menu']);

        add_action(
            'admin_post_fgucf7_export_logs',
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
        $is_plugin_page = strpos($hook, 'fgucf7') !== false;

        if (!$is_plugin_page) {
            return;
        }

        wp_enqueue_style(
            'fgucf7-admin',
            FGUCF7_URL . 'assets/css/admin.css',
            [],
            FGUCF7_VERSION
        );

        if ($hook === 'toplevel_page_fgucf7-dashboard') {
            wp_enqueue_script(
                'fgucf7-dashboard',
                FGUCF7_URL . 'assets/js/dashboard.js',
                [],
                FGUCF7_VERSION,
                true
            );

            global $wpdb;

            $chart_data = wp_cache_get('fgucf7_dashboard_chart_data', 'form-guardian');

            if (false === $chart_data) {
                $chart_data = [
                    'labels' => [],
                    'blocked' => [],
                    'passed' => [],
                ];

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $results = $wpdb->get_results(
                    'SELECT status, DATE(created_at) AS day, COUNT(*) AS count FROM ' . $wpdb->prefix . 'fgucf7_logs WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY status, DATE(created_at)',
                    ARRAY_A
                );

                $counts = [];
                foreach ($results as $row) {
                    $counts[$row['day']][$row['status']] = (int) $row['count'];
                }

                for ($i = 29; $i >= 0; $i--) {
                    $date = wp_date('Y-m-d', strtotime("-{$i} days"));

                    $chart_data['labels'][] = wp_date('d M', strtotime($date));
                    $chart_data['blocked'][] = $counts[$date]['blocked'] ?? 0;
                    $chart_data['passed'][] = $counts[$date]['passed'] ?? 0;
                }

                wp_cache_set('fgucf7_dashboard_chart_data', $chart_data, 'form-guardian', 300);
            }

            wp_localize_script(
                'fgucf7-dashboard',
                'fgucf7Stats',
                $chart_data
            );
        }
    }

    public function dashboard_page()
    {

        global $wpdb;

        $table = $this->get_logs_table_name();

        $cached_counts = wp_cache_get('fgucf7_dashboard_counts', 'form-guardian');

        if (false === $cached_counts) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $results = $wpdb->get_results(
                'SELECT status, COUNT(*) AS count FROM ' . $wpdb->prefix . 'fgucf7_logs GROUP BY status',
                ARRAY_A
            );

            $cached_counts = [
                'total' => 0,
                'blocked' => 0,
                'passed' => 0,
                'today_blocked' => 0,
            ];

            foreach ($results as $row) {
                $cached_counts['total'] += (int) $row['count'];

                if ($row['status'] === 'blocked') {
                    $cached_counts['blocked'] = (int) $row['count'];
                }

                if ($row['status'] === 'passed') {
                    $cached_counts['passed'] = (int) $row['count'];
                }
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $cached_counts['today_blocked'] = (int) $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'fgucf7_logs WHERE status = %s AND DATE(created_at) = CURDATE()',
                    'blocked'
                )
            );

            wp_cache_set('fgucf7_dashboard_counts', $cached_counts, 'form-guardian', 300);
        }

        $total = $cached_counts['total'];
        $blocked = $cached_counts['blocked'];
        $passed = $cached_counts['passed'];
        $today = $cached_counts['today_blocked'];

        include FGUCF7_PATH . 'templates/dashboard.php';
    }

    // Add dashboard widget
    public function dashboard_widget()
    {

        wp_add_dashboard_widget(
            'fgucf7_dashboard_widget',
            'WP Form Guardian Stats',
            [$this, 'dashboard_content']
        );
    }

    public function dashboard_content()
    {

        global $wpdb;

        $table = $this->get_logs_table_name();

        $cached_widget_counts = wp_cache_get('fgucf7_dashboard_widget_counts', 'form-guardian');

        if (false === $cached_widget_counts) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $counts = $wpdb->get_results(
                'SELECT status, COUNT(*) AS count FROM ' . $wpdb->prefix . 'fgucf7_logs GROUP BY status',
                ARRAY_A
            );

            $cached_widget_counts = [
                'total' => 0,
                'blocked' => 0,
                'passed' => 0,
            ];

            foreach ($counts as $row) {
                $cached_widget_counts['total'] += (int) $row['count'];

                if ($row['status'] === 'blocked') {
                    $cached_widget_counts['blocked'] = (int) $row['count'];
                }

                if ($row['status'] === 'passed') {
                    $cached_widget_counts['passed'] = (int) $row['count'];
                }
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            wp_cache_set('fgucf7_dashboard_widget_counts', $cached_widget_counts, 'form-guardian', 300);
        }

        $total = $cached_widget_counts['total'];
        $blocked = $cached_widget_counts['blocked'];
        $passed = $cached_widget_counts['passed'];

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

        $logs = wp_cache_get('fgucf7_export_logs', 'form-guardian');

        if (false === $logs) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $logs = $wpdb->get_results(
                'SELECT * FROM ' . $wpdb->prefix . 'fgucf7_logs',
                ARRAY_A
            );
            wp_cache_set('fgucf7_export_logs', $logs, 'form-guardian', 300);
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=fgucf7-logs.csv');

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
            'fgucf7-dashboard',
            [$this, 'dashboard_page'],
            'dashicons-shield',
            30
        );

        add_submenu_page(
            'fgucf7-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'fgucf7-dashboard',
            [$this, 'dashboard_page']
        );

        add_submenu_page(
            'fgucf7-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'form-guardian-for-contact-form-7',
            [$this, 'settings_page']
        );

        add_submenu_page(
            'fgucf7-dashboard',
            'Logs',
            'Logs',
            'manage_options',
            'fgucf7-logs',
            [$this, 'logs_page']
        );
    }

    // Render settings page
    public function settings_page()
    {
        include FGUCF7_PATH . 'templates/settings-page.php';
    }

    // Render logs page
    public function logs_page()
    {

        $table = new FGUCF7_List_Table();

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
                    value="fgucf7-logs">

                <?php wp_nonce_field('fgucf7_logs_search', 'fgucf7_logs_search_nonce', false); ?>

                <?php
                $table->search_box(
                    'Search Email',
                    'fgucf7-search'
                );

                $table->display();
                ?>

            </form>

        </div>
<?php
    }
}
