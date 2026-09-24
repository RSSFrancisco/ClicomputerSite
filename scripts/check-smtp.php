<?php
declare(strict_types=1);

// Solo CLI: comprueba TLS y autenticación. No envía correos.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\ContactMailer;

$mail = null;
try {
    $settings = require ROOT_PATH . '/config/site.php';
    $mail = (new ContactMailer())->createClient($settings['mail_from'] ?: $settings['email']);
    if (in_array('--config-only', $argv, true)) {
        echo "OK: configuración SMTP privada válida.\n";
        exit(0);
    }
    if (!$mail->smtpConnect()) throw new RuntimeException('smtp_connection');
    echo "OK: conexión cifrada y autenticación SMTP aceptadas por Titan. No se envió ningún correo.\n";
    $mail->smtpClose();
} catch (Throwable) {
    $error = $mail?->getSMTPInstance()->getError() ?? [];
    $code = preg_replace('/[^0-9.]/', '', (string) ($error['smtp_code'] ?? ''));
    fwrite(STDERR, "No se pudo verificar SMTP. Comprueba el archivo privado, la contraseña, el acceso de terceros en Titan y el puerto de salida. Código SMTP: " . ($code ?: 'no disponible') . "\n");
    $mail?->smtpClose();
    exit(1);
}
