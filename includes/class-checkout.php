<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Checkout {

    public function __construct() {
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_cashback']);
        add_action('woocommerce_checkout_create_order', [$this, 'mark_used'], 20, 2);
    }

    private function get_available_balance($user_id) {

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        return $wpdb->get_var(
            $wpdb->prepare("
                SELECT SUM(amount - amount_used)
                FROM $table
                WHERE user_id = %d
                AND status = 'available'
                AND (expires_at IS NULL OR expires_at > NOW())
            ", $user_id)
        );
    }

    public function apply_cashback() {

        if (!is_user_logged_in()) return;

        $user_id = get_current_user_id();
        $balance = $this->get_available_balance($user_id);

        if ($balance <= 0) return;

        $cart_total = WC()->cart->get_subtotal();

        $discount = min($balance, $cart_total);

        if ($discount > 0) {
            WC()->cart->add_fee('Cashback aplicado', -$discount);
        }
    }

    public function mark_used($order, $data) {

        if (!is_user_logged_in()) return;

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        $user_id = get_current_user_id();
        $balance = $this->get_available_balance($user_id);

        $used = min($balance, $order->get_subtotal());

        if ($used <= 0) return;

        $remaining = $used;

        $rows = $wpdb->get_results(
            $wpdb->prepare("
                SELECT * FROM $table
                WHERE user_id = %d
                AND status = 'available'
                ORDER BY created_at ASC
            ", $user_id)
        );

        foreach ($rows as $row) {

            $available = $row->amount - $row->amount_used;
            if ($available <= 0) continue;

            $consume = min($available, $remaining);

            $wpdb->update($table,
                [
                    'amount_used' => $row->amount_used + $consume,
                    'status' => ($row->amount_used + $consume >= $row->amount) ? 'used' : 'available',
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $row->id]
            );

            $remaining -= $consume;

            if ($remaining <= 0) break;
        }
    }
}
