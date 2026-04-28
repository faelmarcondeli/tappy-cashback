<?php
if (!defined('ABSPATH')) exit;

echo "= " . wp_strip_all_tags($email_heading) . " =\n\n";

echo __('Olá!', 'tappy') . "\n\n";

printf(
    __('Faltam apenas %2$d dias para o seu cashback de %1$s expirar.', 'tappy'),
    wp_strip_all_tags(wc_price($remaining)),
    intval($days_remaining)
);
echo "\n\n";

if (!empty($expires_at)) {
    printf(
        __('Data de expiração: %s.', 'tappy'),
        wc_format_datetime(wc_string_to_datetime($expires_at))
    );
    echo "\n\n";
}

echo __('Não perca essa oportunidade — utilize o saldo na sua próxima compra antes que ele expire.', 'tappy') . "\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
