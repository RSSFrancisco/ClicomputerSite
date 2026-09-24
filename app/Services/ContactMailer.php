<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ContactRequest;
use Closure;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

/** SMTP autenticado de Titan. Nunca vuelve silenciosamente a mail() de PHP. */
final class ContactMailer
{
    public function __construct(private ?Closure $transport = null, private ?array $configuration = null) {}

    public function send(array $values, array $settings): bool
    {
        $recipient = $settings['email'];
        $sender = ($settings['mail_from'] ?? '') ?: $recipient;
        foreach ([$recipient, $sender, $values['email']] as $address) {
            if (preg_match('/[\r\n]/', $address) || !filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }
        $stage = 'configuration';
        $mail = null;
        try {
            $mail = $this->createClient($sender);
            // El remitente SMTP y From coinciden con el buzón autenticado.
            $mail->setFrom($mail->Username, 'Clicomputer');
            $mail->addAddress($recipient);
            $mail->addReplyTo($values['email']);
            $mail->Subject = 'Nueva solicitud de contacto - Clicomputer';
            $mail->Body = ContactRequest::message($values);
            $mail->MessageID = '<contact.' . bin2hex(random_bytes(16)) . '@' . substr(strrchr($mail->Username, '@'), 1) . '>';
            $stage = 'smtp';
            $accepted = $this->transport ? ($this->transport)($mail) : $mail->send();
            $this->logResult($accepted, $stage, $mail);
            return $accepted;
        } catch (Throwable) {
            $this->logResult(false, $stage, $mail);
            return false;
        }
    }

    public function createClient(string $defaultUsername): PHPMailer
    {
        $config = $this->configuration !== null ? SmtpConfiguration::validate($this->configuration) : SmtpConfiguration::load($defaultUsername);
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = $config['port'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPDebug = 0;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false]];
        $mail->Timeout = 15;
        $mail->getSMTPInstance()->Timelimit = 30;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;
        $mail->XMailer = '';
        return $mail;
    }

    private function logResult(bool $accepted, string $stage, ?PHPMailer $mail): void
    {
        $smtpError = $mail?->getSMTPInstance()->getError() ?? [];
        // Sin contraseña, contenido, Reply-To ni la respuesta SMTP completa (puede contener datos personales).
        error_log('Clicomputer: ' . json_encode([
            'event' => $accepted ? 'contact_mail_accepted' : 'contact_mail_failed', 'stage' => $stage,
            'reference' => $mail?->MessageID,
            'smtp_code' => preg_replace('/[^0-9]/', '', (string) ($smtpError['smtp_code'] ?? '')),
            'smtp_code_ex' => preg_replace('/[^0-9.]/', '', (string) ($smtpError['smtp_code_ex'] ?? '')),
        ], JSON_UNESCAPED_SLASHES));
    }
}
