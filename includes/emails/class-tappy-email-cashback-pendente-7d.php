<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Tappy_Email_Cashback_Pendente_Base')) return;

class Tappy_Email_Cashback_Pendente_7d extends Tappy_Email_Cashback_Pendente_Base {

    protected $days_before = 7;

    public function __construct() {
        $this->id          = 'tappy_cashback_pendente_7d';
        $this->title       = __('Tappy Cashback: lembrete 7 dias', 'tappy-cashback-pro');
        $this->description = __('Enviado ao cliente 7 dias antes da expiração do cashback, apenas se houver saldo disponível.', 'tappy-cashback-pro');
        parent::__construct();
    }

    public function get_default_subject() {
        return __('[{site_title}] Seu cashback expira em 7 dias', 'tappy-cashback-pro');
    }

    public function get_default_heading() {
        return __('Seu cashback expira em 7 dias', 'tappy-cashback-pro');
    }
}
