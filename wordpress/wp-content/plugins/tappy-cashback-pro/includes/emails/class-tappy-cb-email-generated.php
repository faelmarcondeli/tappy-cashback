<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WC_Email')) return;

/**
 * E-mail enviado ao cliente quando um cashback é gerado.
 */
class Tappy_CB_Email_Generated extends WC_Email {

    public $cashback_id;
    public $cashback_amount;
    public $cashback_expires_at;

    public function __construct() {

        $this->id             = 'tappy_cb_generated';
        $this->customer_email = true;
        $this->title          = __('Cashback gerado', 'tappy');
        $this->description    = __('E-mail enviado ao cliente quando um novo cashback é creditado em sua conta.', 'tappy');
        $this->template_html  = 'emails/tappy-cashback-generated.php';
        $this->template_plain = 'emails/plain/tappy-cashback-generated.php';
        $this->template_base  = TAPPY_CB_PATH . 'templates/';
        $this->placeholders   = [];

        parent::__construct();
    }

    public function get_default_subject() {
        return __('[{site_title}] Você recebeu um novo cashback!', 'tappy');
    }

    public function get_default_heading() {
        return __('Novo cashback disponível', 'tappy');
    }

    public function trigger($cashback_id, $user_id, $amount, $expires_at) {

        $this->setup_locale();

        $user = get_user_by('id', $user_id);
        if (!$user) {
            $this->restore_locale();
            return;
        }

        $this->object              = (object) [
            'id'         => $cashback_id,
            'amount'     => $amount,
            'expires_at' => $expires_at,
            'user_id'    => $user_id,
        ];
        $this->cashback_id         = $cashback_id;
        $this->cashback_amount     = $amount;
        $this->cashback_expires_at = $expires_at;
        $this->recipient           = $user->user_email;

        $this->placeholders['{cashback_amount}']  = wp_strip_all_tags(wc_price($amount));
        $this->placeholders['{cashback_expires}'] = $expires_at
            ? wc_format_datetime(wc_string_to_datetime($expires_at))
            : __('sem validade', 'tappy');

        if (!$this->is_enabled() || !$this->get_recipient()) {
            $this->restore_locale();
            return;
        }

        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
            $this->get_attachments()
        );

        $this->restore_locale();
    }

    public function get_content_html() {
        return wc_get_template_html($this->template_html, [
            'email'         => $this,
            'email_heading' => $this->get_heading(),
            'amount'        => $this->cashback_amount,
            'expires_at'    => $this->cashback_expires_at,
            'sent_to_admin' => false,
            'plain_text'    => false,
        ], '', $this->template_base);
    }

    public function get_content_plain() {
        return wc_get_template_html($this->template_plain, [
            'email'         => $this,
            'email_heading' => $this->get_heading(),
            'amount'        => $this->cashback_amount,
            'expires_at'    => $this->cashback_expires_at,
            'sent_to_admin' => false,
            'plain_text'    => true,
        ], '', $this->template_base);
    }
}
