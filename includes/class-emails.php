<?php
if (!defined('ABSPATH')) exit;

/**
 * Registra as classes de e-mail do plugin no WooCommerce.
 */
class Tappy_CB_Emails {

    public function __construct() {
        add_filter('woocommerce_email_classes', [$this, 'register_emails']);
    }

    public function register_emails($emails) {

        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-cb-email-generated.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-cb-email-pending.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-cb-email-pending-1.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-cb-email-pending-2.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-cb-email-pending-3.php';
        require_once TAPPY_CB_PATH . 'includes/emails/class-tappy-cb-email-pending-4.php';

        $emails['Tappy_CB_Email_Generated']  = new Tappy_CB_Email_Generated();
        $emails['Tappy_CB_Email_Pending_1']  = new Tappy_CB_Email_Pending_1();
        $emails['Tappy_CB_Email_Pending_2']  = new Tappy_CB_Email_Pending_2();
        $emails['Tappy_CB_Email_Pending_3']  = new Tappy_CB_Email_Pending_3();
        $emails['Tappy_CB_Email_Pending_4']  = new Tappy_CB_Email_Pending_4();

        return $emails;
    }

    /**
     * Dispara o e-mail de cashback gerado.
     */
    public static function trigger_generated($cashback_id, $user_id, $amount, $expires_at) {

        if (!class_exists('WC_Emails')) {
            return;
        }

        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();

        if (!isset($emails['Tappy_CB_Email_Generated'])) {
            return;
        }

        $emails['Tappy_CB_Email_Generated']->trigger($cashback_id, $user_id, $amount, $expires_at);
    }

    /**
     * Dispara o e-mail de cashback pendente para um determinado e-mail (1 a 4).
     */
    public static function trigger_pending($email_id, $cashback_id, $user_id, $remaining, $expires_at, $days) {

        if (!class_exists('WC_Emails')) {
            return;
        }

        $mailer = WC()->mailer();
        $emails = $mailer->get_emails();

        if (!isset($emails[$email_id])) {
            return;
        }

        $emails[$email_id]->trigger($cashback_id, $user_id, $remaining, $expires_at, $days);
    }
}
