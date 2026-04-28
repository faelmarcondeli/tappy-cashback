<?php
if (!defined('ABSPATH')) exit;

echo "= " . wp_strip_all_tags($email_heading) . " =\n\n";

echo __('Olá!', 'tappy') . "\n\n";

printf(
    __('Boas notícias: você recebeu um cashback de %s pelo seu último pedido.', 'tappy'),
    wp_strip_all_tags(wc_price($amount))
);
echo "\n\n";

if (!empty($expires_at)) {
    printf(
        __('Esse saldo está disponível até %s. Aproveite para usá-lo na sua próxima compra!', 'tappy'),
        wc_format_datetime(wc_string_to_datetime($expires_at))
    );
} else {
    echo __('Esse saldo não tem prazo de validade. Use quando quiser na sua próxima compra!', 'tappy');
}
echo "\n\n";

echo __('Acesse sua conta para ver todos os detalhes do seu cashback.', 'tappy') . "\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
