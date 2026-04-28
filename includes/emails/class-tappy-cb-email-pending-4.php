<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Email_Pending_4 extends Tappy_CB_Email_Pending_Base {

    public function __construct() {

        $this->id             = 'tappy_cb_pending_4';
        $this->title          = __('Cashback pendente — aviso 4 (1 dia)', 'tappy');
        $this->description    = __('E-mail de aviso enviado ao cliente quando o cashback está próximo do vencimento.', 'tappy');
        $this->template_html  = 'emails/tappy-cashback-pending-4.php';
        $this->template_plain = 'emails/plain/tappy-cashback-pending-4.php';

        parent::__construct();
    }

    protected function default_days_threshold() {
        return 1;
    }
}
