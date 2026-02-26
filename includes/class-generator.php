<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Generator {

    public function __construct() {
        add_action('woocommerce_order_status_completed', [$this, 'generate']);
    }

    public function generate($order_id) {

        if (get_option('tappy_cashback_enabled') !== 'yes') return;

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $table WHERE order_id = %d", $order_id)
        );

        if ($exists) return;

        $order = wc_get_order($order_id);
        if (!$order || !$order->get_user_id()) return;

        $percentage = floatval(get_option('tappy_cashback_percentage'));
        if ($percentage <= 0) return;

        $amount = ($order->get_total() * $percentage) / 100;

        $expires = null;
        $days = get_option('tappy_cashback_expiration');
        if ($days) {
            $expires = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        }

        $wpdb->insert($table, [
            'user_id' => $order->get_user_id(),
            'order_id' => $order_id,
            'amount' => $amount,
            'status' => 'available',
            'expires_at' => $expires,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);
    }
}
