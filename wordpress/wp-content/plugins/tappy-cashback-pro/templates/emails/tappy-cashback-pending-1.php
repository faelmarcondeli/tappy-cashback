<?php
/**
 * Template HTML: Cashback pendente — aviso 1.
 *
 * Variáveis disponíveis: $email, $email_heading, $remaining, $expires_at, $days_remaining
 */
if (!defined('ABSPATH')) exit;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php esc_html_e('Olá!', 'tappy'); ?></p>

<p><?php
printf(
    /* translators: 1: valor restante; 2: número de dias */
    esc_html__('Você ainda tem %1$s de cashback disponível. Esse saldo expira em %2$d dias.', 'tappy'),
    wp_kses_post(wc_price($remaining)),
    intval($days_remaining)
);
?></p>

<?php if (!empty($expires_at)) : ?>
    <p><?php
    printf(
        /* translators: %s: data de expiração */
        esc_html__('Data de expiração: %s.', 'tappy'),
        esc_html(wc_format_datetime(wc_string_to_datetime($expires_at)))
    );
    ?></p>
<?php endif; ?>

<p><?php esc_html_e('Que tal aproveitar para fazer uma nova compra e usar esse saldo?', 'tappy'); ?></p>

<?php
do_action('woocommerce_email_footer', $email);
