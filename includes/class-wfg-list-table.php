<?php

if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WFG_List_Table extends WP_List_Table
{

  public function __construct()
  {

    parent::__construct([
      'singular' => 'log',
      'plural'   => 'logs',
      'ajax'     => false,
    ]);
  }

  public function get_columns()
  {

    return [
      'cb'         => '<input type="checkbox" />',
      'id'         => 'ID',
      'email'      => 'Email',
      'ip_address' => 'IP Address',
      'status'     => 'Status',
      'reason'     => 'Reason',
      'created_at' => 'Date',
    ];
  }

  public function column_cb($item)
  {

    return sprintf(
      '<input type="checkbox" name="log_ids[]" value="%d" />',
      $item['id']
    );
  }

  public function column_default($item, $column_name)
  {
    if ($column_name === 'status') {
      if (($item['status'] ?? '') === 'blocked') {
        return '<span style="color:red;font-weight:bold;">Blocked</span>';
      }

      return '<span style="color:green;font-weight:bold;">Passed</span>';
    }

    return esc_html($item[$column_name] ?? '');
  }

  public function get_sortable_columns()
  {

    return [
      'id'         => ['id', true],
      'email'      => ['email', false],
      'created_at' => ['created_at', false],
    ];
  }

  public function get_bulk_actions()
  {

    return [
      'delete' => 'Delete',
    ];
  }

  public function process_bulk_action()
  {

    global $wpdb;

    if ($this->current_action() === 'delete') {

      check_admin_referer('bulk-' . $this->_args['plural']);

      if (empty($_POST['log_ids'])) {
        return;
      }

      $ids = array_map(
        'intval',
        (array) wp_unslash($_POST['log_ids'])
      );

      foreach ($ids as $id) {

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete(
          $wpdb->prefix . 'wfg_logs',
          ['id' => $id],
          ['%d']
        );
      }
    }
  }

  public function prepare_items()
  {

    global $wpdb;

    $table = $wpdb->prefix . 'wfg_logs';

    $per_page = 20;

    $current_page = $this->get_pagenum();

    if (isset($_REQUEST['s'])) {
      check_admin_referer('wfg_logs_search', 'wfg_logs_search_nonce');
    }

    $search = isset($_REQUEST['s'])
      ? sanitize_text_field(
        wp_unslash($_REQUEST['s'])
      )
      : '';

    if (!empty($search)) {
      $search_like = '%' . $wpdb->esc_like($search) . '%';

      $search_cache_key = 'wfg_total_items_' . md5($search_like);
      $total_items = wp_cache_get($search_cache_key, 'form-guardian');
      
      if (false === $total_items) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $total_items = (int) $wpdb->get_var(
          $wpdb->prepare(
            'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs WHERE email LIKE %s',
            $search_like
          )
        );
        wp_cache_set($search_cache_key, $total_items, 'form-guardian', 60);
      }

      $offset = ($current_page - 1) * $per_page;

      $items_cache_key = 'wfg_items_' . md5($search_like . '_' . $per_page . '_' . $offset);
      $items = wp_cache_get($items_cache_key, 'form-guardian');
      
      if (false === $items) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $items = $wpdb->get_results(
          $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wfg_logs WHERE email LIKE %s ORDER BY id DESC LIMIT %d OFFSET %d',
            $search_like,
            $per_page,
            $offset
          ),
          ARRAY_A
        );
        wp_cache_set($items_cache_key, $items, 'form-guardian', 60);
      }
    } else {
      $total_cache_key = 'wfg_total_items_all';
      $total_items = wp_cache_get($total_cache_key, 'form-guardian');
      
      if (false === $total_items) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $total_items = (int) $wpdb->get_var(
          'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wfg_logs'
        );
        wp_cache_set($total_cache_key, $total_items, 'form-guardian', 60);
      }

      $offset = ($current_page - 1) * $per_page;

      $items_all_cache_key = 'wfg_items_all_' . $per_page . '_' . $offset;
      $items = wp_cache_get($items_all_cache_key, 'form-guardian');
      
      if (false === $items) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $items = $wpdb->get_results(
          $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wfg_logs ORDER BY id DESC LIMIT %d OFFSET %d',
            $per_page,
            $offset
          ),
          ARRAY_A
        );
        wp_cache_set($items_all_cache_key, $items, 'form-guardian', 60);
      }
    }

    $this->items = $items;

    $this->_column_headers = [
      $this->get_columns(),
      [],
      $this->get_sortable_columns(),
    ];

    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page'    => $per_page,
    ]);
  }
}
