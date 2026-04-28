<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Email_Pending_3 extends Tappy_CB_Email_Pending_Base {

    public function __construct() {

        $this->id             = 'tappy_cb_pending_3';
        $this->title          = __('Cashback pendente — aviso 3 (7 dias)', 'tappy');
        $this->description    = __('E-mail de aviso enviado ao cliente quando o cashback está próximo do vencimento.', 'tappy');
        $this->template_html  = 'emails/tappy-cashback-pending-3.php';
        $this->template_plain = 'emails/plain/tappy-cashback-pending-3.php';

        parent::__construct();
    }

    protected function default_days_threshold() {
        return 7;
    }
}
