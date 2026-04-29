<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Tappy_Email_Cashback_Pendente_Base')) return;

class Tappy_Email_Cashback_Pendente_1d extends Tappy_Email_Cashback_Pendente_Base {

    protected $days_before = 1;

    public function __construct() {
        $this->id          = 'tappy_cashback_pendente_1d';
        $this->title       = __('Tappy Cashback: lembrete último dia', 'tappy-cashback-pro');
        $this->description = __('Enviado ao cliente 1 dia antes da expiração do cashback, apenas se houver saldo disponível.', 'tappy-cashback-pro');
        parent::__construct();
    }

    public function get_default_subject() {
        return __('[{site_title}] Último dia para usar seu cashback', 'tappy-cashback-pro');
    }

    public function get_default_heading() {
        return __('Último dia para usar seu cashback', 'tappy-cashback-pro');
    }
}
