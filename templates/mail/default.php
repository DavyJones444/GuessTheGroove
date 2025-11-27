<?php
// Wir brauchen die volle URL für Bilder (aus .env oder Konstante)
$baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
$logoUrl = rtrim($baseUrl, '/') . '/assets/images/logo.png';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject) ?></title>
    <style>
        /* Mobile Styles */
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .content { padding: 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0b14; font-family: Arial, sans-serif; color: #ffffff;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #0a0b14; padding: 40px 0;">
        <tr>
            <td align="center">
                
                <table role="presentation" class="container" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #141522; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                    
                    <tr>
                        <td align="center" style="padding: 40px 0 20px 0; border-bottom: 1px solid #333;">
                            <img src="<?= $logoUrl ?>" alt="Guess The Groove" width="150" style="display: block; border: 0;">
                        </td>
                    </tr>

                    <tr>
                        <td class="content" style="padding: 40px;">
                            <h1 style="font-size: 24px; margin: 0 0 20px 0; color: #7da7ff; text-align: center;">
                                <?= htmlspecialchars($headline) ?>
                            </h1>
                            
                            <p style="font-size: 16px; line-height: 1.6; color: #dddddd; margin-bottom: 30px; white-space: pre-line;">
                                <?= $content ?>
                            </p>

                            <?php if (!empty($buttonUrl) && !empty($buttonText)): ?>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td align="center">
                                            <a href="<?= htmlspecialchars($buttonUrl) ?>" 
                                               style="background-color: #7da7ff; color: #141522; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;">
                                                <?= htmlspecialchars($buttonText) ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #1c1c2b; padding: 20px; text-align: center; color: #666; font-size: 12px;">
                            &copy; <?= date('Y') ?> Guess The Groove.<br>
                            Diese E-Mail wurde automatisch generiert.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>