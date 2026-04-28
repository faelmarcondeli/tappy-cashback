<?php
/**
 * Template HTML: Cashback pendente — aviso 4 (último aviso).
 *
 * Variáveis disponíveis: $email, $email_heading, $remaining, $expires_at, $days_remaining
 */
if (!defined('ABSPATH')) exit;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php esc_html_e('Último aviso!', 'tappy'); ?></p>

<p><?php
printf(
    /* translators: 1: valor restante; 2: número de dias */
    esc_html__('Seu cashback de %1$s expira em apenas %2$d dia. Essa é a sua última chance de utilizar o saldo.', 'tappy'),
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

<p><?php esc_html_e('Faça uma compra hoje mesmo e aproveite o desconto antes que o saldo expire!', 'tappy'); ?></p>

<?php
do_action('woocommerce_email_footer', $email);
