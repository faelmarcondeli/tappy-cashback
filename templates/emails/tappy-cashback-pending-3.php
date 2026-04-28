<?php
/**
 * Template HTML: Cashback pendente — aviso 3.
 *
 * Variáveis disponíveis: $email, $email_heading, $remaining, $expires_at, $days_remaining
 */
if (!defined('ABSPATH')) exit;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php esc_html_e('Atenção!', 'tappy'); ?></p>

<p><?php
printf(
    /* translators: 1: valor restante; 2: número de dias */
    esc_html__('Seu cashback de %1$s expira em %2$d dias. Esse é um aviso importante para você não perder esse saldo.', 'tappy'),
    wp_kses_post(wc_price($remaining)),
    intval($days_remaining)
);
?></p>

<?php if (!empty($expires_at)) : ?>
    <p><strong><?php
    printf(
        /* translators: %s: data de expiração */
        esc_html__('Vence em: %s.', 'tappy'),
        esc_html(wc_format_datetime(wc_string_to_datetime($expires_at)))
    );
    ?></strong></p>
<?php endif; ?>

<p><?php esc_html_e('Acesse a loja agora e aproveite seu saldo antes que seja tarde!', 'tappy'); ?></p>

<?php
do_action('woocommerce_email_footer', $email);
