<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WC_Email')) return;

abstract class Tappy_Email_Cashback_Pendente_Base extends WC_Email {

    protected $days_before = 0;
    protected $rows = [];

    public function __construct() {

        $this->customer_email = true;
        $this->template_html  = 'emails/cashback-pendente-' . $this->days_before . 'd.php';
        $this->template_plain = 'emails/plain/cashback-pendente-' . $this->days_before . 'd.php';
        $this->template_base  = TAPPY_CB_PATH . 'template/';

        $this->placeholders = [
            '{site_title}'      => $this->get_blogname(),
            '{days}'            => (string) $this->days_before,
            '{balance}'         => '',
            '{expiring_amount}' => '',
            '{expires_at}'      => '',
        ];

        add_action('tappy_cb_send_pendente_' . $this->days_before . 'd_notification', [$this, 'trigger'], 10, 2);

        parent::__construct();
    }

    public function trigger($user_id, $rows) {

        $this->setup_locale();

        $user = $user_id ? get_user_by('id', $user_id) : false;

        if (!$user || !is_email($user->user_email) || empty($rows)) {
            $this->restore_locale();
            return;
        }

        $this->user      = $user;
        $this->rows      = $rows;
        $this->recipient = $user->user_email;

        $balance = class_exists('Tappy_CB_Database') ? Tappy_CB_Database::get_balance($user_id) : 0;
        $this->balance = $balance;

        $expiring_amount = 0;
        $earliest_expiry = null;
        foreach ($rows as $row) {
            $expiring_amount += ((float) $row->amount - (float) $row->amount_used);
            if (!$earliest_expiry || strtotime($row->expires_at) < strtotime($earliest_expiry)) {
                $earliest_expiry = $row->expires_at;
            }
        }
        $this->expiring_amount = $expiring_amount;
        $this->earliest_expiry = $earliest_expiry;

        $this->placeholders['{balance}']         = wp_strip_all_tags(wc_price($balance));
        $this->placeholders['{expiring_amount}'] = wp_strip_all_tags(wc_price($expiring_amount));
        $this->placeholders['{expires_at}']      = $this->format_date($earliest_expiry);

        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_content(),
                $this->get_headers(),
                $this->get_attachments()
            );
        }

        $this->restore_locale();
    }

    public function get_content_html() {
        return wc_get_template_html($this->template_html, $this->get_template_args(false), '', $this->template_base);
    }

    public function get_content_plain() {
        return wc_get_template_html($this->template_plain, $this->get_template_args(true), '', $this->template_base);
    }

    private function get_template_args($plain_text = false) {

        $user_name = '';
        if ($this->user) {
            $user_name = trim($this->user->first_name);
            if (!$user_name) $user_name = $this->user->display_name;
            if (!$user_name) $user_name = $this->user->user_login;
        }

        $shop_url    = function_exists('wc_get_page_permalink') ? (wc_get_page_permalink('shop') ?: home_url('/')) : home_url('/');
        $account_url = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('cashback') : home_url('/');

        return [
            'email'              => $this,
            'email_heading'      => $this->get_heading(),
            'sent_to_admin'      => false,
            'plain_text'         => $plain_text,
            'additional_content' => $this->get_additional_content(),
            'user_name'          => $user_name,
            'balance'            => isset($this->balance) ? wp_strip_all_tags(wc_price($this->balance)) : '',
            'expiring_amount'    => isset($this->expiring_amount) ? wp_strip_all_tags(wc_price($this->expiring_amount)) : '',
            'expires_at'         => isset($this->earliest_expiry) ? $this->format_date($this->earliest_expiry) : '',
            'days'               => $this->days_before,
            'site_name'          => $this->get_blogname(),
            'shop_url'           => $shop_url,
            'account_url'        => $account_url,
        ];
    }

    protected function format_date($mysql_date) {
        if (!$mysql_date) return '—';
        $ts = strtotime($mysql_date);
        if (!$ts) return '—';
        return date_i18n(get_option('date_format', 'd/m/Y'), $ts);
    }

    public function init_form_fields() {

        $this->form_fields = [
            'enabled' => [
                'title'   => __('Ativar/Desativar', 'tappy-cashback-pro'),
                'type'    => 'checkbox',
                'label'   => __('Ativar este e-mail', 'tappy-cashback-pro'),
                'default' => 'yes',
            ],
            'subject' => [
                'title'       => __('Assunto', 'tappy-cashback-pro'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Marcadores disponíveis: %s', 'tappy-cashback-pro'), '<code>{site_title}, {days}, {balance}, {expiring_amount}, {expires_at}</code>'),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ],
            'heading' => [
                'title'       => __('Cabeçalho', 'tappy-cashback-pro'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Marcadores disponíveis: %s', 'tappy-cashback-pro'), '<code>{site_title}, {days}, {balance}, {expiring_amount}, {expires_at}</code>'),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ],
            'additional_content' => [
                'title'       => __('Conteúdo adicional', 'tappy-cashback-pro'),
                'description' => __('Texto exibido abaixo do conteúdo principal do e-mail.', 'tappy-cashback-pro'),
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __('N/D', 'tappy-cashback-pro'),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ],
            'email_type' => [
                'title'       => __('Tipo de e-mail', 'tappy-cashback-pro'),
                'type'        => 'select',
                'description' => __('Escolha o formato do e-mail.', 'tappy-cashback-pro'),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => ['html' => __('HTML', 'tappy-cashback-pro')],
                'desc_tip'    => true,
            ],
        ];
    }

    public function get_default_additional_content() {
        return __('Aproveite seu saldo antes que expire!', 'tappy-cashback-pro');
    }
}
