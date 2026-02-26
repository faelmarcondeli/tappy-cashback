<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Generator {

    public function __construct() {
        add_action('woocommerce_order_status_completed', [$this, 'generate_cashback']);
    }

    public function generate_cashback($order_id) {

        if (get_option('tappy_cashback_enabled') !== 'yes') return;

        $order = wc_get_order($order_id);

        if (!$order) return;

        if ($order->get_meta('_tappy_cashback_generated')) return;

        $percentage = floatval(get_option('tappy_cashback_percentage'));
        if ($percentage <= 0) return;

        $amount = ($order->get_total() * $percentage) / 100;

        $user_id = $order->get_user_id();
        if (!$user_id) return;

        $expiration_days = get_option('tappy_cashback_expiration');

        $expiration_date = null;
        if (!empty($expiration_days)) {
            $expiration_date = date('Y-m-d', strtotime("+{$expiration_days} days"));
        }

        $cashbacks = get_user_meta($user_id, '_tappy_cashbacks', true);
        if (!is_array($cashbacks)) $cashbacks = [];

        $cashbacks[] = [
            'order_id' => $order_id,
            'amount' => $amount,
            'created' => current_time('mysql'),
            'expiration' => $expiration_date,
        ];

        update_user_meta($user_id, '_tappy_cashbacks', $cashbacks);

        $order->update_meta_data('_tappy_cashback_generated', 'yes');
        $order->update_meta_data('_tappy_cashback_amount', $amount);
        $order->save();
    }
}
