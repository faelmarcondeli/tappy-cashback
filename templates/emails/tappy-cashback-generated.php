<?php
/**
 * Template HTML: Cashback gerado.
 *
 * Variáveis disponíveis: $email, $email_heading, $amount, $expires_at
 */
if (!defined('ABSPATH')) exit;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php esc_html_e('Olá!', 'tappy'); ?></p>

<p><?php
printf(
    /* translators: %s: valor do cashback formatado */
    esc_html__('Boas notícias: você recebeu um cashback de %s pelo seu último pedido.', 'tappy'),
    wp_kses_post(wc_price($amount))
);
?></p>

<?php if (!empty($expires_at)) : ?>
    <p><?php
    printf(
        /* translators: %s: data formatada de expiração */
        esc_html__('Esse saldo está disponível até %s. Aproveite para usá-lo na sua próxima compra!', 'tappy'),
        esc_html(wc_format_datetime(wc_string_to_datetime($expires_at)))
    );
    ?></p>
<?php else : ?>
    <p><?php esc_html_e('Esse saldo não tem prazo de validade. Use quando quiser na sua próxima compra!', 'tappy'); ?></p>
<?php endif; ?>

<p><?php esc_html_e('Acesse sua conta para ver todos os detalhes do seu cashback.', 'tappy'); ?></p>

<?php
do_action('woocommerce_email_footer', $email);
