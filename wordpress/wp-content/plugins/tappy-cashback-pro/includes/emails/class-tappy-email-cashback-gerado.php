<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('WC_Email')) return;

class Tappy_Email_Cashback_Gerado extends WC_Email {

    public function __construct() {

        $this->id             = 'tappy_cashback_gerado';
        $this->customer_email = true;
        $this->title          = __('Tappy Cashback: cashback gerado', 'tappy-cashback-pro');
        $this->description    = __('Enviado ao cliente quando um novo cashback é creditado em sua conta após a confirmação de um pedido.', 'tappy-cashback-pro');

        $this->template_html  = 'emails/cashback-gerado.php';
        $this->template_plain = 'emails/plain/cashback-gerado.php';
        $this->template_base  = TAPPY_CB_PATH . 'template/';

        $this->placeholders = [
            '{site_title}' => $this->get_blogname(),
            '{order_id}'   => '',
            '{amount}'     => '',
        ];

        add_action('tappy_cb_send_generated_email_notification', [$this, 'trigger'], 10, 1);

        parent::__construct();
    }

    public function get_default_subject() {
        return __('[{site_title}] Você ganhou cashback no pedido #{order_id}', 'tappy-cashback-pro');
    }

    public function get_default_heading() {
        return __('Você ganhou cashback!', 'tappy-cashback-pro');
    }

    public function trigger($cashback_id) {

        $this->setup_locale();

        if (!$cashback_id) {
            $this->restore_locale();
            return;
        }

        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}tappy_cashback WHERE id = %d", $cashback_id)
        );

        if (!$row) {
            $this->restore_locale();
            return;
        }

        $user = get_user_by('id', $row->user_id);
        if (!$user || !is_email($user->user_email)) {
            $this->restore_locale();
            return;
        }

        $this->object    = $row;
        $this->user      = $user;
        $this->recipient = $user->user_email;

        $this->placeholders['{order_id}'] = (int) $row->order_id;
        $this->placeholders['{amount}']   = wp_strip_all_tags(wc_price($row->amount));

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

        $expires_at = __('Não expira', 'tappy-cashback-pro');
        if ($this->object && !empty($this->object->expires_at)) {
            $ts = strtotime($this->object->expires_at);
            if ($ts) $expires_at = date_i18n(get_option('date_format', 'd/m/Y'), $ts);
        }

        $account_url = home_url('/');
        if (function_exists('wc_get_account_endpoint_url')) {
            $account_url = wc_get_account_endpoint_url('cashback');
        }

        return [
            'email'              => $this,
            'email_heading'      => $this->get_heading(),
            'sent_to_admin'      => false,
            'plain_text'         => $plain_text,
            'additional_content' => $this->get_additional_content(),
            'user_name'          => $user_name,
            'amount'             => $this->object ? wp_strip_all_tags(wc_price($this->object->amount)) : '',
            'expires_at'         => $expires_at,
            'order_id'           => $this->object ? (int) $this->object->order_id : 0,
            'site_name'          => $this->get_blogname(),
            'account_url'        => $account_url,
        ];
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
                'description' => sprintf(__('Marcadores disponíveis: %s', 'tappy-cashback-pro'), '<code>{site_title}, {order_id}, {amount}</code>'),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ],
            'heading' => [
                'title'       => __('Cabeçalho', 'tappy-cashback-pro'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Marcadores disponíveis: %s', 'tappy-cashback-pro'), '<code>{site_title}, {order_id}, {amount}</code>'),
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
        return __('Obrigado por escolher nossa loja!', 'tappy-cashback-pro');
    }
}
