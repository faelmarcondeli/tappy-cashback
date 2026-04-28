<?php
if (!defined('ABSPATH')) exit;

/**
 * Verifica diariamente os cashbacks pendentes e dispara os e-mails de aviso
 * antes do vencimento. Os e-mails só são enviados quando há saldo
 * efetivamente pendente (amount - amount_used > 0) e o aviso ainda não
 * foi disparado para aquele cashback.
 */
class Tappy_CB_Notifications {

    const HOOK      = 'tappy_cb_daily_notifications';
    const LOCK_KEY  = 'tappy_cb_notifications_lock';

    public function __construct() {

        add_action(self::HOOK, [$this, 'run']);

        $this->ensure_schedule();
    }

    /**
     * Garante que o cron diário esteja agendado.
     */
    private function ensure_schedule() {

        if (!wp_next_scheduled(self::HOOK)) {

            // Agenda para o início do próximo dia (00:05) usando o fuso do site.
            $next = strtotime('tomorrow 00:05', current_time('timestamp'));

            wp_schedule_event(
                get_gmt_from_date(date('Y-m-d H:i:s', $next), 'U'),
                'daily',
                self::HOOK
            );
        }
    }

    /**
     * Lista dos IDs das classes de e-mail de aviso de pendência.
     */
    public static function pending_email_ids() {
        return [
            'Tappy_CB_Email_Pending_1',
            'Tappy_CB_Email_Pending_2',
            'Tappy_CB_Email_Pending_3',
            'Tappy_CB_Email_Pending_4',
        ];
    }

    /**
     * Executa a varredura diária.
     */
    public function run() {

        if (get_option('tappy_cashback_enabled') !== 'yes') {
            return;
        }

        if (!class_exists('WC_Emails')) {
            return;
        }

        // Lock anti-concorrência: evita que duas execuções simultâneas
        // (cron + WP-CLI por exemplo) disparem e-mails duplicados.
        if (get_transient(self::LOCK_KEY)) {
            return;
        }
        set_transient(self::LOCK_KEY, 1, 10 * MINUTE_IN_SECONDS);

        try {
            $this->process();
        } finally {
            delete_transient(self::LOCK_KEY);
        }
    }

    private function process() {

        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();

        // Coleta os thresholds dos e-mails ativos: [email_id => dias].
        $thresholds = [];
        foreach (self::pending_email_ids() as $email_id) {

            if (!isset($emails[$email_id])) {
                continue;
            }

            $email = $emails[$email_id];

            if (!$email->is_enabled()) {
                continue;
            }

            $days = method_exists($email, 'get_days_threshold')
                ? $email->get_days_threshold()
                : 0;

            if ($days <= 0) {
                continue;
            }

            $thresholds[$email_id] = $days;
        }

        if (empty($thresholds)) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'tappy_cashback';

        // Busca apenas cashbacks com saldo realmente pendente de uso e com prazo.
        $rows = $wpdb->get_results("
            SELECT id, user_id, amount, amount_used, expires_at, notified_days
            FROM $table
            WHERE status = 'available'
            AND expires_at IS NOT NULL
            AND expires_at > NOW()
            AND (amount - amount_used) > 0
        ");

        if (empty($rows)) {
            return;
        }

        // WP/SQLite armazenam datetime sem timezone, mas PHP em WordPress
        // usa UTC como timezone padrão, então strtotime e time() são
        // consistentes (ambos UTC).
        $now = time();

        foreach ($rows as $row) {

            $remaining = floatval($row->amount) - floatval($row->amount_used);
            if ($remaining <= 0) {
                continue;
            }

            $expires_ts = strtotime($row->expires_at);
            if (!$expires_ts || $expires_ts <= $now) {
                continue;
            }

            $days_remaining = (int) ceil(($expires_ts - $now) / DAY_IN_SECONDS);
            if ($days_remaining <= 0) {
                continue;
            }

            $notified = array_filter(array_map('intval', explode(',', (string) $row->notified_days)));
            $changed  = false;

            foreach ($thresholds as $email_id => $threshold) {

                // Marca thresholds maiores já ultrapassados como "notificados"
                // sem enviar e-mail (evita backfill de avisos antigos quando
                // o plugin é instalado em cashbacks já dentro da janela).
                if ($days_remaining < $threshold && !in_array($threshold, $notified, true)) {
                    $notified[] = $threshold;
                    $changed    = true;
                    continue;
                }

                // Envia e-mail apenas quando os dias restantes batem
                // exatamente com o threshold configurado e ainda não foi
                // notificado.
                if ($days_remaining !== $threshold) {
                    continue;
                }

                if (in_array($threshold, $notified, true)) {
                    continue;
                }

                Tappy_CB_Emails::trigger_pending(
                    $email_id,
                    $row->id,
                    $row->user_id,
                    $remaining,
                    $row->expires_at,
                    $days_remaining
                );

                $notified[] = $threshold;
                $changed    = true;
            }

            if ($changed) {
                $wpdb->update(
                    $table,
                    [
                        'notified_days' => implode(',', array_unique(array_map('intval', $notified))),
                        'updated_at'    => current_time('mysql'),
                    ],
                    ['id' => $row->id]
                );
            }
        }
    }
}
