<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

/*
|--------------------------------------------------------------------------
| REMOVER TABELA
|--------------------------------------------------------------------------
*/

$table = $wpdb->prefix . 'tappy_cashback';

$wpdb->query("DROP TABLE IF EXISTS $table");


/*
|--------------------------------------------------------------------------
| REMOVER OPÇÕES
|--------------------------------------------------------------------------
*/

delete_option('tappy_cashback_enabled');
delete_option('tappy_cashback_percentage');
delete_option('tappy_cashback_expiration');
delete_option('tappy_cashback_cron_interval');
delete_option('tappy_cb_db_version');


/*
|--------------------------------------------------------------------------
| REMOVER CRON
|--------------------------------------------------------------------------
*/

wp_clear_scheduled_hook('tappy_cb_daily_expiration');
wp_clear_scheduled_hook('tappy_cb_daily_notifications');


/*
|--------------------------------------------------------------------------
| LIMPAR CACHE
|--------------------------------------------------------------------------
*/

wp_cache_flush();
