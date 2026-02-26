<?php
if (!defined('ABSPATH')) exit;

class Tappy_CB_Settings {

    public function __construct() {
        add_filter('woocommerce_general_settings', [$this, 'add_settings']);
    }

    public function add_settings($settings) {

        $new_settings = array(
            array(
                'title' => 'Cashback',
                'type'  => 'title',
                'desc'  => 'Configurações de Cashback',
                'id'    => 'tappy_cashback_section'
            ),

            array(
                'title' => 'Habilitar Cashback',
                'id'    => 'tappy_cashback_enabled',
                'type'  => 'checkbox',
                'default' => 'no'
            ),

            array(
                'title' => 'Porcentagem (%)',
                'id'    => 'tappy_cashback_percentage',
                'type'  => 'number',
                'default' => '5',
                'custom_attributes' => array(
                    'step' => '0.1',
                    'min'  => '0'
                )
            ),

            array(
                'title' => 'Validade (dias)',
                'id'    => 'tappy_cashback_expiration',
                'type'  => 'number',
                'desc'  => 'Deixe vazio para não expirar',
            ),

            array(
                'type' => 'sectionend',
                'id'   => 'tappy_cashback_section'
            ),
        );

        return array_merge($settings, $new_settings);
    }
}
