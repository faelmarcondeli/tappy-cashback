<?php
/**
 * E-mail: Cashback pendente — 60 dias para expirar
 *
 * Variáveis disponíveis:
 * @var string $user_name        Nome do cliente
 * @var string $balance          Saldo total disponível (formatado)
 * @var string $expiring_amount  Valor que expira em 60 dias (formatado)
 * @var string $expires_at       Data de expiração formatada
 * @var int    $days             Dias até expirar (60)
 * @var string $site_name        Nome da loja
 * @var string $shop_url         URL da loja
 * @var string $account_url      URL "Minha Conta > Cashback"
 */

if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title><?php echo esc_html($site_name); ?> - Seu cashback expira em 60 dias</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f5f5;padding:24px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:6px;overflow:hidden;max-width:600px;">
                <tr>
                    <td style="background:#2c7a3d;padding:24px;text-align:center;color:#ffffff;">
                        <h1 style="margin:0;font-size:22px;">Você tem cashback para usar</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 28px;">
                        <p style="margin:0 0 16px;font-size:16px;">Olá, <strong><?php echo esc_html($user_name); ?></strong>,</p>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.5;">
                            Lembramos que você possui saldo de cashback disponível para utilizar em sua próxima compra. Falta bastante tempo, mas vale planejar!
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9f9f9;border:1px solid #e5e5e5;border-radius:4px;margin:20px 0;">
                            <tr>
                                <td style="padding:16px 20px;">
                                    <p style="margin:0 0 6px;font-size:13px;color:#777;">Saldo disponível</p>
                                    <p style="margin:0;font-size:24px;color:#2c7a3d;font-weight:bold;"><?php echo wp_kses_post($balance); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 20px 16px;">
                                    <p style="margin:0 0 4px;font-size:13px;color:#777;">Expira em <?php echo esc_html($days); ?> dias</p>
                                    <p style="margin:0;font-size:15px;color:#333;">
                                        <?php echo wp_kses_post($expiring_amount); ?> em <?php echo esc_html($expires_at); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p style="text-align:center;margin:0 0 12px;">
                            <a href="<?php echo esc_url($shop_url); ?>" style="background:#2c7a3d;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:bold;">Ir às compras</a>
                        </p>
                        <p style="text-align:center;margin:0;font-size:13px;">
                            <a href="<?php echo esc_url($account_url); ?>" style="color:#2c7a3d;">Ver meu cashback</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px;background:#f0f0f0;text-align:center;font-size:12px;color:#888;">
                        <?php echo esc_html($site_name); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
