<?php
if (!defined('ABSPATH')) exit;

echo "= " . wp_strip_all_tags($email_heading) . " =\n\n";

echo __('Último aviso!', 'tappy') . "\n\n";

printf(
    __('Seu cashback de %1$s expira em apenas %2$d dia. Essa é a sua última chance de utilizar o saldo.', 'tappy'),
    wp_strip_all_tags(wc_price($remaining)),
    intval($days_remaining)
);
echo "\n\n";

if (!empty($expires_at)) {
    printf(
        __('Vence em: %s.', 'tappy'),
        wc_format_datetime(wc_string_to_datetime($expires_at))
    );
    echo "\n\n";
}

echo __('Faça uma compra hoje mesmo e aproveite o desconto antes que o saldo expire!', 'tappy') . "\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
