<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Cron {

    public function __construct() {

        add_filter('cron_schedules', [$this, 'add_schedules']);
        add_action('tappy_cb_daily_expiration', [$this, 'expire_cashbacks']);

        $this->ensure_schedule();
        $this->ensure_daily_reminders_schedule();
    }

    private function ensure_daily_reminders_schedule() {

        $hook = 'tappy_cb_daily_reminders';
        $next = wp_next_scheduled($hook);

        if ($next) {
            // Migra agendamentos antigos que foram feitos no fuso do servidor
            // (ex.: instalações criadas antes da correção de timezone) para o
            // fuso configurado em "Configurações > Geral".
            $hour_local = (int) wp_date('G', $next);
            if ($hour_local !== 8) {
                wp_unschedule_event($next, $hook);
                $next = false;
            }
        }

        if (!$next) {
            // Roda 1x ao dia. Primeira execução agendada para o próximo
            // 08:00 no fuso configurado em "Configurações > Geral".
            $first = Tappy_CB_Emails::next_daily_run_timestamp(8);
            wp_schedule_event($first, 'daily', $hook);
        }
    }

    private function get_interval_slug() {
        $option = get_option('tappy_cashback_cron_interval', 'daily');
        $allowed = ['hourly', 'three_hours', 'six_hours', 'twelve_hours', 'daily'];
        return in_array($option, $allowed, true) ? $option : 'daily';
    }

    public function add_schedules($schedules) {

        $schedules['three_hours'] = array(
            'interval' => 3 * HOUR_IN_SECONDS,
            'display'  => 'A cada 3 horas'
        );

        $schedules['six_hours'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => 'A cada 6 horas'
        );

        $schedules['twelve_hours'] = array(
            'interval' => 12 * HOUR_IN_SECONDS,
            'display'  => 'A cada 12 horas'
        );

        return $schedules;
    }

    private function ensure_schedule() {

        $interval = $this->get_interval_slug();
        $current = wp_get_schedule('tappy_cb_daily_expiration');

        if ($current !== $interval) {
            wp_clear_scheduled_hook('tappy_cb_daily_expiration');
        }

        if (!wp_next_scheduled('tappy_cb_daily_expiration')) {
            wp_schedule_event(time(), $interval, 'tappy_cb_daily_expiration');
        }
    }

    public function expire_cashbacks() {

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        $expired_users = $wpdb->get_col("
            SELECT DISTINCT user_id
            FROM $table
            WHERE status = 'available'
            AND expires_at IS NOT NULL
            AND expires_at <= NOW()
        ");

        $wpdb->query("
            UPDATE $table
            SET status = 'expired',
                updated_at = NOW()
            WHERE status = 'available'
            AND expires_at IS NOT NULL
            AND expires_at <= NOW()
        ");

        foreach ($expired_users as $user_id) {
            Tappy_CB_Database::clear_balance_cache($user_id);
        }
    }
}
