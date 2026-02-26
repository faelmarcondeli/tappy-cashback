<?php
/**
 * Plugin Name: Tappy Cashback
 * Description: Sistema de cashback para WooCommerce.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

define('TAPPY_CB_PATH', plugin_dir_path(__FILE__));

require_once TAPPY_CB_PATH . 'includes/class-settings.php';
require_once TAPPY_CB_PATH . 'includes/class-generator.php';
require_once TAPPY_CB_PATH . 'includes/class-myaccount.php';

class Tappy_Cashback {

    public function __construct() {
        new Tappy_CB_Settings();
        new Tappy_CB_Generator();
        new Tappy_CB_MyAccount();
    }
}

new Tappy_Cashback();
