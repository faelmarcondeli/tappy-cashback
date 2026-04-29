<?php
/**
 * E-mail: Lembrete de cashback expirando em 1 dia (último dia)
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
 * yourtheme/tappy-cashback-pro/emails/cashback-pendente-1d.php
 */

if (!defined('ABSPATH')) exit;

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p style="margin:0 0 16px;font-size:16px;">
    <?php printf(esc_html__('Olá, %s,', 'tappy-cashback-pro'), '<strong>' . esc_html($user_name) . '</strong>'); ?>
</p>

<p style="margin:0 0 16px;font-size:15px;line-height:1.5;color:#b71c1c;font-weight:bold;">
    <?php esc_html_e('Hoje é o último dia para usar seu cashback!', 'tappy-cashback-pro'); ?>
</p>

<p style="margin:0 0 16px;font-size:15px;line-height:1.5;">
    <?php esc_html_e('Após a data de expiração, o saldo será cancelado e você não poderá mais utilizá-lo. Aproveite agora!', 'tappy-cashback-pro'); ?>
</p>

<table cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#ffebee;border:2px solid #b71c1c;border-radius:4px;margin:20px 0;">
    <tr>
        <td style="padding:16px 20px;">
            <p style="margin:0 0 6px;font-size:13px;color:#777;"><?php esc_html_e('Saldo disponível', 'tappy-cashback-pro'); ?></p>
            <p style="margin:0 0 12px;font-size:28px;color:#b71c1c;font-weight:bold;"><?php echo wp_kses_post($balance); ?></p>
            <p style="margin:0 0 4px;font-size:13px;color:#777;"><?php esc_html_e('Vence', 'tappy-cashback-pro'); ?></p>
            <p style="margin:0;font-size:15px;color:#333;"><?php echo esc_html($expires_at); ?></p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0 0 12px;">
    <a href="<?php echo esc_url($shop_url); ?>" style="background:#b71c1c;color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:bold;font-size:16px;">
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
