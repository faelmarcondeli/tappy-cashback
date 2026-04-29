<?php
/**
 * E-mail: Cashback Gerado
 *
 * Variáveis disponíveis (vindas de Tappy_Email_Cashback_Gerado):
 * @var WC_Email $email
 * @var string   $email_heading
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var string   $additional_content
 * @var string   $user_name
 * @var string   $amount
 * @var string   $expires_at
 * @var int      $order_id
 * @var string   $site_name
 * @var string   $account_url
 *
 * Para sobrescrever este template no tema, copie para:
 * yourtheme/tappy-cashback-pro/emails/cashback-gerado.php
 */

if (!defined('ABSPATH')) exit;

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p style="margin:0 0 16px;font-size:16px;">
    <?php printf(esc_html__('Olá, %s,', 'tappy-cashback-pro'), '<strong>' . esc_html($user_name) . '</strong>'); ?>
</p>
<p style="margin:0 0 16px;font-size:15px;line-height:1.5;">
    <?php
    printf(
        wp_kses_post(__('Boas notícias! Seu pedido <strong>#%d</strong> foi processado e gerou um cashback para você usar em compras futuras.', 'tappy-cashback-pro')),
        (int) $order_id
    );
    ?>
</p>

<table cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f9f9f9;border:1px solid #e5e5e5;border-radius:4px;margin:20px 0;">
    <tr>
        <td style="padding:16px 20px;">
            <p style="margin:0 0 6px;font-size:13px;color:#777;"><?php esc_html_e('Valor do cashback', 'tappy-cashback-pro'); ?></p>
            <p style="margin:0;font-size:24px;color:#2c7a3d;font-weight:bold;"><?php echo wp_kses_post($amount); ?></p>
        </td>
    </tr>
    <tr>
        <td style="padding:0 20px 16px;">
            <p style="margin:0 0 4px;font-size:13px;color:#777;"><?php esc_html_e('Validade', 'tappy-cashback-pro'); ?></p>
            <p style="margin:0;font-size:15px;color:#333;"><?php echo esc_html($expires_at); ?></p>
        </td>
    </tr>
</table>

<p style="margin:0 0 24px;font-size:15px;line-height:1.5;">
    <?php esc_html_e('O valor já está disponível na sua conta e será aplicado automaticamente no seu próximo pedido.', 'tappy-cashback-pro'); ?>
</p>

<p style="text-align:center;margin:0 0 12px;">
    <a href="<?php echo esc_url($account_url); ?>" style="background:#2c7a3d;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:bold;">
        <?php esc_html_e('Ver meu cashback', 'tappy-cashback-pro'); ?>
    </a>
</p>

<?php
if (!empty($additional_content)) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
