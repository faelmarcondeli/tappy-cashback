<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Emails {

    /**
     * Mapa de dias -> coluna na tabela usada para evitar reenvio.
     */
    const REMINDER_COLUMNS = [
        60 => 'reminder_60_sent_at',
        30 => 'reminder_30_sent_at',
        7  => 'reminder_7_sent_at',
        1  => 'reminder_1_sent_at',
    ];

    public function __construct() {
        add_action('tappy_cb_send_generated_email', [$this, 'send_generated'], 10, 1);
        add_action('tappy_cb_daily_reminders', [$this, 'process_pending_reminders']);
    }

    /*
    |--------------------------------------------------------------------------
    | UTILITÁRIOS
    |--------------------------------------------------------------------------
    */

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'tappy_cashback';
    }

    private function load_template($template, array $vars) {

        $path = TAPPY_CB_PATH . 'template/emails/' . $template . '.php';

        if (!file_exists($path)) {
            return '';
        }

        extract($vars, EXTR_SKIP);

        ob_start();
        include $path;
        return ob_get_clean();
    }

    private function send($to, $subject, $body) {

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        $from_name  = get_option('woocommerce_email_from_name', get_bloginfo('name'));
        $from_email = get_option('woocommerce_email_from_address', get_option('admin_email'));

        if ($from_email) {
            $headers[] = sprintf('From: %s <%s>', $from_name, $from_email);
        }

        return wp_mail($to, $subject, $body, $headers);
    }

    private function format_user_name($user) {
        $name = trim($user->first_name);
        if (!$name) {
            $name = $user->display_name;
        }
        if (!$name) {
            $name = $user->user_login;
        }
        return $name;
    }

    private function account_url() {
        if (function_exists('wc_get_account_endpoint_url')) {
            return wc_get_account_endpoint_url('cashback');
        }
        return home_url('/');
    }

    private function shop_url() {
        if (function_exists('wc_get_page_permalink')) {
            $url = wc_get_page_permalink('shop');
            if ($url) return $url;
        }
        return home_url('/');
    }

    private function format_date($mysql_date) {
        if (!$mysql_date) return '—';
        $ts = strtotime($mysql_date);
        if (!$ts) return '—';
        return date_i18n(get_option('date_format', 'd/m/Y'), $ts);
    }

    /*
    |--------------------------------------------------------------------------
    | E-MAIL DE CASHBACK GERADO
    |--------------------------------------------------------------------------
    */

    public function send_generated($cashback_id) {

        if (get_option('tappy_cashback_enabled') !== 'yes') {
            return false;
        }

        global $wpdb;
        $table = self::table();

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $cashback_id)
        );

        if (!$row) return false;

        $user = get_user_by('id', $row->user_id);
        if (!$user || !is_email($user->user_email)) return false;

        $body = $this->load_template('cashback-gerado', [
            'user_name'   => $this->format_user_name($user),
            'amount'      => wp_strip_all_tags(wc_price($row->amount)),
            'expires_at'  => $row->expires_at ? $this->format_date($row->expires_at) : 'Não expira',
            'order_id'    => (int) $row->order_id,
            'site_name'   => get_bloginfo('name'),
            'account_url' => $this->account_url(),
        ]);

        if (!$body) return false;

        $subject = sprintf('[%s] Você ganhou cashback no pedido #%d', get_bloginfo('name'), $row->order_id);

        return $this->send($user->user_email, $subject, $body);
    }

    /*
    |--------------------------------------------------------------------------
    | E-MAILS DE LEMBRETE (60, 30, 7 E 1 DIA)
    |--------------------------------------------------------------------------
    */

    public function process_pending_reminders() {

        if (get_option('tappy_cashback_enabled') !== 'yes') {
            return;
        }

        foreach (array_keys(self::REMINDER_COLUMNS) as $days) {
            $this->process_reminder_for_days($days);
        }
    }

    private function process_reminder_for_days($days) {

        global $wpdb;
        $table = self::table();
        $column = self::REMINDER_COLUMNS[$days];

        // Janela de 24h centrada em "agora + $days dias" no fuso do site,
        // para tolerar pequenos atrasos do agendador entre execuções diárias.
        $now_local = new DateTimeImmutable('now', wp_timezone());
        $window_start = $now_local->modify("+{$days} days")->setTime(0, 0, 0);
        $window_end   = $now_local->modify("+{$days} days")->setTime(23, 59, 59);

        $start = $window_start->format('Y-m-d H:i:s');
        $end   = $window_end->format('Y-m-d H:i:s');

        $candidates = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table
                 WHERE status = 'available'
                   AND expires_at IS NOT NULL
                   AND expires_at BETWEEN %s AND %s
                   AND $column IS NULL
                   AND (amount - amount_used) > 0",
                $start,
                $end
            )
        );

        if (!$candidates) return;

        // Reivindica cada linha de forma atômica: o UPDATE só afeta linhas
        // cuja coluna ainda esteja NULL. Evita reenvio em execuções concorrentes.
        $now_marker = current_time('mysql');
        $claimed = [];

        foreach ($candidates as $row) {
            $affected = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $table SET $column = %s
                     WHERE id = %d AND $column IS NULL",
                    $now_marker,
                    $row->id
                )
            );
            if ($affected === 1) {
                $claimed[] = $row;
            }
        }

        if (!$claimed) return;

        // Agrupa por usuário para enviar 1 e-mail por usuário,
        // mesmo com vários cashbacks vencendo na janela.
        $by_user = [];
        foreach ($claimed as $row) {
            $by_user[$row->user_id][] = $row;
        }

        foreach ($by_user as $user_id => $user_rows) {
            $this->send_reminder_for_user($user_id, $days, $user_rows);
        }
    }

    private function send_reminder_for_user($user_id, $days, $user_rows) {

        // Linhas já estão reivindicadas (coluna marcada). Qualquer saída sem
        // envio é definitiva e não será reprocessada — atende ao requisito de
        // não disparar email quando não há saldo real pendente.
        $balance = Tappy_CB_Database::get_balance($user_id);

        if ($balance <= 0) {
            return;
        }

        $user = get_user_by('id', $user_id);
        if (!$user || !is_email($user->user_email)) {
            return;
        }

        // Soma o valor disponível dos cashbacks que vencem nesta janela
        $expiring_amount = 0;
        $earliest_expiry = null;
        foreach ($user_rows as $row) {
            $expiring_amount += ((float) $row->amount - (float) $row->amount_used);
            if (!$earliest_expiry || strtotime($row->expires_at) < strtotime($earliest_expiry)) {
                $earliest_expiry = $row->expires_at;
            }
        }

        $template = 'cashback-pendente-' . $days . 'd';

        $body = $this->load_template($template, [
            'user_name'       => $this->format_user_name($user),
            'balance'         => wp_strip_all_tags(wc_price($balance)),
            'expiring_amount' => wp_strip_all_tags(wc_price($expiring_amount)),
            'expires_at'      => $this->format_date($earliest_expiry),
            'days'            => $days,
            'site_name'       => get_bloginfo('name'),
            'shop_url'        => $this->shop_url(),
            'account_url'     => $this->account_url(),
        ]);

        if (!$body) {
            return;
        }

        $subject = $days === 1
            ? sprintf('[%s] Último dia para usar seu cashback', get_bloginfo('name'))
            : sprintf('[%s] Seu cashback expira em %d dias', get_bloginfo('name'), $days);

        return $this->send($user->user_email, $subject, $body);
    }

    /*
    |--------------------------------------------------------------------------
    | AGENDAMENTO
    |--------------------------------------------------------------------------
    */

    /**
     * Calcula o próximo timestamp UTC para "amanhã às $hour:00" no fuso do site.
     */
    public static function next_daily_run_timestamp($hour = 8) {

        $tz = wp_timezone();
        $now = new DateTimeImmutable('now', $tz);
        $candidate = $now->setTime((int) $hour, 0, 0);

        if ($candidate <= $now) {
            $candidate = $candidate->modify('+1 day');
        }

        return $candidate->getTimestamp();
    }
}
