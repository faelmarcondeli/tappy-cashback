<?php
/**
 * E-mail: Lembrete de cashback expirando em 7 dias
 *
 * @var WC_Email $email
 * @var string   $email_heading
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var string   $additional_content
 * @var string   $user_name
 * @var string   $balance
 * @var string   $expiring_amount
 * @var string   $expires_at
 * @var int      $days
 * @var string   $site_name
 * @var string   $shop_url
 * @var string   $account_url
 *
 * Para sobrescrever no tema:
 * yourtheme/tappy-cashback-pro/emails/cashback-pendente-7d.php
 */

if (!defined('ABSPATH')) exit;

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p style="margin:0 0 16px;font-size:16px;">
    <?php printf(esc_html__('Olá, %s,', 'tappy-cashback-pro'), '<strong>' . esc_html($user_name) . '</strong>'); ?>
</p>

<p style="margin:0 0 16px;font-size:15px;line-height:1.5;">
    <?php esc_html_e('Atenção! Seu cashback expira em apenas 7 dias. Não deixe esse benefício escapar.', 'tappy-cashback-pro'); ?>
</p>

<table cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#ffebee;border:1px solid #ef9a9a;border-radius:4px;margin:20px 0;">
    <tr>
        <td style="padding:16px 20px;">
            <p style="margin:0 0 6px;font-size:13px;color:#777;"><?php esc_html_e('Saldo disponível', 'tappy-cashback-pro'); ?></p>
            <p style="margin:0 0 12px;font-size:24px;color:#c62828;font-weight:bold;"><?php echo wp_kses_post($balance); ?></p>
            <p style="margin:0 0 4px;font-size:13px;color:#777;"><?php printf(esc_html__('Vencendo em %d dias', 'tappy-cashback-pro'), (int) $days); ?></p>
            <p style="margin:0;font-size:15px;color:#333;"><?php echo wp_kses_post($expiring_amount); ?> &middot; <?php echo esc_html($expires_at); ?></p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0 0 12px;">
    <a href="<?php echo esc_url($shop_url); ?>" style="background:#c62828;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:bold;">
        <?php esc_html_e('Usar meu cashback agora', 'tappy-cashback-pro'); ?>
    </a>
</p>

<p style="text-align:center;margin:8px 0 0;font-size:13px;">
    <a href="<?php echo esc_url($account_url); ?>" style="color:#555;text-decoration:underline;">
        <?php esc_html_e('Ver meu cashback', 'tappy-cashback-pro'); ?>
    </a>
</p>

<?php
if (!empty($additional_content)) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_footer', $email);
