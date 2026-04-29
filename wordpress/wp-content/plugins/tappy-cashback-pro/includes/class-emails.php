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

        // Registra as classes de e-mail no WooCommerce para que apareçam em
        // "WooCommerce > Configurações > Emails".
        add_filter('woocommerce_email_classes', [$this, 'register_email_classes']);

        // Indica ao WC que o diretório base de templates deste plugin existe,
        // para permitir override no tema do cliente em themes/<tema>/tappy-cashback-pro/.
        add_filter('woocommerce_template_directory', [$this, 'register_template_directory'], 10, 2);

        // Disparadores internos: o gerador de cashback chama este hook;
        // delegamos para o WC_Email correspondente.
        add_action('tappy_cb_send_generated_email', [$this, 'send_generated'], 10, 1);
        add_action('tappy_cb_daily_reminders', [$this, 'process_pending_reminders']);
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTRO NO WOOCOMMERCE
    |--------------------------------------------------------------------------
    */

    public function register_email_classes($emails) {

        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-email-cashback-gerado.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-email-cashback-pendente-base.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-email-cashback-pendente-60d.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-email-cashback-pendente-30d.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-email-cashback-pendente-7d.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-email-cashback-pendente-1d.php';

        $emails['Tappy_Email_Cashback_Gerado']       = new Tappy_Email_Cashback_Gerado();
        $emails['Tappy_Email_Cashback_Pendente_60d'] = new Tappy_Email_Cashback_Pendente_60d();
        $emails['Tappy_Email_Cashback_Pendente_30d'] = new Tappy_Email_Cashback_Pendente_30d();
        $emails['Tappy_Email_Cashback_Pendente_7d']  = new Tappy_Email_Cashback_Pendente_7d();
        $emails['Tappy_Email_Cashback_Pendente_1d']  = new Tappy_Email_Cashback_Pendente_1d();

        return $emails;
    }

    public function register_template_directory($directory, $template) {
        // Permite override no tema em themes/<tema>/tappy-cashback-pro/emails/cashback-*.php
        if (0 === strpos($template, 'emails/cashback-gerado') ||
            0 === strpos($template, 'emails/cashback-pendente-')) {
            return 'tappy-cashback-pro';
        }
        return $directory;
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

    /*
    |--------------------------------------------------------------------------
    | E-MAIL DE CASHBACK GERADO
    |--------------------------------------------------------------------------
    */

    public function send_generated($cashback_id) {

        if (get_option('tappy_cashback_enabled') !== 'yes') {
            return;
        }

        $mailer = $this->get_mailer();
        if (!$mailer) return;

        $emails = $mailer->get_emails();
        if (empty($emails['Tappy_Email_Cashback_Gerado'])) return;

        $emails['Tappy_Email_Cashback_Gerado']->trigger($cashback_id);
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

        $mailer = $this->get_mailer();
        if (!$mailer) return;

        $emails = $mailer->get_emails();
        $class  = 'Tappy_Email_Cashback_Pendente_' . $days . 'd';

        if (empty($emails[$class])) return;

        foreach ($by_user as $user_id => $user_rows) {

            // Linhas já estão reivindicadas (coluna marcada). Qualquer saída sem
            // envio é definitiva e não será reprocessada — atende ao requisito de
            // não disparar email quando não há saldo real pendente.
            $balance = Tappy_CB_Database::get_balance($user_id);
            if ($balance <= 0) {
                continue;
            }

            $emails[$class]->trigger($user_id, $user_rows);
        }
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

    /**
     * Garante que o WooCommerce mailer esteja inicializado e retorna a instância.
     */
    private function get_mailer() {
        if (!function_exists('WC')) return null;
        $wc = WC();
        if (!$wc) return null;
        return $wc->mailer();
    }
}
