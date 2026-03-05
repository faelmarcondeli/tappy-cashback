<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Tappy_CB_List_Table extends WP_List_Table {

    public function get_columns() {
        return [
            'id' => 'ID',
            'user' => 'Usuário',
            'order' => 'Pedido',
            'amount' => 'Valor',
            'used' => 'Usado',
            'status' => 'Status',
            'expires_at' => 'Expira em',
            'created_at' => 'Criado em',
        ];
    }

    public function prepare_items() {

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        $per_page = 20;
        $paged = $this->get_pagenum();

        $offset = ($paged - 1) * $per_page;

        $this->items = $wpdb->get_results("
            SELECT * FROM $table
            ORDER BY created_at DESC
            LIMIT $per_page OFFSET $offset
        ");

        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $this->set_pagination_args([
            'total_items' => $total,
            'per_page' => $per_page,
        ]);
    }

    public function column_default($item, $column_name) {

        switch ($column_name) {
            case 'user':
                return get_userdata($item->user_id)->user_email ?? '-';

            case 'order':
                return '<a href="'.admin_url('post.php?post='.$item->order_id.'&action=edit').'">#'.$item->order_id.'</a>';

            case 'amount':
                return wc_price($item->amount);

            case 'used':
                return wc_price($item->amount_used);

            default:
                return $item->$column_name ?? '-';
        }
    }
}
