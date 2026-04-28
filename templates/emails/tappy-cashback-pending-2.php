<?php
/**
 * Template HTML: Cashback pendente — aviso 2.
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
    esc_html__('Faltam apenas %2$d dias para o seu cashback de %1$s expirar.', 'tappy'),
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

<p><?php esc_html_e('Não perca essa oportunidade — utilize o saldo na sua próxima compra antes que ele expire.', 'tappy'); ?></p>

<?php
do_action('woocommerce_email_footer', $email);
