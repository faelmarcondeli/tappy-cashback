<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Email_Pending_2 extends Tappy_CB_Email_Pending_Base {

    public function __construct() {

        $this->id             = 'tappy_cb_pending_2';
        $this->title          = __('Cashback pendente — aviso 2 (15 dias)', 'tappy');
        $this->description    = __('E-mail de aviso enviado ao cliente quando o cashback está próximo do vencimento.', 'tappy');
        $this->template_html  = 'emails/tappy-cashback-pending-2.php';
        $this->template_plain = 'emails/plain/tappy-cashback-pending-2.php';

        parent::__construct();
    }

    protected function default_days_threshold() {
        return 15;
    }
}
