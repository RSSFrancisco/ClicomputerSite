<?php
declare(strict_types=1);

// Solo CLI: comprueba TLS y autenticación. No envía correos.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\ContactMailer;

$mail = null;
$stage = 'configuration';
$smtpCode = '';
try {
    $settings = require ROOT_PATH . '/config/site.php';
    $mail = (new ContactMailer())->createClient($settings['mail_from'] ?: $settings['email']);
    if (in_array('--config-only', $argv, true)) {
        echo "OK: configuración SMTP privada válida.\n";
        exit(0);
    }
    echo "OK: configuración privada leída. Comprobando conexión con Titan.\n";
    $stage = 'connection';
    // El callback consume el protocolo, pero nunca imprime líneas SMTP ni credenciales.
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = static function (string $line, int $level) use (&$stage, &$smtpCode): void {
        if (str_starts_with($line, 'CLIENT -> SERVER: STARTTLS')) $stage = 'tls';
        if (str_starts_with($line, 'CLIENT -> SERVER: AUTH')) $stage = 'authentication';
        if (preg_match('/^SERVER -> CLIENT: ([45][0-9]{2})(?:[ -]([245]\\.[0-9]{1,3}\\.[0-9]{1,3}))?/', $line, $match)) {
            $smtpCode = $match[1] . (isset($match[2]) ? ' ' . $match[2] : '');
        }
    };
    if (!$mail->smtpConnect()) throw new RuntimeException('smtp_connection');
    echo "OK: conexión cifrada y autenticación SMTP aceptadas por Titan. No se envió ningún correo.\n";
    $mail->smtpClose();
} catch (Throwable $failure) {
    $error = $mail?->getSMTPInstance()->getError() ?? [];
    $code = preg_replace('/[^0-9.]/', '', (string) ($error['smtp_code'] ?? ''));
    $reason = $stage;
    if ($failure instanceof JsonException) $reason = 'configuration_json';
    elseif (in_array($failure->getMessage(), ['smtp_config_location', 'smtp_config_format', 'smtp_config_incomplete'], true)) $reason = $failure->getMessage();
    elseif (str_contains(strtolower($failure->getMessage()), 'authenticate')) $reason = 'authentication';
    elseif (!defined('OPENSSL_ALGO_SHA256')) $reason = 'openssl_missing';
    fwrite(STDERR, "No se pudo verificar SMTP. Etapa: $reason. Código SMTP: " . ($smtpCode ?: $code ?: 'no disponible') . ".\n");
    $mail?->smtpClose();
    exit(1);
}
