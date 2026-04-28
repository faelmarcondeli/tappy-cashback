<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WC_Email')) return;

/**
 * Classe base abstrata para os e-mails de cashback pendente.
 *
 * Cada subclasse define o ID único, o template e o número padrão
 * de dias antes do vencimento em que o e-mail é disparado.
 */
abstract class Tappy_CB_Email_Pending_Base extends WC_Email {

    public $cashback_id;
    public $cashback_remaining;
    public $cashback_expires_at;
    public $cashback_days_remaining;

    /**
     * Número padrão de dias antes do vencimento em que esse e-mail dispara.
     */
    abstract protected function default_days_threshold();

    public function __construct() {

        $this->customer_email = true;
        $this->template_base  = TAPPY_CB_PATH . 'templates/';
        $this->placeholders   = [];

        parent::__construct();
    }

    public function init_form_fields() {

        parent::init_form_fields();

        $this->form_fields['days_threshold'] = [
            'title'       => __('Dias antes do vencimento', 'tappy'),
            'type'        => 'number',
            'description' => __('Disparar este e-mail quando o cashback estiver a este número de dias do vencimento.', 'tappy'),
            'default'     => (string) $this->default_days_threshold(),
            'desc_tip'    => true,
            'custom_attributes' => [
                'min'  => '1',
                'step' => '1',
            ],
        ];
    }

    public function get_days_threshold() {
        $value = absint($this->get_option('days_threshold', $this->default_days_threshold()));
        return $value > 0 ? $value : $this->default_days_threshold();
    }

    public function get_default_subject() {
        return __('[{site_title}] Seu cashback expira em {days} dia(s)!', 'tappy');
    }

    public function get_default_heading() {
        return __('Seu cashback está prestes a expirar', 'tappy');
    }

    public function trigger($cashback_id, $user_id, $remaining, $expires_at, $days) {

        $this->setup_locale();

        $user = get_user_by('id', $user_id);
        if (!$user) {
            $this->restore_locale();
            return;
        }

        $this->object                  = (object) [
            'id'         => $cashback_id,
            'remaining'  => $remaining,
            'expires_at' => $expires_at,
            'user_id'    => $user_id,
        ];
        $this->cashback_id             = $cashback_id;
        $this->cashback_remaining      = $remaining;
        $this->cashback_expires_at     = $expires_at;
        $this->cashback_days_remaining = $days;
        $this->recipient               = $user->user_email;

        $this->placeholders['{cashback_amount}']  = wp_strip_all_tags(wc_price($remaining));
        $this->placeholders['{cashback_expires}'] = $expires_at
            ? wc_format_datetime(wc_string_to_datetime($expires_at))
            : '';
        $this->placeholders['{days}'] = (string) $days;

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
            'email'           => $this,
            'email_heading'   => $this->get_heading(),
            'remaining'       => $this->cashback_remaining,
            'expires_at'      => $this->cashback_expires_at,
            'days_remaining'  => $this->cashback_days_remaining,
            'sent_to_admin'   => false,
            'plain_text'      => false,
        ], '', $this->template_base);
    }

    public function get_content_plain() {
        return wc_get_template_html($this->template_plain, [
            'email'           => $this,
            'email_heading'   => $this->get_heading(),
            'remaining'       => $this->cashback_remaining,
            'expires_at'      => $this->cashback_expires_at,
            'days_remaining'  => $this->cashback_days_remaining,
            'sent_to_admin'   => false,
            'plain_text'      => true,
        ], '', $this->template_base);
    }
}
